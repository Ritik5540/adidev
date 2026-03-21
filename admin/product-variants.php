<?php
// product-variants.php - Manage Product Variants
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Get product ID from URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($product_id <= 0) {
    $_SESSION['error_message'] = "Invalid product ID";
    header("Location: products.php");
    exit;
}

// Fetch product details
$product = null;
$sql = "SELECT p.*, sc.name as sub_category_name, mc.name as main_category_name 
        FROM products p
        LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE p.id = $product_id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "Product not found";
    header("Location: products.php");
    exit;
}

// Check if product can have variants
if ($product['product_type'] != 'variable') {
    $_SESSION['error_message'] = "This product is not set as variable type. Please change product type to 'variable' first.";
    header("Location: product-edit.php?id=" . $product_id);
    exit;
}

// Handle Add Variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $sku = $conn->real_escape_string(trim($_POST['sku']));
    $variant_code = $conn->real_escape_string(trim($_POST['variant_code'] ?? ''));
    $size = $conn->real_escape_string(trim($_POST['size'] ?? ''));
    $color = $conn->real_escape_string(trim($_POST['color'] ?? ''));
    $material = $conn->real_escape_string(trim($_POST['material'] ?? ''));
    $pattern = $conn->real_escape_string(trim($_POST['pattern'] ?? ''));
    $style = $conn->real_escape_string(trim($_POST['style'] ?? ''));
    
    // Build attributes JSON
    $attributes = [];
    if (!empty($size)) $attributes['size'] = $size;
    if (!empty($color)) $attributes['color'] = $color;
    if (!empty($material)) $attributes['material'] = $material;
    if (!empty($pattern)) $attributes['pattern'] = $pattern;
    if (!empty($style)) $attributes['style'] = $style;
    $attributes_json = json_encode($attributes);
    
    $retail_price = !empty($_POST['retail_price']) ? floatval($_POST['retail_price']) : NULL;
    $wholesale_price = !empty($_POST['wholesale_price']) ? floatval($_POST['wholesale_price']) : NULL;
    $cost_price = !empty($_POST['cost_price']) ? floatval($_POST['cost_price']) : NULL;
    $mrp = !empty($_POST['mrp']) ? floatval($_POST['mrp']) : NULL;
    $price_adjustment = !empty($_POST['price_adjustment']) ? floatval($_POST['price_adjustment']) : 0;
    
    $is_bulk_only = isset($_POST['is_bulk_only']) ? 1 : 0;
    $bulk_min_quantity = intval($_POST['bulk_min_quantity'] ?? 10);
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 5);
    $track_inventory = isset($_POST['track_inventory']) ? 1 : 0;
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : NULL;
    $dimensions = $conn->real_escape_string(trim($_POST['dimensions'] ?? ''));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Handle variant image upload
    $variant_image = '';
    if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/variants/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['variant_image']['name'], PATHINFO_EXTENSION);
        $variant_image = time() . '_variant_' . rand(1000, 9999) . '.' . $file_ext;
        move_uploaded_file($_FILES['variant_image']['tmp_name'], $upload_dir . $variant_image);
    }
    
    // Generate SKU if empty
    if (empty($sku)) {
        $sku = $product['product_code'] . '-' . strtoupper(substr($color, 0, 2) . substr($size, 0, 2)) . rand(100, 999);
    }
    
    // If this is set as default, unset other defaults
    if ($is_default) {
        $conn->query("UPDATE product_variants SET is_default = 0 WHERE product_id = $product_id");
    }
    
    $sql = "INSERT INTO product_variants (
        product_id, sku, variant_code, attributes, size, color, material, pattern, style,
        retail_price, wholesale_price, cost_price, mrp, price_adjustment,
        is_bulk_only, bulk_min_quantity, stock_quantity, low_stock_threshold, track_inventory,
        weight, dimensions, is_active, is_default, image
    ) VALUES (
        $product_id, '$sku', '$variant_code', '$attributes_json', '$size', '$color', '$material', '$pattern', '$style',
        " . ($retail_price !== NULL ? $retail_price : "NULL") . ", " . ($wholesale_price !== NULL ? $wholesale_price : "NULL") . ",
        " . ($cost_price !== NULL ? $cost_price : "NULL") . ", " . ($mrp !== NULL ? $mrp : "NULL") . ", $price_adjustment,
        $is_bulk_only, $bulk_min_quantity, $stock_quantity, $low_stock_threshold, $track_inventory,
        " . ($weight !== NULL ? $weight : "NULL") . ", '$dimensions', $is_active, $is_default, " . (!empty($variant_image) ? "'$variant_image'" : "NULL") . "
    )";
    
    if ($conn->query($sql)) {
        // Update product has_variants flag
        $conn->query("UPDATE products SET has_variants = 1 WHERE id = $product_id");
        $_SESSION['success_message'] = "Variant added successfully";
    } else {
        $_SESSION['error_message'] = "Error adding variant: " . $conn->error;
    }
    
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Handle Edit Variant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $variant_id = intval($_POST['variant_id']);
    $sku = $conn->real_escape_string(trim($_POST['sku']));
    $variant_code = $conn->real_escape_string(trim($_POST['variant_code'] ?? ''));
    $size = $conn->real_escape_string(trim($_POST['size'] ?? ''));
    $color = $conn->real_escape_string(trim($_POST['color'] ?? ''));
    $material = $conn->real_escape_string(trim($_POST['material'] ?? ''));
    $pattern = $conn->real_escape_string(trim($_POST['pattern'] ?? ''));
    $style = $conn->real_escape_string(trim($_POST['style'] ?? ''));
    
    // Build attributes JSON
    $attributes = [];
    if (!empty($size)) $attributes['size'] = $size;
    if (!empty($color)) $attributes['color'] = $color;
    if (!empty($material)) $attributes['material'] = $material;
    if (!empty($pattern)) $attributes['pattern'] = $pattern;
    if (!empty($style)) $attributes['style'] = $style;
    $attributes_json = json_encode($attributes);
    
    $retail_price = !empty($_POST['retail_price']) ? floatval($_POST['retail_price']) : NULL;
    $wholesale_price = !empty($_POST['wholesale_price']) ? floatval($_POST['wholesale_price']) : NULL;
    $cost_price = !empty($_POST['cost_price']) ? floatval($_POST['cost_price']) : NULL;
    $mrp = !empty($_POST['mrp']) ? floatval($_POST['mrp']) : NULL;
    $price_adjustment = !empty($_POST['price_adjustment']) ? floatval($_POST['price_adjustment']) : 0;
    
    $is_bulk_only = isset($_POST['is_bulk_only']) ? 1 : 0;
    $bulk_min_quantity = intval($_POST['bulk_min_quantity'] ?? 10);
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 5);
    $track_inventory = isset($_POST['track_inventory']) ? 1 : 0;
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : NULL;
    $dimensions = $conn->real_escape_string(trim($_POST['dimensions'] ?? ''));
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Handle variant image upload
    $image_sql = "";
    if (isset($_FILES['variant_image']) && $_FILES['variant_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/variants/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old image
        $old_result = $conn->query("SELECT image FROM product_variants WHERE id = $variant_id");
        if ($old_row = $old_result->fetch_assoc()) {
            if (!empty($old_row['image']) && file_exists($upload_dir . $old_row['image'])) {
                unlink($upload_dir . $old_row['image']);
            }
        }
        
        $file_ext = pathinfo($_FILES['variant_image']['name'], PATHINFO_EXTENSION);
        $variant_image = time() . '_variant_' . rand(1000, 9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['variant_image']['tmp_name'], $upload_dir . $variant_image)) {
            $image_sql = ", image = '$variant_image'";
        }
    }
    
    // If this is set as default, unset other defaults
    if ($is_default) {
        $conn->query("UPDATE product_variants SET is_default = 0 WHERE product_id = $product_id AND id != $variant_id");
    }
    
    $sql = "UPDATE product_variants SET 
        sku = '$sku',
        variant_code = '$variant_code',
        attributes = '$attributes_json',
        size = '$size',
        color = '$color',
        material = '$material',
        pattern = '$pattern',
        style = '$style',
        retail_price = " . ($retail_price !== NULL ? $retail_price : "NULL") . ",
        wholesale_price = " . ($wholesale_price !== NULL ? $wholesale_price : "NULL") . ",
        cost_price = " . ($cost_price !== NULL ? $cost_price : "NULL") . ",
        mrp = " . ($mrp !== NULL ? $mrp : "NULL") . ",
        price_adjustment = $price_adjustment,
        is_bulk_only = $is_bulk_only,
        bulk_min_quantity = $bulk_min_quantity,
        stock_quantity = $stock_quantity,
        low_stock_threshold = $low_stock_threshold,
        track_inventory = $track_inventory,
        weight = " . ($weight !== NULL ? $weight : "NULL") . ",
        dimensions = '$dimensions',
        is_active = $is_active,
        is_default = $is_default
        $image_sql
        WHERE id = $variant_id AND product_id = $product_id";
    
    if ($conn->query($sql)) {
        $_SESSION['success_message'] = "Variant updated successfully";
    } else {
        $_SESSION['error_message'] = "Error updating variant: " . $conn->error;
    }
    
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Handle Delete Variant
if (isset($_GET['delete'])) {
    $variant_id = intval($_GET['delete']);
    
    // Check if variant is used in orders
    $check_orders = $conn->query("SELECT COUNT(*) as total FROM order_items WHERE product_id = $product_id AND product_code LIKE '%$variant_id%'");
    
    // Get image to delete
    $img_result = $conn->query("SELECT image FROM product_variants WHERE id = $variant_id AND product_id = $product_id");
    if ($img_row = $img_result->fetch_assoc()) {
        if (!empty($img_row['image']) && file_exists('../uploads/products/variants/' . $img_row['image'])) {
            unlink('../uploads/products/variants/' . $img_row['image']);
        }
    }
    
    $conn->query("DELETE FROM product_variants WHERE id = $variant_id AND product_id = $product_id");
    $_SESSION['success_message'] = "Variant deleted successfully";
    
    // Check if any variants remain
    $remaining = $conn->query("SELECT COUNT(*) as total FROM product_variants WHERE product_id = $product_id")->fetch_assoc()['total'];
    if ($remaining == 0) {
        $conn->query("UPDATE products SET has_variants = 0 WHERE id = $product_id");
    }
    
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Handle Bulk Price Update
if (isset($_POST['bulk_update_prices'])) {
    $price_increase = floatval($_POST['price_increase']);
    $apply_to = $_POST['apply_to'] ?? 'retail';
    
    if ($apply_to == 'retail') {
        $conn->query("UPDATE product_variants SET retail_price = retail_price + $price_increase WHERE product_id = $product_id");
    } elseif ($apply_to == 'wholesale') {
        $conn->query("UPDATE product_variants SET wholesale_price = wholesale_price + $price_increase WHERE product_id = $product_id");
    } elseif ($apply_to == 'both') {
        $conn->query("UPDATE product_variants SET retail_price = retail_price + $price_increase, wholesale_price = wholesale_price + $price_increase WHERE product_id = $product_id");
    }
    
    $_SESSION['success_message'] = "Prices updated successfully";
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Handle Toggle Status
if (isset($_GET['toggle_status'])) {
    $variant_id = intval($_GET['variant_id']);
    $current = intval($_GET['current']);
    $new = $current ? 0 : 1;
    
    $conn->query("UPDATE product_variants SET is_active = $new WHERE id = $variant_id AND product_id = $product_id");
    $_SESSION['success_message'] = "Variant status updated";
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Handle Set Default
if (isset($_GET['set_default'])) {
    $variant_id = intval($_GET['variant_id']);
    
    $conn->query("UPDATE product_variants SET is_default = 0 WHERE product_id = $product_id");
    $conn->query("UPDATE product_variants SET is_default = 1 WHERE id = $variant_id AND product_id = $product_id");
    
    $_SESSION['success_message'] = "Default variant updated";
    header("Location: product-variants.php?product_id=" . $product_id);
    exit;
}

// Fetch all variants
$variants = [];
$variants_result = $conn->query("SELECT * FROM product_variants WHERE product_id = $product_id ORDER BY is_default DESC, id ASC");
if ($variants_result) {
    while ($row = $variants_result->fetch_assoc()) {
        $variants[] = $row;
    }
}

// Get variant stats
$total_variants = count($variants);
$active_variants = $conn->query("SELECT COUNT(*) as total FROM product_variants WHERE product_id = $product_id AND is_active = 1")->fetch_assoc()['total'];
$total_stock = $conn->query("SELECT SUM(stock_quantity) as total FROM product_variants WHERE product_id = $product_id")->fetch_assoc()['total'];

// NOW include header and sidebar
include 'layout/header.php';
include 'layout/sidebar.php';

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Helper functions
function formatPrice($price) {
    return $price ? '₹' . number_format($price, 2) : 'N/A';
}

function getStockBadge($stock, $low_threshold) {
    if ($stock <= 0) {
        return '<span class="badge-stock out"><i class="fas fa-times-circle"></i> Out of Stock</span>';
    } elseif ($stock <= $low_threshold) {
        return '<span class="badge-stock low"><i class="fas fa-exclamation-triangle"></i> Low Stock (' . $stock . ')</span>';
    } else {
        return '<span class="badge-stock in"><i class="fas fa-check-circle"></i> In Stock (' . $stock . ')</span>';
    }
}

function getStatusBadge($status) {
    return $status ? 
        '<span class="badge-status active"><i class="fas fa-check-circle"></i> Active</span>' : 
        '<span class="badge-status inactive"><i class="fas fa-times-circle"></i> Inactive</span>';
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
                <i class="fas fa-code-branch"></i>
                Product Variants: <?php echo htmlspecialchars($product['name']); ?>
                <span class="product-code">#<?php echo $product['product_code']; ?></span>
            </h2>
            <div class="header-actions">
                <a href="product-view.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Product
                </a>
                <a href="product-edit.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total Variants</span>
                <span class="stat-number"><?php echo $total_variants; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Active Variants</span>
                <span class="stat-number"><?php echo $active_variants; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Total Stock</span>
                <span class="stat-number"><?php echo $total_stock; ?></span>
            </div>
        </div>

        <!-- Bulk Price Update -->
        <div class="bulk-price-section">
            <h3><i class="fas fa-chart-line"></i> Bulk Price Update</h3>
            <form method="POST" class="bulk-price-form">
                <div class="form-row">
                    <div class="form-group half">
                        <label>Price Change Amount</label>
                        <input type="number" name="price_increase" step="0.01" class="form-control" placeholder="+10.00 or -5.00" required>
                    </div>
                    <div class="form-group half">
                        <label>Apply To</label>
                        <select name="apply_to" class="form-control">
                            <option value="retail">Retail Price Only</option>
                            <option value="wholesale">Wholesale Price Only</option>
                            <option value="both">Both Retail & Wholesale</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="bulk_update_prices" class="btn btn-primary">
                            <i class="fas fa-calculator"></i> Apply Price Change
                        </button>
                    </div>
                </div>
                <small>Use positive numbers to increase price, negative numbers to decrease price</small>
            </form>
        </div>

        <!-- Add New Variant Button -->
        <div class="add-variant-bar">
            <button onclick="openAddModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Variant
            </button>
        </div>

        <!-- Variants Table -->
        <?php if (!empty($variants)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>SKU / Code</th>
                            <th>Attributes</th>
                            <th>Prices</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $variant): 
                            $attrs = json_decode($variant['attributes'], true);
                        ?>
                            <tr class="<?php echo $variant['is_default'] ? 'default-row' : ''; ?>">
                                <td>#<?php echo $variant['id']; ?>
                                    <?php if ($variant['is_default']): ?>
                                        <span class="default-badge"><i class="fas fa-star"></i> Default</span>
                                    <?php endif; ?>
                                </td>
                                <td class="icon-cell">
                                    <?php if (!empty($variant['image']) && file_exists('../uploads/products/variants/' . $variant['image'])): ?>
                                        <img src="../uploads/products/variants/<?php echo $variant['image']; ?>" 
                                             alt="Variant Image" 
                                             class="variant-icon"
                                             onerror="this.src='../assets/images/no-image.png';">
                                    <?php else: ?>
                                        <div class="no-icon">
                                            <i class="fas fa-code-branch"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($variant['sku']); ?></strong>
                                    <div class="variant-code"><?php echo htmlspecialchars($variant['variant_code'] ?: 'No code'); ?></div>
                                </td>
                                <td>
                                    <div class="attributes">
                                        <?php if (!empty($attrs['size'])): ?>
                                            <span class="attr-badge">Size: <?php echo htmlspecialchars($attrs['size']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($attrs['color'])): ?>
                                            <span class="attr-badge" style="background: <?php echo $attrs['color']; ?>20;">
                                                Color: <?php echo htmlspecialchars($attrs['color']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($attrs['material'])): ?>
                                            <span class="attr-badge">Material: <?php echo htmlspecialchars($attrs['material']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($attrs['pattern'])): ?>
                                            <span class="attr-badge">Pattern: <?php echo htmlspecialchars($attrs['pattern']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($variant['is_bulk_only']): ?>
                                        <span class="bulk-badge"><i class="fas fa-boxes"></i> Bulk Only (Min: <?php echo $variant['bulk_min_quantity']; ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="prices">
                                        <div class="price-item">MRP: <?php echo formatPrice($variant['mrp']); ?></div>
                                        <div class="price-item retail">Retail: <?php echo formatPrice($variant['retail_price']); ?></div>
                                        <div class="price-item wholesale">Wholesale: <?php echo formatPrice($variant['wholesale_price']); ?></div>
                                        <div class="price-item cost">Cost: <?php echo formatPrice($variant['cost_price']); ?></div>
                                        <?php if ($variant['price_adjustment'] != 0): ?>
                                            <div class="price-adjustment">Adj: <?php echo ($variant['price_adjustment'] > 0 ? '+' : '') . $variant['price_adjustment']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo getStockBadge($variant['stock_quantity'], $variant['low_stock_threshold']); ?>
                                    <?php if (!$variant['track_inventory']): ?>
                                        <div class="tracking-badge">Inventory not tracked</div>
                                    <?php endif; ?>
                                    <?php if ($variant['weight']): ?>
                                        <div class="weight-info"><i class="fas fa-weight"></i> <?php echo $variant['weight']; ?> kg</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="status-group">
                                        <?php echo getStatusBadge($variant['is_active']); ?>
                                        <a href="?product_id=<?php echo $product_id; ?>&toggle_status=1&variant_id=<?php echo $variant['id']; ?>&current=<?php echo $variant['is_active']; ?>" 
                                           class="toggle-link" title="Toggle Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="actions-cell">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($variant)); ?>)" 
                                            class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (!$variant['is_default']): ?>
                                        <a href="?product_id=<?php echo $product_id; ?>&set_default=<?php echo $variant['id']; ?>" 
                                           class="btn-icon" title="Set as Default">
                                            <i class="fas fa-star"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?product_id=<?php echo $product_id; ?>&delete=<?php echo $variant['id']; ?>" 
                                       class="btn-icon delete" 
                                       onclick="return confirmDelete('<?php echo addslashes($variant['sku']); ?>')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-code-branch"></i>
                <h3>No Variants Found</h3>
                <p>Add variants to this product for different sizes, colors, or styles.</p>
                <button onclick="openAddModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add First Variant
                </button>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Add Variant Modal -->
<div id="addModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Variant</h3>
            <span class="close" onclick="closeAddModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>SKU <span class="required">*</span></label>
                <input type="text" name="sku" class="form-control" placeholder="Leave empty to auto-generate">
            </div>
            
            <div class="form-group">
                <label>Variant Code</label>
                <input type="text" name="variant_code" class="form-control" placeholder="Optional internal code">
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Size</label>
                    <input type="text" name="size" class="form-control" placeholder="e.g., S, M, L, XL">
                </div>
                <div class="form-group half">
                    <label>Color</label>
                    <input type="text" name="color" class="form-control" placeholder="e.g., Red, Blue, Black">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Material</label>
                    <input type="text" name="material" class="form-control" placeholder="e.g., Cotton, Polyester">
                </div>
                <div class="form-group half">
                    <label>Pattern</label>
                    <input type="text" name="pattern" class="form-control" placeholder="e.g., Solid, Striped">
                </div>
            </div>
            
            <div class="form-group">
                <label>Style</label>
                <input type="text" name="style" class="form-control" placeholder="e.g., Casual, Formal">
            </div>
            
            <div class="form-group">
                <label>Variant Image</label>
                <input type="file" name="variant_image" class="form-control" accept="image/*">
                <small>Optional: Specific image for this variant</small>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>MRP</label>
                    <input type="number" name="mrp" step="0.01" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group half">
                    <label>Cost Price</label>
                    <input type="number" name="cost_price" step="0.01" class="form-control" placeholder="0.00">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Retail Price</label>
                    <input type="number" name="retail_price" step="0.01" class="form-control" placeholder="0.00">
                </div>
                <div class="form-group half">
                    <label>Wholesale Price</label>
                    <input type="number" name="wholesale_price" step="0.01" class="form-control" placeholder="0.00">
                </div>
            </div>
            
            <div class="form-group">
                <label>Price Adjustment</label>
                <input type="number" name="price_adjustment" step="0.01" class="form-control" value="0">
                <small>Relative adjustment from base product price</small>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" class="form-control" value="0">
                </div>
                <div class="form-group half">
                    <label>Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="5">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Bulk Min Quantity</label>
                    <input type="number" name="bulk_min_quantity" class="form-control" value="10">
                </div>
                <div class="form-group half">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" class="form-control" placeholder="0.00">
                </div>
            </div>
            
            <div class="form-group">
                <label>Dimensions (LxWxH)</label>
                <input type="text" name="dimensions" class="form-control" placeholder="e.g., 10x20x30 cm">
            </div>
            
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="track_inventory" checked> Track Inventory
                </label>
                <label>
                    <input type="checkbox" name="is_bulk_only"> Bulk Only
                </label>
                <label>
                    <input type="checkbox" name="is_default"> Set as Default Variant
                </label>
                <label>
                    <input type="checkbox" name="is_active" checked> Active
                </label>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeAddModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Variant</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Variant Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Variant</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="variant_id" id="edit_variant_id">
            
            <div class="form-group">
                <label>SKU <span class="required">*</span></label>
                <input type="text" name="sku" id="edit_sku" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Variant Code</label>
                <input type="text" name="variant_code" id="edit_variant_code" class="form-control">
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Size</label>
                    <input type="text" name="size" id="edit_size" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Color</label>
                    <input type="text" name="color" id="edit_color" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Material</label>
                    <input type="text" name="material" id="edit_material" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Pattern</label>
                    <input type="text" name="pattern" id="edit_pattern" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Style</label>
                <input type="text" name="style" id="edit_style" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Current Image</label>
                <div id="current_image_container"></div>
                <label>Change Image</label>
                <input type="file" name="variant_image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>MRP</label>
                    <input type="number" name="mrp" step="0.01" id="edit_mrp" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Cost Price</label>
                    <input type="number" name="cost_price" step="0.01" id="edit_cost_price" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Retail Price</label>
                    <input type="number" name="retail_price" step="0.01" id="edit_retail_price" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Wholesale Price</label>
                    <input type="number" name="wholesale_price" step="0.01" id="edit_wholesale_price" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Price Adjustment</label>
                <input type="number" name="price_adjustment" step="0.01" id="edit_price_adjustment" class="form-control">
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="edit_stock_quantity" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" id="edit_low_stock_threshold" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group half">
                    <label>Bulk Min Quantity</label>
                    <input type="number" name="bulk_min_quantity" id="edit_bulk_min_quantity" class="form-control">
                </div>
                <div class="form-group half">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" step="0.01" id="edit_weight" class="form-control">
                </div>
            </div>
            
            <div class="form-group">
                <label>Dimensions (LxWxH)</label>
                <input type="text" name="dimensions" id="edit_dimensions" class="form-control">
            </div>
            
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="track_inventory" id="edit_track_inventory"> Track Inventory
                </label>
                <label>
                    <input type="checkbox" name="is_bulk_only" id="edit_is_bulk_only"> Bulk Only
                </label>
                <label>
                    <input type="checkbox" name="is_default" id="edit_is_default"> Set as Default Variant
                </label>
                <label>
                    <input type="checkbox" name="is_active" id="edit_is_active"> Active
                </label>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Variant</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Product Variants Styles */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }

    .main-content {
        padding: 25px;
    }

    .container {
        background: white;
        padding: 20px;
        border-radius: 8px;
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--gold);
        flex-wrap: wrap;
        gap: 10px;
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

    .product-code {
        font-size: 14px;
        color: var(--gold);
        background: var(--gold-light);
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: normal;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-box {
        background: var(--gray-bg);
        padding: 15px 20px;
        border: 1px solid var(--border);
        border-radius: 8px;
        text-align: center;
    }

    .stat-label {
        display: block;
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 600;
        color: var(--teal);
    }

    .bulk-price-section {
        background: var(--teal-light);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .bulk-price-section h3 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bulk-price-form .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-end;
    }

    .add-variant-bar {
        margin-bottom: 20px;
        text-align: right;
    }

    .table-container {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1200px;
    }

    .data-table th {
        background: var(--teal-light);
        padding: 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: var(--dark);
        border-bottom: 2px solid var(--teal);
    }

    .data-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
    }

    .default-row {
        background: var(--gold-light);
    }

    .default-badge {
        display: inline-block;
        background: var(--gold);
        color: var(--dark);
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 5px;
    }

    .variant-icon {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--border);
    }

    .no-icon {
        width: 50px;
        height: 50px;
        background: var(--teal-light);
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        font-size: 20px;
        border: 1px solid var(--border);
    }

    .variant-code {
        font-size: 11px;
        color: #6c757d;
        margin-top: 2px;
    }

    .attributes {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 5px;
    }

    .attr-badge {
        display: inline-block;
        padding: 2px 6px;
        background: var(--gray-bg);
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 11px;
    }

    .bulk-badge {
        display: inline-block;
        background: #ffebee;
        color: #c62828;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 5px;
    }

    .prices {
        font-size: 12px;
    }

    .price-item {
        margin: 2px 0;
    }

    .price-item.retail {
        color: var(--teal);
        font-weight: 600;
    }

    .price-item.wholesale {
        color: #fd7e14;
    }

    .price-item.cost {
        color: #6c757d;
    }

    .price-adjustment {
        font-size: 11px;
        color: #28a745;
        margin-top: 2px;
    }

    .tracking-badge {
        font-size: 11px;
        color: #6c757d;
        margin-top: 5px;
    }

    .weight-info {
        font-size: 11px;
        color: #6c757d;
        margin-top: 5px;
    }

    .badge-stock {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 20px;
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

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 20px;
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

    .status-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .toggle-link {
        color: #6c757d;
        text-decoration: none;
        opacity: 0.6;
        transition: all 0.2s;
    }

    .toggle-link:hover {
        opacity: 1;
        color: var(--teal);
    }

    .btn {
        padding: 8px 16px;
        border: none;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
    }

    .actions-cell {
        white-space: nowrap;
    }

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
        max-width: 700px;
        border-radius: 8px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        animation: slideDown 0.3s ease;
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

    .modal-header .close {
        color: white;
        font-size: 24px;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-footer {
        padding: 15px 20px;
        background: var(--gray-bg);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 15px;
        padding: 0 20px;
    }

    .form-row {
        display: flex;
        gap: 15px;
        padding: 0 20px;
        margin-bottom: 15px;
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
        padding: 8px 12px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--teal);
    }

    .checkbox-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        padding: 0 20px;
        margin-bottom: 15px;
    }

    .checkbox-group label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: normal;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--gray-bg);
        border-radius: 8px;
    }

    .empty-state i {
        font-size: 60px;
        color: var(--teal-light);
        margin-bottom: 15px;
    }

    .alert {
        padding: 12px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-left: 4px solid transparent;
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

    small {
        display: block;
        color: #6c757d;
        font-size: 11px;
        margin-top: 5px;
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

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: 1fr;
        }
        .form-row {
            flex-direction: column;
        }
        .bulk-price-form .form-row {
            flex-direction: column;
        }
        .modal-content {
            margin: 20px;
            width: auto;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let tierCounter = 1;
    
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }
    
    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
    }
    
    function openEditModal(variant) {
        document.getElementById('edit_variant_id').value = variant.id;
        document.getElementById('edit_sku').value = variant.sku || '';
        document.getElementById('edit_variant_code').value = variant.variant_code || '';
        document.getElementById('edit_size').value = variant.size || '';
        document.getElementById('edit_color').value = variant.color || '';
        document.getElementById('edit_material').value = variant.material || '';
        document.getElementById('edit_pattern').value = variant.pattern || '';
        document.getElementById('edit_style').value = variant.style || '';
        document.getElementById('edit_mrp').value = variant.mrp || '';
        document.getElementById('edit_cost_price').value = variant.cost_price || '';
        document.getElementById('edit_retail_price').value = variant.retail_price || '';
        document.getElementById('edit_wholesale_price').value = variant.wholesale_price || '';
        document.getElementById('edit_price_adjustment').value = variant.price_adjustment || 0;
        document.getElementById('edit_stock_quantity').value = variant.stock_quantity || 0;
        document.getElementById('edit_low_stock_threshold').value = variant.low_stock_threshold || 5;
        document.getElementById('edit_bulk_min_quantity').value = variant.bulk_min_quantity || 10;
        document.getElementById('edit_weight').value = variant.weight || '';
        document.getElementById('edit_dimensions').value = variant.dimensions || '';
        document.getElementById('edit_track_inventory').checked = variant.track_inventory == 1;
        document.getElementById('edit_is_bulk_only').checked = variant.is_bulk_only == 1;
        document.getElementById('edit_is_default').checked = variant.is_default == 1;
        document.getElementById('edit_is_active').checked = variant.is_active == 1;
        
        const imageContainer = document.getElementById('current_image_container');
        if (variant.image) {
            imageContainer.innerHTML = `<img src="../uploads/products/variants/${variant.image}" alt="Current Image" style="max-width: 100px; border: 1px solid var(--border); border-radius: 4px; margin-bottom: 10px;">`;
        } else {
            imageContainer.innerHTML = '<p>No image</p>';
        }
        
        document.getElementById('editModal').style.display = 'block';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == document.getElementById('addModal')) {
            closeAddModal();
        }
        if (event.target == document.getElementById('editModal')) {
            closeEditModal();
        }
    }
    
    function confirmDelete(sku) {
        return Swal.fire({
            title: 'Delete Variant?',
            html: `Are you sure you want to delete variant "<strong>${sku}</strong>"?`,
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

<?php include 'layout/footer.php'; ?>