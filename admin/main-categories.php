<?php
// main-categories.php - Main Categories listing page
ob_start(); // Start output buffering at the VERY TOP
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

include 'layout/header.php';
include 'layout/sidebar.php';

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Check if category has sub-categories
    $check_sub = $conn->query("SELECT COUNT(*) as total FROM sub_categories WHERE main_category_id = $delete_id");
    $sub_count = $check_sub->fetch_assoc()['total'];

    if ($sub_count > 0) {
        $_SESSION['error_message'] = "Cannot delete category because it has $sub_count sub-categories. Please delete sub-categories first.";
    } else {
        // Get image files to delete
        $result = $conn->query("SELECT icon, banner_image, thumbnail_image FROM main_categories WHERE id = $delete_id");
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['icon']) && file_exists('../uploads/categories/icons/' . $row['icon'])) {
                unlink('../uploads/categories/icons/' . $row['icon']);
            }
            if (!empty($row['banner_image']) && file_exists('../uploads/categories/banners/' . $row['banner_image'])) {
                unlink('../uploads/categories/banners/' . $row['banner_image']);
            }
            if (!empty($row['thumbnail_image']) && file_exists('../uploads/categories/thumbnails/' . $row['thumbnail_image'])) {
                unlink('../uploads/categories/thumbnails/' . $row['thumbnail_image']);
            }
        }

        $conn->query("DELETE FROM main_categories WHERE id = $delete_id");
        $_SESSION['success_message'] = "Main category deleted successfully";
    }

    header("Location: main-categories.php");
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_status = intval($_GET['current']);
    $new_status = $current_status ? 0 : 1;

    $conn->query("UPDATE main_categories SET is_active = $new_status WHERE id = $toggle_id");

    $_SESSION['success_message'] = "Category status updated successfully";
    header("Location: main-categories.php");
    exit;
}

