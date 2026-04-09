<?php
// shop_details.php - Single Product View
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// Get product ID from URL
$product_id = (!empty($_GET['id']) && is_numeric($id = decrypt_id($_GET['id'])))
    ? (int)$id
    : 0;

if ($product_id <= 0) {
    header("Location: shop.php");
    exit;
}

$user_id = current_user_id() ?? 0;
$currency = get_user_currency($user_id);

// 🔥 Currency based column select
$priceColumn = ($currency === 'USD') 
    ? 'p.usd_base_retail_price' 
    : 'p.base_retail_price';

$mrpColumn = ($currency === 'USD') 
    ? 'p.usd_mrp' 
    : 'p.mrp';

// Fetch product details
$product = null;
$sql = "SELECT 
            p.*,
            {$priceColumn} AS base_retail_price,
            {$mrpColumn} AS mrp,
            sc.id as sub_category_id,
            sc.name as sub_category_name,
            sc.slug as sub_category_slug,
            mc.id as main_category_id,
            mc.name as main_category_name,
            mc.slug as main_category_slug,
            mc.category_code
        FROM products p
        LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE p.id = ? AND p.is_active = 1
        LIMIT 1";

$stmt = db_execute($sql, 'i', [$product_id]);
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: shop.php");
    exit;
}

// Fetch gallery images
$gallery_images = [];
$stmt = db_execute("SELECT * FROM product_images WHERE product_id = ? AND image_type = 'gallery' ORDER BY sort_order", 'i', [$product_id]);
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $gallery_images[] = $row;
}
$stmt->close();

// Fetch product variants (for variable products)
$variants = [];
if ($product['has_variants'] || $product['product_type'] == 'variable') {
    $stmt = db_execute("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1 ORDER BY is_default DESC, id ASC", 'i', [$product_id]);
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $variants[] = $row;
    }
    $stmt->close();
}

