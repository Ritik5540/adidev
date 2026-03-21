<?php
// products.php - Products listing page with filtering
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');


// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if product has order items
    $check_orders = $conn->query("SELECT COUNT(*) as total FROM order_items WHERE product_id = $delete_id");
    $order_count = $check_orders->fetch_assoc()['total'];
    
    if ($order_count > 0) {
        $_SESSION['error_message'] = "Cannot delete product because it has $order_count orders. You can deactivate it instead.";
    } else {
        // Delete product images
        $result = $conn->query("SELECT main_image, hover_image FROM products WHERE id = $delete_id");
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['main_image']) && file_exists('../uploads/products/main/' . $row['main_image'])) {
                unlink('../uploads/products/main/' . $row['main_image']);
            }
            if (!empty($row['hover_image']) && file_exists('../uploads/products/hover/' . $row['hover_image'])) {
                unlink('../uploads/products/hover/' . $row['hover_image']);
            }
        }
        
        // Delete gallery images
        $gallery_result = $conn->query("SELECT image_url FROM product_images WHERE product_id = $delete_id");
        while ($gallery = $gallery_result->fetch_assoc()) {
            if (!empty($gallery['image_url']) && file_exists('../uploads/products/gallery/' . $gallery['image_url'])) {
                unlink('../uploads/products/gallery/' . $gallery['image_url']);
            }
        }
        $conn->query("DELETE FROM product_images WHERE product_id = $delete_id");
        
        // Delete variants
        $conn->query("DELETE FROM product_variants WHERE product_id = $delete_id");
        
        // Delete product
        $conn->query("DELETE FROM products WHERE id = $delete_id");
        $_SESSION['success_message'] = "Product deleted successfully";
    }
    
    header("Location: products.php" . (isset($_GET['sub_id']) ? "?sub_id=" . $_GET['sub_id'] : ""));
    exit;
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_status = intval($_GET['current']);
    $new_status = $current_status ? 0 : 1;
    
    $conn->query("UPDATE products SET is_active = $new_status WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Product status updated successfully";
    header("Location: products.php" . (isset($_GET['sub_id']) ? "?sub_id=" . $_GET['sub_id'] : ""));
    exit;
}

