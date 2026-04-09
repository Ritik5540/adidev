<?php
// thankyou.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('sign_in.php?redirect=thankyou.php');
}

// Get order number from URL
$order_number = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$paymentstatus = isset($_GET['payment']) ? trim($_GET['payment']) : '';

if (empty($order_number)) {
    redirect('orders.php');
}

$user_id = current_user_id() ?? 0;

// Fetch order details with order number
$order_query = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
                (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as total_quantity
                FROM orders o 
                WHERE o.order_number = '$order_number' AND o.user_id = $user_id 
                LIMIT 1";
$order_result = mysqli_query($GLOBALS['mysqli'], $order_query);

if (!$order_result || mysqli_num_rows($order_result) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT oi.*, p.name as product_name, p.product_code, p.main_image 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = {$order['id']} 
                ORDER BY oi.id ASC";
$items_result = mysqli_query($GLOBALS['mysqli'], $items_query);

// Fetch invoice details
$invoice_query = "SELECT * FROM invoices WHERE order_id = {$order['id']} LIMIT 1";
$invoice_result = mysqli_query($GLOBALS['mysqli'], $invoice_query);
$invoice = mysqli_fetch_assoc($invoice_result);

// Determine page title and message based on payment status
$is_payment_success = ($paymentstatus == 'success');
$is_payment_failed = ($paymentstatus == 'failed');
$is_payment_pending = (!$is_payment_success && !$is_payment_failed && $order['payment_status'] == 'pending');

if ($is_payment_success) {
    $page_title = "Payment Successful - Order Confirmed";
    $header_icon = "fa-check-circle";
    $header_color = "linear-gradient(135deg, #28a745 0%, #20c997 100%)";
    $header_title = "Payment Successful!";
    $header_message = "Your payment has been received and order is confirmed";
    $show_retry_button = false;
} elseif ($is_payment_failed) {
    $page_title = "Payment Failed - Order Not Confirmed";
    $header_icon = "fa-times-circle";
    $header_color = "linear-gradient(135deg, #dc3545 0%, #c82333 100%)";
    $header_title = "Payment Failed!";
    $header_message = "Your payment could not be processed. Please try again.";
    $show_retry_button = true;
} elseif ($order['payment_method'] == 'cod') {
    $page_title = "Order Placed - Cash on Delivery";
    $header_icon = "fa-truck";
    $header_color = "linear-gradient(135deg, #ffc107 0%, #ff9800 100%)";
    $header_title = "Order Placed Successfully!";
    $header_message = "Your order has been placed. You'll pay cash on delivery.";
    $show_retry_button = false;
} else {
    $page_title = "Order Received - Awaiting Payment";
    $header_icon = "fa-clock";
    $header_color = "linear-gradient(135deg, #17a2b8 0%, #138496 100%)";
    $header_title = "Order Received!";
    $header_message = "Your order has been received. Awaiting payment confirmation.";
    $show_retry_button = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | Adidev</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .thankyou-section {
            padding: 80px 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .thankyou-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .thankyou-header {
            background: <?php echo $header_color; ?>;
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .thankyou-header i {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleUp 0.5s ease;
        }
        
        .thankyou-header h2 {
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .thankyou-header p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 0;
        }
        
        @keyframes scaleUp {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .order-info {
            padding: 30px 40px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-info h4 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .order-items {
            padding: 30px 40px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-items h4 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .item-card {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: transform 0.2s;
        }
        
        .item-card:hover {
            transform: translateX(5px);
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .item-price {
            color: #28a745;
            font-weight: 600;
        }
        
        .item-quantity {
            color: #6c757d;
            font-size: 14px;
        }
        
        .payment-info {
            padding: 30px 40px;
        }
        
        .payment-info h4 {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .payment-badge.cod {
            background: #fff3cd;
            color: #856404;
        }
        
        .payment-badge.card, .payment-badge.online {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .payment-badge.upi {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending, .status-pending_payment {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing, .status-payment_received {
            background: #cfe2ff;
            color: #084298;
        }
        
        .status-completed, .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }
        
        .status-payment_failed, .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-box {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-danger-custom {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .alert-warning-custom {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert-success-custom {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .action-buttons {
            padding: 0 40px 40px 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom-primary {
            background: #28a745;
            color: white;
            border: none;
        }
        
        .btn-custom-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }
        
        .btn-custom-danger {
            background: #dc3545;
            color: white;
            border: none;
        }
        
        .btn-custom-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-custom-outline {
            background: transparent;
            color: #28a745;
            border: 2px solid #28a745;
        }
        
        .btn-custom-outline:hover {
            background: #28a745;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-custom-outline-danger {
            background: transparent;
            color: #dc3545;
            border: 2px solid #dc3545;
        }
        
        .btn-custom-outline-danger:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .thankyou-section {
                padding: 40px 0;
            }
            
            .thankyou-header {
                padding: 30px 20px;
            }
            
            .order-info, .order-items, .payment-info {
                padding: 20px;
            }
            
            .action-buttons {
                padding: 0 20px 30px 20px;
                flex-direction: column;
            }
            
            .item-card {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="thankyou-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="thankyou-card">
                        <div class="thankyou-header">
                            <i class="fas <?php echo $header_icon; ?>"></i>
                            <h2><?php echo $header_title; ?></h2>
                            <p><?php echo $header_message; ?></p>
                        </div>
                        
                        <?php if ($is_payment_failed): ?>
                        <div class="alert-box alert-danger-custom" style="margin: 20px 40px 0 40px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Payment Failed!</strong> Your payment could not be processed. Possible reasons:
                            <ul class="mt-2 mb-0">
                                <li>Insufficient funds in your account</li>
                                <li>Incorrect payment details entered</li>
                                <li>Bank server timeout or technical issue</li>
                                <li>Transaction declined by bank</li>
                            </ul>
                            <p class="mt-2 mb-0"><strong>Order #<?php echo htmlspecialchars($order['order_number']); ?></strong> has been created but not confirmed until payment is received.</p>
                        </div>
                        <?php elseif ($is_payment_pending && $order['payment_method'] == 'online'): ?>
                        <div class="alert-box alert-warning-custom" style="margin: 20px 40px 0 40px;">
                            <i class="fas fa-hourglass-half me-2"></i>
                            <strong>Payment Pending!</strong> Your order has been created but payment is yet to be confirmed.
                            <p class="mt-2 mb-0">Once payment is confirmed, you will receive an email notification.</p>
                        </div>
                        <?php elseif ($is_payment_success): ?>
                        <div class="alert-box alert-success-custom" style="margin: 20px 40px 0 40px;">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Payment Confirmed!</strong> Your payment has been successfully received.
                            <p class="mt-2 mb-0">You will receive an order confirmation email shortly.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="order-info">
                            <h4><i class="fas fa-shopping-bag me-2"></i>Order Details</h4>
                            <div class="info-row">
                                <span class="info-label">Order Number:</span>
                                <span class="info-value"><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Order Date:</span>
                                <span class="info-value"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Order Status:</span>
                                <span class="info-value">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Total Items:</span>
                                <span class="info-value"><?php echo $order['total_quantity']; ?> items</span>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            <h4><i class="fas fa-box me-2"></i>Items Ordered</h4>
                            <?php if (mysqli_num_rows($items_result) > 0): ?>
                                <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                    <div class="item-card">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= get_product_image($item, 'main') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="item-image">
                                        <?php else: ?>
                                            <div class="item-image bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image fa-2x text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                            <?php if (!empty($item['product_size'])): ?>
                                                <div class="item-size text-muted small"><?php echo htmlspecialchars($item['product_size']); ?></div>
                                            <?php endif; ?>
                                            <div class="item-price mt-2">₹<?php echo number_format($item['unit_price'], 2); ?></div>
                                        </div>
                                        <div class="item-quantity">
                                            Qty: <?php echo $item['quantity']; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">No items found</p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="payment-info">
                            <h4><i class="fas fa-credit-card me-2"></i>Payment & Billing</h4>
                            <div class="info-row">
                                <span class="info-label">Subtotal:</span>
                                <span class="info-value">₹<?php echo number_format($order['subtotal'], 2); ?></span>
                            </div>
                            <?php if ($order['shipping_amount'] > 0): ?>
                            <div class="info-row">
                                <span class="info-label">Shipping:</span>
                                <span class="info-value">₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <div class="info-row">
                                <span class="info-label">Discount:</span>
                                <span class="info-value text-danger">-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-row">
                                <span class="info-label"><strong>Grand Total:</strong></span>
                                <span class="info-value"><strong>₹<?php echo number_format($order['grand_total'], 2); ?></strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Payment Method:</span>
                                <span class="info-value">
                                    <span class="payment-badge <?php echo $order['payment_method']; ?>">
                                        <?php echo strtoupper(str_replace('_', ' ', $order['payment_method'])); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Payment Status:</span>
                                <span class="info-value">
                                    <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['payment_status'])); ?>
                                    </span>
                                </span>
                            </div>
                            <?php if ($invoice && $invoice['invoice_number']): ?>
                            <div class="info-row">
                                <span class="info-label">Invoice Number:</span>
                                <span class="info-value"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-buttons">
                            <?php if ($show_retry_button): ?>
                            <button onclick="retryPayment('<?php echo $order['order_number']; ?>', '<?php echo $order['grand_total']; ?>')" class="btn btn-custom btn-custom-danger">
                                <i class="fas fa-credit-card me-2"></i>Retry Payment
                            </button>
                            <?php endif; ?>
                            <a href="orders.php" class="btn btn-custom btn-custom-outline">
                                <i class="fas fa-list me-2"></i>View All Orders
                            </a>
                            <a href="shop.php" class="btn btn-custom <?php echo $show_retry_button ? 'btn-custom-outline' : 'btn-custom-primary'; ?>">
                                <i class="fas fa-shopping-cart me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function retryPayment(orderNumber, amount) {
            if (confirm('Do you want to retry payment for order #' + orderNumber + '? Amount: ₹' + amount)) {
                // Create a form to submit payment again
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'retry-payment.php';
                
                var orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = 'order_number';
                orderInput.value = orderNumber;
                form.appendChild(orderInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>