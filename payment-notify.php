<?php
// payment-notify.php
require_once __DIR__ . '/config.php';

// Get webhook data
$webhookData = file_get_contents('php://input');
$data = json_decode($webhookData, true);

if ($data) {
    $orderId = $data['orderId'] ?? '';
    $orderAmount = $data['orderAmount'] ?? '';
    $referenceId = $data['referenceId'] ?? '';
    $txStatus = $data['txStatus'] ?? '';
    $paymentMode = $data['paymentMode'] ?? '';
    $txMsg = $data['txMsg'] ?? '';
    $txTime = $data['txTime'] ?? '';
    
    // Update payment status
    $query = "UPDATE payment_transaction SET txns_id = '$referenceId', txns_date = '$txTime', status='$txStatus' WHERE order_id = '$orderId'";
    mysqli_query($GLOBALS['mysqli'], $query);
    
    if ($txStatus == 'SUCCESS') {
        mysqli_query($GLOBALS['mysqli'], "UPDATE orders SET payment_status = 'paid', status = 'payment_received', amount_paid = '$orderAmount', paid_at = NOW(), transaction_id = '$referenceId' WHERE order_number = '$orderId'");
        mysqli_query($GLOBALS['mysqli'], "UPDATE invoices SET paid_amount = '$orderAmount' WHERE order_id = (SELECT id FROM orders WHERE order_number = '$orderId')");
    }
    
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "Invalid data";
}
?>