<?php
// payment-callback.php
define('ADIDEV_SKIP_SESSION', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/success-mail.php';

$secretkey = CASHFREE_SECRET_KEY;

$orderId = $_POST["orderId"] ?? $_GET["orderId"] ?? '';
$orderAmount = $_POST["orderAmount"] ?? $_GET["orderAmount"] ?? '';
$referenceId = $_POST["referenceId"] ?? $_GET["referenceId"] ?? '';
$txStatus = $_POST["txStatus"] ?? $_GET["txStatus"] ?? '';
$paymentMode = $_POST["paymentMode"] ?? $_GET["paymentMode"] ?? '';
$txMsg = $_POST["txMsg"] ?? $_GET["txMsg"] ?? '';
$txTime = $_POST["txTime"] ?? $_GET["txTime"] ?? '';
$signature = $_POST["signature"] ?? $_GET["signature"] ?? '';
$paymentModeForOrder = 'card';
$paymentModeLower = strtolower((string) $paymentMode);
if (strpos($paymentModeLower, 'upi') !== false) {
    $paymentModeForOrder = 'upi';
} elseif (strpos($paymentModeLower, 'wallet') !== false) {
    $paymentModeForOrder = 'wallet';
} elseif (strpos($paymentModeLower, 'bank') !== false) {
    $paymentModeForOrder = 'bank_transfer';
} elseif (strpos($paymentModeLower, 'paytm') !== false) {
    $paymentModeForOrder = 'paytm';
} elseif (strpos($paymentModeLower, 'razorpay') !== false) {
    $paymentModeForOrder = 'razorpay';
}

// Verify signature
$data = $orderId . $orderAmount . $referenceId . $txStatus . $paymentMode . $txMsg . $txTime;
$hash_hmac = hash_hmac('sha256', $data, $secretkey, true);
$computedSignature = base64_encode($hash_hmac);

$redirectOrderId = urlencode($orderId);
$paymentResult = 'error';

if ($orderId !== '' && hash_equals((string) $computedSignature, (string) $signature)) {
    $paymentDetails = json_encode([
        'txStatus' => $txStatus,
        'paymentMode' => $paymentMode,
        'txMsg' => $txMsg,
        'txTime' => $txTime,
        'referenceId' => $referenceId
    ]);

    db_execute(
        "UPDATE payment_transaction SET txns_id = ?, txns_date = ?, status = ? WHERE order_id = ?",
        'ssss',
        [$referenceId, $txTime, $txStatus, $orderId]
    )->close();
    
    if ($txStatus == 'SUCCESS') {
        db_execute(
            "UPDATE orders SET
                payment_status = 'paid',
                status = 'payment_received',
                amount_paid = ?,
                paid_at = NOW(),
                transaction_id = ?,
                payment_method = ?,
                payment_details = ?
             WHERE order_number = ?",
            'dssss',
            [(float) $orderAmount, $referenceId, $paymentModeForOrder, $paymentDetails, $orderId]
        )->close();
        
        db_execute(
            "UPDATE invoices SET paid_amount = ? WHERE order_id = (SELECT id FROM orders WHERE order_number = ? LIMIT 1)",
            'ds',
            [(float) $orderAmount, $orderId]
        )->close();

        $stmt = db_execute("SELECT * FROM orders WHERE order_number = ? LIMIT 1", 's', [$orderId]);
        $order_query = $stmt->get_result();
        if ($order_query && $order_query->num_rows > 0) {
            $order = $order_query->fetch_assoc();
            send_paid_email($order);
        }
        $stmt->close();

        $paymentResult = 'success';
    } else {
        db_execute(
            "UPDATE orders SET
                status = 'pending',
                payment_status = 'failed',
                payment_details = ?
             WHERE order_number = ?",
            'ss',
            [$paymentDetails, $orderId]
        )->close();

        $paymentResult = 'failed';
    }
}

header("Location: thankyou.php?order_id={$redirectOrderId}&payment={$paymentResult}");
exit;
?>
