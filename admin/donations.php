<?php
// donations.php - Manage donation records
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Include header and sidebar (adjust paths as needed)
include 'layout/header.php';
include 'layout/sidebar.php';

// Determine action (list, view, add, edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$is_view_mode = ($action == 'view' && isset($_GET['id']));
$is_edit_mode = ($action == 'edit' && isset($_GET['id']));
$view_id = ($is_view_mode || $is_edit_mode) ? intval($_GET['id']) : 0;

// Initialize variables
$donation_data = [];
$donors = [];
$categories = [];
$error_message = '';
$success_message = '';

// Fetch donors for dropdown
$donors_result = $conn->query("SELECT id, name, email FROM donors ORDER BY name");
if ($donors_result) {
    while ($row = $donors_result->fetch_assoc()) {
        $donors[] = $row;
    }
}

// Fetch donation categories for dropdown
$cats_result = $conn->query("SELECT id, title FROM donation_categories WHERE status = 1 ORDER BY title");
if ($cats_result) {
    while ($row = $cats_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitDonation'])) {
    // Get form data
    $donation_no = trim($_POST['donation_no'] ?? '');
    $donor_id = !empty($_POST['donor_id']) ? intval($_POST['donor_id']) : null;
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    $payment_status = trim($_POST['payment_status'] ?? 'pending');
    $transaction_id = trim($_POST['transaction_id'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $donated_at = !empty($_POST['donated_at']) ? $_POST['donated_at'] : date('Y-m-d H:i:s');
    
    // Validate required fields
    if ($amount <= 0) {
        $error_message = "Amount must be greater than zero.";
    } elseif (empty($donor_id)) {
        $error_message = "Please select a donor.";
    } else {
        // Auto-generate donation number if not provided
        if (empty($donation_no)) {
            $prefix = 'DON-' . date('Ymd');
            $result = $conn->query("SELECT COUNT(*) as count FROM donations WHERE donation_no LIKE '$prefix%'");
            $count = $result->fetch_assoc()['count'] + 1;
            $donation_no = $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
        
        if ($is_edit_mode) {
            // Update existing record
            $sql = "UPDATE donations SET 
                    donation_no = ?,
                    donor_id = ?,
                    category_id = ?,
                    amount = ?,
                    payment_method = ?,
                    payment_status = ?,
                    transaction_id = ?,
                    message = ?,
                    donated_at = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siidsssssi", $donation_no, $donor_id, $category_id, $amount, $payment_method, $payment_status, $transaction_id, $message, $donated_at, $view_id);
        } else {
            // Insert new record
            $sql = "INSERT INTO donations (donation_no, donor_id, category_id, amount, payment_method, payment_status, transaction_id, message, donated_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siidsssss", $donation_no, $donor_id, $category_id, $amount, $payment_method, $payment_status, $transaction_id, $message, $donated_at);
        }
        
        if ($stmt->execute()) {
            // Update collected amount in donation_categories
            if ($category_id && $payment_status == 'success') {
                $conn->query("UPDATE donation_categories SET collected_amount = collected_amount + $amount WHERE id = $category_id");
            }
            
            $success_message = $is_edit_mode ? "Donation updated successfully!" : "Donation added successfully!";
            // Redirect to list view after successful operation
            header("Location: donations.php?status=success&message=" . urlencode($success_message));
            exit;
        } else {
            $error_message = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Get donation details to update category collected amount
    $result = $conn->query("SELECT category_id, amount, payment_status FROM donations WHERE id = $delete_id");
    if ($row = $result->fetch_assoc()) {
        if ($row['payment_status'] == 'success' && !empty($row['category_id'])) {
            // Subtract from collected amount
            $conn->query("UPDATE donation_categories SET collected_amount = collected_amount - {$row['amount']} WHERE id = {$row['category_id']}");
        }
    }
    
    $conn->query("DELETE FROM donations WHERE id = $delete_id");
    header("Location: donations.php?status=success&message=Donation+record+deleted+successfully");
    exit;
}

// Handle status update (mark as success/failed)
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $update_id = intval($_GET['update_status']);
    $new_status = $conn->real_escape_string($_GET['status']);
    
    // Get current status and category/amount
    $result = $conn->query("SELECT payment_status, category_id, amount FROM donations WHERE id = $update_id");
    if ($row = $result->fetch_assoc()) {
        $old_status = $row['payment_status'];
        $category_id = $row['category_id'];
        $amount = $row['amount'];
        
        // Update collected amount in category
        if ($new_status == 'success' && $old_status != 'success') {
            // Add to collected amount
            if ($category_id) {
                $conn->query("UPDATE donation_categories SET collected_amount = collected_amount + $amount WHERE id = $category_id");
            }
        } elseif ($new_status != 'success' && $old_status == 'success') {
            // Subtract from collected amount
            if ($category_id) {
                $conn->query("UPDATE donation_categories SET collected_amount = collected_amount - $amount WHERE id = $category_id");
            }
        }
    }
    
    $conn->query("UPDATE donations SET payment_status = '$new_status' WHERE id = $update_id");
    header("Location: donations.php?status=success&message=Status+updated+successfully");
    exit;
}

// Fetch data for view/edit mode
if (($is_view_mode || $is_edit_mode) && $view_id > 0) {
    $sql = "SELECT d.*, 
            donor.name as donor_name, donor.email as donor_email, donor.phone as donor_phone,
            cat.title as category_title
            FROM donations d
            LEFT JOIN donors donor ON d.donor_id = donor.id
            LEFT JOIN donation_categories cat ON d.category_id = cat.id
            WHERE d.id = $view_id";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $donation_data = $row;
    } else {
        $error_message = "Donation record not found.";
        $is_view_mode = false;
        $is_edit_mode = false;
    }
}

// Pagination for list view
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20; // Show 20 items per page
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_condition = " WHERE d.donation_no LIKE '%$search%' 
                          OR donor.name LIKE '%$search%' 
                          OR donor.email LIKE '%$search%'
                          OR d.transaction_id LIKE '%$search%' ";
}

// Filter by status
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
if (!empty($status_filter) && empty($search_condition)) {
    $status_filter = $conn->real_escape_string($status_filter);
    $search_condition = " WHERE d.payment_status = '$status_filter' ";
} elseif (!empty($status_filter)) {
    $status_filter = $conn->real_escape_string($status_filter);
    $search_condition .= " AND d.payment_status = '$status_filter' ";
}

// Filter by category
$cat_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
if ($cat_filter > 0 && empty($search_condition)) {
    $search_condition = " WHERE d.category_id = $cat_filter ";
} elseif ($cat_filter > 0) {
    $search_condition .= " AND d.category_id = $cat_filter ";
}

// Date range filter
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
if (!empty($date_from) && !empty($date_to)) {
    $date_from = $conn->real_escape_string($date_from);
    $date_to = $conn->real_escape_string($date_to);
    $date_condition = " DATE(d.donated_at) BETWEEN '$date_from' AND '$date_to' ";
    
    if (empty($search_condition)) {
        $search_condition = " WHERE $date_condition ";
    } else {
        $search_condition .= " AND $date_condition ";
    }
} elseif (!empty($date_from)) {
    $date_from = $conn->real_escape_string($date_from);
    $date_condition = " DATE(d.donated_at) >= '$date_from' ";
    
    if (empty($search_condition)) {
        $search_condition = " WHERE $date_condition ";
    } else {
        $search_condition .= " AND $date_condition ";
    }
}

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM donations d
              LEFT JOIN donors donor ON d.donor_id = donor.id
              $search_condition";
$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch donations for list view
$donations = [];
if ($action == 'list') {
    $sql = "SELECT d.*, 
            donor.name as donor_name, donor.email as donor_email,
            cat.title as category_title
            FROM donations d
            LEFT JOIN donors donor ON d.donor_id = donor.id
            LEFT JOIN donation_categories cat ON d.category_id = cat.id
            $search_condition 
            ORDER BY d.id DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donations[] = $row;
        }
    }
}

