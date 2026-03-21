<?php
// donors.php - Manage donor records (view-only)
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Include header and sidebar (adjust paths as needed)
include 'layout/header.php';
include 'layout/sidebar.php';

// Determine action (list, view)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$is_view_mode = ($action == 'view' && isset($_GET['id']));
$view_id = $is_view_mode ? intval($_GET['id']) : 0;

// Initialize variables
$donor_data = [];
$error_message = '';
$success_message = '';

// Handle delete if needed (optional - remove if you want truly view-only)
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if donor has any donations before deleting
    $check = $conn->query("SELECT COUNT(*) as count FROM donations WHERE donor_id = $delete_id");
    $donation_count = $check->fetch_assoc()['count'];
    
    if ($donation_count > 0) {
        $error_message = "Cannot delete donor with existing donation records.";
    } else {
        $conn->query("DELETE FROM donors WHERE id = $delete_id");
        header("Location: donors.php?status=success&message=Donor+deleted+successfully");
        exit;
    }
}

// Fetch data for view mode
if ($is_view_mode && $view_id > 0) {
    // Get donor details with donation summary
    $sql = "SELECT d.*,
            COUNT(don.id) as total_donations,
            SUM(CASE WHEN don.payment_status = 'success' THEN don.amount ELSE 0 END) as total_amount,
            MAX(don.donated_at) as last_donation_date
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id
            WHERE d.id = $view_id
            GROUP BY d.id";
    
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $donor_data = $row;
        
        // Get recent donations for this donor
        $donations_sql = "SELECT don.*, cat.title as category_title 
                          FROM donations don
                          LEFT JOIN donation_categories cat ON don.category_id = cat.id
                          WHERE don.donor_id = $view_id
                          ORDER BY don.donated_at DESC
                          LIMIT 10";
        $donations_result = $conn->query($donations_sql);
        $recent_donations = [];
        if ($donations_result) {
            while ($row = $donations_result->fetch_assoc()) {
                $recent_donations[] = $row;
            }
        }
    } else {
        $error_message = "Donor not found.";
        $is_view_mode = false;
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
    $search_condition = " WHERE name LIKE '%$search%' 
                          OR email LIKE '%$search%' 
                          OR phone LIKE '%$search%'
                          OR city LIKE '%$search%' ";
}

// Filter by city
$city_filter = isset($_GET['city']) ? trim($_GET['city']) : '';
if (!empty($city_filter) && empty($search_condition)) {
    $city_filter = $conn->real_escape_string($city_filter);
    $search_condition = " WHERE city = '$city_filter' ";
} elseif (!empty($city_filter)) {
    $city_filter = $conn->real_escape_string($city_filter);
    $search_condition .= " AND city = '$city_filter' ";
}

// Get total records for pagination
$count_sql = "SELECT COUNT(*) as total FROM donors $search_condition";
$total_result = $conn->query($count_sql);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch donors for list view with donation summary
$donors = [];
if ($action == 'list') {
    $sql = "SELECT d.*,
            COUNT(don.id) as donation_count,
            SUM(CASE WHEN don.payment_status = 'success' THEN don.amount ELSE 0 END) as total_donated
            FROM donors d
            LEFT JOIN donations don ON d.id = don.donor_id
            $search_condition 
            GROUP BY d.id
            ORDER BY d.id DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row;
        }
    }
}

