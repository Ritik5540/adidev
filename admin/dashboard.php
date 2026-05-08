<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    /* Dashboard Specific Styles */
    .dashboard-main {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #007bff;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-card.primary {
        border-left-color: #007bff;
    }

    .stat-card.success {
        border-left-color: #28a745;
    }

    .stat-card.warning {
        border-left-color: #ffc107;
    }

    .stat-card.danger {
        border-left-color: #dc3545;
    }

    .stat-card-icon {
        font-size: 28px;
        margin-bottom: 10px;
        opacity: 0.7;
    }

    .stat-card-title {
        font-size: 14px;
        color: #666;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card-value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }

    .stat-card-footer {
        font-size: 12px;
        color: #999;
        margin-top: 10px;
    }

    .charts-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .order-status-chart {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .status-label {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    }

    .status-badge {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .status-badge.pending {
        background-color: #ffc107;
    }

    .status-badge.processing {
        background-color: #0dcaf0;
    }

    .status-badge.shipped {
        background-color: #0d6efd;
    }

    .status-badge.delivered {
        background-color: #198754;
    }

    .status-badge.cancelled {
        background-color: #dc3545;
    }

    .status-count {
        font-weight: 600;
        color: #333;
        min-width: 50px;
        text-align: right;
    }

    .status-percentage {
        font-size: 12px;
        color: #999;
    }

    .recent-orders-section {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .view-all-btn {
        font-size: 14px;
        color: #007bff;
        text-decoration: none;
        transition: color 0.3s;
    }

    .view-all-btn:hover {
        color: #0056b3;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .orders-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #555;
        font-size: 14px;
    }

    .orders-table td {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .orders-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .order-id {
        font-weight: 600;
        color: #007bff;
        text-decoration: none;
    }

    .order-id:hover {
        text-decoration: underline;
    }

    .order-amount {
        font-weight: 600;
        color: #333;
    }

    .status-label-table {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-label-table.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-label-table.processing {
        background-color: #cfe2ff;
        color: #084298;
    }

    .status-label-table.shipped {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-label-table.delivered {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-label-table.cancelled {
        background-color: #f8d7da;
        color: #842029;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .action-btn.view {
        background-color: #007bff;
        color: white;
    }

    .action-btn.view:hover {
        background-color: #0056b3;
    }

    .action-btn.edit {
        background-color: #28a745;
        color: white;
    }

    .action-btn.edit:hover {
        background-color: #218838;
    }

    .revenue-chart {
        height: 300px;
        display: flex;
        align-items: flex-end;
        gap: 10px;
        padding: 20px 0;
    }

    .revenue-bar {
        flex: 1;
        background: linear-gradient(to top, #007bff, #0056b3);
        border-radius: 8px 8px 0 0;
        position: relative;
        min-height: 40px;
        transition: all 0.3s;
        cursor: pointer;
    }

    .revenue-bar:hover {
        opacity: 0.8;
    }

    .revenue-bar-label {
        position: absolute;
        bottom: -30px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 12px;
        color: #666;
    }

    .revenue-bar-value {
        position: absolute;
        top: -25px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .dashboard-main {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .charts-section {
            grid-template-columns: 1fr;
        }

        .orders-table {
            font-size: 14px;
        }

        .orders-table th,
        .orders-table td {
            padding: 10px;
        }
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid">
        <div class="page-header">
            <h2><i class="fas fa-chart-line"></i> Dashboard Overview</h2>
            <p>Welcome back! Here's what's happening with your orders today.</p>
        </div>

        <!-- Key Statistics Cards -->
        <div class="dashboard-main">
            <?php
            // Fetch dashboard statistics
            $stats = array(
                'total_orders' => 0,
                'pending_orders' => 0,
                'delivered_orders' => 0,
                'total_revenue' => 0
            );

            // Total Orders
            $query = "SELECT COUNT(*) as count FROM orders";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $stats['total_orders'] = $row['count'];

            // Pending Orders
            $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $stats['pending_orders'] = $row['count'];

            // Delivered Orders
            $query = "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $stats['delivered_orders'] = $row['count'];

            // Paid Orders
            $query = "SELECT COUNT(*) as count FROM orders WHERE payment_status = 'paid'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $stats['paid_orders'] = $row['count'];

            // Total Revenue
            $query = "SELECT SUM(grand_total) as total FROM orders WHERE status = 'delivered' OR payment_status = 'paid'";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $stats['total_revenue'] = $row['total'] ?? 0;
            ?>

            <!-- Total Orders Card -->
            <div class="stat-card primary">
                <div class="stat-card-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-card-title">Total Orders</div>
                <div class="stat-card-value"><?php echo $stats['total_orders']; ?></div>
                <div class="stat-card-footer">All time orders</div>
            </div>

            <!-- Pending Orders Card -->
            <div class="stat-card warning">
                <div class="stat-card-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-card-title">Pending Orders</div>
                <div class="stat-card-value"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-card-footer">Need attention</div>
            </div>

            <!-- Delivered Orders Card -->
            <div class="stat-card success">
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-title">Delivered</div>
                <div class="stat-card-value"><?php echo $stats['delivered_orders']; ?></div>
                <div class="stat-card-footer">Completed orders</div>
            </div>

            <!-- Paid Orders Card -->
            <div class="stat-card primary">
                <div class="stat-card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="stat-card-title">Paid Orders</div>
                <div class="stat-card-value"><?php echo $stats['paid_orders']; ?></div>
                <div class="stat-card-footer">Payment received</div>
            </div>

            <!-- Total Revenue Card -->
            <div class="stat-card success">
                <div class="stat-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-card-title">Total Revenue</div>
                <div class="stat-card-value">₹<?php echo number_format($stats['total_revenue'], 0); ?></div>
                <div class="stat-card-footer">Completed sales</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <!-- Revenue Chart -->
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-chart-bar"></i> Revenue Overview (Last 30 Days)
                </div>
                <div class="revenue-chart">
                    <?php
                    // Fetch revenue for last 30 days
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        $query = "SELECT COALESCE(SUM(grand_total), 0) as total FROM orders WHERE DATE(order_date) = '$date' AND (status = 'delivered' OR payment_status = 'paid')";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        $revenue = $row['total'] ?? 0;
                        $height = $revenue > 0 ? ($revenue / 10000) * 100 : 5; // Normalize height
                        $day = date('D', strtotime($date));
                        ?>
                        <div class="revenue-bar" style="height: <?php echo $height; ?>%;" title="₹<?php echo number_format($revenue, 2); ?>">
                            <div class="revenue-bar-value">₹<?php echo $revenue > 0 ? number_format($revenue / 1000, 1) . 'K' : '0'; ?></div>
                            <div class="revenue-bar-label"><?php echo $day; ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <!-- Order Status Distribution -->
            <div class="chart-card">
                <div class="chart-title">
                    <i class="fas fa-pie-chart"></i> Order Status
                </div>
                <div class="order-status-chart">
                    <?php
                    $statuses = array('pending', 'payment_received', 'processing', 'shipped', 'delivered', 'cancelled');
                    $status_labels = array(
                        'pending' => 'Pending',
                        'payment_received' => 'Payment Received',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled'
                    );
                    $status_colors = array(
                        'pending' => 'pending',
                        'payment_received' => 'processing',
                        'processing' => 'processing',
                        'shipped' => 'shipped',
                        'delivered' => 'delivered',
                        'cancelled' => 'cancelled'
                    );

                    foreach ($statuses as $status) {
                        $query = "SELECT COUNT(*) as count FROM orders WHERE status = '$status'";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        $count = $row['count'];
                        $percentage = $stats['total_orders'] > 0 ? round(($count / $stats['total_orders']) * 100) : 0;
                        ?>
                        <div class="status-item">
                            <div class="status-label">
                                <span class="status-badge <?php echo $status_colors[$status]; ?>"></span>
                                <span><?php echo $status_labels[$status]; ?></span>
                            </div>
                            <div class="status-count"><?php echo $count; ?></div>
                            <div class="status-percentage"><?php echo $percentage; ?>%</div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders-section">
            <div class="section-title">
                <span><i class="fas fa-list"></i> Recent Orders</span>
                <a href="./orders.php" class="view-all-btn">View All Orders →</a>
            </div>

            <?php
            $query = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 10";
            $result = mysqli_query($conn, $query);
            $orders = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $orders[] = $row;
            }

            if (!empty($orders)) {
                ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($orders as $order) {
                            $status_class = strtolower($order['status']);
                            $payment_status = $order['payment_status'] ?? 'pending';
                            ?>
                            <tr>
                                <td><a href="./order-details.php?id=<?php echo $order['id']; ?>" class="order-id"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td class="order-amount">₹<?php echo number_format($order['grand_total'], 2); ?></td>
                                <td><span class="status-label-table <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><span class="status-label-table <?php echo strtolower($payment_status); ?>"><?php echo ucfirst($payment_status); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="./order-details.php?id=<?php echo $order['id']; ?>" class="action-btn view">View</a>
                                        <a href="./order-edit.php?id=<?php echo $order['id']; ?>" class="action-btn edit">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
            } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Orders Yet</h3>
                    <p>Start receiving orders to see them here</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<?php include 'layout/footer.php'; ?>
</body>

</html>