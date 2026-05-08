<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    .orders-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .search-filter-section {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .search-box {
        flex: 1;
        min-width: 200px;
    }

    .search-box input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .filter-select {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        min-width: 150px;
    }

    .export-btn,
    .add-order-btn {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }

    .export-btn:hover,
    .add-order-btn:hover {
        background-color: #0056b3;
    }

    .orders-container {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
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
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge.delivered {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge.cancelled {
        background-color: #f8d7da;
        color: #842029;
    }

    .payment-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .payment-status.paid {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .payment-status.pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .payment-status.failed {
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

    .action-btn.delete {
        background-color: #dc3545;
        color: white;
    }

    .action-btn.delete:hover {
        background-color: #c82333;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 20px;
    }

    .pagination a,
    .pagination span {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        text-decoration: none;
        color: #007bff;
        transition: all 0.3s;
    }

    .pagination a:hover {
        background-color: #007bff;
        color: white;
    }

    .pagination .active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    @media (max-width: 768px) {
        .orders-header {
            flex-direction: column;
        }

        .search-filter-section {
            flex-direction: column;
        }

        .filter-select {
            width: 100%;
        }

        .orders-table {
            font-size: 12px;
        }

        .orders-table th,
        .orders-table td {
            padding: 10px;
        }

        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid">
        <div class="page-header">
            <h2><i class="fas fa-shopping-cart"></i> Orders Management</h2>
            <p>Manage all customer orders</p>
        </div>

        <div class="orders-container">
            <!-- Search and Filter Section -->
            <form method="GET" class="search-filter-section">
                <div class="search-box">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by Order ID, Customer Name, Email...">
                </div>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo (($_GET['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="payment_received" <?php echo (($_GET['status'] ?? '') === 'payment_received') ? 'selected' : ''; ?>>Payment Received</option>
                    <option value="processing" <?php echo (($_GET['status'] ?? '') === 'processing') ? 'selected' : ''; ?>>Processing</option>
                    <option value="confirmed" <?php echo (($_GET['status'] ?? '') === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="packed" <?php echo (($_GET['status'] ?? '') === 'packed') ? 'selected' : ''; ?>>Packed</option>
                    <option value="shipped" <?php echo (($_GET['status'] ?? '') === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                    <option value="out_for_delivery" <?php echo (($_GET['status'] ?? '') === 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                    <option value="delivered" <?php echo (($_GET['status'] ?? '') === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo (($_GET['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="refunded" <?php echo (($_GET['status'] ?? '') === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                    <option value="returned" <?php echo (($_GET['status'] ?? '') === 'returned') ? 'selected' : ''; ?>>Returned</option>
                    <option value="on_hold" <?php echo (($_GET['status'] ?? '') === 'on_hold') ? 'selected' : ''; ?>>On Hold</option>
                </select>
                <select name="payment_status" class="filter-select">
                    <option value="">All Payment Status</option>
                    <option value="paid" <?php echo (($_GET['payment_status'] ?? '') === 'paid') ? 'selected' : ''; ?>>Paid</option>
                    <option value="pending" <?php echo (($_GET['payment_status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="failed" <?php echo (($_GET['payment_status'] ?? '') === 'failed') ? 'selected' : ''; ?>>Failed</option>
                    <option value="refunded" <?php echo (($_GET['payment_status'] ?? '') === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                </select>
                <button type="submit" class="export-btn"><i class="fas fa-search"></i> Filter</button>
                <button type="button" class="export-btn" onclick="exportOrders()"><i class="fas fa-download"></i> Export</button>
            </form>

            <!-- Orders Table -->
            <?php
            $per_page = 20;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $per_page;

            // Build query with filters
            $where = "WHERE 1=1";
            
            // Search filter
            if (!empty($_GET['search'])) {
                $search = mysqli_real_escape_string($conn, $_GET['search']);
                $where .= " AND (order_number LIKE '%$search%' OR customer_name LIKE '%$search%' OR customer_email LIKE '%$search%')";
            }

            // Status filter
            if (!empty($_GET['status'])) {
                $status = mysqli_real_escape_string($conn, $_GET['status']);
                $where .= " AND status = '$status'";
            }

            // Payment status filter
            if (!empty($_GET['payment_status'])) {
                $payment_status = mysqli_real_escape_string($conn, $_GET['payment_status']);
                $where .= " AND payment_status = '$payment_status'";
            }

            // Count total orders
            $count_query = "SELECT COUNT(*) as total FROM orders $where";
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            $total_orders = $count_row['total'];
            $total_pages = ceil($total_orders / $per_page);

            // Preserve filters across pagination
            $query_params = [];
            if (!empty($_GET['search'])) {
                $query_params['search'] = $_GET['search'];
            }
            if (!empty($_GET['status'])) {
                $query_params['status'] = $_GET['status'];
            }
            if (!empty($_GET['payment_status'])) {
                $query_params['payment_status'] = $_GET['payment_status'];
            }

            // Fetch paginated orders
            $query = "SELECT * FROM orders $where ORDER BY order_date DESC LIMIT $offset, $per_page";
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
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Order Status</th>
                            <th>Payment Status</th>
                            <th>Order Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($orders as $order) {
                            ?>
                            <tr>
                                <td><a href="order-details.php?id=<?php echo $order['id']; ?>" class="order-id"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                </td>
                                <td><strong>₹<?php echo number_format($order['grand_total'], 2); ?></strong></td>
                                <td><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><span class="payment-status <?php echo strtolower($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                                <td><?php echo ucfirst($order['order_type']); ?></td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="action-btn view" title="View"><i class="fas fa-eye"></i></a>
                                        <a href="order-edit.php?id=<?php echo $order['id']; ?>" class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1) { ?>
                    <div class="pagination">
                        <?php
                        // Previous button
                        if ($page > 1) {
                            $previousParams = $query_params;
                            $previousParams['page'] = $page - 1;
                            echo '<a href="?' . http_build_query($previousParams) . '"><i class="fas fa-chevron-left"></i> Previous</a>';
                        }

                        // Page numbers
                        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++) {
                            if ($i == $page) {
                                echo '<span class="active">' . $i . '</span>';
                            } else {
                                $pageParams = $query_params;
                                $pageParams['page'] = $i;
                                echo '<a href="?' . http_build_query($pageParams) . '">' . $i . '</a>';
                            }
                        }

                        // Next button
                        if ($page < $total_pages) {
                            $nextParams = $query_params;
                            $nextParams['page'] = $page + 1;
                            echo '<a href="?' . http_build_query($nextParams) . '">Next <i class="fas fa-chevron-right"></i></a>';
                        }
                        ?>
                    </div>
                <?php } ?>
            <?php
            } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Orders Found</h3>
                    <p>No orders match your search criteria</p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<script>
    function exportOrders() {
        alert('Export functionality will be implemented soon!');
    }
</script>

<?php include 'layout/footer.php'; ?>
</body>

</html>
