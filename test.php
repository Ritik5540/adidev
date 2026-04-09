<?php

// ================= CONFIG ================= //
$appId = "YOUR_APP_ID";          // Cashfree App ID
$secretKey = "YOUR_SECRET_KEY";  // Cashfree Secret Key
$mode = "TEST";                 // TEST / PROD

// ================= DUMMY DATA ================= //
$orderId = "TEST" . time();

$orderAmount = "10";
$orderCurrency = "INR";

$customerName = "Test User";
$customerEmail = "test@test.com";
$customerPhone = "9999999999";

$returnUrl = "https://www.google.com"; // redirect after payment
$notifyUrl = "https://www.google.com"; // webhook (not needed now)

// ================= SIGNATURE ================= //
$postData = [
    "appId" => $appId,
    "orderId" => $orderId,
    "orderAmount" => $orderAmount,
    "orderCurrency" => $orderCurrency,
    "customerName" => $customerName,
    "customerPhone" => $customerPhone,
    "customerEmail" => $customerEmail,
    "returnUrl" => $returnUrl,
    "notifyUrl" => $notifyUrl
];

ksort($postData);

$signatureData = "";
foreach ($postData as $key => $value) {
    $signatureData .= $key . $value;
}

$signature = base64_encode(hash_hmac('sha256', $signatureData, $secretKey, true));

// ================= URL ================= //
$url = ($mode == "PROD") 
    ? "https://www.cashfree.com/checkout/post/submit"
    : "https://test.cashfree.com/billpay/checkout/post/submit";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Cashfree Test</title>
</head>

<body onload="document.forms['cfForm'].submit()">

<form method="post" action="<?= $url ?>" name="cfForm">

    <input type="hidden" name="appId" value="<?= $appId ?>">
    <input type="hidden" name="orderId" value="<?= $orderId ?>">
    <input type="hidden" name="orderAmount" value="<?= $orderAmount ?>">
    <input type="hidden" name="orderCurrency" value="<?= $orderCurrency ?>">
    <input type="hidden" name="customerName" value="<?= $customerName ?>">
    <input type="hidden" name="customerEmail" value="<?= $customerEmail ?>">
    <input type="hidden" name="customerPhone" value="<?= $customerPhone ?>">
    <input type="hidden" name="returnUrl" value="<?= $returnUrl ?>">
    <input type="hidden" name="notifyUrl" value="<?= $notifyUrl ?>">
    <input type="hidden" name="signature" value="<?= $signature ?>">

    <h2>Redirecting to Cashfree...</h2>

</form>

</body>
</html>