// Fetch product reviews
$reviews = [];
$stmt = db_execute("SELECT r.*, u.first_name, u.last_name 
                    FROM product_reviews r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = ? AND r.status = 'approved'
                    ORDER BY r.created_at DESC
                    LIMIT 10", 'i', [$product_id]);
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

$reviews_count = count($reviews);
$avg_rating = $product['average_rating'] ?? 0;

// Fetch related products (same sub-category)
$related_products = [];

$stmt = db_execute("
    SELECT 
        p.id, 
        p.name, 
        p.slug, 
        {$priceColumn} AS base_retail_price,
        {$mrpColumn} AS mrp, 
        p.main_image, 
        p.average_rating, 
        p.review_count, 
        p.is_new, 
        p.is_on_sale, 
        p.color
    FROM products p
    WHERE 
        p.sub_category_id = ?
        AND p.id != ?
        AND p.is_active = 1

        -- ✅ only products with image
        AND p.main_image IS NOT NULL
        AND p.main_image != ''

    ORDER BY RAND()
    LIMIT 6
", 'ii', [$product['sub_category_id'], $product_id]);

$result = $stmt->get_result();
$related_products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Helper function to format price
function formatPrice($price)
{
    return $price ? '₹' . number_format($price, 2) : '₹0.00';
}

// Helper function for star rating
function getStarRating($rating)
{
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= round($rating)) {
            $html .= '<i class="fas fa-star"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    return $html;
}

// Update view count
db_execute("UPDATE products SET view_count = view_count + 1 WHERE id = ?", 'i', [$product_id]);

// SEO Meta
$page_meta = [
    'title' => $product['meta_title'] ?: ($product['name'] . ' | Adidev'),
    'description' => $product['meta_description'] ?: ($product['short_description'] ?: 'Shop ' . $product['name'] . ' at Adidev'),
    'keywords' => $product['meta_keywords'] ?: $product['name'] . ', Adidev, products'
];

include "header.php";
?>

<!--=========================
    PAGE BANNER START
==========================-->
<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                        <ul>
                            <li><a href="/"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="shop.php">Shop</a></li>
                            <li><a href="shop.php?category=<?php echo $product['main_category_slug']; ?>"><?php echo htmlspecialchars($product['main_category_name']); ?></a></li>
                            <li><a href="shop.php?sub=<?php echo $product['sub_category_slug']; ?>"><?php echo htmlspecialchars($product['sub_category_name']); ?></a></li>
                            <li><?php echo htmlspecialchars($product['name']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
    PAGE BANNER END
==========================-->

<!--============================
    SHOP DETAILS START
============================-->
<section class="shop_details mt_100">
    <div class="container">
        <div class="row">
            <div class="col-xxl-10">
                <div class="row">
                    <div class="col-lg-6 col-md-10 wow fadeInLeft">
                        <div class="shop_details_slider_area">
                            <div class="row">
                                <?php if (!empty($gallery_images) || !empty($product['main_image'])): ?>
                                    <div class="col-xl-2 col-lg-3 col-md-3 order-2 order-md-1">
                                        <div class="row details_slider_nav">
                                            <!-- Main Image Thumbnail -->
                                            <?php if (!empty($product['main_image']) && file_exists('./uploads/products/main/' . $product['main_image'])): ?>
                                                <div class="col-12">
                                                    <div class="details_slider_nav_item">
                                                        <img src="<?= get_product_image($product, 'main') ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid w-100">
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Gallery Thumbnails -->
                                            <?php foreach ($gallery_images as $gallery): ?>
                                                <?php if (file_exists('./uploads/products/gallery/' . $gallery['image_url'])): ?>
                                                    <div class="col-12">
                                                        <div class="details_slider_nav_item">
                                                            <img src="<?= get_product_image($gallery, 'gallery') ?>" alt="Gallery Image" class="img-fluid w-100">
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-xl-10 col-lg-9 col-md-9 order-md-1">
                                        <div class="row details_slider_thumb">
                                            <!-- Main Image -->
                                            <?php if (!empty($product['main_image']) && file_exists('./uploads/products/main/' . $product['main_image'])): ?>
                                                <div class="col-12">
                                                    <div class="details_slider_thumb_item">
                                                        <img src="<?= get_product_image($product, 'main') ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid w-100">
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Gallery Images -->
                                            <?php foreach ($gallery_images as $gallery): ?>
                                                <?php if (file_exists('./uploads/products/gallery/' . $gallery['image_url'])): ?>
                                                    <div class="col-12">
                                                        <div class="details_slider_thumb_item">
                                                            <img src="<?= get_product_image($gallery, 'gallery') ?>" alt="Gallery Image" class="img-fluid w-100">
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="col-12 text-center">
                                        <div class="no-image-placeholder">
                                            <i class="fas fa-image fa-4x"></i>
                                            <p>No image available</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInUp">
                        <div class="shop_details_text">
                            <p class="category"><?php echo htmlspecialchars($product['main_category_name']); ?> / <?php echo htmlspecialchars($product['sub_category_name']); ?></p>
                            <h2 class="details_title"><?php echo htmlspecialchars($product['name']); ?></h2>
                            <div class="d-flex flex-wrap align-items-center">
                                <p class="stock <?php echo $product['stock_quantity'] > 0 ? 'in_stock' : 'out_stock'; ?>">
                                    <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                </p>
                                <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span> &nbsp; &nbsp;
                                <p class="rating">
                                    <?php echo getStarRating($avg_rating); ?>
                                    <span>(<?php echo $reviews_count; ?> reviews)</span>
                                </p>
                                
                            </div>
                            <h3 class="price">
                                <?= pricing_format($product['base_retail_price'], $currency) ?>
                                <?php if ($product['mrp'] > $product['base_retail_price']): ?>
                                    <del><?= pricing_format($product['mrp'], $currency) ?></del>
                                <?php endif; ?>
                            </h3>
                            <p class="short_description"><?php echo nl2br(htmlspecialchars($product['short_description'] ?: $product['description'])); ?></p>

                            <!-- Color Variants -->
                            <?php if (!empty($product['color'])): ?>
                                <div class="details_single_variant">
                                    <p class="variant_title">Color :</p>
                                    <ul class="details_variant_color">
                                        <li class="active" style="background: <?php echo getColorCode($product['color']); ?>;"></li>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <!-- Size Variants from Attributes -->
                            <?php if (!empty($variants)):
                                $sizes = [];
                                foreach ($variants as $variant) {
                                    if (!empty($variant['size'])) {
                                        $sizes[] = $variant['size'];
                                    }
                                }
                                $sizes = array_unique($sizes);
                            ?>
                                <?php if (!empty($sizes)): ?>
                                    <div class="details_single_variant">
                                        <p class="variant_title">Size :</p>
                                        <ul class="details_variant_size">
                                            <?php foreach ($sizes as $size): ?>
                                                <li data-size="<?php echo strtolower($size); ?>"><?php echo strtoupper($size); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Quantity and Add to Cart -->
                            <div class="d-flex flex-wrap align-items-center">
                                <!-- <div class="details_qty_input">
                                    <button class="minus"><i class="fal fa-minus"></i></button>
                                    <input type="text" id="quantity" value="1">
                                    <button class="plus"><i class="fal fa-plus"></i></button>
                                </div> -->
                                <div class="details_btn_area">
                                    <!-- <a class="common_btn buy_now" href="#" data-id="<?php echo $product['id']; ?>">Buy Now <i class="fas fa-long-arrow-right"></i></a> -->
                                    <a class="common_btn add-to-cart" href="#" data-id="<?php echo $product['id']; ?>">Add to cart <i class="fas fa-long-arrow-right"></i></a>
                                </div>
                            </div>

                            <ul class="details_list_btn">
                                <li>
                                    <a href="#" class="add-to-wishlist" data-id="<?php echo $product['id']; ?>"> <i class="fal fa-heart"></i> Add Wishlist </a>
                                </li>
                                <!-- <li>
                                    <a href="#"><i class="fal fa-exchange"></i> Compare</a>
                                </li> -->
                            </ul>

                            <ul class="details_tags_sku">
                                <li><span>SKU:</span> <?php echo htmlspecialchars($product['product_code']); ?></li>
                                <li><span>Category:</span> <?php echo htmlspecialchars($product['main_category_name']); ?></li>
                                <?php if ($product['material']): ?>
                                    <li><span>Material:</span> <?php echo htmlspecialchars($product['material']); ?></li>
                                <?php endif; ?>
                                <?php if ($product['size']): ?>
                                    <li><span>Size:</span> <?php echo htmlspecialchars($product['size']); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tabs Section -->
                <div class="row mt_90 wow fadeInUp">
                    <div class="col-12">
                        <div class="shop_details_des_area">
                            <ul class="nav nav-pills" id="pills-tab2" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="description-tab" data-bs-toggle="pill"
                                        data-bs-target="#description" type="button" role="tab">Description</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="info-tab" data-bs-toggle="pill"
                                        data-bs-target="#info" type="button" role="tab">Additional info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="reviews-tab" data-bs-toggle="pill"
                                        data-bs-target="#reviews" type="button" role="tab">Reviews (<?php echo $reviews_count; ?>)</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent2">
                                <!-- Description Tab -->
                                <div class="tab-pane fade show active" id="description" role="tabpanel">
                                    <br>
                                    <div class="shop_details_description">
                                        <h3>Product Description</h3>
                                        <p><?php echo nl2br(htmlspecialchars($product['description'] ?: 'No description available.')); ?></p>

                                        <?php if (!empty($product['meta_keywords'])): ?>
                                            <h3>Keywords</h3>
                                            <p>
                                                <?php
                                                $keywords = explode(',', $product['meta_keywords']);

                                                $formatted = array_map(function ($item) {
                                                    return ucwords(strtolower(trim($item)));
                                                }, $keywords);

                                                echo htmlspecialchars(implode(', ', $formatted));
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Additional Info Tab -->
                                <div class="tab-pane fade" id="info" role="tabpanel">
                                    <div class="shop_details_additional_info">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <tbody>
                                                    <?php if ($product['product_code']): ?>
                                                        <tr>
                                                            <th>Product Code</th>
                                                            <td><?php echo htmlspecialchars($product['product_code']); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['size']): ?>
                                                        <tr>
                                                            <th>Size</th>
                                                            <td><?php echo htmlspecialchars($product['size']); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['color']): ?>
                                                        <tr>
                                                            <th>Color</th>
                                                            <td><?php echo htmlspecialchars($product['color']); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['material']): ?>
                                                        <tr>
                                                            <th>Material</th>
                                                            <td><?php echo htmlspecialchars($product['material']); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['weight']): ?>
                                                        <tr>
                                                            <th>Weight</th>
                                                            <td><?php echo $product['weight']; ?> kg</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['dimensions']): ?>
                                                        <tr>
                                                            <th>Dimensions</th>
                                                            <td><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if ($product['gst_rate']): ?>
                                                        <tr>
                                                            <th>GST Rate</th>
                                                            <td><?php echo $product['gst_rate']; ?>%</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews Tab -->
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    <div class="shop_details_review">
                                        <div class="single_review_list_area">
                                            <h3>Customer Reviews</h3>

                                            <?php if (!empty($reviews)): ?>
                                                <?php foreach ($reviews as $review): ?>
                                                    <div class="single_review">
                                                        <div class="img">
                                                            <img src="assets/images/avatar-placeholder.jpg" alt="Avatar" class="img-fluid w-100">
                                                        </div>
                                                        <div class="text">
                                                            <h5>
                                                                <?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?>
                                                                <span>
                                                                    <?php echo getStarRating($review['rating']); ?>
                                                                </span>
                                                            </h5>
                                                            <p class="date"><?php echo date('d F Y', strtotime($review['created_at'])); ?></p>
                                                            <p class="description"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                                                            <?php if ($review['title']): ?>
                                                                <p class="review-title"><strong><?php echo htmlspecialchars($review['title']); ?></strong></p>
                                                            <?php endif; ?>
                                                            <?php if ($review['is_verified_purchase']): ?>
                                                                <span class="verified-badge"><i class="fas fa-check-circle"></i> Verified Purchase</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="text-center py-4">
                                                    <i class="fas fa-comment-dots fa-3x mb-3" style="color: #ccc;"></i>
                                                    <p>No reviews yet. Be the first to review this product!</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-xxl-2 wow fadeInRight">
                <div id="sticky_sidebar_2">
                    <div class="shop_details_sidebar">
                        <div class="shop_details_sidebar_info">
                            <ul>
                                <!-- <li>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                        </svg>
                                    </span>
                                    Shipping worldwide
                                </li> -->
                                <li>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                    </span>
                                    Always Authentic
                                </li>
                                <li>
                                    <span>
                                        <svg fill="#7D7B7B" height="800px" width="800px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.015 512.015" xml:space="preserve">
                                            <path d="M341.333,273.074c75.281,0,136.533-61.252,136.533-136.533S416.614,0.008,341.333,0.008
                                                C266.052,0.008,204.8,61.26,204.8,136.541S266.052,273.074,341.333,273.074z M341.333,17.074
                                                c65.877,0,119.467,53.589,119.467,119.467s-53.589,119.467-119.467,119.467s-119.467-53.589-119.467-119.467
                                                S275.456,17.074,341.333,17.074z" />
                                            <path d="M507.426,358.408c-9.412-16.316-30.362-21.888-46.089-12.774l-98.219,47.804c-15.266,7.637-30.677,7.637-64.452,7.637
                                                c-33.015,0-83.422-8.337-83.925-8.414c-4.693-0.759-9.054,2.372-9.822,7.006c-0.777,4.651,2.364,9.054,7.006,9.822
                                                c2.125,0.358,52.301,8.653,86.741,8.653c35.43,0,53.214,0,72.004-9.395l98.662-48.051c3.942-2.278,8.542-2.884,12.954-1.707
                                                c4.395,1.186,8.081,4.011,10.351,7.953c2.287,3.951,2.893,8.55,1.715,12.954s-4.002,8.081-8.192,10.505l-115.379,71.808
                                                c-0.239,0.162-24.858,15.667-80.648,15.667c-48.367,0-123.11-41.182-124.186-41.771c-0.768-0.375-19.277-9.429-55.014-9.429
                                                c-4.71,0-8.533,3.823-8.533,8.533s3.823,8.533,8.533,8.533c31.036,0,47.027,7.467,47.061,7.467v-0.009
                                                c3.217,1.792,79.334,43.742,132.139,43.742c61.611,0,88.934-17.749,89.839-18.355l114.961-71.552
                                                c7.893-4.557,13.542-11.921,15.898-20.719C513.203,375.5,511.983,366.301,507.426,358.408z" />
                                            <path d="M341.333,179.208c-9.412,0-17.067-7.654-17.067-17.067c0-4.71-3.814-8.533-8.533-8.533s-8.533,3.823-8.533,8.533
                                                c0,15.855,10.914,29.107,25.6,32.922v1.212c0,4.71,3.814,8.533,8.533,8.533c4.719,0,8.533-3.823,8.533-8.533v-1.212
                                                c14.686-3.814,25.6-17.067,25.6-32.922c0-18.825-15.309-34.133-34.133-34.133c-9.412,0-17.067-7.654-17.067-17.067
                                                c0-9.412,7.654-17.067,17.067-17.067c9.412,0,17.067,7.654,17.067,17.067c0,4.71,3.814,8.533,8.533,8.533
                                                s8.533-3.823,8.533-8.533c0-15.855-10.914-29.107-25.6-32.922v-1.212c0-4.71-3.814-8.533-8.533-8.533
                                                c-4.719,0-8.533,3.823-8.533,8.533v1.212c-14.686,3.814-25.6,17.067-25.6,32.922c0,18.825,15.309,34.133,34.133,34.133
                                                c9.412,0,17.067,7.654,17.067,17.067C358.4,171.553,350.746,179.208,341.333,179.208z" />
                                            <path d="M59.733,273.074h-51.2c-4.71,0-8.533,3.823-8.533,8.533s3.823,8.533,8.533,8.533h51.2c4.702,0,8.533,3.831,8.533,8.533
                                                v187.733c0,4.702-3.831,8.533-8.533,8.533h-51.2c-4.71,0-8.533,3.823-8.533,8.533s3.823,8.533,8.533,8.533h51.2
                                                c14.114,0,25.6-11.486,25.6-25.6V298.674C85.333,284.56,73.847,273.074,59.733,273.074z" />
                                            <path d="M110.933,324.274H179.2c9.958,0,26.88,12.698,41.813,23.893c18.722,14.046,36.412,27.307,52.053,27.307h51.2
                                                c12.962,0,19.396,5.879,19.567,6.033c1.664,1.664,3.849,2.5,6.033,2.5c2.185,0,4.369-0.836,6.033-2.5
                                                c3.336-3.337,3.336-8.73,0-12.066c-1.126-1.126-11.605-11.034-31.633-11.034h-51.2c-9.958,0-26.88-12.698-41.813-23.893
                                                c-18.722-14.046-36.412-27.307-52.053-27.307h-68.267c-4.71,0-8.533,3.823-8.533,8.533S106.223,324.274,110.933,324.274z" />
                                            <path d="M42.667,456.541c0-7.057-5.743-12.8-12.8-12.8c-7.057,0-12.8,5.743-12.8,12.8c0,7.057,5.743,12.8,12.8,12.8
                                                C36.924,469.341,42.667,463.598,42.667,456.541z" />
                                        </svg>
                                    </span>
                                    Cash on Delivery Available
                                </li>
                            </ul>
                            <h5>Return & Warranty</h5>
                            <ul>
                                <li>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                        </svg>
                                    </span>
                                    14 days easy return
                                </li>
                                <li>
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                                        </svg>
                                    </span>
                                    Warranty not available
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!--============================
    RELATED PRODUCTS START
