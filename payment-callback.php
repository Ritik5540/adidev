<?php
// payment-callback.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/success-mail.php';
session_start();

$secretkey = CASHFREE_SECRET_KEY;

$orderId = $_POST["orderId"] ?? $_GET["orderId"] ?? '';
$orderAmount = $_POST["orderAmount"] ?? $_GET["orderAmount"] ?? '';
$referenceId = $_POST["referenceId"] ?? $_GET["referenceId"] ?? '';
$txStatus = $_POST["txStatus"] ?? $_GET["txStatus"] ?? '';
$paymentMode = $_POST["paymentMode"] ?? $_GET["paymentMode"] ?? '';
$txMsg = $_POST["txMsg"] ?? $_GET["txMsg"] ?? '';
$txTime = $_POST["txTime"] ?? $_GET["txTime"] ?? '';
$signature = $_POST["signature"] ?? $_GET["signature"] ?? '';

// Verify signature
$data = $orderId . $orderAmount . $referenceId . $txStatus . $paymentMode . $txMsg . $txTime;
$hash_hmac = hash_hmac('sha256', $data, $secretkey, true);
$computedSignature = base64_encode($hash_hmac);

// Update payment status in payment_transaction table
if ($signature == $computedSignature) {
    // Update payment_transaction
    $query = "UPDATE payment_transaction SET txns_id = '$referenceId', txns_date = '$txTime', status='$txStatus' WHERE order_id = '$orderId'";
    mysqli_query($GLOBALS['mysqli'], $query);
    
    // Update orders table
    if ($txStatus == 'SUCCESS') {
        $update_order = "UPDATE orders SET 
                        payment_status = 'paid',
                        status = 'payment_received',
                        amount_paid = '$orderAmount',
                        paid_at = NOW(),
                        transaction_id = '$referenceId',
                        payment_method = '$paymentMode',
                        payment_details = '" . mysqli_real_escape_string($GLOBALS['mysqli'], json_encode([
                            'txStatus' => $txStatus,
                            'paymentMode' => $paymentMode,
                            'txMsg' => $txMsg,
                            'txTime' => $txTime,
                            'referenceId' => $referenceId
                        ])) . "'
                        WHERE order_number = '$orderId'";
        mysqli_query($GLOBALS['mysqli'], $update_order);
        
        // Update invoice paid amount
        mysqli_query($GLOBALS['mysqli'], "UPDATE invoices SET paid_amount = '$orderAmount' WHERE order_id = (SELECT id FROM orders WHERE order_number = '$orderId')");

        // send mail to customer
        $order_query = mysqli_query($GLOBALS['mysqli'], "SELECT * FROM orders WHERE order_number = '$orderId'");
        if (mysqli_num_rows($order_query) > 0) {
            $order = mysqli_fetch_assoc($order_query);
            send_paid_email($order);
        }
        
        // Redirect to success page
        header("Location: thankyou.php?order_id=" . $orderId . "&payment=success");
    } else {
        // Update failed order
        $update_order = "UPDATE orders SET 
                        status = 'payment_failed',
                        payment_details = '" . mysqli_real_escape_string($GLOBALS['mysqli'], json_encode([
                            'txStatus' => $txStatus,
                            'paymentMode' => $paymentMode,
                            'txMsg' => $txMsg,
                            'txTime' => $txTime
                        ])) . "'
                        WHERE order_number = '$orderId'";
        mysqli_query($GLOBALS['mysqli'], $update_order);
        
        // Redirect to failure page
        header("Location: thankyou.php?order_id=" . $orderId . "&payment=failed");
    }
} else {
    // Signature mismatch - possible tampering
    header("Location: thankyou.php?order_id=" . $orderId . "&payment=error");
}
exit;
?>