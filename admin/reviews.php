<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<style>
    .review-table {
        width: 100%;
        border-collapse: collapse;
    }

    .review-table th,
    .review-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #dee2e6;
        text-align: left;
        font-size: 14px;
        color: #333;
    }

    .review-table thead {
        background-color: #f8f9fa;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    .status-badge.approved {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .status-badge.rejected {
        background-color: #f8d7da;
        color: #842029;
    }

    .payment-badge.paid {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .payment-badge.unpaid {
        background-color: #f8d7da;
        color: #842029;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        color: #fff;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .action-btn.approve {
        background-color: #198754;
    }

    .action-btn.reject {
        background-color: #dc3545;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
        align-items: center;
    }

    .filter-row select,
    .filter-row input {
        padding: 10px 14px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        min-width: 180px;
    }

    .filter-row button {
        padding: 10px 18px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }
</style>

<main class="main-content">
    <div class="container-fluid">
        <div class="page-header">
            <h2><i class="fas fa-star"></i> Review Management</h2>
            <p>Approve, reject, and manage customer product reviews.</p>
        </div>

        <?php
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'], $_POST['action'])) {
            $review_id = (int) $_POST['review_id'];
            $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
            $adminId = (int) ($_SESSION['user_id'] ?? 0);
            $update = "UPDATE product_reviews SET status = '$action', admin_id = $adminId, admin_responded_at = NOW() WHERE id = $review_id";
            if (mysqli_query($conn, $update)) {
                $message = '<div class="alert success"><i class="fas fa-check-circle"></i> Review updated successfully.</div>';
            } else {
                $message = '<div class="alert error"><i class="fas fa-times-circle"></i> Unable to update review: ' . mysqli_real_escape_string($conn, mysqli_error($conn)) . '</div>';
            }
        }

        $status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
        $search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';

        $where = 'WHERE 1=1';
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $where .= " AND r.status = '$status'";
        }
        if (!empty($search)) {
            $where .= " AND (p.name LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR r.title LIKE '%$search%' OR r.review LIKE '%$search%')";
        }

        $query = "SELECT r.*, p.name AS product_name, u.first_name AS reviewer_first_name, u.last_name AS reviewer_last_name, o.order_number, o.payment_status
            FROM product_reviews r
            LEFT JOIN products p ON p.id = r.product_id
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN orders o ON o.id = r.order_id
            $where
            ORDER BY r.created_at DESC
            LIMIT 250";

        $result = mysqli_query($conn, $query);
        $reviews = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $reviews[] = $row;
        }
        ?>

        <?php echo $message; ?>

        <div class="review-panel">
            <div class="filter-row">
                <form method="GET" style="display:flex; flex-wrap:wrap; gap:12px; width:100%; align-items:center;">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by product, reviewer, or comment...">
                    <button type="submit">Filter</button>
                </form>
            </div>

            <?php if (!empty($reviews)) { ?>
                <div class="orders-container">
                    <table class="review-table">
                        <thead>
                            <tr>
                                <th>Review ID</th>
                                <th>Product</th>
                                <th>Reviewer</th>
                                <th>Order</th>
                                <th>Payment</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review) {
                                $reviewerName = trim($review['reviewer_first_name'] . ' ' . $review['reviewer_last_name']);
                                $reviewerName = $reviewerName ?: 'Guest';
                                ?>
                                <tr>
                                    <td>#<?php echo $review['id']; ?></td>
                                    <td><?php echo htmlspecialchars($review['product_name'] ?: 'Unknown Product'); ?></td>
                                    <td><?php echo htmlspecialchars($reviewerName); ?></td>
                                    <td><?php echo htmlspecialchars($review['order_number'] ?: '-'); ?></td>
                                    <td>
                                        <?php if (($review['payment_status'] ?? '') === 'paid') { ?>
                                            <span class="status-badge payment-badge paid">Paid</span>
                                        <?php } else { ?>
                                            <span class="status-badge payment-badge unpaid">Unpaid</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo intval($review['rating']); ?> / 5</td>
                                    <td><span class="status-badge <?php echo htmlspecialchars($review['status']); ?>"><?php echo htmlspecialchars($review['status']); ?></span></td>
                                    <td><?php echo date('d M Y', strtotime($review['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($review['status'] !== 'approved') { ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="action-btn approve">Approve</button>
                                                </form>
                                            <?php } ?>
                                            <?php if ($review['status'] !== 'rejected') { ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="action-btn reject">Reject</button>
                                                </form>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" style="background:#fafafa; font-size:13px; color:#555;">
                                        <strong><?php echo htmlspecialchars($review['title'] ?: 'No title'); ?></strong><br>
                                        <?php echo nl2br(htmlspecialchars($review['review'] ?: 'No review text provided.')); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty-state" style="padding:40px 20px; margin-top:20px; background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05); text-align:center;">
                    <i class="fas fa-star-half-alt" style="font-size:48px; color:#ccc;"></i>
                    <h3>No Reviews Found</h3>
                    <p>There are no reviews matching your filter criteria.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<?php include 'layout/footer.php'; ?>
</body>

</html>