// Get unique cities for filter dropdown
$cities_result = $conn->query("SELECT DISTINCT city FROM donors WHERE city IS NOT NULL AND city != '' ORDER BY city");
$cities = [];
if ($cities_result) {
    while ($row = $cities_result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}

// Calculate summary stats
$stats_sql = "SELECT 
                COUNT(*) as total_donors,
                COUNT(DISTINCT city) as total_cities,
                COUNT(DISTINCT CASE WHEN email IS NOT NULL AND email != '' THEN 1 END) as with_email,
                COUNT(DISTINCT CASE WHEN phone IS NOT NULL AND phone != '' THEN 1 END) as with_phone
              FROM donors";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get recent activity
$recent_result = $conn->query("SELECT d.name, don.donation_no, don.amount, don.donated_at 
                               FROM donations don
                               JOIN donors d ON don.donor_id = d.id
                               WHERE don.payment_status = 'success'
                               ORDER BY don.donated_at DESC
                               LIMIT 5");
$recent_activities = [];
if ($recent_result) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
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
            <!-- List View - Donors Table -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-users"></i>
                    Donor Management
                </h2>
                <!-- <div class="header-actions">
                    <a href="donor-add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add New Donor
                    </a>
                </div> -->
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Donors</span>
                        <span class="stat-number"><?php echo number_format($stats['total_donors'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon cities">
                        <i class="fas fa-city"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Cities</span>
                        <span class="stat-number"><?php echo number_format($stats['total_cities'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">With Email</span>
                        <span class="stat-number"><?php echo number_format($stats['with_email'] ?? 0); ?></span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon phone">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">With Phone</span>
                        <span class="stat-number"><?php echo number_format($stats['with_phone'] ?? 0); ?></span>
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
                               placeholder="Search by name, email, phone, city..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input">
                    </div>
                    
                    <div class="filter-row">
                        <select name="city" class="filter-select">
                            <option value="">All Cities</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>" 
                                    <?php echo $city_filter == $city ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div class="filter-actions">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="?" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Donors Table -->
            <?php if (!empty($donors)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donor</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Donations</th>
                                <th>Total Amount</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donors as $donor): ?>
                                <tr>
                                    <td>#<?php echo $donor['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donor['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($donor['email'])): ?>
                                            <div class="contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($donor['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($donor['phone'])): ?>
                                            <div class="contact-item">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($donor['phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $location = [];
                                        if (!empty($donor['city'])) $location[] = $donor['city'];
                                        if (!empty($donor['state'])) $location[] = $donor['state'];
                                        if (!empty($donor['country'])) $location[] = $donor['country'];
                                        echo htmlspecialchars(implode(', ', $location)) ?: '—';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-donation-count">
                                            <?php echo number_format($donor['donation_count'] ?? 0); ?>
                                        </span>
                                    </td>
                                    <td class="amount-cell">
                                        <?php if (($donor['total_donated'] ?? 0) > 0): ?>
                                            <strong>₹<?php echo number_format($donor['total_donated'], 2); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="date-cell">
                                            <i class="far fa-calendar-alt"></i>
                                            <?php echo date('d/m/Y', strtotime($donor['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="?action=view&id=<?php echo $donor['id']; ?>" 
                                           class="btn-icon" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="donor-edit.php?id=<?php echo $donor['id']; ?>" 
                                           class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (($donor['donation_count'] ?? 0) == 0): ?>
                                            <a href="?delete=<?php echo $donor['id']; ?>" 
                                               class="btn-icon delete" 
                                               onclick="return confirmDelete('<?php echo addslashes($donor['name']); ?>')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
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
                        if (!empty($city_filter)) $query_params['city'] = $city_filter;
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
                    <i class="fas fa-users"></i>
                    <h3>No Donors Found</h3>
                    <!-- <p>Start by adding your first donor.</p>
                    <a href="donor-add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add New Donor
                    </a> -->
                </div>
            <?php endif; ?>
            
        <?php elseif ($action == 'view'): ?>
            <!-- View Donor Details -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-user-circle"></i>
                    Donor Profile
                </h2>
                <div class="header-actions">
                    <a href="donor-edit.php?id=<?php echo $donor_data['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
            
            <div class="profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-title">
                        <h1><?php echo htmlspecialchars($donor_data['name']); ?></h1>
                        <p>Donor since <?php echo date('F Y', strtotime($donor_data['created_at'])); ?></p>
                    </div>
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <span class="stat-value"><?php echo number_format($donor_data['total_donations'] ?? 0); ?></span>
                            <span class="stat-label">Donations</span>
                        </div>
                        <div class="profile-stat">
                            <span class="stat-value">₹<?php echo number_format($donor_data['total_amount'] ?? 0, 2); ?></span>
                            <span class="stat-label">Total Given</span>
                        </div>
                        <?php if (!empty($donor_data['last_donation_date'])): ?>
                        <div class="profile-stat">
                            <span class="stat-value"><?php echo date('d M', strtotime($donor_data['last_donation_date'])); ?></span>
                            <span class="stat-label">Last Donation</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-grid">
                    <!-- Contact Information -->
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-address-card"></i>
                            Contact Information
                        </h3>
                        <table class="info-table">
                            <tr>
                                <th>Email:</th>
                                <td>
                                    <?php if (!empty($donor_data['email'])): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($donor_data['email']); ?>">
                                            <?php echo htmlspecialchars($donor_data['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td>
                                    <?php if (!empty($donor_data['phone'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($donor_data['phone']); ?>">
                                            <?php echo htmlspecialchars($donor_data['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Address Information -->
                    <div class="profile-section">
                        <h3 class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Address
                        </h3>
                        <?php if (!empty($donor_data['address']) || !empty($donor_data['city']) || !empty($donor_data['state'])): ?>
                            <div class="address-block">
                                <?php if (!empty($donor_data['address'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($donor_data['address'])); ?></p>
                                <?php endif; ?>
                                
                                <?php 
                                $city_line = [];
                                if (!empty($donor_data['city'])) $city_line[] = $donor_data['city'];
                                if (!empty($donor_data['state'])) $city_line[] = $donor_data['state'];
                                if (!empty($donor_data['pincode'])) $city_line[] = $donor_data['pincode'];
                                if (!empty($city_line)): 
                                ?>
                                    <p><?php echo htmlspecialchars(implode(', ', $city_line)); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($donor_data['country'])): ?>
                                    <p><?php echo htmlspecialchars($donor_data['country']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No address provided</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Donation History -->
                    <div class="profile-section full-width">
                        <h3 class="section-title">
                            <i class="fas fa-history"></i>
                            Recent Donation History
                        </h3>
                        
                        <?php if (!empty($recent_donations)): ?>
                            <table class="donations-table">
                                <thead>
                                    <tr>
                                        <th>Donation No.</th>
                                        <th>Date</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_donations as $donation): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($donation['donation_no'] ?? 'N/A'); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('d M Y', strtotime($donation['donated_at'] ?? $donation['created_at'])); ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($donation['category_title'])): ?>
                                                    <span class="badge-category">
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
                                                <span class="status-badge status-<?php echo $donation['payment_status']; ?>">
                                                    <?php echo ucfirst($donation['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="donations.php?action=view&id=<?php echo $donation['id']; ?>" 
                                                   class="btn-icon" title="View Donation">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <?php if (($donor_data['total_donations'] ?? 0) > 10): ?>
                                <div class="view-all-link">
                                    <a href="donations.php?donor_id=<?php echo $donor_data['id']; ?>" class="btn btn-secondary">
                                        View All Donations
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <p class="text-muted">No donation records found for this donor.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
    </div>
    
    <!-- Recent Activity Sidebar (only on list view) -->
    <?php if ($action == 'list' && !empty($recent_activities)): ?>
    <div class="activity-sidebar">
        <div class="activity-card">
            <h3 class="activity-title">
                <i class="fas fa-clock"></i>
                Recent Donations
            </h3>
            <div class="activity-list">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-donor">
                                <strong><?php echo htmlspecialchars($activity['name']); ?></strong>
                            </div>
                            <div class="activity-info">
                                <span class="activity-amount">₹<?php echo number_format($activity['amount'], 2); ?></span>
                                <span class="activity-date"><?php echo date('d M', strtotime($activity['donated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="activity-footer">
                <a href="donations.php" class="btn btn-secondary btn-block">View All Donations</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
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
        display: flex;
        gap: 25px;
    }
    
    .container {
        flex: 1;
        /* max-width: 1400px; */
        margin: 0 auto;
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
    
    .btn-block {
        display: block;
        text-align: center;
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
    
    .stat-icon.cities {
        background: var(--gold-light);
        color: var(--gold);
    }
    
    .stat-icon.email {
        background: #d4edda;
        color: var(--success);
    }
    
    .stat-icon.phone {
        background: #d1ecf1;
        color: #17a2b8;
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
        align-items: center;
    }
    
    .filter-select {
        padding: 10px;
        border: 1px solid var(--border);
        background: white;
        min-width: 200px;
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
    
    .contact-item {
        font-size: 13px;
        margin: 3px 0;
        color: #5f7d76;
    }
    
    .contact-item i {
        width: 16px;
        color: var(--teal);
        margin-right: 5px;
    }
    
    .text-center {
        text-align: center;
    }
    
    .badge-donation-count {
        display: inline-block;
        padding: 4px 10px;
        background: var(--teal-light);
        color: var(--teal);
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
    }
    
    .amount-cell {
        font-weight: 600;
        color: var(--teal);
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
    
    .text-muted {
        color: #9bb7b0;
    }
    
    /* Profile View */
    .profile-container {
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .profile-header {
        background: linear-gradient(135deg, var(--teal-light) 0%, white 100%);
        padding: 30px;
        display: flex;
        align-items: center;
        gap: 30px;
        border-bottom: 1px solid var(--border);
    }
    
    
    .profile-title h1 {
        font-size: 28px;
        color: var(--teal);
        margin-bottom: 5px;
    }
    
    .profile-title p {
        color: #5f7d76;
    }
    
    .profile-stats {
        display: flex;
        gap: 30px;
        margin-left: auto;
    }
    
    .profile-stat {
        text-align: center;
    }
    
    .profile-stat .stat-value {
        display: block;
        font-size: 24px;
        font-weight: 700;
        color: var(--teal);
    }
    
    .profile-stat .stat-label {
        font-size: 12px;
        color: #5f7d76;
        text-transform: uppercase;
    }
    
    .profile-grid {
        padding: 30px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
    
    .profile-section {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .profile-section.full-width {
        grid-column: 1 / -1;
    }
    
    .section-title {
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
    
    .info-table {
        width: 100%;
    }
    
    .info-table th {
        width: 30%;
        text-align: left;
        padding: 8px 0;
        color: #5f7d76;
        font-weight: 500;
    }
    
    .info-table td {
        padding: 8px 0;
    }
    
    .info-table a {
        color: var(--teal);
        text-decoration: none;
    }
    
    .info-table a:hover {
        text-decoration: underline;
    }
    
    .address-block {
        line-height: 1.6;
        color: var(--dark);
    }
    
    .address-block p {
        margin-bottom: 5px;
    }
    
    /* Donations Table in Profile */
    .donations-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .donations-table th {
        text-align: left;
        padding: 12px 8px;
        background: var(--teal-light);
        font-size: 13px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .donations-table td {
        padding: 12px 8px;
        border-bottom: 1px solid var(--border);
    }
    
    .badge-category {
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
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-success {
        background: #d4edda;
        color: #155724;
    }
    
    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }
    
    .view-all-link {
        margin-top: 20px;
        text-align: center;
    }
    
    /* Activity Sidebar */
    .activity-sidebar {
        width: 300px;
    }
    
    .activity-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
        overflow: hidden;
    }
    
    .activity-title {
        padding: 15px;
        background: var(--teal-light);
        font-size: 16px;
        font-weight: 600;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 2px solid var(--gold);
    }
    
    .activity-list {
        padding: 10px;
    }
    
    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-icon {
        width: 36px;
        height: 36px;
        background: var(--teal-light);
        color: var(--teal);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }
    
    .activity-details {
        flex: 1;
    }
    
    .activity-donor {
        margin-bottom: 4px;
    }
    
    .activity-info {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
    }
    
    .activity-amount {
        font-weight: 600;
        color: var(--teal);
    }
    
    .activity-date {
        color: #5f7d76;
    }
    
    .activity-footer {
        padding: 15px;
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
    @media (max-width: 1200px) {
        .main-content {
            flex-direction: column;
        }
        
        .activity-sidebar {
            width: 100%;
        }
    }
    
    @media (max-width: 1024px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-stats {
            margin-left: 0;
        }
        
        .profile-grid {
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
        
        .filter-select {
            width: 100%;
        }
        
        .header-bar {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .profile-stats {
            flex-direction: column;
            gap: 15px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirm delete
    function confirmDelete(name) {
        return Swal.fire({
            title: 'Delete Donor?',
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