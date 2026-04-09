<?php
// ajax/place-order.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'redirect' => true,
        'redirect_url' => 'sign_in.php',
        'message' => 'Please login first'
    ]);
    exit;
}

// Get POST data
$input = $_POST;

// Get form data
$customer_name = trim($input['customer_name'] ?? '');
$customer_email = trim($input['customer_email'] ?? '');
$customer_phone = trim($input['customer_phone'] ?? '');
$company_name = trim($input['company_name'] ?? '');
$customer_country = trim($input['customer_country'] ?? 'India');
$customer_city = trim($input['customer_city'] ?? '');
$zip = trim($input['zip'] ?? '');
$address = trim($input['address'] ?? '');
$cart_id = (int)($input['cart_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);
$order_notes = trim($input['order_notes'] ?? '');
$payment_method = trim($input['payment_method'] ?? 'cod');

// Validate required fields
if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($address)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Validate email
if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]);
    exit;
}

$user_id = current_user_id() ?? 0;

// Get cart items
$cart_items = get_cart_items_by_cart_id($cart_id, $user_id);
if (empty($cart_items)) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty or invalid'
    ]);
    exit;
}

// Calculate totals from cart items
$subtotal = 0;
$total_quantity = 0;
$items_list = [];
$product_summary = '';

foreach ($cart_items as $item) {
    $subtotal += $item['total_price'];
    $total_quantity += $item['quantity'];
    $items_list[] = $item;
    $product_summary .= $item['product_name'] . ' x ' . $item['quantity'] . ', ';
}
$product_summary = rtrim($product_summary, ', ');

// Get cart data for additional calculations
$cart_data = get_cart_by_id($cart_id);
$discount_amount = $cart_data['discount_amount'] ?? 0;
$shipping_amount = $cart_data['shipping_amount'] ?? 0;
$tax_amount = 0;
$grand_total = $amount;

// Prepare shipping address
$full_address = mysqli_real_escape_string($GLOBALS['mysqli'], $address . ", " . $customer_city . " - " . $zip);
if (!empty($company_name)) {
    $full_address = mysqli_real_escape_string($GLOBALS['mysqli'], $company_name) . ", " . $full_address;
}

// Initialize invoice data
$invoice_number = generate_invoice_number();
$invoice_date = date('Y-m-d H:i:s');
$due_date = date('Y-m-d H:i:s', strtotime('+15 days'));

$gst_details = NULL;
$place_of_supply = mysqli_real_escape_string($GLOBALS['mysqli'], $customer_city);

// Start transaction
mysqli_begin_transaction($GLOBALS['mysqli']);

