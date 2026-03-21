<?php
// product-add.php - Add New Product
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get basic product info
    $sub_category_id = intval($_POST['sub_category_id']);
    $name = $conn->real_escape_string(trim($_POST['name']));
    $slug = $conn->real_escape_string(trim($_POST['slug']));
    $short_description = $conn->real_escape_string(trim($_POST['short_description'] ?? ''));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $product_type = $conn->real_escape_string($_POST['product_type']);
    $has_variants = isset($_POST['has_variants']) ? 1 : 0;

    // Basic attributes
    $size = $conn->real_escape_string(trim($_POST['size'] ?? ''));
    $weight = !empty($_POST['weight']) ? floatval($_POST['weight']) : NULL;
    $dimensions = $conn->real_escape_string(trim($_POST['dimensions'] ?? ''));
    $material = $conn->real_escape_string(trim($_POST['material'] ?? ''));
    $color = $conn->real_escape_string(trim($_POST['color'] ?? ''));

    // Pricing
    $base_retail_price = !empty($_POST['base_retail_price']) ? floatval($_POST['base_retail_price']) : NULL;
    $base_wholesale_price = !empty($_POST['base_wholesale_price']) ? floatval($_POST['base_wholesale_price']) : NULL;
    $cost_price = !empty($_POST['cost_price']) ? floatval($_POST['cost_price']) : NULL;
    $mrp = !empty($_POST['mrp']) ? floatval($_POST['mrp']) : NULL;

    // Selling settings
    $selling_mode = $conn->real_escape_string($_POST['selling_mode']);
    $min_order_quantity = intval($_POST['min_order_quantity']);
    $max_order_quantity = !empty($_POST['max_order_quantity']) ? intval($_POST['max_order_quantity']) : NULL;
    $bulk_min_quantity = intval($_POST['bulk_min_quantity']);
    $is_bulk_only = isset($_POST['is_bulk_only']) ? 1 : 0;

    // Inventory
    $stock_quantity = intval($_POST['stock_quantity']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);
    $track_inventory = isset($_POST['track_inventory']) ? 1 : 0;
    $allow_backorder = isset($_POST['allow_backorder']) ? 1 : 0;

    // Shipping
    $shipping_class = $conn->real_escape_string(trim($_POST['shipping_class'] ?? ''));
    $shipping_weight = !empty($_POST['shipping_weight']) ? floatval($_POST['shipping_weight']) : NULL;
    $free_shipping = isset($_POST['free_shipping']) ? 1 : 0;

    // Tax
    $tax_class = $conn->real_escape_string(trim($_POST['tax_class'] ?? ''));
    $gst_rate = !empty($_POST['gst_rate']) ? floatval($_POST['gst_rate']) : NULL;

    // SEO
    $meta_title = $conn->real_escape_string(trim($_POST['meta_title'] ?? ''));
    $meta_description = $conn->real_escape_string(trim($_POST['meta_description'] ?? ''));
    $meta_keywords = $conn->real_escape_string(trim($_POST['meta_keywords'] ?? ''));
    $canonical_url = $conn->real_escape_string(trim($_POST['canonical_url'] ?? ''));
    $search_keywords = $conn->real_escape_string(trim($_POST['search_keywords'] ?? ''));

    // Flags
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;
    $is_bulk_item = isset($_POST['is_bulk_item']) ? 1 : 0;

    // Bulk pricing settings
    $bulk_pricing_model = $conn->real_escape_string($_POST['bulk_pricing_model']);
    $has_tiered_pricing = isset($_POST['has_tiered_pricing']) ? 1 : 0;

    // Generate product code if not provided
    if (empty($_POST['product_code'])) {
        $year = date('Y');
        $month = date('m');
        $random = rand(1000, 9999);
        $product_code = "PROD-{$year}{$month}-{$random}";
    } else {
        $product_code = $conn->real_escape_string($_POST['product_code']);
    }

    // Generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    // Handle main image upload
    $main_image = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/main/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION);
        $main_image = time() . '_main_' . rand(1000, 9999) . '.' . $file_ext;
        move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image);
    }

    // Handle hover image upload
    $hover_image = '';
    if (isset($_FILES['hover_image']) && $_FILES['hover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/hover/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['hover_image']['name'], PATHINFO_EXTENSION);
        $hover_image = time() . '_hover_' . rand(1000, 9999) . '.' . $file_ext;
        move_uploaded_file($_FILES['hover_image']['tmp_name'], $upload_dir . $hover_image);
    }

    // Insert product
    $sql = "INSERT INTO products (
        sub_category_id, product_code, name, slug, short_description, description,
        product_type, has_variants, size, weight, dimensions, material, color,
        base_retail_price, base_wholesale_price, cost_price, mrp,
        selling_mode, min_order_quantity, max_order_quantity, bulk_min_quantity, is_bulk_only,
        stock_quantity, low_stock_threshold, track_inventory, allow_backorder,
        shipping_class, shipping_weight, free_shipping,
        tax_class, gst_rate,
        main_image, hover_image,
        meta_title, meta_description, meta_keywords, canonical_url, search_keywords,
        is_active, is_featured, is_new, is_on_sale, is_trending, is_bulk_item,
        bulk_pricing_model, has_tiered_pricing
    ) VALUES (
        $sub_category_id, '$product_code', '$name', '$slug', '$short_description', '$description',
        '$product_type', $has_variants, '$size', " . ($weight !== NULL ? $weight : "NULL") . ", '$dimensions', '$material', '$color',
        " . ($base_retail_price !== NULL ? $base_retail_price : "NULL") . ", " . ($base_wholesale_price !== NULL ? $base_wholesale_price : "NULL") . ", " . ($cost_price !== NULL ? $cost_price : "NULL") . ", " . ($mrp !== NULL ? $mrp : "NULL") . ",
        '$selling_mode', $min_order_quantity, " . ($max_order_quantity !== NULL ? $max_order_quantity : "NULL") . ", $bulk_min_quantity, $is_bulk_only,
        $stock_quantity, $low_stock_threshold, $track_inventory, $allow_backorder,
        '$shipping_class', " . ($shipping_weight !== NULL ? $shipping_weight : "NULL") . ", $free_shipping,
        '$tax_class', " . ($gst_rate !== NULL ? $gst_rate : "NULL") . ",
        " . (!empty($main_image) ? "'$main_image'" : "NULL") . ", " . (!empty($hover_image) ? "'$hover_image'" : "NULL") . ",
        '$meta_title', '$meta_description', '$meta_keywords', '$canonical_url', '$search_keywords',
        $is_active, $is_featured, $is_new, $is_on_sale, $is_trending, $is_bulk_item,
        '$bulk_pricing_model', $has_tiered_pricing
    )";

    if ($conn->query($sql)) {
        $product_id = $conn->insert_id;

        // Handle gallery images
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $gallery_dir = '../uploads/products/gallery/';
            if (!is_dir($gallery_dir)) {
                mkdir($gallery_dir, 0777, true);
            }

            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_ext = pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION);
                    $gallery_name = time() . '_gallery_' . $key . '_' . rand(1000, 9999) . '.' . $file_ext;
                    move_uploaded_file($tmp_name, $gallery_dir . $gallery_name);

                    $conn->query("INSERT INTO product_images (product_id, image_url, image_type, sort_order) 
                                 VALUES ($product_id, '$gallery_name', 'gallery', $key)");
                }
            }
        }

        // Handle tiered pricing
        if ($has_tiered_pricing && isset($_POST['tiers']) && is_array($_POST['tiers'])) {
            foreach ($_POST['tiers'] as $tier) {
                $tier_name = $conn->real_escape_string($tier['name']);
                $tier_level = intval($tier['level']);
                $min_qty = intval($tier['min_qty']);
                $max_qty = !empty($tier['max_qty']) ? intval($tier['max_qty']) : NULL;
                $price = floatval($tier['price']);
                $discount_percent = !empty($tier['discount_percent']) ? floatval($tier['discount_percent']) : NULL;
                $is_active = isset($tier['is_active']) ? 1 : 1;

                $conn->query("INSERT INTO bulk_pricing_tiers (
                    product_id, tier_name, tier_level, min_quantity, max_quantity, 
                    price_per_piece, discount_percentage, is_active, applies_to
                ) VALUES (
                    $product_id, '$tier_name', $tier_level, $min_qty, " . ($max_qty !== NULL ? $max_qty : "NULL") . ",
                    $price, " . ($discount_percent !== NULL ? $discount_percent : "NULL") . ", $is_active, 'all'
                )");
            }
        }

        // Handle shipping restrictions
        if (isset($_POST['shipping_pincodes']) && is_array($_POST['shipping_pincodes'])) {
            foreach ($_POST['shipping_pincodes'] as $pincode_data) {
                $pincode = $conn->real_escape_string($pincode_data['pincode']);
                $is_serviceable = isset($pincode_data['serviceable']) ? 1 : 0;
                $delivery_days = !empty($pincode_data['delivery_days']) ? intval($pincode_data['delivery_days']) : NULL;
                $additional_charge = !empty($pincode_data['additional_charge']) ? floatval($pincode_data['additional_charge']) : 0;

                $conn->query("INSERT INTO product_shipping_restrictions (
                    product_id, pincode, is_serviceable, delivery_days, additional_charge, created_at
                ) VALUES (
                    $product_id, '$pincode', $is_serviceable, " . ($delivery_days !== NULL ? $delivery_days : "NULL") . ", $additional_charge, NOW()
                )");
            }
        }

        $_SESSION['success_message'] = "Product added successfully! Product ID: " . $product_id;
        header("Location: product-edit.php?id=" . $product_id);
        exit;
    } else {
        $_SESSION['error_message'] = "Error adding product: " . $conn->error;
        header("Location: product-add.php");
        exit;
    }
}

