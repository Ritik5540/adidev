<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    .edit-container {
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        max-width: 800px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .form-control.disabled {
        background-color: #f8f9fa;
        color: #666;
        cursor: not-allowed;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-control-group {
        display: flex;
        gap: 10px;
    }

    .help-text {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin: 30px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        justify-content: flex-end;
    }

    .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn.primary {
        background-color: #007bff;
        color: white;
    }

    .btn.primary:hover {
        background-color: #0056b3;
    }

    .btn.secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn.secondary:hover {
        background-color: #5a6268;
    }

    .btn.danger {
        background-color: #dc3545;
        color: white;
    }

    .btn.danger:hover {
        background-color: #c82333;
    }

    .status-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }

    .status-option {
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s;
    }

    .status-option:hover {
        border-color: #007bff;
        background-color: #f0f7ff;
    }

    .status-option input[type="radio"] {
        display: none;
    }

    .status-option input[type="radio"]:checked+.status-label {
        color: #007bff;
        font-weight: 600;
    }

    .status-option.checked {
        border-color: #007bff;
        background-color: #f0f7ff;
    }

    .status-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .status-icon {
        font-size: 24px;
    }

    .alert {
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .alert.success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
    }

    .alert.error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
    }

    .alert.info {
        background-color: #cfe2ff;
        color: #084298;
        border: 1px solid #b6d4fe;
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
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-buttons {
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
        <a href="order-details.php?id=<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Order</a>

        <div class="page-header">
            <h2><i class="fas fa-edit"></i> Edit Order</h2>
        </div>

        <?php
        if (!isset($_GET['id'])) {
            echo '<div class="alert error">Order not found</div>';
            include 'layout/footer.php';
            exit;
        }

        $order_id = (int)$_GET['id'];
        $query = "SELECT * FROM orders WHERE id = $order_id";
        $result = mysqli_query($conn, $query);
        $order = mysqli_fetch_assoc($result);

        if (!$order) {
            echo '<div class="alert error">Order not found</div>';
            include 'layout/footer.php';
            exit;
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = mysqli_real_escape_string($conn, $_POST['status']);
            $payment_status = mysqli_real_escape_string($conn, $_POST['payment_status']);
            $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
            $customer_email = mysqli_real_escape_string($conn, $_POST['customer_email']);
            $customer_phone = mysqli_real_escape_string($conn, $_POST['customer_phone']);
            $order_notes = mysqli_real_escape_string($conn, $_POST['order_notes']);

            // Set timestamp fields based on status change
            $timestamp_updates = "";
            if ($status == 'confirmed' && $order['status'] != 'confirmed') {
                $timestamp_updates .= ", confirmed_at = NOW()";
            }
            if ($status == 'shipped' && $order['status'] != 'shipped') {
                $timestamp_updates .= ", shipped_at = NOW()";
            }
            if ($status == 'delivered' && $order['status'] != 'delivered') {
                $timestamp_updates .= ", delivered_at = NOW()";
            }

            $update_query = "UPDATE orders SET 
                            status = '$status',
                            payment_status = '$payment_status',
                            customer_name = '$customer_name',
                            customer_email = '$customer_email',
                            customer_phone = '$customer_phone',
                            order_notes = '$order_notes'
                            $timestamp_updates
                            WHERE id = $order_id";

            if (mysqli_query($conn, $update_query)) {
                echo '<div class="alert success"><i class="fas fa-check-circle"></i> Order updated successfully!</div>';
                // Refresh order data
                $result = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id");
                $order = mysqli_fetch_assoc($result);
            } else {
                echo '<div class="alert error"><i class="fas fa-times-circle"></i> Error updating order: ' . mysqli_error($conn) . '</div>';
            }
        }
        ?>

        <div class="edit-container">
            <form method="POST">
                <!-- Order Basic Information -->
                <div class="section-title">
                    <i class="fas fa-receipt"></i> Order Information
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Order Number</label>
                        <input type="text" class="form-control disabled" value="<?php echo htmlspecialchars($order['order_number']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Order Date</label>
                        <input type="text" class="form-control disabled" value="<?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?>" disabled>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="section-title">
                    <i class="fas fa-user"></i> Customer Information
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="customer_name" class="form-control" value="<?php echo htmlspecialchars($order['customer_name']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="customer_email" class="form-control" value="<?php echo htmlspecialchars($order['customer_email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="customer_phone" class="form-control" value="<?php echo htmlspecialchars($order['customer_phone']); ?>" required>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="section-title">
                    <i class="fas fa-tasks"></i> Order Status
                </div>

                <div class="form-group">
                    <label class="form-label">Order Status</label>
                    <select name="status" class="form-control" required>
                        <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="payment_received" <?php echo ($order['status'] == 'payment_received') ? 'selected' : ''; ?>>Payment Received</option>
                        <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="confirmed" <?php echo ($order['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="packed" <?php echo ($order['status'] == 'packed') ? 'selected' : ''; ?>>Packed</option>
                        <option value="shipped" <?php echo ($order['status'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="out_for_delivery" <?php echo ($order['status'] == 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                        <option value="delivered" <?php echo ($order['status'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <!-- Payment Status -->
                <div class="section-title">
                    <i class="fas fa-credit-card"></i> Payment Status
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control" required>
                        <option value="pending" <?php echo ($order['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="paid" <?php echo ($order['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                        <option value="failed" <?php echo ($order['payment_status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo ($order['payment_status'] == 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>

                <!-- Order Notes -->
                <div class="section-title">
                    <i class="fas fa-sticky-note"></i> Notes
                </div>

                <div class="form-group">
                    <label class="form-label">Order Notes</label>
                    <textarea name="order_notes" class="form-control" rows="4" placeholder="Add any notes about this order..."><?php echo htmlspecialchars($order['order_notes'] ?? ''); ?></textarea>
                    <div class="help-text">Internal notes for your reference</div>
                </div>

                <!-- Order Summary -->
                <div class="section-title">
                    <i class="fas fa-calculator"></i> Order Summary
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['subtotal'], 2); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tax Amount</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['tax_amount'], 2); ?>" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Shipping Amount</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['shipping_amount'], 2); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grand Total</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['grand_total'], 2); ?>" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Amount Paid</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['amount_paid'], 2); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Due</label>
                        <input type="text" class="form-control disabled" value="₹<?php echo number_format($order['amount_due'], 2); ?>" disabled>
                    </div>
                </div>

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn secondary">Cancel</a>
                    <button type="submit" class="btn primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'layout/footer.php'; ?>
</body>

</html>
