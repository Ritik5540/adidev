<?php
// sub-categories.php - Sub Categories listing page
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Handle Add/Edit operations first (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle Add Sub Category
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $main_category_id = intval($_POST['main_category_id']);
        $name = $conn->real_escape_string(trim($_POST['name']));
        $slug = $conn->real_escape_string(trim($_POST['slug']));
        $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/categories/subcategories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $upload_path = $upload_dir . $image;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Image uploaded successfully
            } else {
                $image = '';
            }
        }
        
        $sql = "INSERT INTO sub_categories (main_category_id, name, slug, description, image, sort_order, is_active, show_in_menu, is_featured) 
                VALUES ($main_category_id, '$name', '$slug', '$description', '$image', $sort_order, $is_active, $show_in_menu, $is_featured)";
        
        if ($conn->query($sql)) {
            $_SESSION['success_message'] = "Sub category added successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
        }
        
        header("Location: sub-categories.php" . (isset($_GET['main_id']) ? "?main_id=" . $_GET['main_id'] : ""));
        exit;
    }
    
    // Handle Edit Sub Category
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $main_category_id = intval($_POST['main_category_id']);
        $name = $conn->real_escape_string(trim($_POST['name']));
        $slug = $conn->real_escape_string(trim($_POST['slug']));
        $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $show_in_menu = isset($_POST['show_in_menu']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        }
        
        // Handle image upload
        $image_sql = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/categories/subcategories/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Delete old image
            $old_result = $conn->query("SELECT image FROM sub_categories WHERE id = $id");
            if ($old_row = $old_result->fetch_assoc()) {
                if (!empty($old_row['image']) && file_exists($upload_dir . $old_row['image'])) {
                    unlink($upload_dir . $old_row['image']);
                }
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $upload_path = $upload_dir . $image;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_sql = ", image = '$image'";
            }
        }
        
        $sql = "UPDATE sub_categories SET 
                main_category_id = $main_category_id,
                name = '$name',
                slug = '$slug',
                description = '$description',
                sort_order = $sort_order,
                is_active = $is_active,
                show_in_menu = $show_in_menu,
                is_featured = $is_featured
                $image_sql
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            $_SESSION['success_message'] = "Sub category updated successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
        }
        
        header("Location: sub-categories.php" . (isset($_GET['main_id']) ? "?main_id=" . $_GET['main_id'] : ""));
        exit;
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if sub-category has products
    $check_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE sub_category_id = $delete_id");
    $product_count = $check_products->fetch_assoc()['total'];
    
    if ($product_count > 0) {
        $_SESSION['error_message'] = "Cannot delete sub-category because it has $product_count products. Please delete products first.";
    } else {
        // Delete image file
        $result = $conn->query("SELECT image FROM sub_categories WHERE id = $delete_id");
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['image']) && file_exists('../uploads/categories/subcategories/' . $row['image'])) {
                unlink('../uploads/categories/subcategories/' . $row['image']);
            }
        }
        
        $conn->query("DELETE FROM sub_categories WHERE id = $delete_id");
        $_SESSION['success_message'] = "Sub category deleted successfully";
    }
    
    header("Location: sub-categories.php" . (isset($_GET['main_id']) ? "?main_id=" . $_GET['main_id'] : ""));
    exit;
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_status = intval($_GET['current']);
    $new_status = $current_status ? 0 : 1;
    
    $conn->query("UPDATE sub_categories SET is_active = $new_status WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Sub category status updated successfully";
    header("Location: sub-categories.php" . (isset($_GET['main_id']) ? "?main_id=" . $_GET['main_id'] : ""));
    exit;
}

