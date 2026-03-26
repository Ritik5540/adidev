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
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty'
    ]);
    exit;
}

// Get form data
$customer_name = trim($input['customer_name'] ?? '');
$customer_email = trim($input['customer_email'] ?? '');
$customer_phone = trim($input['customer_phone'] ?? '');
$company_name = trim($input['company_name'] ?? '');
$city = trim($input['city'] ?? '');
$state = trim($input['state'] ?? '');
$zip = trim($input['zip'] ?? '');
$address = trim($input['address'] ?? '');
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

$user_id = current_user_id();
$items = $input['items'];

// Calculate totals
$subtotal = 0;
$total_quantity = 0;
$total_items = count($items);

foreach ($items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $total_quantity += $item['quantity'];
}

// Prepare shipping address
$full_address = mysqli_real_escape_string($GLOBALS['mysqli'], $address . ", " . $city . ", " . $state . " - " . $zip);
if (!empty($company_name)) {
    $full_address = mysqli_real_escape_string($GLOBALS['mysqli'], $company_name) . ", " . $full_address;
}

// Initialize invoice data
$invoice_number = generate_invoice_number();
$invoice_date = date('Y-m-d H:i:s');
$due_date = date('Y-m-d H:i:s', strtotime('+15 days'));

// Calculate GST (assuming 18% GST)
$tax_total = $subtotal * 0.18;
$grand_total = $subtotal + $tax_total;
$paid_amount = 0;
$balance_due = $grand_total;

// GST details
$gst_details = mysqli_real_escape_string($GLOBALS['mysqli'], json_encode([
    'cgst' => $tax_total / 2,
    'sgst' => $tax_total / 2,
    'rate' => 18,
    'taxable_amount' => $subtotal
]));

// Place of supply
$place_of_supply = mysqli_real_escape_string($GLOBALS['mysqli'], $state);
$pdf_url = null;

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
        0.00, 0.00, 0.00,
        0.00, $tax_total, $grand_total,
        0.00, $grand_total, '$payment_method_escaped', 'pending',
        '$order_notes_escaped', NOW()
    )";
    
    if (!mysqli_query($GLOBALS['mysqli'], $order_query)) {
        throw new Exception("Failed to insert order: " . mysqli_error($GLOBALS['mysqli']));
    }
    
    $order_id = mysqli_insert_id($GLOBALS['mysqli']);

    // 2. Insert into order_items table
    foreach ($items as $item) {
        // Get product ID
        $product_id = getProductIdByName($item['name']);
        
        if (!$product_id && !empty($item['product_code'])) {
            $product_id = getProductIdByCode($item['product_code']);
        }
        
        if (!$product_id) {
            throw new Exception("Product not found: " . $item['name']);
        }
        
        $product_code = mysqli_real_escape_string($GLOBALS['mysqli'], $item['product_code'] ?? '');
        $product_name = mysqli_real_escape_string($GLOBALS['mysqli'], $item['name']);
        $product_size = mysqli_real_escape_string($GLOBALS['mysqli'], $item['color'] ?? '');
        $product_image = mysqli_real_escape_string($GLOBALS['mysqli'], $item['image'] ?? '');
        $quantity = (int)$item['quantity'];
        $unit_price = (float)$item['price'];
        $item_total = $unit_price * $quantity;
        
        $item_query = "INSERT INTO order_items (
            order_id, product_id, product_code, product_name,
            product_description, product_size, product_image,
            quantity, unit_price, total_price,
            is_bulk_item, status, created_at
        ) VALUES (
            $order_id, $product_id, '$product_code', '$product_name',
            '', '$product_size', '$product_image',
            $quantity, $unit_price, $item_total,
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
        '$invoice_date', '$due_date', $subtotal, 0.00,
        $tax_total, 0.00, $grand_total, $paid_amount,
        $balance_due, '$gst_details', '$place_of_supply', NULL,
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
    $cart_query = "SELECT id FROM carts WHERE user_id = $user_id AND status = 'active' LIMIT 1";
    $cart_result = mysqli_query($GLOBALS['mysqli'], $cart_query);
    
    if ($cart_result && mysqli_num_rows($cart_result) > 0) {
        $cart = mysqli_fetch_assoc($cart_result);
        $cart_id = $cart['id'];
        
        // Update cart status
        mysqli_query($GLOBALS['mysqli'], "UPDATE carts SET status = 'completed', updated_at = NOW() WHERE id = $cart_id");
        
        // Clear cart items
        mysqli_query($GLOBALS['mysqli'], "DELETE FROM cart_items WHERE cart_id = $cart_id");
    }

    // Commit transaction
    mysqli_commit($GLOBALS['mysqli']);

    // Send email notification
    send_order_confirmation_email($customer_email, $order_number, $order_id);

    // Return success response with order_number for redirect
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'order_number' => $order_number,  // This is the order number (like TSAJ98393)
        'invoice_number' => $invoice_number,
        'redirect_url' => 'thankyou.php?order_id=' . $order_number  // Redirect with order_number, not ID
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($GLOBALS['mysqli']);
    
    error_log("Order placement error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to place order: ' . $e->getMessage()
    ]);
}

// Helper functions
function getProductIdByName($product_name) {
    $product_name_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $product_name);
    $query = "SELECT id FROM products WHERE name = '$product_name_escaped' AND is_active = 1 LIMIT 1";
    $result = mysqli_query($GLOBALS['mysqli'], $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }
    return null;
}

function getProductIdByCode($product_code) {
    $product_code_escaped = mysqli_real_escape_string($GLOBALS['mysqli'], $product_code);
    $query = "SELECT id FROM products WHERE product_code = '$product_code_escaped' AND is_active = 1 LIMIT 1";
    $result = mysqli_query($GLOBALS['mysqli'], $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['id'];
    }
    return null;
}

function generate_order_number() {
    // Generate a unique order number like: ORD20260326A1B2C3
    $prefix = 'ORD';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

function generate_invoice_number() {
    // Generate a unique invoice number like: INV20260326X9Y8Z7
    $prefix = 'INV';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -6));
    return $prefix . $date . $random;
}

function send_order_confirmation_email($email, $order_number, $order_id) {
    // You can implement email sending here
    // Example: mail($email, "Order Confirmation - $order_number", "Your order has been placed successfully.");
    return true;
}
?>