// Handle featured toggle
if (isset($_GET['toggle_featured'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_featured = intval($_GET['current']);
    $new_featured = $current_featured ? 0 : 1;

    $conn->query("UPDATE main_categories SET is_featured = $new_featured WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Category featured status updated successfully";
    header("Location: main-categories.php");
    exit;
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$code_filter = isset($_GET['code']) ? trim($_GET['code']) : '';

$conditions = [];
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $conditions[] = "(name LIKE '%$search%' OR description LIKE '%$search%' OR category_code LIKE '%$search%')";
}
if ($status_filter !== '') {
    $status_filter = intval($status_filter);
    $conditions[] = "is_active = $status_filter";
}
if (!empty($code_filter)) {
    $code_filter = $conn->real_escape_string($code_filter);
    $conditions[] = "category_code = '$code_filter'";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM main_categories $where_clause");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch main categories
$categories = [];
$sql = "SELECT * FROM main_categories 
        $where_clause 
        ORDER BY sort_order ASC, name ASC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Get sub-category count
        $sub_result = $conn->query("SELECT COUNT(*) as total FROM sub_categories WHERE main_category_id = {$row['id']}");
        $row['sub_category_count'] = $sub_result->fetch_assoc()['total'];

        // Get product count
        $prod_result = $conn->query("SELECT COUNT(*) as total FROM products WHERE sub_category_id IN (SELECT id FROM sub_categories WHERE main_category_id = {$row['id']})");
        $row['product_count'] = $prod_result->fetch_assoc()['total'];

        $categories[] = $row;
    }
}

// Get unique category codes for filter
$codes = ['HAND', 'TEX', 'FMCG'];

// Get active count
$active_count = $conn->query("SELECT COUNT(*) as total FROM main_categories WHERE is_active = 1")->fetch_assoc()['total'];
$featured_count = $conn->query("SELECT COUNT(*) as total FROM main_categories WHERE is_featured = 1")->fetch_assoc()['total'];

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Status badge function
function getStatusBadge($status)
{
    return $status ?
        '<span class="badge-status active"><i class="fas fa-check-circle"></i> Active</span>' :
        '<span class="badge-status inactive"><i class="fas fa-times-circle"></i> Inactive</span>';
}

// Featured badge function
function getFeaturedBadge($featured)
{
    return $featured ?
        '<span class="badge-featured"><i class="fas fa-star"></i> Featured</span>' : '';
}

// Get category code badge
function getCodeBadge($code)
{
    $colors = [
        'HAND' => '#4CAF50',
        'TEX' => '#2196F3',
        'FMCG' => '#FF9800'
    ];
    $color = $colors[$code] ?? '#6c757d';
    return "<span class=\"badge-code\" style=\"background: {$color}20; color: {$color}; border-color: {$color}\">{$code}</span>";
}

?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Alert Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-sitemap"></i>
                Main Categories Management
            </h2>
            <!-- <div class="header-actions">
                <a href="main-category-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </a>
                <a href="main-category-bulk.php" class="btn btn-primary" style="background: var(--gold); border-color: var(--gold); color: var(--dark);">
                    <i class="fas fa-layer-group"></i> Bulk Upload
                </a>
            </div> -->
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total Categories</span>
                <span class="stat-number"><?php echo $total_records; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Active Categories</span>
                <span class="stat-number"><?php echo $active_count; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Featured</span>
                <span class="stat-number"><?php echo $featured_count; ?></span>
            </div>
        </div>

        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text"
                        name="search"
                        placeholder="Search categories..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="search-input">
                </div>

                <div class="filter-group">
                    <select name="code" class="filter-select">
                        <option value="">All Codes</option>
                        <?php foreach ($codes as $code): ?>
                            <option value="<?php echo $code; ?>"
                                <?php echo $code_filter == $code ? 'selected' : ''; ?>>
                                <?php echo $code; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Apply</button>

                <?php if (!empty($search) || $status_filter !== '' || !empty($code_filter)): ?>
                    <a href="?" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Categories Table -->
        <?php if (!empty($categories)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Icon</th>
                            <th>Category</th>
                            <th>Code</th>
                            <th>Stats</th>
                            <th>Status</th>
                            <th>Sort</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td>#<?php echo $cat['id']; ?></td>
                                <td class="icon-cell">
                                    <?php if (!empty($cat['icon'])): ?>
                                        <img src="../uploads/categories/icons/<?php echo $cat['icon']; ?>"
                                            alt="<?php echo htmlspecialchars($cat['name']); ?>"
                                            class="category-icon" onerror="this.src='../assets/images/track_icon_1.png';">
                                    <?php else: ?>
                                        <div class="no-icon">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <div class="category-slug">
                                        <small><?php echo htmlspecialchars($cat['slug']); ?></small>
                                    </div>
                                    <?php echo getFeaturedBadge($cat['is_featured']); ?>
                                </td>
                                <td><?php echo getCodeBadge($cat['category_code']); ?></td>
                                <td>
                                    <div class="stats-badge">
                                        <span class="stat-item" title="Sub Categories">
                                            <i class="fas fa-folder-open"></i> <?php echo $cat['sub_category_count']; ?>
                                        </span>
                                        <span class="stat-item" title="Products">
                                            <i class="fas fa-box"></i> <?php echo $cat['product_count'] ?: $cat['total_products']; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="status-group">
                                        <?php echo getStatusBadge($cat['is_active']); ?>
                                        <a href="?toggle_status=1&toggle_id=<?php echo $cat['id']; ?>&current=<?php echo $cat['is_active']; ?>"
                                            class="toggle-link"
                                            title="Toggle Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="sort-order">
                                        <?php echo $cat['sort_order']; ?>
                                    </span>
                                </td>
                                <!-- <td class="actions-cell">
                                    <a href="main-category-view.php?id=<?php #echo $cat['id']; ?>"
                                        class="btn-icon"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="main-category-edit.php?id=<?php #echo $cat['id']; ?>"
                                        class="btn-icon"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?toggle_featured=1&toggle_id=<?php #echo $cat['id']; ?>&current=<?php #echo $cat['is_featured']; ?>"
                                        class="btn-icon"
                                        title="<?php #echo $cat['is_featured'] ? 'Remove Featured' : 'Set Featured'; ?>">
                                        <i class="fas fa-star" style="<?php #echo $cat['is_featured'] ? 'color: var(--gold);' : ''; ?>"></i>
                                    </a>
                                    <a href="sub-categories.php?main_id=<?php #echo $cat['id']; ?>"
                                        class="btn-icon"
                                        title="View Sub Categories">
                                        <i class="fas fa-sitemap"></i>
                                    </a>
                                    <a href="?delete=<?php #echo $cat['id']; ?>"
                                        class="btn-icon delete"
                                        onclick="return confirmDelete('<?php #echo addslashes($cat['name']); ?>', <?php #echo $cat['sub_category_count']; ?>)"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td> -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== '' ? '&status=' . $status_filter : ''; ?><?php echo !empty($code_filter) ? '&code=' . urlencode($code_filter) : ''; ?>"
                            class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== '' ? '&status=' . $status_filter : ''; ?><?php echo !empty($code_filter) ? '&code=' . urlencode($code_filter) : ''; ?>"
                            class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $status_filter !== '' ? '&status=' . $status_filter : ''; ?><?php echo !empty($code_filter) ? '&code=' . urlencode($code_filter) : ''; ?>"
                            class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-sitemap"></i>
                <h3>No Main Categories Found</h3>
                <p>Create your first main category to organize your products.</p>
                <a href="main-category-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Category
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Copy the styles from your original file and add these */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }

    /* Category Icon */
    .icon-cell {
        width: 50px;
        text-align: center;
    }

    .category-icon {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }

    .no-icon {
        width: 40px;
        height: 40px;
        background: var(--teal-light);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        font-size: 20px;
        border: 1px solid var(--border);
    }

    /* Category Slug */
    .category-slug {
        color: #5f7d76;
        font-size: 11px;
        margin-top: 2px;
    }

    /* Status Badges */
    .badge-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-status.active {
        background: #e6f3f0;
        color: #0f4f44;
        border: 1px solid var(--teal);
    }

    .badge-status.inactive {
        background: #ffe6e6;
        color: #b02a37;
        border: 1px solid #dc3545;
    }

    .badge-featured {
        display: inline-block;
        padding: 2px 6px;
        background: var(--gold-light);
        color: var(--dark);
        font-size: 10px;
        font-weight: 600;
        border-radius: 4px;
        margin-top: 4px;
        border: 1px solid var(--gold);
    }

    .badge-code {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }

    /* Stats Badge */
    .stats-badge {
        display: flex;
        gap: 10px;
    }

    .stat-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
        color: #5f7d76;
        white-space: nowrap;
    }

    .stat-item i {
        color: var(--teal);
        font-size: 12px;
    }

    /* Status Group */
    .status-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .toggle-link {
        color: #5f7d76;
        text-decoration: none;
        opacity: 0.6;
        transition: all 0.2s;
    }

    .toggle-link:hover {
        opacity: 1;
        color: var(--teal);
    }

    /* Sort Order */
    .sort-order {
        display: inline-block;
        padding: 4px 8px;
        background: var(--teal-light);
        border-radius: 4px;
        font-weight: 600;
        font-size: 12px;
        color: var(--teal);
        border: 1px solid var(--border);
    }

    /* Keep all your original styles from documents.php */
    .main-content {
        padding: 25px;
    }

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
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-box {
        background: white;
        padding: 15px 25px;
        border: 1px solid var(--border);
        flex: 1;
        border-radius: 4px;
    }

    .stat-label {
        display: block;
        font-size: 13px;
        color: #5f7d76;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 600;
        color: var(--teal);
    }

    .filter-bar {
        background: white;
        padding: 15px;
        border: 1px solid var(--border);
        margin-bottom: 25px;
        border-radius: 4px;
    }

    .filter-form {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-group {
        flex: 2;
        min-width: 250px;
        position: relative;
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
        padding: 10px 10px 10px 40px;
        border: 1px solid var(--border);
        font-size: 14px;
        background: white;
        border-radius: 4px;
    }

    .filter-group {
        flex: 1;
        min-width: 150px;
    }

    .filter-select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border);
        background: white;
        border-radius: 4px;
    }

    .table-container {
        background: white;
        border: 1px solid var(--border);
        overflow-x: auto;
        border-radius: 4px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
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

    .alert-success {
        background: #e6f3f0;
        color: #0f4f44;
        border-left-color: var(--teal);
    }

    .alert-error {
        background: #ffe6e6;
        color: #b02a37;
        border-left-color: #dc3545;
    }

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

    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .stats-row {
            flex-direction: column;
        }

        .stats-badge {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(name, subCount) {
        let message = `Are you sure you want to delete "<strong>${name}</strong>"?`;
        if (subCount > 0) {
            message += `<br><br><span style="color: #dc3545;">Warning: This category has ${subCount} sub-categories!</span>`;
        }

        return Swal.fire({
            title: 'Delete Category?',
            html: message,
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

    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.style.display = 'none', 300);
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ob_end_flush(); ?>