// Handle Featured Toggle
if (isset($_GET['toggle_featured'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_featured = intval($_GET['current']);
    $new_featured = $current_featured ? 0 : 1;
    
    $conn->query("UPDATE products SET is_featured = $new_featured WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Product featured status updated successfully";
    header("Location: products.php" . (isset($_GET['sub_id']) ? "?sub_id=" . $_GET['sub_id'] : ""));
    exit;
}

// Handle Bulk Only Toggle
if (isset($_GET['toggle_bulk'])) {
    $toggle_id = intval($_GET['toggle_id']);
    $current_bulk = intval($_GET['current']);
    $new_bulk = $current_bulk ? 0 : 1;
    
    $conn->query("UPDATE products SET is_bulk_only = $new_bulk WHERE id = $toggle_id");
    $_SESSION['success_message'] = "Product bulk status updated successfully";
    header("Location: products.php" . (isset($_GET['sub_id']) ? "?sub_id=" . $_GET['sub_id'] : ""));
    exit;
}

// NOW include header and sidebar (after all redirects)
include 'layout/header.php';
include 'layout/sidebar.php';

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Get filter parameters
$sub_category_id = isset($_GET['sub_id']) ? intval($_GET['sub_id']) : 0;
$main_category_id = isset($_GET['main_id']) ? intval($_GET['main_id']) : 0;
$product_type = isset($_GET['type']) ? trim($_GET['type']) : '';
$stock_status = isset($_GET['stock']) ? trim($_GET['stock']) : '';

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$conditions = [];
if ($sub_category_id > 0) {
    $conditions[] = "p.sub_category_id = $sub_category_id";
}
if ($main_category_id > 0) {
    $conditions[] = "sc.main_category_id = $main_category_id";
}
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $conditions[] = "(p.name LIKE '%$search%' OR p.product_code LIKE '%$search%' OR p.short_description LIKE '%$search%' OR p.search_keywords LIKE '%$search%')";
}
if ($status_filter !== '') {
    $status_filter = intval($status_filter);
    $conditions[] = "p.is_active = $status_filter";
}
if (!empty($product_type)) {
    $product_type = $conn->real_escape_string($product_type);
    $conditions[] = "p.product_type = '$product_type'";
}
if (!empty($stock_status)) {
    if ($stock_status === 'in_stock') {
        $conditions[] = "p.stock_quantity > p.low_stock_threshold";
    } elseif ($stock_status === 'low_stock') {
        $conditions[] = "p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0";
    } elseif ($stock_status === 'out_of_stock') {
        $conditions[] = "p.stock_quantity <= 0";
    }
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM products p 
                              LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id 
                              $where_clause");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch products
$products = [];
$sql = "SELECT p.*, 
               sc.name as sub_category_name, 
               mc.name as main_category_name,
               mc.category_code,
               (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
               (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count,
               (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as review_count,
               (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id AND status = 'approved') as avg_rating
        FROM products p
        LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        $where_clause 
        ORDER BY p.id DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get sub categories for filter
$sub_categories = [];
$sub_sql = "SELECT sc.id, sc.name, mc.name as main_name, mc.category_code 
            FROM sub_categories sc
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            WHERE sc.is_active = 1 
            ORDER BY mc.name, sc.name";
$sub_result = $conn->query($sub_sql);
if ($sub_result) {
    while ($row = $sub_result->fetch_assoc()) {
        $sub_categories[] = $row;
    }
}

// Get main categories for filter
$main_categories = [];
$mc_result = $conn->query("SELECT id, name, category_code FROM main_categories WHERE is_active = 1 ORDER BY name");
if ($mc_result) {
    while ($row = $mc_result->fetch_assoc()) {
        $main_categories[] = $row;
    }
}

// Get current sub category name if filtered
$current_sub = '';
$current_main = '';
if ($sub_category_id > 0) {
    $sub = $conn->query("SELECT sc.name, mc.name as main_name 
                        FROM sub_categories sc
                        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
                        WHERE sc.id = $sub_category_id")->fetch_assoc();
    $current_sub = $sub['name'] ?? '';
    $current_main = $sub['main_name'] ?? '';
} elseif ($main_category_id > 0) {
    $mc = $conn->query("SELECT name FROM main_categories WHERE id = $main_category_id")->fetch_assoc();
    $current_main = $mc['name'] ?? '';
}

// Get counts for stats
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$active_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1")->fetch_assoc()['total'];
$featured_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_featured = 1")->fetch_assoc()['total'];
$bulk_products = $conn->query("SELECT COUNT(*) as total FROM products WHERE is_bulk_only = 1")->fetch_assoc()['total'];
$out_of_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= 0")->fetch_assoc()['total'];
$low_stock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= low_stock_threshold AND stock_quantity > 0")->fetch_assoc()['total'];

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

// Bulk badge function
function getBulkBadge($bulk) {
    return $bulk ? 
        '<span class="badge-bulk"><i class="fas fa-boxes"></i> Bulk Only</span>' : '';
}

// Stock badge function
function getStockBadge($stock, $low_threshold) {
    if ($stock <= 0) {
        return '<span class="badge-stock out"><i class="fas fa-times-circle"></i> Out of Stock</span>';
    } elseif ($stock <= $low_threshold) {
        return '<span class="badge-stock low"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>';
    } else {
        return '<span class="badge-stock in"><i class="fas fa-check-circle"></i> In Stock</span>';
    }
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

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Get product type badge
function getProductTypeBadge($type) {
    $types = [
        'simple' => ['label' => 'Simple', 'color' => '#6c757d'],
        'variable' => ['label' => 'Variable', 'color' => '#17a2b8'],
        'bundle' => ['label' => 'Bundle', 'color' => '#fd7e14']
    ];
    $type_info = $types[$type] ?? ['label' => ucfirst($type), 'color' => '#6c757d'];
    return "<span class=\"badge-type\" style=\"background: {$type_info['color']}20; color: {$type_info['color']}; border-color: {$type_info['color']}\">{$type_info['label']}</span>";
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
                <i class="fas fa-box"></i>
                Products Management
                <?php if (!empty($current_sub)): ?>
                    <span style="font-size: 16px; color: var(--gold); margin-left: 10px;">
                        <?php echo htmlspecialchars($current_main); ?> → <?php echo htmlspecialchars($current_sub); ?>
                    </span>
                <?php elseif (!empty($current_main)): ?>
                    <span style="font-size: 16px; color: var(--gold); margin-left: 10px;">
                        <?php echo htmlspecialchars($current_main); ?>
                    </span>
                <?php endif; ?>
            </h2>
            <div class="header-actions">
                <a href="product-add.php<?php echo $sub_category_id > 0 ? '?sub_id=' . $sub_category_id : ''; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <?php if ($sub_category_id > 0 || $main_category_id > 0): ?>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total Products</span>
                <span class="stat-number"><?php echo $total_products; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Active</span>
                <span class="stat-number"><?php echo $active_products; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Featured</span>
                <span class="stat-number"><?php echo $featured_products; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Bulk Only</span>
                <span class="stat-number"><?php echo $bulk_products; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Low Stock</span>
                <span class="stat-number" style="color: #f59e0b;"><?php echo $low_stock; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Out of Stock</span>
                <span class="stat-number" style="color: #dc3545;"><?php echo $out_of_stock; ?></span>
            </div>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <?php if ($sub_category_id > 0): ?>
                    <input type="hidden" name="sub_id" value="<?php echo $sub_category_id; ?>">
                <?php endif; ?>
                <?php if ($main_category_id > 0): ?>
                    <input type="hidden" name="main_id" value="<?php echo $main_category_id; ?>">
                <?php endif; ?>
                
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search products by name, code, keywords..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="main_id" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Main Categories</option>
                        <?php foreach ($main_categories as $mc): ?>
                            <option value="<?php echo $mc['id']; ?>" <?php echo $main_category_id == $mc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mc['name']); ?> (<?php echo $mc['category_code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="sub_id" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Sub Categories</option>
                        <?php 
                        $current_main_for_sub = $main_category_id;
                        foreach ($sub_categories as $sc): 
                            if ($current_main_for_sub > 0 && strpos($sc['main_name'], '') === false) continue;
                        ?>
                            <option value="<?php echo $sc['id']; ?>" <?php echo $sub_category_id == $sc['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sc['main_name']); ?> → <?php echo htmlspecialchars($sc['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <option value="simple" <?php echo $product_type == 'simple' ? 'selected' : ''; ?>>Simple</option>
                        <option value="variable" <?php echo $product_type == 'variable' ? 'selected' : ''; ?>>Variable</option>
                        <option value="bundle" <?php echo $product_type == 'bundle' ? 'selected' : ''; ?>>Bundle</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <select name="stock" class="filter-select">
                        <option value="">All Stock</option>
                        <option value="in_stock" <?php echo $stock_status == 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="low_stock" <?php echo $stock_status == 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $stock_status == 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
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
                
                <?php if (!empty($search) || $status_filter !== '' || !empty($product_type) || !empty($stock_status) || $main_category_id > 0 || $sub_category_id > 0): ?>
                    <a href="products.php" class="btn btn-secondary">Clear All</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Table -->
        <?php if (!empty($products)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?php echo $product['id']; ?></td>
                                <td class="icon-cell">
                                    <?php if (!empty($product['main_image'])): ?>
                                        <img src="../uploads/products/main/<?php echo $product['main_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="product-icon"
                                             onerror="this.src='../assets/images/track_icon_1.png';">
                                    <?php else: ?>
                                        <div class="no-icon">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product['image_count'] > 1): ?>
                                        <span class="image-count">+<?php echo $product['image_count']-1; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <div class="product-meta">
                                        <small>Code: <?php echo htmlspecialchars($product['product_code']); ?></small>
                                        <?php if ($product['has_variants']): ?>
                                            <span class="variant-badge" title="<?php echo $product['variant_count']; ?> Variants">
                                                <i class="fas fa-code-branch"></i> <?php echo $product['variant_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-badges">
                                        <?php echo getFeaturedBadge($product['is_featured']); ?>
                                        <?php echo getBulkBadge($product['is_bulk_only']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="category-info">
                                        <span class="main-cat"><?php echo htmlspecialchars($product['main_category_name'] ?? 'N/A'); ?></span>
                                        <span class="sub-cat"><?php echo htmlspecialchars($product['sub_category_name'] ?? 'N/A'); ?></span>
                                        <?php echo getCodeBadge($product['category_code'] ?? ''); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-info">
                                        <span class="mrp">MRP: <?php echo formatPrice($product['mrp']); ?></span>
                                        <?php if ($product['base_retail_price']): ?>
                                            <span class="retail">Retail: <?php echo formatPrice($product['base_retail_price']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($product['base_wholesale_price']): ?>
                                            <span class="wholesale">Wholesale: <?php echo formatPrice($product['base_wholesale_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo getStockBadge($product['stock_quantity'], $product['low_stock_threshold']); ?>
                                    <div class="stock-qty">Qty: <?php echo $product['stock_quantity']; ?></div>
                                </td>
                                <td>
                                    <?php echo getProductTypeBadge($product['product_type']); ?>
                                    <?php if ($product['review_count'] > 0): ?>
                                        <div class="rating">
                                            <i class="fas fa-star" style="color: var(--gold);"></i>
                                            <?php echo number_format($product['avg_rating'], 1); ?> (<?php echo $product['review_count']; ?>)
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="status-group">
                                        <?php echo getStatusBadge($product['is_active']); ?>
                                        <a href="?toggle_status=1&toggle_id=<?php echo $product['id']; ?>&current=<?php echo $product['is_active']; ?><?php echo $sub_category_id > 0 ? '&sub_id=' . $sub_category_id : ''; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                           class="toggle-link" 
                                           title="Toggle Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="actions-cell">
                                    <a href="product-view.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?toggle_featured=1&toggle_id=<?php echo $product['id']; ?>&current=<?php echo $product['is_featured']; ?><?php echo $sub_category_id > 0 ? '&sub_id=' . $sub_category_id : ''; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                       class="btn-icon" 
                                       title="<?php echo $product['is_featured'] ? 'Remove Featured' : 'Set Featured'; ?>">
                                        <i class="fas fa-star" style="<?php echo $product['is_featured'] ? 'color: var(--gold);' : ''; ?>"></i>
                                    </a>
                                    <a href="?toggle_bulk=1&toggle_id=<?php echo $product['id']; ?>&current=<?php echo $product['is_bulk_only']; ?><?php echo $sub_category_id > 0 ? '&sub_id=' . $sub_category_id : ''; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                       class="btn-icon" 
                                       title="<?php echo $product['is_bulk_only'] ? 'Remove Bulk Only' : 'Set Bulk Only'; ?>">
                                        <i class="fas fa-boxes" style="<?php echo $product['is_bulk_only'] ? 'color: var(--teal);' : ''; ?>"></i>
                                    </a>
                                    <?php if ($product['has_variants']): ?>
                                        <a href="product-variants.php?product_id=<?php echo $product['id']; ?>" 
                                           class="btn-icon" 
                                           title="Manage Variants">
                                            <i class="fas fa-code-branch"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="product-images.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" 
                                       title="Manage Images">
                                        <i class="fas fa-images"></i>
                                    </a>
                                    <a href="?delete=<?php echo $product['id']; ?><?php echo $sub_category_id > 0 ? '&sub_id=' . $sub_category_id : ''; ?><?php echo $main_category_id > 0 ? '&main_id=' . $main_category_id : ''; ?>" 
                                       class="btn-icon delete" 
                                       onclick="return confirmDelete('<?php echo addslashes($product['name']); ?>', '<?php echo $product['product_code']; ?>')"
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
                    if ($sub_category_id > 0) $query_params[] = 'sub_id=' . $sub_category_id;
                    if ($main_category_id > 0) $query_params[] = 'main_id=' . $main_category_id;
                    if (!empty($search)) $query_params[] = 'search=' . urlencode($search);
                    if ($status_filter !== '') $query_params[] = 'status=' . $status_filter;
                    if (!empty($product_type)) $query_params[] = 'type=' . urlencode($product_type);
                    if (!empty($stock_status)) $query_params[] = 'stock=' . urlencode($stock_status);
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
                <i class="fas fa-box-open"></i>
                <h3>No Products Found</h3>
                <p>Add your first product to get started.</p>
                <a href="product-add.php<?php echo $sub_category_id > 0 ? '?sub_id=' . $sub_category_id : ''; ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Product
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Copy all styles from previous files and add these product-specific styles */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }
    
    /* Product Icon */
    .product-icon {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    
    .image-count {
        position: absolute;
        bottom: -5px;
        right: -5px;
        background: var(--teal);
        color: white;
        font-size: 9px;
        padding: 2px 4px;
        border-radius: 10px;
        min-width: 18px;
        text-align: center;
    }
    
    .icon-cell {
        position: relative;
    }
    
    /* Product Meta */
    .product-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 2px;
        flex-wrap: wrap;
    }
    
    .product-meta small {
        color: #5f7d76;
        font-size: 11px;
    }
    
    .variant-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        padding: 2px 6px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        border: 1px solid #1976d2;
    }
    
    /* Product Badges */
    .product-badges {
        display: flex;
        gap: 4px;
        margin-top: 4px;
        flex-wrap: wrap;
    }
    
    .badge-bulk {
        display: inline-block;
        padding: 2px 6px;
        background: #e8f5e9;
        color: #2e7d32;
        font-size: 10px;
        font-weight: 600;
        border-radius: 4px;
        border: 1px solid #2e7d32;
    }
    
    /* Category Info */
    .category-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .main-cat {
        font-size: 12px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .sub-cat {
        font-size: 11px;
        color: #5f7d76;
    }
    
    /* Price Info */
    .price-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .mrp {
        font-size: 12px;
        color: #6c757d;
        text-decoration: line-through;
    }
    
    .retail {
        font-size: 13px;
        font-weight: 600;
        color: var(--teal);
    }
    
    .wholesale {
        font-size: 12px;
        color: #f59e0b;
    }
    
    /* Stock Badges */
    .badge-stock {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .badge-stock.in {
        background: #e6f3f0;
        color: #0f4f44;
        border: 1px solid var(--teal);
    }
    
    .badge-stock.low {
        background: #fff2d6;
        color: #b45b0b;
        border: 1px solid #f59e0b;
    }
    
    .badge-stock.out {
        background: #ffe6e6;
        color: #b02a37;
        border: 1px solid #dc3545;
    }
    
    .stock-qty {
        font-size: 11px;
        color: #5f7d76;
        margin-top: 2px;
    }
    
    /* Badge Type */
    .badge-type {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }
    
    /* Rating */
    .rating {
        font-size: 11px;
        color: #5f7d76;
        margin-top: 2px;
        white-space: nowrap;
    }
    
    /* Stats Row - Updated for 6 items */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }
    
    .stat-box {
        background: white;
        padding: 12px 15px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
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
        flex-wrap: wrap;
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
        flex: 3;
        min-width: 300px;
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
        cursor: pointer;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: var(--teal);
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
        min-width: 1400px;
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
        white-space: nowrap;
    }
    
    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    
    .badge-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
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
        border: 1px solid var(--gold);
    }
    
    .badge-code {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        border: 1px solid;
        width: fit-content;
        margin-top: 2px;
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
    
    .actions-cell {
        white-space: nowrap;
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
    
    @media (max-width: 1200px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .search-group, .filter-group {
            width: 100%;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(name, code) {
        return Swal.fire({
            title: 'Delete Product?',
            html: `Are you sure you want to delete "<strong>${name}</strong>" (${code})?`,
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
    
    // Auto-submit filters when dropdowns change
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            if (this.name !== 'main_id' && this.name !== 'sub_id') {
                this.form.submit();
            }
        });
    });
</script>

<?php include 'layout/footer.php'; ?>