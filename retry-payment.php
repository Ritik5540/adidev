<?php
// retry-payment.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('sign_in.php?redirect=retry-payment.php');
}

$order_number = isset($_POST['order_number']) ? trim($_POST['order_number']) : '';

if (empty($order_number)) {
    redirect('orders.php');
}

$user_id = current_user_id() ?? 0;

// Fetch order details
$order_query = "SELECT o.*, 
                (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as total_quantity
                FROM orders o 
                WHERE o.order_number = '$order_number' AND o.user_id = $user_id 
                LIMIT 1";
$order_result = mysqli_query($GLOBALS['mysqli'], $order_query);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($order_result);

// Get product summary
$items_query = "SELECT product_name, quantity FROM order_items WHERE order_id = {$order['id']}";
$items_result = mysqli_query($GLOBALS['mysqli'], $items_query);
$product_summary = '';
while ($item = mysqli_fetch_assoc($items_result)) {
    $product_summary .= $item['product_name'] . ' x ' . $item['quantity'] . ', ';
}
$product_summary = rtrim($product_summary, ', ');

// Store in session for Cashfree
$_SESSION['cashfree_order'] = [
    'order_id' => $order['id'],
    'order_number' => $order['order_number'],
    'amount' => $order['grand_total'],
    'customer_name' => $order['customer_name'],
    'customer_email' => $order['customer_email'],
    'customer_phone' => $order['customer_phone'],
    'product_summary' => $product_summary,
    'address' => $order['shipping_address'],
    'city' => '',
    'pincode' => ''
];

// Extract city and pincode from address
if (!empty($order['shipping_address'])) {
    preg_match('/([^-]+)$/', $order['shipping_address'], $matches);
    if (isset($matches[1])) {
        $_SESSION['cashfree_order']['city'] = trim($matches[1]);
    }
}

session_write_close();

// Redirect to Cashfree payment
header('Location: cashfree-redirect.php');
exit;
?>