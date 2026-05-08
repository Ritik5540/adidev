<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    .order-details-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }

    .order-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .info-group {
        margin-bottom: 15px;
    }

    .info-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #666;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .info-value {
        font-size: 16px;
        color: #333;
        font-weight: 500;
    }

    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-badge.payment_received {
        background-color: #cfe2ff;
        color: #084298;
    }

    .status-badge.processing {
        background-color: #cfe2ff;
        color: #084298;
    }

    .status-badge.shipped {
        background-color: #cfe2ff;
        color: #084298;
    }

    .status-badge.delivered {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge.cancelled {
        background-color: #f8d7da;
        color: #842029;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }

    .items-table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .items-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #555;
        font-size: 13px;
    }

    .items-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 6px;
        object-fit: cover;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .summary-row.total {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 16px;
        padding: 15px 0;
        color: #333;
    }

    .summary-label {
        color: #666;
    }

    .summary-value {
        font-weight: 500;
        color: #333;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
    }

    .btn.primary {
        background-color: #007bff;
        color: white;
    }

    .btn.primary:hover {
        background-color: #0056b3;
    }

    .btn.success {
        background-color: #28a745;
        color: white;
    }

    .btn.success:hover {
        background-color: #218838;
    }

    .btn.danger {
        background-color: #dc3545;
        color: white;
    }

    .btn.danger:hover {
        background-color: #c82333;
    }

    .btn.secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn.secondary:hover {
        background-color: #5a6268;
    }

    .timeline {
        margin: 20px 0;
    }

    .timeline-item {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .timeline-item:last-child {
        border-bottom: none;
    }

    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #007bff;
        font-size: 18px;
        flex-shrink: 0;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-label {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .timeline-time {
        font-size: 12px;
        color: #999;
    }

    .address-box {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .order-details-container {
            grid-template-columns: 1fr;
        }

        .order-header {
            grid-template-columns: 1fr;
        }

        .items-table {
            font-size: 12px;
        }

        .action-buttons {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid">
        <a href="orders.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Orders</a>

        <div class="page-header">
            <h2><i class="fas fa-receipt"></i> Order Details</h2>
        </div>

        <?php
        if (!isset($_GET['id'])) {
            echo '<div class="card"><p style="color: #dc3545;">Order not found</p></div>';
            include 'layout/footer.php';
            exit;
        }

        $order_id = (int)$_GET['id'];
        $query = "SELECT * FROM orders WHERE id = $order_id";
        $result = mysqli_query($conn, $query);
        $order = mysqli_fetch_assoc($result);

        if (!$order) {
            echo '<div class="card"><p style="color: #dc3545;">Order not found</p></div>';
            include 'layout/footer.php';
            exit;
        }

        // Fetch order items
        $items_query = "SELECT * FROM order_items WHERE order_id = $order_id";
        $items_result = mysqli_query($conn, $items_query);
        $order_items = array();
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $item;
        }
        ?>

        <div class="order-details-container">
            <!-- Left Column -->
            <div>
                <!-- Order Header -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-info-circle"></i> Order Information
                    </div>

                    <div class="order-header">
                        <div>
                            <div class="info-group">
                                <div class="info-label">Order Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Order Date</div>
                                <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></div>
                            </div>
                        </div>
                        <div>
                            <div class="info-group">
                                <div class="info-label">Order Status</div>
                                <div class="info-value"><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Order Type</div>
                                <div class="info-value"><?php echo ucfirst($order['order_type']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-user"></i> Customer Information
                    </div>

                    <div class="info-group">
                        <div class="info-label">Customer Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Email Address</div>
                        <div class="info-value"><a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>"><?php echo htmlspecialchars($order['customer_email']); ?></a></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><a href="tel:<?php echo htmlspecialchars($order['customer_phone']); ?>"><?php echo htmlspecialchars($order['customer_phone']); ?></a></div>
                    </div>
                    <?php if (!empty($order['customer_gst'])) { ?>
                        <div class="info-group">
                            <div class="info-label">GST Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['customer_gst']); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Shipping Address -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-map-marker-alt"></i> Shipping Address
                    </div>
                    <div class="address-box">
                        <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-map-marker-alt"></i> Billing Address
                    </div>
                    <div class="address-box">
                        <?php echo nl2br(htmlspecialchars($order['billing_address'])); ?>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-box"></i> Order Items
                    </div>

                    <?php if (!empty($order_items)) { ?>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($order_items as $item) {
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($item['product_code']); ?></small>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₹<?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td><strong>₹<?php echo number_format($item['total_price'], 2); ?></strong></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <p style="color: #999;">No items in this order</p>
                    <?php } ?>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-calculator"></i> Order Summary
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">₹<?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>

                    <?php if ($order['discount_amount'] > 0) { ?>
                        <div class="summary-row">
                            <span class="summary-label">Discount</span>
                            <span class="summary-value" style="color: #28a745;">-₹<?php echo number_format($order['discount_amount'], 2); ?></span>
                        </div>
                    <?php } ?>

                    <?php if ($order['tax_amount'] > 0) { ?>
                        <div class="summary-row">
                            <span class="summary-label">Tax</span>
                            <span class="summary-value">₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                        </div>
                    <?php } ?>

                    <?php if ($order['shipping_amount'] > 0) { ?>
                        <div class="summary-row">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value">₹<?php echo number_format($order['shipping_amount'], 2); ?></span>
                        </div>
                    <?php } ?>

                    <div class="summary-row total">
                        <span class="summary-label">Grand Total</span>
                        <span class="summary-value">₹<?php echo number_format($order['grand_total'], 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Amount Paid</span>
                        <span class="summary-value" style="color: #28a745;">₹<?php echo number_format($order['amount_paid'], 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span class="summary-label">Amount Due</span>
                        <span class="summary-value" style="color: <?php echo ($order['amount_due'] > 0) ? '#dc3545' : '#28a745'; ?>;">₹<?php echo number_format($order['amount_due'], 2); ?></span>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-credit-card"></i> Payment Information
                    </div>

                    <div class="info-group">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value"><?php echo ucfirst($order['payment_method']); ?></div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Payment Status</div>
                        <div class="info-value"><span class="status-badge <?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></div>
                    </div>

                    <?php if (!empty($order['transaction_id'])) { ?>
                        <div class="info-group">
                            <div class="info-label">Transaction ID</div>
                            <div class="info-value"><?php echo htmlspecialchars($order['transaction_id']); ?></div>
                        </div>
                    <?php } ?>

                    <?php if (!empty($order['paid_at'])) { ?>
                        <div class="info-group">
                            <div class="info-label">Paid At</div>
                            <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($order['paid_at'])); ?></div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-cog"></i> Actions
                    </div>

                    <div class="action-buttons">
                        <a href="order-edit.php?id=<?php echo $order['id']; ?>" class="btn primary">
                            <i class="fas fa-edit"></i> Edit Order
                        </a>
                        <a href="javascript:void(0)" class="btn secondary" onclick="printOrder()">
                            <i class="fas fa-print"></i> Print
                        </a>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-title">
                        <i class="fas fa-history"></i> Order Timeline
                    </div>

                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label">Order Created</div>
                                <div class="timeline-time"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($order['confirmed_at'])) { ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Order Confirmed</div>
                                    <div class="timeline-time"><?php echo date('d M Y, h:i A', strtotime($order['confirmed_at'])); ?></div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($order['shipped_at'])) { ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Order Shipped</div>
                                    <div class="timeline-time"><?php echo date('d M Y, h:i A', strtotime($order['shipped_at'])); ?></div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($order['delivered_at'])) { ?>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-label">Order Delivered</div>
                                    <div class="timeline-time"><?php echo date('d M Y, h:i A', strtotime($order['delivered_at'])); ?></div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function printOrder() {
        window.print();
    }
</script>

<?php include 'layout/footer.php'; ?>
</body>

</html>