// NOW include header and sidebar
include 'layout/header.php';
include 'layout/sidebar.php';

// Get sub categories for dropdown
$sub_categories = [];
$sql = "SELECT sc.id, sc.name, mc.name as main_name, mc.category_code 
        FROM sub_categories sc
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE sc.is_active = 1 
        ORDER BY mc.name, sc.name";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $sub_categories[] = $row;
    }
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
                <i class="fas fa-plus-circle"></i>
                Add New Product
            </h2>
            <div class="header-actions">
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Product Form -->
        <form method="POST" enctype="multipart/form-data" id="productForm" class="product-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Sub Category <span class="required">*</span></label>
                        <select name="sub_category_id" required class="form-control">
                            <option value="">Select Sub Category</option>
                            <?php foreach ($sub_categories as $sc): ?>
                                <option value="<?php echo $sc['id']; ?>">
                                    <?php echo htmlspecialchars($sc['main_name']); ?> → <?php echo htmlspecialchars($sc['name']); ?> (<?php echo $sc['category_code']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Product Name <span class="required">*</span></label>
                            <input type="text" name="name" required class="form-control" id="product_name" placeholder="Enter product name">
                        </div>
                        <div class="form-group half">
                            <label>Product Code</label>
                            <input type="text" name="product_code" class="form-control" placeholder="Auto-generate if empty">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" class="form-control" placeholder="Leave empty to auto-generate">
                        <small>URL-friendly version of the product name</small>
                    </div>

                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2" placeholder="Brief description for listings"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Full Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Detailed product description"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Product Type</label>
                            <select name="product_type" class="form-control" id="product_type">
                                <option value="simple">Simple</option>
                                <option value="variable">Variable (with variants)</option>
                                <option value="bundle">Bundle</option>
                            </select>
                        </div>
                        <div class="form-group half">
                            <label>&nbsp;</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="has_variants" id="has_variants"> Has Variants
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Size</label>
                            <input type="text" name="size" class="form-control" placeholder="e.g., Medium, Large, 1kg">
                        </div>
                        <div class="form-group half">
                            <label>Color</label>
                            <input type="text" name="color" class="form-control" placeholder="e.g., Red, Blue, Green">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Weight (kg)</label>
                            <input type="number" name="weight" step="0.01" class="form-control" placeholder="0.00">
                        </div>
                        <div class="form-group half">
                            <label>Dimensions (LxWxH)</label>
                            <input type="text" name="dimensions" class="form-control" placeholder="e.g., 10x20x30 cm">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Material</label>
                        <input type="text" name="material" class="form-control" placeholder="e.g., Cotton, Wood, Plastic">
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="form-section">
                    <h3><i class="fas fa-tag"></i> Pricing</h3>

                    <div class="form-row" style="margin-top: 10px;">
                        <div class="form-group half">
                            <label>MRP</label>
                            <input type="number" name="mrp" step="0.01" class="form-control" id="mrp" placeholder="0.00">
                        </div>
                        <div class="form-group half">
                            <label>Cost Price</label>
                            <input type="number" name="cost_price" step="0.01" class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Retail Price</label>
                            <input type="number" name="base_retail_price" step="0.01" class="form-control" id="retail_price" placeholder="0.00">
                        </div>
                        <div class="form-group half">
                            <label>Wholesale Price</label>
                            <input type="number" name="base_wholesale_price" step="0.01" class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Selling Mode</label>
                        <select name="selling_mode" class="form-control">
                            <option value="both">Both (Single & Bulk)</option>
                            <option value="single_only">Single Only</option>
                            <option value="bulk_only">Bulk Only</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Min Order Quantity</label>
                            <input type="number" name="min_order_quantity" class="form-control" value="1">
                        </div>
                        <div class="form-group half">
                            <label>Max Order Quantity</label>
                            <input type="number" name="max_order_quantity" class="form-control" placeholder="Unlimited">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label>Bulk Min Quantity</label>
                            <input type="number" name="bulk_min_quantity" class="form-control" value="10">
                        </div>
                        <div class="form-group half">
                            <label>&nbsp;</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="is_bulk_only"> Bulk Only Product
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Bulk Pricing Model</label>
                        <select name="bulk_pricing_model" class="form-control">
                            <option value="fixed">Fixed Price</option>
                            <option value="tiered">Tiered Pricing</option>
                            <option value="range">Range Based</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="checkbox-group">
                            <label>
                                <input type="checkbox" name="has_tiered_pricing" id="has_tiered_pricing"> Enable Tiered Pricing
                            </label>
                        </div>
                    </div>

                    <!-- Tiered Pricing Container -->
                    <div id="tiered_pricing_container" style="display: none;">
                        <div class="tiered-pricing">
                            <h4>Tiered Pricing Tiers</h4>
                            <div id="tiers_list">
                                <div class="tier-item">
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label>Tier Name</label>
                                            <input type="text" name="tiers[0][name]" class="form-control" placeholder="e.g., Bronze">
                                        </div>
                                        <div class="form-group half">
                                            <label>Level</label>
                                            <input type="number" name="tiers[0][level]" class="form-control" value="1">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label>Min Quantity</label>
                                            <input type="number" name="tiers[0][min_qty]" class="form-control" required>
                                        </div>
                                        <div class="form-group half">
                                            <label>Max Quantity</label>
                                            <input type="number" name="tiers[0][max_qty]" class="form-control" placeholder="Unlimited">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label>Price per Piece</label>
                                            <input type="number" name="tiers[0][price]" step="0.01" class="form-control" required>
                                        </div>
                                        <div class="form-group half">
                                            <label>Discount %</label>
                                            <input type="number" name="tiers[0][discount_percent]" step="0.01" class="form-control">
                                        </div>
                                    </div>
                                    <div class="checkbox-group">
                                        <label>
                                            <input type="checkbox" name="tiers[0][is_active]" checked> Active
                                        </label>
                                    </div>
                                    <hr>
                                </div>
                            </div>
                            <button type="button" onclick="addTier()" class="btn btn-secondary">+ Add Another Tier</button>
                        </div>
                    </div>
                </div>

                <!-- Inventory Section -->
                <div class="form-section">
                    <h3><i class="fas fa-warehouse"></i> Inventory</h3>

                    <div class="form-row" style="margin-top: 10px;">
                        <div class="form-group half">
                            <label>Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" value="0">
                        </div>
                        <div class="form-group half">
                            <label>Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" class="form-control" value="5">
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="track_inventory" checked> Track Inventory
                        </label>
                        <label>
                            <input type="checkbox" name="allow_backorder"> Allow Backorder
                        </label>
                    </div>
                </div>

                <!-- Shipping Section -->
                <div class="form-section">
                    <h3><i class="fas fa-truck"></i> Shipping</h3>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Shipping Class</label>
                        <input type="text" name="shipping_class" class="form-control" placeholder="e.g., Standard, Express, Heavy">
                    </div>

                    <div class="form-row" style="margin-top: 10px;">
                        <div class="form-group half">
                            <label>Shipping Weight (kg)</label>
                            <input type="number" name="shipping_weight" step="0.01" class="form-control">
                        </div>
                        <div class="form-group half">
                            <label>&nbsp;</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="free_shipping"> Free Shipping
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Shipping Restrictions by Pincode</label>
                        <div id="shipping_pincodes">
                            <div class="pincode-item">
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label>Pincode</label>
                                        <input type="text" name="shipping_pincodes[0][pincode]" class="form-control" placeholder="e.g., 400001">
                                    </div>
                                    <div class="form-group half">
                                        <label>Delivery Days</label>
                                        <input type="number" name="shipping_pincodes[0][delivery_days]" class="form-control">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group half">
                                        <label>Additional Charge</label>
                                        <input type="number" name="shipping_pincodes[0][additional_charge]" step="0.01" class="form-control" value="0">
                                    </div>
                                    <div class="form-group half">
                                        <label>&nbsp;</label>
                                        <div class="checkbox-group">
                                            <label>
                                                <input type="checkbox" name="shipping_pincodes[0][serviceable]" checked> Serviceable
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
                        <button type="button" onclick="addPincode()" class="btn btn-secondary">+ Add Pincode Restriction</button>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="form-section">
                    <h3><i class="fas fa-images"></i> Product Images</h3>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Main Image</label>
                        <input type="file" name="main_image" class="form-control" accept="image/*" onchange="previewImage(this, 'main_preview')">
                        <div id="main_preview" class="image-preview1"></div>
                    </div>

                    <div class="form-group">
                        <label>Hover Image</label>
                        <input type="file" name="hover_image" class="form-control" accept="image/*" onchange="previewImage(this, 'hover_preview')">
                        <div id="hover_preview" class="image-preview1"></div>
                    </div>

                    <div class="form-group">
                        <label>Gallery Images (Multiple)</label>
                        <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple onchange="previewGalleryImages(this)">
                        <div id="gallery_preview" class="gallery-preview"></div>
                        <small>You can select multiple images. They will be added to the product gallery.</small>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="form-section">
                    <h3><i class="fas fa-search"></i> SEO & Meta Data</h3>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" class="form-control" maxlength="60">
                        <small>Recommended: 50-60 characters</small>
                    </div>

                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="2" maxlength="160"></textarea>
                        <small>Recommended: 150-160 characters</small>
                    </div>

                    <div class="form-group">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3">
                    </div>

                    <div class="form-group">
                        <label>Canonical URL</label>
                        <input type="url" name="canonical_url" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Search Keywords</label>
                        <textarea name="search_keywords" class="form-control" rows="2" placeholder="Alternative search terms, comma separated"></textarea>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="form-section">
                    <h3><i class="fas fa-toggle-on"></i> Status & Flags</h3>

                    <div class="checkbox-group" style="margin-top: 10px;">
                        <label>
                            <input type="checkbox" name="is_active" checked> Active
                        </label>
                        <label>
                            <input type="checkbox" name="is_featured"> Featured
                        </label>
                        <label>
                            <input type="checkbox" name="is_new"> New Arrival
                        </label>
                        <label>
                            <input type="checkbox" name="is_on_sale"> On Sale
                        </label>
                        <label>
                            <input type="checkbox" name="is_trending"> Trending
                        </label>
                        <label>
                            <input type="checkbox" name="is_bulk_item"> Bulk Item
                        </label>
                    </div>
                </div>

                <!-- Tax Section -->
                <div class="form-section">
                    <h3><i class="fas fa-percent"></i> Tax Information</h3>

                    <div class="form-group" style="margin-top: 10px;">
                        <label>Tax Class</label>
                        <select name="tax_class" class="form-control">
                            <option value="">Standard</option>
                            <option value="reduced">Reduced Rate</option>
                            <option value="zero">Zero Rate</option>
                            <option value="exempt">Exempt</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>GST Rate (%)</label>
                        <select name="gst_rate" class="form-control">
                            <option value="">No GST</option>
                            <option value="0">0%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18">18%</option>
                            <option value="28">28%</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    <i class="fas fa-save"></i> Save Product
                </button>
                <a href="products.php" class="btn btn-secondary btn-large">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</main>

<style>
    /* Product Form Styles */
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
        padding: 15px;
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

    .product-form {
        border-radius: 8px;
        overflow: hidden;
    }


    .form-section {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 0px !important;
        overflow: hidden;
    }

    .form-section h3 {
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

    .form-section h3 i {
        color: var(--gold);
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
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(26, 104, 91, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .checkbox-group {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 0 20px;
        margin-bottom: 15px;
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

    .image-preview1 {
        margin-top: 10px;
    }

    .image-preview1 img {
        max-width: 150px;
        max-height: 150px;
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 5px;
    }

    .gallery-preview {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .gallery-preview img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 2px;
    }

    .tier-item,
    .pincode-item {
        background: var(--gray-bg);
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid var(--border);
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
        background: var(--gray-bg);
        border-color: var(--teal);
    }

    .btn-large {
        padding: 12px 30px;
        font-size: 16px;
    }

    .form-actions {
        padding: 20px;
        background: var(--gray-bg);
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 15px;
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

    hr {
        margin: 15px 0;
        border: none;
        border-top: 1px solid var(--border);
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 10px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-large {
            width: 100%;
            justify-content: center;
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
    let tierCounter = 1;
    let pincodeCounter = 1;

    // Auto-generate slug from name
    document.getElementById('product_name').addEventListener('keyup', function() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (!slugInput.value || slugInput.value === '') {
            let slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });

    // Show/hide tiered pricing based on checkbox
    document.getElementById('has_tiered_pricing').addEventListener('change', function() {
        const container = document.getElementById('tiered_pricing_container');
        container.style.display = this.checked ? 'block' : 'none';
    });

    // Add tier function
    function addTier() {
        const tiersList = document.getElementById('tiers_list');
        const newTier = document.createElement('div');
        newTier.className = 'tier-item';
        newTier.innerHTML = `
            <div class="form-row">
                <div class="form-group half">
                    <label>Tier Name</label>
                    <input type="text" name="tiers[${tierCounter}][name]" class="form-control" placeholder="e.g., Silver">
                </div>
                <div class="form-group half">
                    <label>Level</label>
                    <input type="number" name="tiers[${tierCounter}][level]" class="form-control" value="${tierCounter + 1}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group half">
                    <label>Min Quantity</label>
                    <input type="number" name="tiers[${tierCounter}][min_qty]" class="form-control" required>
                </div>
                <div class="form-group half">
                    <label>Max Quantity</label>
                    <input type="number" name="tiers[${tierCounter}][max_qty]" class="form-control" placeholder="Unlimited">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group half">
                    <label>Price per Piece</label>
                    <input type="number" name="tiers[${tierCounter}][price]" step="0.01" class="form-control" required>
                </div>
                <div class="form-group half">
                    <label>Discount %</label>
                    <input type="number" name="tiers[${tierCounter}][discount_percent]" step="0.01" class="form-control">
                </div>
            </div>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="tiers[${tierCounter}][is_active]" checked> Active
                </label>
                <button type="button" onclick="this.closest('.tier-item').remove()" class="btn-icon" style="background: #dc3545; color: white;">Remove</button>
            </div>
            <hr>
        `;
        tiersList.appendChild(newTier);
        tierCounter++;
    }

    // Add pincode function
    function addPincode() {
        const pincodesList = document.getElementById('shipping_pincodes');
        const newPincode = document.createElement('div');
        newPincode.className = 'pincode-item';
        newPincode.innerHTML = `
            <div class="form-row">
                <div class="form-group half">
                    <label>Pincode</label>
                    <input type="text" name="shipping_pincodes[${pincodeCounter}][pincode]" class="form-control" placeholder="e.g., 400001">
                </div>
                <div class="form-group half">
                    <label>Delivery Days</label>
                    <input type="number" name="shipping_pincodes[${pincodeCounter}][delivery_days]" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group half">
                    <label>Additional Charge</label>
                    <input type="number" name="shipping_pincodes[${pincodeCounter}][additional_charge]" step="0.01" class="form-control" value="0">
                </div>
                <div class="form-group half">
                    <label>&nbsp;</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="shipping_pincodes[${pincodeCounter}][serviceable]" checked> Serviceable
                        </label>
                        <button type="button" onclick="this.closest('.pincode-item').remove()" class="btn-icon" style="background: #dc3545; color: white;">Remove</button>
                    </div>
                </div>
            </div>
            <hr>
        `;
        pincodesList.appendChild(newPincode);
        pincodeCounter++;
    }

    // Preview main/hover image
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Preview gallery images
    function previewGalleryImages(input) {
        const preview = document.getElementById('gallery_preview');
        preview.innerHTML = '';
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
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