============================-->
<?php if (!empty($related_products)): ?>
    <section class="related_products mt_90 mb_70 wow fadeInUp">
        <div class="container">
            <div class="row">
                <div class="col-xl-6">
                    <div class="section_heading_2 section_heading">
                        <h3><span>Related</span> Products</h3>
                    </div>
                </div>
            </div>
            <div class="row mt_25 flash_sell_2_slider">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-xl-1-5">
                        <div class="product_item_2 product_item">
                            <div class="product_img">
                                <img src="<?= get_product_image($related, 'main') ?>"
                                    alt="<?php echo htmlspecialchars($related['name']); ?>" class="img-fluid w-100">
                                <ul class="discount_list">
                                    <?php if ($related['is_new']): ?>
                                        <li class="new">new</li>
                                    <?php endif; ?>
                                    <?php if ($related['is_on_sale'] && $related['mrp'] > $related['base_retail_price']): ?>
                                        <li class="discount"> -<?php echo round((($related['mrp'] - $related['base_retail_price']) / $related['mrp']) * 100); ?>%</li>
                                    <?php endif; ?>
                                </ul>
                                <ul class="btn_list">
                                    <li><a href="#"><img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid"></a></li>
                                    <li><a href="#" class="add-to-wishlist" data-id="<?php echo $related['id']; ?>"><img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid"></a></li>
                                    <li><a href="#" class="add-to-cart" data-id="<?php echo $related['id']; ?>"><img src="assets/images/cart_icon_white.svg" alt="Cart" class="img-fluid"></a></li>
                                </ul>
                            </div>
                            <div class="product_text">
                                <a class="title" href="shop_details.php?id=<?= encrypt_id($related['id']) ?>"><?php echo htmlspecialchars($related['name']); ?></a>
                                <p class="price">
                                    <?= pricing_format($related['base_retail_price'], $currency) ?>
                                    <?php if ($related['mrp'] > $related['base_retail_price']): ?>
                                        <del><?= pricing_format($related['mrp'], $currency) ?></del>
                                    <?php endif; ?>
                                </p>
                                <p class="rating">
                                    <?php echo getStarRating($related['average_rating']); ?>
                                    <span>(<?php echo $related['review_count']; ?> reviews)</span>
                                </p>
                                <?php if (!empty($related['color'])): ?>
                                    <ul class="color">
                                        <li class="active" style="background: <?php echo getColorCode($related['color']); ?>;"></li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<style>
    /* Additional Styles */
    .stock.in_stock {
        color: #28a745;
        background: #e8f5e9;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
    }

    .stock.out_stock {
        color: #dc3545;
        background: #ffe6e6;
        padding: 5px 12px;
        border-radius: 20px;
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
    }

    .no-image-placeholder {
        text-align: center;
        padding: 80px 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .no-image-placeholder i {
        color: #dee2e6;
        margin-bottom: 15px;
    }

    .no-image-placeholder p {
        color: #6c757d;
        margin: 0;
    }

    .verified-badge {
        display: inline-block;
        margin-top: 10px;
        font-size: 12px;
        color: #28a745;
    }

    .review-title {
        margin-top: 10px;
        font-weight: 600;
    }

    .shop_details_description h3 {
        margin-top: 20px;
        margin-bottom: 15px;
        font-size: 18px;
        color: #333;
    }

    .shop_details_description h3:first-child {
        margin-top: 0;
    }

    .table th {
        width: 30%;
        background-color: #f8f9fa;
    }

    .details_variant_size li {
        cursor: pointer;
    }

    .details_variant_size li.active {
        background-color: #1a685b;
        color: white;
        border-color: #1a685b;
    }

    .details_variant_color li {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .details_variant_color li:hover,
    .details_variant_color li.active {
        transform: scale(1.1);
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #1a685b;
    }
</style>

<script>
    $(document).ready(function() {
        // Quantity increment/decrement
        $('.minus').click(function() {
            var input = $(this).siblings('input');
            var value = parseInt(input.val());
            if (value > 1) {
                input.val(value - 1);
            }
        });

        $('.plus').click(function() {
            var input = $(this).siblings('input');
            var value = parseInt(input.val());
            input.val(value + 1);
        });

        // Size selection
        $('.details_variant_size li').click(function() {
            $('.details_variant_size li').removeClass('active');
            $(this).addClass('active');
        });

        // Color selection
        $('.details_variant_color li').click(function() {
            $('.details_variant_color li').removeClass('active');
            $(this).addClass('active');
        });

        // Add to cart
        $('.add-to-cart').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('id');
            var quantity = $('#quantity').val();

            $.ajax({
                url: 'ajax/add_to_cart.php',
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        alert('Product added to cart!');
                        updateCartCount();
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        // Buy now
        $('.buy_now').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('id');
            var quantity = $('#quantity').val();

            $.ajax({
                url: 'ajax/add_to_cart.php',
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'checkout.php';
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        // Add to wishlist
        $('.add-to-wishlist').click(function(e) {
            e.preventDefault();
            var productId = $(this).data('id');

            $.ajax({
                url: 'ajax/add_to_wishlist.php',
                type: 'POST',
                data: {
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Added to wishlist!');
                    } else {
                        alert(response.message);
                    }
                }
            });
        });

        function updateCartCount() {
            $.get('ajax/get_cart_count.php', function(data) {
                $('.cart-count').text(data.count);
            });
        }
    });
</script>

<?php include "footer.php"; ?>