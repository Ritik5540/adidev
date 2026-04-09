<?php
// cashfree-redirect.php
require_once __DIR__ . '/config.php'; // Session already starts here
require_once __DIR__ . '/catalog_functions.php';

// Check if cashfree order data exists in session
if (!isset($_SESSION['cashfree_order'])) {
    header('Location: index.php');
    exit;
}

$user_id = current_user_id() ?? 0;
$currency = get_user_currency($user_id);

$order_data = $_SESSION['cashfree_order'];

// Cashfree Configuration (already defined in config.php)
$appId = CASHFREE_APP_ID;
$secretKey = CASHFREE_SECRET_KEY;
$mode = CASHFREE_MODE; // 'TEST' or 'PROD'

$orderId = $order_data['order_number'];
$orderAmount = $order_data['amount'];
$orderCurrency = $currency ?: 'INR'; // Default to INR if currency is not set
$customerName = $order_data['customer_name'];
$customerEmail = $order_data['customer_email'];
$customerPhone = $order_data['customer_phone'];
$returnUrl = SITE_URL . 'payment-callback.php';
$notifyUrl = SITE_URL . 'payment-notify.php';

// Prepare signature data
$postData = array(
    "appId" => $appId,
    "orderId" => $orderId,
    "orderAmount" => $orderAmount,
    "orderCurrency" => $orderCurrency,
    "customerName" => $customerName,
    "customerPhone" => $customerPhone,
    "customerEmail" => $customerEmail,
    "returnUrl" => $returnUrl,
    "notifyUrl" => $notifyUrl,
);

ksort($postData);
$signatureData = "";
foreach ($postData as $key => $value) {
    $signatureData .= $key . $value;
}

$signature = hash_hmac('sha256', $signatureData, $secretKey, true);
$signature = base64_encode($signature);

// Cashfree URL based on mode
if ($mode == "PROD") {
    $url = "https://www.cashfree.com/checkout/post/submit";
} else {
    $url = "https://test.cashfree.com/billpay/checkout/post/submit";
}

// Insert into payment_transaction table
$customerName = mysqli_real_escape_string($GLOBALS['mysqli'], $customerName);
$customerEmail = mysqli_real_escape_string($GLOBALS['mysqli'], $customerEmail);
$customerPhone = mysqli_real_escape_string($GLOBALS['mysqli'], $customerPhone);
$product_summary = mysqli_real_escape_string($GLOBALS['mysqli'], $order_data['product_summary']);
$address = mysqli_real_escape_string($GLOBALS['mysqli'], $order_data['address']);
$city = mysqli_real_escape_string($GLOBALS['mysqli'], $order_data['city']);
$pincode = mysqli_real_escape_string($GLOBALS['mysqli'], $order_data['pincode']);
$amount = $orderAmount;
$currency = $orderCurrency;
$payment_status = 'Pending';

// Create payment_transaction table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS `payment_transaction` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` varchar(100) NOT NULL,
    `product_summary` text,
    `full_name` varchar(255) NOT NULL,
    `mobile_number` varchar(20) NOT NULL,
    `email` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    `currency` varchar(10) DEFAULT 'INR',
    `status` varchar(50) DEFAULT 'Pending',
    `txns_id` varchar(255) DEFAULT NULL,
    `txns_date` datetime DEFAULT NULL,
    `address` text,
    `pincode` varchar(10) DEFAULT NULL,
    `city` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

mysqli_query($GLOBALS['mysqli'], $create_table);

// Insert transaction data
$query = "INSERT INTO payment_transaction (order_id, product_summary, full_name, mobile_number, email, amount, currency, status, address, pincode, city) 
          VALUES ('$orderId', '$product_summary', '$customerName', '$customerPhone', '$customerEmail', '$amount', '$currency', '$payment_status', '$address', '$pincode', '$city')";

mysqli_query($GLOBALS['mysqli'], $query);

// Clear session data after retrieving
unset($_SESSION['cashfree_order']);
session_write_close(); // Close session to prevent locking issues
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Payment Gateway...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .loader-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .sub-message {
            color: #666;
            font-size: 14px;
        }
        .button-manual {
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .button-manual:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <div class="message">Redirecting to secure payment gateway...</div>
        <div class="sub-message">Please wait while we redirect you to Cashfree</div>
        <button class="button-manual" onclick="document.getElementById('cashfreeForm').submit();">
            Click here if not redirected automatically
        </button>
    </div>

    <form action="<?php echo $url; ?>" method="post" id="cashfreeForm" name="cashfreeForm">
        <input type="hidden" name="signature" value="<?php echo $signature; ?>"/>
        <input type="hidden" name="orderCurrency" value="<?php echo $orderCurrency; ?>"/>
        <input type="hidden" name="customerName" value="<?php echo htmlspecialchars($customerName); ?>"/>
        <input type="hidden" name="customerEmail" value="<?php echo htmlspecialchars($customerEmail); ?>"/>
        <input type="hidden" name="customerPhone" value="<?php echo htmlspecialchars($customerPhone); ?>"/>
        <input type="hidden" name="orderAmount" value="<?php echo $orderAmount; ?>"/>
        <input type="hidden" name="notifyUrl" value="<?php echo $notifyUrl; ?>"/>
        <input type="hidden" name="returnUrl" value="<?php echo $returnUrl; ?>"/>
        <input type="hidden" name="appId" value="<?php echo $appId; ?>"/>
        <input type="hidden" name="orderId" value="<?php echo $orderId; ?>"/>
    </form>

    <script>
        // Auto-submit the form after a short delay
        setTimeout(function() {
            document.getElementById('cashfreeForm').submit();
        }, 1000);
    </script>
</body>
</html>