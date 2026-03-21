<?php
// product-view.php - View Product Details
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    $_SESSION['error_message'] = "Invalid product ID";
    header("Location: products.php");
    exit;
}

// Fetch product data with all related info
$product = null;
$sql = "SELECT p.*, 
               sc.id as sub_category_id, 
               sc.name as sub_category_name, 
               sc.slug as sub_category_slug,
               mc.id as main_category_id,
               mc.name as main_category_name, 
               mc.category_code,
               mc.category_code as main_category_code
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

// Fetch gallery images
$gallery_images = [];
$gallery_result = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id AND image_type = 'gallery' ORDER BY sort_order");
if ($gallery_result) {
    while ($row = $gallery_result->fetch_assoc()) {
        $gallery_images[] = $row;
    }
}

// Fetch tiered pricing
$tiers = [];
$tiers_result = $conn->query("SELECT * FROM bulk_pricing_tiers WHERE product_id = $product_id AND variant_id IS NULL ORDER BY tier_level");
if ($tiers_result) {
    while ($row = $tiers_result->fetch_assoc()) {
        $tiers[] = $row;
    }
}

// Fetch shipping restrictions
$shipping_restrictions = [];
$shipping_result = $conn->query("SELECT * FROM product_shipping_restrictions WHERE product_id = $product_id");
if ($shipping_result) {
    while ($row = $shipping_result->fetch_assoc()) {
        $shipping_restrictions[] = $row;
    }
}

// Fetch variants count
$variants_count = 0;
$variants_result = $conn->query("SELECT COUNT(*) as total FROM product_variants WHERE product_id = $product_id");
if ($variants_result) {
    $variants_count = $variants_result->fetch_assoc()['total'];
}