try {
    // Generate unique order number
    $order_number = generate_order_number();
    
    // 1. Insert into orders table
    $order_notes_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $order_notes);
    $customer_name_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $customer_name);
    $customer_email_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $customer_email);
    $customer_phone_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $customer_phone);
    $payment_method_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $payment_method);
    
    $total_items = count($items_list);

    $order_query = "INSERT INTO orders (
        order_number, user_id, status, order_type, is_bulk_order,
        customer_name, customer_email, customer_phone, customer_gst,
        shipping_address, billing_address,
        total_items, total_quantity, subtotal,
        discount_amount, bulk_discount_amount, coupon_discount_amount,
        shipping_amount, tax_amount, grand_total,
        amount_paid, amount_due, payment_method, payment_status,
        order_notes, created_at
    ) VALUES (
        '$order_number', $user_id, 'pending', 'regular', 0,
        '$customer_name_escaped', '$customer_email_escaped', '$customer_phone_escaped', NULL,
        '$full_address', '$full_address',
        $total_items, $total_quantity, $subtotal,
        $discount_amount, 0.00, 0.00,
        $shipping_amount, 0.00, $grand_total,
        0.00, $grand_total, '$payment_method_escaped', 'pending',
        '$order_notes_escaped', NOW()
    )";
    
    if (!mysqli_query($GLOBALS['mysqli'], $order_query)) {
        throw new Exception("Failed to insert order: " . mysqli_error($GLOBALS['mysqli']));
    }
    
    $order_id = mysqli_insert_id($GLOBALS['mysqli']);

    // 2. Insert into order_items table
    foreach ($items_list as $item) {
        $product_id = (int)$item['product_id'];
        $product_code = mysqli_real_escape_string($GLOBALS['mysqli'], $item['product_sku'] ?? '');
        $product_name = mysqli_real_escape_string($GLOBALS['mysqli'], $item['product_name']);
        $product_size = mysqli_real_escape_string($GLOBALS['mysqli'], $item['product_size'] ?? '');
        $product_image = mysqli_real_escape_string($GLOBALS['mysqli'], $item['product_image'] ?? '');
        $item_quantity = (int)$item['quantity'];
        $unit_price = (float)$item['unit_price'];
        $item_total = (float)$item['total_price'];
        
        $item_query = "INSERT INTO order_items (
            order_id, product_id, product_code, product_name,
            product_description, product_size, product_image,
            quantity, unit_price, total_price,
            is_bulk_item, status, created_at
        ) VALUES (
            $order_id, $product_id, '$product_code', '$product_name',
            '', '$product_size', '$product_image',
            $item_quantity, $unit_price, $item_total,
            0, 'pending', NOW()
        )";
        
        if (!mysqli_query($GLOBALS['mysqli'], $item_query)) {
            throw new Exception("Failed to insert order item: " . mysqli_error($GLOBALS['mysqli']));
        }
    }

    // 3. Insert into invoices table
    $invoice_query = "INSERT INTO invoices (
        order_id, user_id, invoice_number, invoice_type,
        invoice_date, due_date, subtotal, discount_total,
        tax_total, shipping_total, grand_total, paid_amount,
        balance_due, gst_details, place_of_supply, pdf_url,
        is_email_sent, email_sent_at, created_at, updated_at
    ) VALUES (
        $order_id, $user_id, '$invoice_number', 'sales_invoice',
        '$invoice_date', '$due_date', $subtotal, $discount_amount,
        0.00, $shipping_amount, $grand_total, 0.00,
        $grand_total, NULL, '$place_of_supply', NULL,
        0, NULL, NOW(), NOW()
    )";
    
    if (!mysqli_query($GLOBALS['mysqli'], $invoice_query)) {
        throw new Exception("Failed to insert invoice: " . mysqli_error($GLOBALS['mysqli']));
    }
    
    $invoice_id = mysqli_insert_id($GLOBALS['mysqli']);

    // 4. Update order with invoice number
    $update_query = "UPDATE orders SET invoice_number = '$invoice_number', invoice_generated_at = NOW() WHERE id = $order_id";
    if (!mysqli_query($GLOBALS['mysqli'], $update_query)) {
        throw new Exception("Failed to update order: " . mysqli_error($GLOBALS['mysqli']));
    }

    // 5. Clear user's cart
    $cart_update_query = "UPDATE carts SET status = 'completed', updated_at = NOW() WHERE id = $cart_id";
    if (!mysqli_query($GLOBALS['mysqli'], $cart_update_query)) {
        throw new Exception("Failed to update cart: " . mysqli_error($GLOBALS['mysqli']));
    }
    
    // Clear cart items
    mysqli_query($GLOBALS['mysqli'], "DELETE FROM cart_items WHERE cart_id = $cart_id");

    // Commit transaction
    mysqli_commit($GLOBALS['mysqli']);

    // Send email notification
    send_order_confirmation_email($customer_email, $order_number, $order_id);

    // Handle payment method
    if ($payment_method === 'online') {
        // Store order data in session for Cashfree form (session is already started in config.php)
        $_SESSION['cashfree_order'] = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'amount' => $grand_total,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'product_summary' => $product_summary,
            'address' => $address,
            'city' => $customer_city,
            'pincode' => $zip
        ];
        
        // IMPORTANT: Commit session data before sending JSON response
        session_write_close();
        
        // Return response with redirect to cashfree form
        echo json_encode([
            'success' => true,
            'payment_required' => true,
            'payment_method' => 'online',
            'redirect_to_cashfree' => true,
            'cashfree_form_url' => 'cashfree-redirect.php',
            'order_id' => $order_id,
            'order_number' => $order_number,
            'message' => 'Redirecting to payment gateway...'
        ]);
    } else {
        // COD order
        echo json_encode([
            'success' => true,
            'payment_required' => false,
            'payment_method' => 'cod',
            'order_id' => $order_id,
            'order_number' => $order_number,
            'invoice_number' => $invoice_number,
            'message' => 'Order placed successfully',
            'redirect_url' => 'thankyou.php?order_id=' . $order_number
        ]);
    }
    
} catch (Exception $e) {
    mysqli_rollback($GLOBALS['mysqli']);
    
    error_log("Order placement error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to place order: ' . $e->getMessage()
    ]);
}

// Helper functions
function get_cart_by_id($cart_id) {
    $query = "SELECT * FROM carts WHERE id = $cart_id LIMIT 1";
    $result = mysqli_query($GLOBALS['mysqli'], $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

function get_cart_items_by_cart_id($cart_id, $user_id) {
    $query = "SELECT ci.*, p.product_code as product_sku 
              FROM cart_items ci 
              LEFT JOIN products p ON ci.product_id = p.id 
              WHERE ci.cart_id = $cart_id";
    $result = mysqli_query($GLOBALS['mysqli'], $query);
    
    $items = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
    return $items;
}

function generate_order_number() {
    $prefix = 'ORD';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

function generate_invoice_number() {
    $prefix = 'INV';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

function send_order_confirmation_email($email, $order_number, $order_id) {
    return true;
}
?>