// Calculate summary stats
$stats_sql = "SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN payment_status = 'success' THEN amount ELSE 0 END) as total_success_amount,
                SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN payment_status = 'success' THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN payment_status = 'failed' THEN 1 ELSE 0 END) as failed_count
              FROM donations";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status']; ?>">
                <i class="fas fa-<?php echo $_GET['status'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($action == 'list'): ?>
            <!-- List View - Donations Table -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-hand-holding-heart"></i>
                    Donation Management
                </h2>
                <!-- <div class="header-actions">
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Donation
                    </a>
                </div> -->
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Donations</span>
                        <span class="stat-number"><?php echo number_format($stats['total_count'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Successful</span>
                        <span class="stat-number"><?php echo number_format($stats['success_count'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amount">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Amount</span>
                        <span class="stat-number">₹<?php echo number_format($stats['total_success_amount'] ?? 0, 2); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Pending</span>
                        <span class="stat-number"><?php echo number_format($stats['pending_count'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <div class="search-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Search by donation no., donor, transaction ID..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input">
                    </div>
                    
                    <div class="filter-row">
                        <select name="status" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="success" <?php echo $status_filter == 'success' ? 'selected' : ''; ?>>Success</option>
                            <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>
                        
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $cat_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="date" name="date_from" class="filter-date" value="<?php echo $date_from; ?>" placeholder="From">
                        <input type="date" name="date_to" class="filter-date" value="<?php echo $date_to; ?>" placeholder="To">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply</button>
                        <a href="?" class="btn btn-secondary">Clear</a>
                    </div>
                </form>
            </div>
            
            <!-- Donations Table -->
            <?php if (!empty($donations)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Donation No.</th>
                                <th>Donor</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donation['donation_no'] ?? 'N/A'); ?></strong>
                                        <?php if (!empty($donation['transaction_id'])): ?>
                                            <div class="text-small">ID: <?php echo htmlspecialchars($donation['transaction_id']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donation['donor_name'] ?? 'Unknown'); ?></strong>
                                        <?php if (!empty($donation['donor_email'])): ?>
                                            <div class="text-small"><?php echo htmlspecialchars($donation['donor_email']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($donation['category_title'])): ?>
                                            <span class="badge badge-category">
                                                <?php echo htmlspecialchars($donation['category_title']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="amount-cell">
                                        <strong>₹<?php echo number_format($donation['amount'], 2); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($donation['payment_method'])): ?>
                                            <span class="badge badge-method">
                                                <?php echo htmlspecialchars(ucfirst($donation['payment_method'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $donation['payment_status']; ?>">
                                            <?php echo ucfirst($donation['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="date-cell">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo date('d/m/Y', strtotime($donation['donated_at'] ?? $donation['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?action=view&id=<?php echo $donation['id']; ?>" 
                                           class="btn-icon" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?action=edit&id=<?php echo $donation['id']; ?>" 
                                           class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($donation['payment_status'] != 'success'): ?>
                                            <a href="?update_status=<?php echo $donation['id']; ?>&status=success" 
                                               class="btn-icon success" title="Mark as Success"
                                               onclick="return confirmStatus('Mark as successful?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($donation['payment_status'] != 'failed'): ?>
                                            <a href="?update_status=<?php echo $donation['id']; ?>&status=failed" 
                                               class="btn-icon warning" title="Mark as Failed"
                                               onclick="return confirmStatus('Mark as failed?')">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?php echo $donation['id']; ?>" 
                                           class="btn-icon delete" 
                                           onclick="return confirmDelete('donation #<?php echo $donation['donation_no']; ?>')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        $query_params = [];
                        if (!empty($search)) $query_params['search'] = $search;
                        if (!empty($status_filter)) $query_params['status'] = $status_filter;
                        if ($cat_filter > 0) $query_params['category'] = $cat_filter;
                        if (!empty($date_from)) $query_params['date_from'] = $date_from;
                        if (!empty($date_to)) $query_params['date_to'] = $date_to;
                        $query_string = http_build_query($query_params);
                        ?>
                        
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?>&<?php echo $query_string; ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?>&<?php echo $query_string; ?>" class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-hand-holding-heart"></i>
                    <h3>No Donations Found</h3>
                    <!-- <p>Start by adding your first donation record.</p>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Donation
                    </a> -->
                </div>
            <?php endif; ?>
            
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-<?php echo $is_edit_mode ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $is_edit_mode ? 'Edit Donation' : 'Add New Donation'; ?>
                </h2>
                <div class="header-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="form-container">
                <form method="POST" action="" class="donation-form">
                    <input type="hidden" name="submitDonation" value="1">
                    
                    <div class="form-row">
                        <!-- Left Column -->
                        <div class="form-main">
                            <div class="form-group">
                                <label class="form-label">Donation Number</label>
                                <input type="text" 
                                       name="donation_no" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($donation_data['donation_no'] ?? ''); ?>"
                                       placeholder="Auto-generated if empty">
                                <div class="form-help">Leave empty to auto-generate</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label required">Donor</label>
                                <select name="donor_id" class="form-control" required>
                                    <option value="">Select Donor</option>
                                    <?php foreach ($donors as $donor): ?>
                                        <option value="<?php echo $donor['id']; ?>" 
                                            <?php echo ($donation_data['donor_id'] ?? '') == $donor['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($donor['name'] . ' (' . $donor['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-help">
                                    <a href="donors.php?action=add" target="_blank">+ Add New Donor</a>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">General Donation</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo ($donation_data['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label required">Amount (₹)</label>
                                    <input type="number" 
                                           name="amount" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($donation_data['amount'] ?? ''); ?>"
                                           step="0.01"
                                           min="1"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-control">
                                        <option value="">Select</option>
                                        <option value="cash" <?php echo ($donation_data['payment_method'] ?? '') == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                        <option value="card" <?php echo ($donation_data['payment_method'] ?? '') == 'card' ? 'selected' : ''; ?>>Credit/Debit Card</option>
                                        <option value="bank" <?php echo ($donation_data['payment_method'] ?? '') == 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                                        <option value="upi" <?php echo ($donation_data['payment_method'] ?? '') == 'upi' ? 'selected' : ''; ?>>UPI</option>
                                        <option value="online" <?php echo ($donation_data['payment_method'] ?? '') == 'online' ? 'selected' : ''; ?>>Online Gateway</option>
                                        <option value="cheque" <?php echo ($donation_data['payment_method'] ?? '') == 'cheque' ? 'selected' : ''; ?>>Cheque</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Payment Status</label>
                                    <select name="payment_status" class="form-control">
                                        <option value="pending" <?php echo ($donation_data['payment_status'] ?? 'pending') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="success" <?php echo ($donation_data['payment_status'] ?? '') == 'success' ? 'selected' : ''; ?>>Success</option>
                                        <option value="failed" <?php echo ($donation_data['payment_status'] ?? '') == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Transaction ID</label>
                                    <input type="text" 
                                           name="transaction_id" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($donation_data['transaction_id'] ?? ''); ?>"
                                           placeholder="Reference/transaction ID">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Donation Date & Time</label>
                                <input type="datetime-local" 
                                       name="donated_at" 
                                       class="form-control" 
                                       value="<?php 
                                           if (!empty($donation_data['donated_at'])) {
                                               echo date('Y-m-d\TH:i', strtotime($donation_data['donated_at']));
                                           } else {
                                               echo date('Y-m-d\TH:i');
                                           }
                                       ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Message / Notes</label>
                                <textarea name="message" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Any additional notes or message from donor"><?php echo htmlspecialchars($donation_data['message'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Right Column - Info & Preview -->
                        <div class="form-sidebar">
                            <?php if ($is_edit_mode): ?>
                                <div class="info-box">
                                    <h4 class="info-title">Donation Details</h4>
                                    <div class="info-row">
                                        <span class="info-label">ID:</span>
                                        <span class="info-value">#<?php echo $donation_data['id']; ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Created:</span>
                                        <span class="info-value"><?php echo date('d M Y, H:i', strtotime($donation_data['created_at'])); ?></span>
                                    </div>
                                    <?php if (!empty($donation_data['donor_name'])): ?>
                                    <div class="info-row">
                                        <span class="info-label">Donor:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($donation_data['donor_name']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Quick Tips -->
                            <div class="tips-box">
                                <h4 class="tips-title">
                                    <i class="fas fa-info-circle"></i>
                                    Quick Tips
                                </h4>
                                <ul class="tips-list">
                                    <li>Donation number is auto-generated if left empty</li>
                                    <li>Select a donor from the list or add new donor</li>
                                    <li>Changing status updates category collected amount</li>
                                    <li>Transaction ID helps track payment reference</li>
                                </ul>
                            </div>
                            
                            <!-- Category Progress Preview (if category selected) -->
                            <?php if (!empty($donation_data['category_id']) && !empty($donation_data['amount'])): ?>
                                <?php
                                $cat_id = $donation_data['category_id'];
                                $cat_info = $conn->query("SELECT title, target_amount, collected_amount FROM donation_categories WHERE id = $cat_id")->fetch_assoc();
                                if ($cat_info):
                                    $progress = ($cat_info['target_amount'] > 0) ? round(($cat_info['collected_amount'] / $cat_info['target_amount']) * 100, 1) : 0;
                                ?>
                                <div class="progress-preview">
                                    <h4><?php echo htmlspecialchars($cat_info['title']); ?></h4>
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                        </div>
                                        <span class="progress-text"><?php echo $progress; ?>%</span>
                                    </div>
                                    <div class="progress-stats">
                                        <span>Collected: ₹<?php echo number_format($cat_info['collected_amount'], 2); ?></span>
                                        <span>Target: ₹<?php echo number_format($cat_info['target_amount'], 2); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $is_edit_mode ? 'Update Donation' : 'Add Donation'; ?>
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <?php if ($is_edit_mode): ?>
                            <a href="?delete=<?php echo $donation_data['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirmDelete('donation #<?php echo $donation_data['donation_no']; ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
        <?php elseif ($action == 'view'): ?>
            <!-- View Donation Details -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-eye"></i>
                    Donation Details
                </h2>
                <div class="header-actions">
                    <a href="?action=edit&id=<?php echo $donation_data['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="view-container">
                <div class="view-header">
                    <div class="view-title">
                        <h3>Donation #<?php echo htmlspecialchars($donation_data['donation_no']); ?></h3>
                        <span class="status-badge status-<?php echo $donation_data['payment_status']; ?>">
                            <?php echo ucfirst($donation_data['payment_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="view-grid">
                    <div class="view-section">
                        <h4 class="section-title">Donor Information</h4>
                        <table class="view-table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo htmlspecialchars($donation_data['donor_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($donation_data['donor_email'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo htmlspecialchars($donation_data['donor_phone'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="view-section">
                        <h4 class="section-title">Donation Details</h4>
                        <table class="view-table">
                            <tr>
                                <th>Amount:</th>
                                <td><strong>₹<?php echo number_format($donation_data['amount'], 2); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td><?php echo htmlspecialchars($donation_data['category_title'] ?? 'General'); ?></td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td><?php echo htmlspecialchars(ucfirst($donation_data['payment_method'] ?? 'N/A')); ?></td>
                            </tr>
                            <tr>
                                <th>Transaction ID:</th>
                                <td><?php echo htmlspecialchars($donation_data['transaction_id'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="view-section">
                        <h4 class="section-title">Dates</h4>
                        <table class="view-table">
                            <tr>
                                <th>Donation Date:</th>
                                <td><?php echo date('d M Y, H:i', strtotime($donation_data['donated_at'] ?? $donation_data['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Record Created:</th>
                                <td><?php echo date('d M Y, H:i', strtotime($donation_data['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (!empty($donation_data['message'])): ?>
                    <div class="view-section full-width">
                        <h4 class="section-title">Message / Notes</h4>
                        <div class="message-box">
                            <?php echo nl2br(htmlspecialchars($donation_data['message'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Brand colors: #1a685b, #ffac00, #051311 */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
        --success: #28a745;
        --danger: #dc3545;
        --warning: #fd7e14;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
    }
    
    body {
        background-color: var(--gray-bg);
        color: var(--dark);
    }
    
    .main-content {
        padding: 25px;
    }
    
    /* Header Bar */
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--gold);
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .page-title i {
        color: var(--gold);
    }
    
    /* Buttons */
    .btn {
        padding: 10px 22px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border-radius: 4px;
    }
    
    .btn-primary {
        background: var(--teal);
        color: white;
        border: 1px solid var(--teal);
    }
    
    .btn-primary:hover {
        background: #0f4f44;
    }
    
    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 1px solid var(--border);
    }
    
    .btn-secondary:hover {
        background: var(--gray-bg);
        border-color: var(--teal);
    }
    
    .btn-danger {
        background: var(--danger);
        color: white;
        border: 1px solid var(--danger);
    }
    
    .btn-danger:hover {
        background: #bb2d3b;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        text-decoration: none;
        border: 1px solid var(--border);
        background: white;
        transition: all 0.2s;
        border-radius: 4px;
        margin: 0 2px;
    }
    
    .btn-icon:hover {
        background: var(--teal);
        color: white;
        border-color: var(--teal);
    }
    
    .btn-icon.success:hover {
        background: var(--success);
        border-color: var(--success);
    }
    
    .btn-icon.warning:hover {
        background: var(--warning);
        border-color: var(--warning);
    }
    
    .btn-icon.delete:hover {
        background: var(--danger);
        border-color: var(--danger);
    }
    
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 15px;
        border-radius: 4px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        background: var(--teal-light);
        color: var(--teal);
        border-radius: 4px;
    }
    
    .stat-icon.success {
        background: #d4edda;
        color: var(--success);
    }
    
    .stat-icon.amount {
        background: var(--gold-light);
        color: var(--gold);
    }
    
    .stat-icon.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-label {
        display: block;
        font-size: 13px;
        color: #5f7d76;
        margin-bottom: 5px;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: 600;
        color: var(--dark);
    }
    
    /* Filter Bar */
    .filter-bar {
        background: white;
        padding: 15px;
        border: 1px solid var(--border);
        margin-bottom: 25px;
        border-radius: 4px;
    }
    
    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    
    .search-group {
        position: relative;
        width: 100%;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9bb7b0;
    }
    
    .search-input {
        width: 100%;
        padding: 12px 12px 12px 40px;
        border: 1px solid var(--border);
        font-size: 14px;
        background: white;
        border-radius: 4px;
    }
    
    .filter-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .filter-select, .filter-date {
        padding: 10px;
        border: 1px solid var(--border);
        background: white;
        min-width: 150px;
        flex: 1;
        border-radius: 4px;
    }
    
    .filter-actions {
        display: flex;
        gap: 10px;
    }
    
    /* Table */
    .table-container {
        background: white;
        border: 1px solid var(--border);
        overflow-x: auto;
        border-radius: 4px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }
    
    .data-table thead {
        background: var(--teal-light);
        border-bottom: 2px solid var(--teal);
    }
    
    .data-table th {
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        color: var(--dark);
        text-transform: uppercase;
    }
    
    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    
    .data-table tbody tr:hover {
        background: #fafdfc;
    }
    
    .text-small {
        font-size: 11px;
        color: #5f7d76;
        margin-top: 3px;
    }
    
    .amount-cell {
        font-weight: 600;
        color: var(--teal);
    }
    
    /* Badges */
    .badge-category {
        display: inline-block;
        padding: 4px 10px;
        background: var(--teal-light);
        color: var(--teal);
        font-size: 12px;
        border: 1px solid var(--teal);
        border-radius: 4px;
    }
    
    .badge-method {
        display: inline-block;
        padding: 4px 10px;
        background: var(--gold-light);
        color: var(--dark);
        font-size: 12px;
        border: 1px solid var(--gold);
        border-radius: 4px;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        border-radius: 4px;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .status-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .status-failed {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .date-cell {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #5f7d76;
    }
    
    .actions-cell {
        white-space: nowrap;
    }
    
    /* Form */
    .form-container {
        background: white;
        border: 1px solid var(--border);
        padding: 25px;
        border-radius: 4px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--dark);
    }
    
    .form-label.required::after {
        content: " *";
        color: var(--danger);
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        font-size: 14px;
        background: white;
        border-radius: 4px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--teal);
    }
    
    .form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #5f7d76;
    }
    
    .form-help a {
        color: var(--teal);
        text-decoration: none;
    }
    
    .form-help a:hover {
        text-decoration: underline;
    }
    
    /* Info Box */
    .info-box, .tips-box, .progress-preview {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .info-title, .tips-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--teal);
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed var(--border);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #5f7d76;
        font-size: 13px;
    }
    
    .info-value {
        font-weight: 600;
        color: var(--dark);
    }
    
    .tips-list {
        list-style: none;
        padding-left: 0;
    }
    
    .tips-list li {
        padding: 6px 0;
        padding-left: 20px;
        position: relative;
        font-size: 13px;
        color: #5f7d76;
    }
    
    .tips-list li:before {
        content: "•";
        color: var(--gold);
        font-weight: bold;
        position: absolute;
        left: 6px;
    }
    
    /* Progress */
    .progress-container {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 10px 0;
    }
    
    .progress-bar {
        flex: 1;
        height: 8px;
        background: var(--border);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--teal), var(--gold));
        border-radius: 4px;
    }
    
    .progress-text {
        font-size: 13px;
        font-weight: 600;
        color: var(--teal);
        min-width: 45px;
    }
    
    .progress-stats {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #5f7d76;
        margin-top: 8px;
    }
    
    /* View Page */
    .view-container {
        background: white;
        border: 1px solid var(--border);
        padding: 30px;
        border-radius: 4px;
    }
    
    .view-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--gold);
    }
    
    .view-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .view-title h3 {
        font-size: 22px;
        color: var(--teal);
    }
    
    .view-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
    
    .view-section {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .view-section.full-width {
        grid-column: 1 / -1;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--teal);
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
    }
    
    .view-table {
        width: 100%;
    }
    
    .view-table th {
        width: 40%;
        text-align: left;
        padding: 8px 0;
        color: #5f7d76;
        font-weight: 500;
        font-size: 14px;
    }
    
    .view-table td {
        padding: 8px 0;
        color: var(--dark);
        font-weight: 500;
    }
    
    .message-box {
        padding: 15px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
        line-height: 1.6;
    }
    
    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    
    /* Alerts */
    .alert {
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 4px solid transparent;
        animation: slideIn 0.3s ease;
        border-radius: 4px;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
        background: #e6f3f0;
        color: #0f4f44;
        border-left-color: var(--teal);
    }
    
    .alert-error {
        background: #ffe6e6;
        color: #b02a37;
        border-left-color: var(--danger);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .empty-state i {
        font-size: 60px;
        color: var(--teal-light);
        margin-bottom: 15px;
    }
    
    .empty-state h3 {
        color: var(--teal);
        margin-bottom: 10px;
        font-size: 20px;
    }
    
    .empty-state p {
        color: #5f7d76;
        margin-bottom: 20px;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 30px;
    }
    
    .page-link {
        padding: 8px 14px;
        border: 1px solid var(--border);
        background: white;
        color: var(--teal);
        text-decoration: none;
        transition: all 0.2s;
        border-radius: 4px;
    }
    
    .page-link:hover {
        background: var(--teal-light);
        border-color: var(--teal);
    }
    
    .page-link.active {
        background: var(--teal);
        color: white;
        border-color: var(--teal);
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .view-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
        
        .filter-row {
            flex-direction: column;
        }
        
        .header-bar {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirm delete
    function confirmDelete(name) {
        return Swal.fire({
            title: 'Delete Donation?',
            html: `Are you sure you want to delete <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            return result.isConfirmed;
        });
    }
    
    // Confirm status update
    function confirmStatus(message) {
        return Swal.fire({
            title: 'Confirm',
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a685b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes'
        }).then((result) => {
            return result.isConfirmed;
        });
    }
    
    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>