// Fetch reviews
$reviews = [];
$avg_rating = 0;
$reviews_count = 0;
$reviews_result = $conn->query("SELECT * FROM product_reviews WHERE product_id = $product_id AND status = 'approved' ORDER BY created_at DESC LIMIT 5");
if ($reviews_result) {
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $reviews_count = $conn->query("SELECT COUNT(*) as total FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['total'];
    $avg_rating = $conn->query("SELECT AVG(rating) as avg FROM product_reviews WHERE product_id = $product_id AND status = 'approved'")->fetch_assoc()['avg'];
}

// NOW include header and sidebar
include 'layout/header.php';
include 'layout/sidebar.php';

// Helper functions
function formatPrice($price) {
    return $price ? '₹' . number_format($price, 2) : 'N/A';
}

function getStockBadge($stock, $low_threshold) {
    if ($stock <= 0) {
        return '<span class="badge-stock out"><i class="fas fa-times-circle"></i> Out of Stock</span>';
    } elseif ($stock <= $low_threshold) {
        return '<span class="badge-stock low"><i class="fas fa-exclamation-triangle"></i> Low Stock (' . $stock . ' left)</span>';
    } else {
        return '<span class="badge-stock in"><i class="fas fa-check-circle"></i> In Stock (' . $stock . ' units)</span>';
    }
}

function getStatusBadge($status) {
    return $status ? 
        '<span class="badge-status active"><i class="fas fa-check-circle"></i> Active</span>' : 
        '<span class="badge-status inactive"><i class="fas fa-times-circle"></i> Inactive</span>';
}

function getCodeBadge($code) {
    $colors = [
        'HAND' => '#4CAF50',
        'TEX' => '#2196F3',
        'FMCG' => '#FF9800'
    ];
    $color = $colors[$code] ?? '#6c757d';
    return "<span class=\"badge-code\" style=\"background: {$color}20; color: {$color}; border-color: {$color}\">{$code}</span>";
}

function getProductTypeBadge($type) {
    $types = [
        'simple' => ['label' => 'Simple', 'color' => '#6c757d', 'icon' => 'fa-box'],
        'variable' => ['label' => 'Variable', 'color' => '#17a2b8', 'icon' => 'fa-code-branch'],
        'bundle' => ['label' => 'Bundle', 'color' => '#fd7e14', 'icon' => 'fa-layer-group']
    ];
    $type_info = $types[$type] ?? ['label' => ucfirst($type), 'color' => '#6c757d', 'icon' => 'fa-box'];
    return "<span class=\"badge-type\" style=\"background: {$type_info['color']}20; color: {$type_info['color']}; border-color: {$type_info['color']}\"><i class=\"fas {$type_info['icon']}\"></i> {$type_info['label']}</span>";
}

function getSellingModeBadge($mode) {
    $modes = [
        'both' => ['label' => 'Both (Single & Bulk)', 'color' => '#28a745', 'icon' => 'fa-exchange-alt'],
        'single_only' => ['label' => 'Single Only', 'color' => '#007bff', 'icon' => 'fa-shopping-cart'],
        'bulk_only' => ['label' => 'Bulk Only', 'color' => '#fd7e14', 'icon' => 'fa-boxes']
    ];
    $mode_info = $modes[$mode] ?? ['label' => ucfirst($mode), 'color' => '#6c757d', 'icon' => 'fa-tag'];
    return "<span class=\"badge-mode\" style=\"background: {$mode_info['color']}20; color: {$mode_info['color']}; border-color: {$mode_info['color']}\"><i class=\"fas {$mode_info['icon']}\"></i> {$mode_info['label']}</span>";
}

function getPricingModelBadge($model) {
    $models = [
        'fixed' => ['label' => 'Fixed Price', 'color' => '#28a745', 'icon' => 'fa-tag'],
        'tiered' => ['label' => 'Tiered Pricing', 'color' => '#17a2b8', 'icon' => 'fa-layer-group'],
        'range' => ['label' => 'Range Based', 'color' => '#fd7e14', 'icon' => 'fa-chart-line']
    ];
    $model_info = $models[$model] ?? ['label' => ucfirst($model), 'color' => '#6c757d', 'icon' => 'fa-tag'];
    return "<span class=\"badge-model\" style=\"background: {$model_info['color']}20; color: {$model_info['color']}; border-color: {$model_info['color']}\"><i class=\"fas {$model_info['icon']}\"></i> {$model_info['label']}</span>";
}

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
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
                Product Details: <?php echo htmlspecialchars($product['name']); ?>
                <span class="product-code">#<?php echo $product['product_code']; ?></span>
            </h2>
            <div class="header-actions">
                <a href="product-edit.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Product View Grid -->
        <div class="product-view">
            
            <!-- Product Images Section -->
            <div class="view-section">
                <h3><i class="fas fa-images"></i> Product Images</h3>
                <div class="product-images-grid">
                    <?php if (!empty($product['main_image']) && file_exists('../uploads/products/main/' . $product['main_image'])): ?>
                        <div class="image-card main-image">
                            <div class="image-label">Main Image</div>
                            <img src="../uploads/products/main/<?php echo $product['main_image']; ?>" alt="Main Image">
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['hover_image']) && file_exists('../uploads/products/hover/' . $product['hover_image'])): ?>
                        <div class="image-card hover-image">
                            <div class="image-label">Hover Image</div>
                            <img src="../uploads/products/hover/<?php echo $product['hover_image']; ?>" alt="Hover Image">
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($gallery_images as $gallery): ?>
                        <?php if (file_exists('../uploads/products/gallery/' . $gallery['image_url'])): ?>
                            <div class="image-card gallery-image">
                                <div class="image-label">Gallery Image</div>
                                <img src="../uploads/products/gallery/<?php echo $gallery['image_url']; ?>" alt="Gallery Image">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if (empty($product['main_image']) && empty($gallery_images)): ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No images uploaded</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Basic Information Section -->
            <div class="view-section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Product Name:</label>
                        <span><?php echo htmlspecialchars($product['name']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Product Code:</label>
                        <span><?php echo $product['product_code']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Slug:</label>
                        <span><?php echo $product['slug']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Product Type:</label>
                        <span><?php echo getProductTypeBadge($product['product_type']); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Category:</label>
                        <span>
                            <a href="sub-categories.php?main_id=<?php echo $product['main_category_id']; ?>" class="category-link">
                                <?php echo htmlspecialchars($product['main_category_name']); ?>
                            </a> → 
                            <a href="products.php?sub_id=<?php echo $product['sub_category_id']; ?>" class="category-link">
                                <?php echo htmlspecialchars($product['sub_category_name']); ?>
                            </a>
                            <?php echo getCodeBadge($product['category_code']); ?>
                        </span>
                    </div>
                    <div class="info-item full-width">
                        <label>Short Description:</label>
                        <span><?php echo nl2br(htmlspecialchars($product['short_description'] ?: 'No description')); ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Full Description:</label>
                        <div class="description-content"><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description')); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Attributes Section -->
            <div class="view-section">
                <h3><i class="fas fa-tags"></i> Product Attributes</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Size:</label>
                        <span><?php echo $product['size'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Color:</label>
                        <span><?php echo $product['color'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Weight:</label>
                        <span><?php echo $product['weight'] ? $product['weight'] . ' kg' : 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Dimensions:</label>
                        <span><?php echo $product['dimensions'] ?: 'N/A'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Material:</label>
                        <span><?php echo $product['material'] ?: 'N/A'; ?></span>
                    </div>
                    <?php if ($product['has_variants']): ?>
                        <div class="info-item">
                            <label>Variants:</label>
                            <span><a href="product-variants.php?product_id=<?php echo $product_id; ?>" class="btn-link"><?php echo $variants_count; ?> Variants</a></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pricing Section -->
            <div class="view-section">
                <h3><i class="fas fa-tag"></i> Pricing Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>MRP:</label>
                        <span class="price-value"><?php echo formatPrice($product['mrp']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Cost Price:</label>
                        <span><?php echo formatPrice($product['cost_price']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Retail Price:</label>
                        <span class="price-value retail"><?php echo formatPrice($product['base_retail_price']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Wholesale Price:</label>
                        <span class="price-value wholesale"><?php echo formatPrice($product['base_wholesale_price']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Selling Mode:</label>
                        <span><?php echo getSellingModeBadge($product['selling_mode']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Min Order Qty:</label>
                        <span><?php echo $product['min_order_quantity']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Max Order Qty:</label>
                        <span><?php echo $product['max_order_quantity'] ?: 'Unlimited'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Bulk Min Qty:</label>
                        <span><?php echo $product['bulk_min_quantity']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Bulk Only:</label>
                        <span><?php echo $product['is_bulk_only'] ? '<i class="fas fa-check-circle" style="color: var(--teal);"></i> Yes' : '<i class="fas fa-times-circle" style="color: #dc3545;"></i> No'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Pricing Model:</label>
                        <span><?php echo getPricingModelBadge($product['bulk_pricing_model']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Tiered Pricing:</label>
                        <span><?php echo $product['has_tiered_pricing'] ? '<i class="fas fa-check-circle" style="color: var(--teal);"></i> Enabled' : '<i class="fas fa-times-circle" style="color: #dc3545;"></i> Disabled'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Tiered Pricing Table -->
            <?php if (!empty($tiers) && $product['has_tiered_pricing']): ?>
            <div class="view-section">
                <h3><i class="fas fa-layer-group"></i> Tiered Pricing</h3>
                <div class="table-responsive">
                    <table class="data-table tiers-table">
                        <thead>
                            <tr>
                                <th>Tier Name</th>
                                <th>Level</th>
                                <th>Quantity Range</th>
                                <th>Price per Piece</th>
                                <th>Discount %</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiers as $tier): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tier['tier_name']); ?></td>
                                    <td><?php echo $tier['tier_level']; ?></td>
                                    <td><?php echo $tier['min_quantity']; ?> - <?php echo $tier['max_quantity'] ?: '+'; ?></td>
                                    <td class="price-value"><?php echo formatPrice($tier['price_per_piece']); ?></td>
                                    <td><?php echo $tier['discount_percentage'] ? $tier['discount_percentage'] . '%' : '-'; ?></td>
                                    <td><?php echo $tier['is_active'] ? '<span class="badge-status active">Active</span>' : '<span class="badge-status inactive">Inactive</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Inventory Section -->
            <div class="view-section">
                <h3><i class="fas fa-warehouse"></i> Inventory</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Stock Status:</label>
                        <span><?php echo getStockBadge($product['stock_quantity'], $product['low_stock_threshold']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Stock Quantity:</label>
                        <span><?php echo $product['stock_quantity']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Low Stock Threshold:</label>
                        <span><?php echo $product['low_stock_threshold']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Track Inventory:</label>
                        <span><?php echo $product['track_inventory'] ? '<i class="fas fa-check-circle" style="color: var(--teal);"></i> Yes' : '<i class="fas fa-times-circle" style="color: #dc3545;"></i> No'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Allow Backorder:</label>
                        <span><?php echo $product['allow_backorder'] ? '<i class="fas fa-check-circle" style="color: var(--teal);"></i> Yes' : '<i class="fas fa-times-circle" style="color: #dc3545;"></i> No'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Sold:</label>
                        <span><?php echo $product['total_sold']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Total Revenue:</label>
                        <span><?php echo formatPrice($product['total_revenue']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Section -->
            <div class="view-section">
                <h3><i class="fas fa-truck"></i> Shipping Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Shipping Class:</label>
                        <span><?php echo $product['shipping_class'] ?: 'Standard'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Shipping Weight:</label>
                        <span><?php echo $product['shipping_weight'] ? $product['shipping_weight'] . ' kg' : 'N/A'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Free Shipping:</label>
                        <span><?php echo $product['free_shipping'] ? '<i class="fas fa-check-circle" style="color: var(--teal);"></i> Yes' : '<i class="fas fa-times-circle" style="color: #dc3545;"></i> No'; ?></span>
                    </div>
                </div>
                
                <?php if (!empty($shipping_restrictions)): ?>
                <div class="shipping-restrictions">
                    <label>Shipping Restrictions by Pincode:</label>
                    <div class="table-responsive">
                        <table class="data-table restrictions-table">
                            <thead>
                                <tr>
                                    <th>Pincode</th>
                                    <th>Serviceable</th>
                                    <th>Delivery Days</th>
                                    <th>Additional Charge</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shipping_restrictions as $restriction): ?>
                                    <tr>
                                        <td><?php echo $restriction['pincode']; ?></td>
                                        <td><?php echo $restriction['is_serviceable'] ? '<span class="badge-status active">Yes</span>' : '<span class="badge-status inactive">No</span>'; ?></td>
                                        <td><?php echo $restriction['delivery_days'] ?: 'N/A'; ?></td>
                                        <td><?php echo formatPrice($restriction['additional_charge']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- SEO Section -->
            <div class="view-section">
                <h3><i class="fas fa-search"></i> SEO & Meta Data</h3>
                <div class="info-grid">
                    <div class="info-item full-width">
                        <label>Meta Title:</label>
                        <span><?php echo $product['meta_title'] ?: 'Not set'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Meta Description:</label>
                        <span><?php echo $product['meta_description'] ?: 'Not set'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Meta Keywords:</label>
                        <span><?php echo $product['meta_keywords'] ?: 'Not set'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Canonical URL:</label>
                        <span><?php echo $product['canonical_url'] ?: 'Not set'; ?></span>
                    </div>
                    <div class="info-item full-width">
                        <label>Search Keywords:</label>
                        <span><?php echo $product['search_keywords'] ?: 'Not set'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Tax Section -->
            <div class="view-section">
                <h3><i class="fas fa-percent"></i> Tax Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Tax Class:</label>
                        <span><?php echo ucfirst($product['tax_class'] ?: 'Standard'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>GST Rate:</label>
                        <span><?php echo $product['gst_rate'] ? $product['gst_rate'] . '%' : 'No GST'; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Status & Flags Section -->
            <div class="view-section">
                <h3><i class="fas fa-flag"></i> Status & Flags</h3>
                <div class="flags-container">
                    <?php echo getStatusBadge($product['is_active']); ?>
                    <?php if ($product['is_featured']): ?>
                        <span class="badge-featured"><i class="fas fa-star"></i> Featured</span>
                    <?php endif; ?>
                    <?php if ($product['is_new']): ?>
                        <span class="badge-new"><i class="fas fa-fire"></i> New Arrival</span>
                    <?php endif; ?>
                    <?php if ($product['is_on_sale']): ?>
                        <span class="badge-sale"><i class="fas fa-tag"></i> On Sale</span>
                    <?php endif; ?>
                    <?php if ($product['is_trending']): ?>
                        <span class="badge-trending"><i class="fas fa-chart-line"></i> Trending</span>
                    <?php endif; ?>
                    <?php if ($product['is_bulk_item']): ?>
                        <span class="badge-bulk"><i class="fas fa-boxes"></i> Bulk Item</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reviews Section -->
            <?php if (!empty($reviews) || $reviews_count > 0): ?>
            <div class="view-section">
                <h3><i class="fas fa-star"></i> Customer Reviews</h3>
                <div class="reviews-summary">
                    <div class="avg-rating">
                        <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= round($avg_rating) ? '' : '-o'; ?>" style="color: var(--gold);"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="review-count">Based on <?php echo $reviews_count; ?> reviews</span>
                    </div>
                </div>
                
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>" style="color: var(--gold);"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                            </div>
                            <div class="review-title"><?php echo htmlspecialchars($review['title']); ?></div>
                            <div class="review-content"><?php echo nl2br(htmlspecialchars($review['review'])); ?></div>
                            <?php if ($review['is_verified_purchase']): ?>
                                <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified Purchase</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if ($reviews_count > 5): ?>
                        <div class="view-all-reviews">
                            <a href="product-reviews.php?id=<?php echo $product_id; ?>" class="btn-link">View all <?php echo $reviews_count; ?> reviews →</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Meta Information -->
            <div class="view-section">
                <h3><i class="fas fa-clock"></i> System Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Created At:</label>
                        <span><?php echo date('d M Y, h:i A', strtotime($product['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Last Updated:</label>
                        <span><?php echo date('d M Y, h:i A', strtotime($product['updated_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>View Count:</label>
                        <span><?php echo $product['view_count']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Average Rating:</label>
                        <span><?php echo number_format($product['average_rating'], 1); ?> (<?php echo $product['review_count']; ?> reviews)</span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="product-edit.php?id=<?php echo $product_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <a href="product-variants.php?product_id=<?php echo $product_id; ?>" class="btn btn-secondary" <?php echo !$product['has_variants'] ? 'style="display: none;"' : ''; ?>>
                    <i class="fas fa-code-branch"></i> Manage Variants
                </a>
                <a href="product-images.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-images"></i> Manage Images
                </a>
            </div>
        </div>
    </div>
</main>

<style>
    /* Product View Styles */
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

    .page-title i {
        color: var(--gold);
    }

    .product-code {
        font-size: 14px;
        color: var(--gold);
        background: var(--gold-light);
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: normal;
    }

    .product-view {
        margin-top: 20px;
    }

    .view-section {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 25px;
        overflow: hidden;
    }

    .view-section h3 {
        background: var(--teal-light);
        padding: 12px 20px;
        margin: 0;
        font-size: 16px;
        color: var(--teal);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .view-section h3 i {
        color: var(--gold);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        padding: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-item.full-width {
        grid-column: span 2;
    }

    .info-item label {
        font-weight: 600;
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item span {
        font-size: 14px;
        color: var(--dark);
        word-break: break-word;
    }

    .price-value {
        font-size: 18px !important;
        font-weight: 600;
        color: var(--teal) !important;
    }

    .price-value.retail {
        color: #28a745 !important;
    }

    .price-value.wholesale {
        color: #fd7e14 !important;
    }

    .description-content {
        line-height: 1.6;
        font-size: 14px;
    }

    .product-images-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px;
    }

    .image-card {
        position: relative;
        text-align: center;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 10px;
        background: var(--gray-bg);
    }

    .image-card img {
        max-width: 150px;
        max-height: 150px;
        object-fit: cover;
        border-radius: 4px;
    }

    .image-label {
        position: absolute;
        top: -10px;
        left: 10px;
        background: var(--teal);
        color: white;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
    }

    .no-image {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .no-image i {
        font-size: 48px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .flags-container {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 20px;
    }

    .badge-new {
        background: #e8f5e9;
        color: #2e7d32;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #2e7d32;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-sale {
        background: #ffebee;
        color: #c62828;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #c62828;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-trending {
        background: #fff3e0;
        color: #ef6c00;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #ef6c00;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-bulk {
        background: #e3f2fd;
        color: #1565c0;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid #1565c0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-mode, .badge-model {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }

    .badge-type {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
    }

    .badge-stock {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
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
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
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
        background: var(--gold-light);
        color: var(--dark);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--gold);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-code {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        border: 1px solid;
    }

    .table-responsive {
        overflow-x: auto;
        padding: 20px;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: var(--teal-light);
        padding: 10px 12px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: var(--dark);
        border-bottom: 1px solid var(--border);
    }

    .data-table td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--border);
        font-size: 13px;
    }

    .category-link {
        color: var(--teal);
        text-decoration: none;
    }

    .category-link:hover {
        text-decoration: underline;
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
        background: var(--gray-bg);
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: #e9ecef;
        border-color: var(--teal);
    }

    .btn-link {
        color: var(--teal);
        text-decoration: none;
        font-size: 13px;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        padding: 20px;
        background: var(--gray-bg);
        border-top: 1px solid var(--border);
        margin-top: 20px;
        border-radius: 8px;
    }

    .reviews-summary {
        padding: 20px;
        border-bottom: 1px solid var(--border);
    }

    .avg-rating {
        text-align: center;
    }

    .rating-value {
        font-size: 36px;
        font-weight: 600;
        color: var(--gold);
    }

    .stars {
        margin: 5px 0;
    }

    .review-count {
        font-size: 12px;
        color: #6c757d;
    }

    .reviews-list {
        padding: 20px;
    }

    .review-item {
        border-bottom: 1px solid var(--border);
        padding: 15px 0;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .review-stars {
        color: var(--gold);
        font-size: 12px;
    }

    .review-date {
        font-size: 11px;
        color: #6c757d;
    }

    .review-title {
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .review-content {
        font-size: 13px;
        color: #555;
        line-height: 1.5;
        margin-bottom: 8px;
    }

    .verified-badge {
        font-size: 11px;
        color: var(--teal);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .view-all-reviews {
        text-align: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border);
    }

    .shipping-restrictions {
        padding: 0 20px 20px 20px;
    }

    .shipping-restrictions label {
        display: block;
        font-weight: 600;
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 10px;
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

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .info-item.full-width {
            grid-column: span 1;
        }
        
        .header-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .product-images-grid {
            justify-content: center;
        }
        
        .review-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.style.display = 'none', 300);
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ?>