// Handle Featured Toggle
if (isset($_GET['toggle_featured'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_featured = intval($_GET['current']);
    $new_featured = $current_featured ? 0 : 1;
    
    $conn->query("UPDATE sub_categories SET is_featured = $new_featured WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Sub category featured status updated successfully";
    header("Location: sub-categories.php" . (isset($_GET['main_id']) ? "?main_id=" . $_GET['main_id'] : ""));
    exit;
}

// NOW include header and sidebar (after all redirects)
include 'layout/header.php';
include 'layout/sidebar.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Get main category filter
$main_category_id = isset($_GET['main_id']) ? intval($_GET['main_id']) : 0;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$conditions = [];
if ($main_category_id > 0) {
    $conditions[] = "main_category_id = $main_category_id";
}
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $conditions[] = "(name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($status_filter !== '') {
    $status_filter = intval($status_filter);
    $conditions[] = "is_active = $status_filter";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM sub_categories $where_clause");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch sub categories
$sub_categories = [];
$sql = "SELECT sc.*, mc.name as main_category_name, mc.category_code 
        FROM sub_categories sc
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        $where_clause 
        ORDER BY sc.sort_order ASC, sc.name ASC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Get product count
        $prod_result = $conn->query("SELECT COUNT(*) as total FROM products WHERE sub_category_id = {$row['id']}");
        $row['product_count'] = $prod_result->fetch_assoc()['total'];
        
        $sub_categories[] = $row;
    }
}

// Get main categories for filter and add form
$main_categories = [];
$mc_result = $conn->query("SELECT id, name, category_code FROM main_categories WHERE is_active = 1 ORDER BY name ASC");
if ($mc_result) {
    while ($row = $mc_result->fetch_assoc()) {
        $main_categories[] = $row;
    }
}

// Get current main category name if filtered
$current_main = '';
if ($main_category_id > 0) {
    $mc = $conn->query("SELECT name FROM main_categories WHERE id = $main_category_id")->fetch_assoc();
    $current_main = $mc['name'] ?? '';
}

// Get active count
$active_count = $conn->query("SELECT COUNT(*) as total FROM sub_categories WHERE is_active = 1")->fetch_assoc()['total'];

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Status badge function
function getStatusBadge($status) {
    return $status ? 
        '<span class="badge-status active"><i class="fas fa-check-circle"></i> Active</span>' : 
        '<span class="badge-status inactive"><i class="fas fa-times-circle"></i> Inactive</span>';
}

// Featured badge function
function getFeaturedBadge($featured) {
    return $featured ? 
        '<span class="badge-featured"><i class="fas fa-star"></i> Featured</span>' : '';
}

// Get category code badge
function getCodeBadge($code) {
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
                <i class="fas fa-folder-open"></i>
                Sub Categories Management
                <?php if (!empty($current_main)): ?>
                    <span style="font-size: 16px; color: var(--gold); margin-left: 10px;">(<?php echo htmlspecialchars($current_main); ?>)</span>
                <?php endif; ?>
            </h2>
            <div class="header-actions">
                <button onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Sub Category
                </button>
                <a href="main-categories.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Main Categories
                </a>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total Sub Categories</span>
                <span class="stat-number"><?php echo $total_records; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Active Sub Categories</span>
                <span class="stat-number"><?php echo $active_count; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Main Categories</span>
                <span class="stat-number"><?php echo count($main_categories); ?></span>
            </div>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <?php if ($main_category_id > 0): ?>
                    <input type="hidden" name="main_id" value="<?php echo $main_category_id; ?>">
                <?php endif; ?>
                
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search sub categories..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Apply</button>
                
                <?php if (!empty($search) || $status_filter !== ''): ?>
                    <a href="?<?php echo $main_category_id > 0 ? 'main_id=' . $main_category_id : ''; ?>" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Sub Categories Table -->
        <?php if (!empty($sub_categories)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Sub Category</th>
                            <th>Main Category</th>
                            <th>Code</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Sort</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sub_categories as $sub): ?>
                            <tr>
                                <td>#<?php echo $sub['id']; ?></td>
                                <td class="icon-cell">
                                    <?php if (!empty($sub['image'])): ?>
                                        <img src="../uploads/categories/subcategories/<?php echo $sub['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($sub['name']); ?>"
                                             class="category-icon"
                                             onerror="this.src='../assets/images/no-image.png';">
                                    <?php else: ?>
                                        <div class="no-icon">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($sub['name']); ?></strong>
                                    <div class="category-slug">
                                        <small><?php echo htmlspecialchars($sub['slug']); ?></small>
                                    </div>
                                    <?php echo getFeaturedBadge($sub['is_featured']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($sub['main_category_name'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <?php echo getCodeBadge($sub['category_code'] ?? ''); ?>
                                </td>
                                <td>
                                    <span class="stat-item">
                                        <i class="fas fa-box"></i> <?php echo $sub['product_count']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="status-group">
                                        <?php echo getStatusBadge($sub['is_active']); ?>
                                        <a href="?toggle_status=1&toggle_id=<?php echo $sub['id']; ?>&current=<?php echo $sub['is_active']; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                           class="toggle-link" 
                                           title="Toggle Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span class="sort-order">
                                        <?php echo $sub['sort_order']; ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="javascript:void(0);" 
                                       onclick="openEditModal(<?php echo htmlspecialchars(json_encode($sub)); ?>)"
                                       class="btn-icon" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?toggle_featured=1&toggle_id=<?php echo $sub['id']; ?>&current=<?php echo $sub['is_featured']; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                       class="btn-icon" 
                                       title="<?php echo $sub['is_featured'] ? 'Remove Featured' : 'Set Featured'; ?>">
                                        <i class="fas fa-star" style="<?php echo $sub['is_featured'] ? 'color: var(--gold);' : ''; ?>"></i>
                                    </a>
                                    <a href="products.php?sub_id=<?php echo $sub['id']; ?>" 
                                       class="btn-icon" 
                                       title="View Products">
                                        <i class="fas fa-boxes"></i>
                                    </a>
                                    <a href="?delete=<?php echo $sub['id']; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                       class="btn-icon delete" 
                                       onclick="return confirmDelete('<?php echo addslashes($sub['name']); ?>', <?php echo $sub['product_count']; ?>)"
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
                    if ($main_category_id > 0) $query_params[] = 'main_id=' . $main_category_id;
                    if (!empty($search)) $query_params[] = 'search=' . urlencode($search);
                    if ($status_filter !== '') $query_params[] = 'status=' . $status_filter;
                    $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1 . $query_string; ?>" class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i . $query_string; ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1 . $query_string; ?>" class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>No Sub Categories Found</h3>
                <p>Create your first sub category to organize your products.</p>
                <button onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Sub Category
                </button>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Add Sub Category Modal -->
<div id="addModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Sub Category</h3>
            <span class="close" onclick="closeAddModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <?php if ($main_category_id > 0): ?>
                <input type="hidden" name="main_category_id" value="<?php echo $main_category_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Main Category <span class="required">*</span></label>
                <select name="main_category_id" required class="form-control" <?php echo $main_category_id > 0 ? 'disabled' : ''; ?>>
                    <option value="">Select Main Category</option>
                    <?php foreach ($main_categories as $mc): ?>
                        <option value="<?php echo $mc['id']; ?>" <?php echo $main_category_id == $mc['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mc['name']); ?> (<?php echo $mc['category_code']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($main_category_id > 0): ?>
                    <input type="hidden" name="main_category_id" value="<?php echo $main_category_id; ?>">
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" name="name" required class="form-control" placeholder="Enter sub category name">
            </div>
            
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" class="form-control" placeholder="Leave empty to auto-generate">
                <small>URL-friendly version of the name</small>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
            </div>
            
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small>Recommended size: 200x200px</small>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                
                <div class="form-group half">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="is_active" checked> Active
                        </label>
                        <label>
                            <input type="checkbox" name="show_in_menu" checked> Show in Menu
                        </label>
                        <label>
                            <input type="checkbox" name="is_featured"> Featured
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeAddModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Sub Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Sub Category Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Sub Category</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="form-group">
                <label>Main Category <span class="required">*</span></label>
                <select name="main_category_id" id="edit_main_category_id" required class="form-control">
                    <option value="">Select Main Category</option>
                    <?php foreach ($main_categories as $mc): ?>
                        <option value="<?php echo $mc['id']; ?>">
                            <?php echo htmlspecialchars($mc['name']); ?> (<?php echo $mc['category_code']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" name="name" id="edit_name" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="slug" id="edit_slug" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Current Image</label>
                <div id="current_image_container"></div>
                <label>Change Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                </div>
                
                <div class="form-group half">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="is_active" id="edit_is_active"> Active
                        </label>
                        <label>
                            <input type="checkbox" name="show_in_menu" id="edit_show_in_menu"> Show in Menu
                        </label>
                        <label>
                            <input type="checkbox" name="is_featured" id="edit_is_featured"> Featured
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Sub Category</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Copy all styles from main-categories.php and add these modal styles */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        overflow-y: auto;
    }
    
    .modal-content {
        background-color: #fff;
        margin: 50px auto;
        padding: 0;
        border: 1px solid var(--border);
        width: 90%;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .modal-header {
        padding: 15px 20px;
        background: var(--teal);
        color: white;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
    }
    
    .modal-header .close {
        color: white;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .modal-header .close:hover {
        color: var(--gold);
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .modal-footer {
        padding: 15px 20px;
        background: var(--gray-bg);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        border-radius: 0 0 8px 8px;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 15px;
        padding: 0 20px;
    }
    
    .form-row {
        display: flex;
        gap: 15px;
        padding: 0 20px;
    }
    
    .form-group.half {
        flex: 1;
        padding: 0;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 13px;
        color: var(--dark);
    }
    
    .form-group .required {
        color: #dc3545;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.2s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(26,104,91,0.1);
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    
    .checkbox-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        padding-top: 5px;
    }
    
    .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: normal;
        cursor: pointer;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
    
    .form-group small {
        display: block;
        color: #6c757d;
        font-size: 11px;
        margin-top: 4px;
    }
    
    #current_image_container {
        margin: 10px 0;
    }
    
    #current_image_container img {
        max-width: 100px;
        max-height: 100px;
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 5px;
    }
    
    /* Keep all original styles from main-categories.php */
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
        min-width: 1200px;
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
    
    .category-slug {
        color: #5f7d76;
        font-size: 11px;
        margin-top: 2px;
    }
    
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
        .form-row {
            flex-direction: column;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Modal functions
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }
    
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }
    
    function openEditModal(sub) {
        document.getElementById('edit_id').value = sub.id;
        document.getElementById('edit_main_category_id').value = sub.main_category_id;
        document.getElementById('edit_name').value = sub.name;
        document.getElementById('edit_slug').value = sub.slug || '';
        document.getElementById('edit_description').value = sub.description || '';
        document.getElementById('edit_sort_order').value = sub.sort_order || 0;
        document.getElementById('edit_is_active').checked = sub.is_active == 1;
        document.getElementById('edit_show_in_menu').checked = sub.show_in_menu == 1;
        document.getElementById('edit_is_featured').checked = sub.is_featured == 1;
        
        // Show current image
        const imageContainer = document.getElementById('current_image_container');
        if (sub.image) {
            imageContainer.innerHTML = `<img src="../uploads/categories/subcategories/${sub.image}" alt="Current Image">`;
        } else {
            imageContainer.innerHTML = '<p>No image</p>';
        }
        
        document.getElementById('editModal').style.display = 'block';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('addModal')) {
            closeAddModal();
        }
        if (event.target == document.getElementById('editModal')) {
            closeEditModal();
        }
    }
    
    // Delete confirmation
    function confirmDelete(name, productCount) {
        let message = `Are you sure you want to delete "<strong>${name}</strong>"?`;
        if (productCount > 0) {
            message += `<br><br><span style="color: #dc3545;">Warning: This sub-category has ${productCount} products!</span>`;
        }
        
        return Swal.fire({
            title: 'Delete Sub Category?',
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
    
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.style.display = 'none', 300);
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ?>