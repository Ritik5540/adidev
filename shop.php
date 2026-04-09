<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// Process filters (category or subcategory via slug)
$subCategoryId = null;
$mainCategoryId = null;
$currentCategory = null;
$categoryType = null;

if (!empty($_GET['sub'])) {
    $slug = trim($_GET['sub']);
    $stmt = db_execute(
        'SELECT id, name, meta_title, meta_description, meta_keywords, main_category_id
         FROM sub_categories
         WHERE slug = ? AND is_active = 1
         LIMIT 1',
        's',
        [$slug]
    );
    $res = $stmt->get_result();
    $currentCategory = $res->fetch_assoc();
    $stmt->close();

    if ($currentCategory) {
        $subCategoryId = (int) $currentCategory['id'];
        $categoryType = 'sub';
    }
} elseif (!empty($_GET['category'])) {
    $slug = trim($_GET['category']);
    $stmt = db_execute(
        'SELECT id, name, meta_title, meta_description, meta_keywords
         FROM main_categories
         WHERE slug = ? AND is_active = 1
         LIMIT 1',
        's',
        [$slug]
    );
    $res = $stmt->get_result();
    $currentCategory = $res->fetch_assoc();
    $stmt->close();

    if ($currentCategory) {
        $mainCategoryId = (int) $currentCategory['id'];
        $categoryType = 'main';
    }
}

// Get filter parameters from AJAX or URL
$minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : 100000;
$selectedColors = isset($_GET['colors']) ? explode(',', $_GET['colors']) : [];
$selectedRatings = isset($_GET['ratings']) ? explode(',', $_GET['ratings']) : [];
$stockStatus = isset($_GET['stock']) ? $_GET['stock'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 12;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Build filters array
$filters = [
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'colors' => $selectedColors,
    'ratings' => $selectedRatings,
    'stock_status' => $stockStatus,
    'sort_by' => $sortBy,
    'limit' => $perPage,
    'offset' => $offset
];

if ($subCategoryId !== null) {
    $filters['sub_category_id'] = $subCategoryId;
}
if ($mainCategoryId !== null) {
    $filters['main_category_id'] = $mainCategoryId;
}
$user_id = current_user_id() ?? 0;
$currency = get_user_currency($user_id);
// Get products and total count
$products = get_productsBySubCat($filters , $currency);
$totalProducts = get_products_count($filters);
$totalPages = ceil($totalProducts / $perPage);

// Get price range for slider
$priceRange = get_price_range($filters);

// Get all colors with counts for filter
$allColors = get_product_colors_with_counts($filters);

// Get categories for sidebar
$sidebarMain = get_main_categories_for_menu();

// SEO meta for shop page
if ($currentCategory) {
    $page_meta = [
        'title'       => $currentCategory['meta_title'] ?: ($currentCategory['name'] . ' | Shop | Adidev'),
        'description' => $currentCategory['meta_description'] ?: 'Browse products in ' . $currentCategory['name'] . ' on Adidev.',
        'keywords'    => $currentCategory['meta_keywords'] ?: $currentCategory['name'] . ', Adidev products',
    ];
} else {
    $page_meta = [
        'title'       => 'Shop | Adidev',
        'description' => 'Browse all available products on Adidev.',
        'keywords'    => 'Adidev, products, shop, ecommerce',
    ];
}

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
                        <h1><?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : 'Shop'; ?></h1>
                        <ul>
                            <li><a href="/"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#"><?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : 'Shop'; ?></a></li>
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
    SHOP PAGE START
============================-->
<section class="shop_page mt_100 mb_100">
    <div class="container">
        <div class="row">
            <div class="col-xxl-2 col-lg-4 col-xl-3">
                <div id="sticky_sidebar">
                    <div class="shop_filter_btn d-lg-none"> Filter </div>
                    <div class="shop_filter_area">
                        <!-- Price Range Filter
                        <div class="sidebar_range">
                            <h3>Price Range</h3>
                            <div class="range_slider" data-min="<?php #echo $priceRange['min']; ?>" data-max="<?php #echo $priceRange['max']; ?>"></div>
                            <div class="price-inputs">
                                <input type="text" id="min_price_input" value="<?php #echo $minPrice; ?>" placeholder="Min">
                                <span>-</span>
                                <input type="text" id="max_price_input" value="<?php #echo $maxPrice == 100000 ? '' : $maxPrice; ?>" placeholder="Max">
                                <button id="apply_price_filter" class="btn-sm">Apply</button>
                            </div>
                        </div> -->

                        <!-- Stock Status Filter -->
                        <div class="sidebar_status">
                            <h3>Product Status</h3>
                            <div class="form-check">
                                <input class="form-check-input stock-filter" type="checkbox" value="in_stock" id="stock_in_stock" <?php echo $stockStatus == 'in_stock' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="stock_in_stock">
                                    In Stock
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input stock-filter" type="checkbox" value="out_of_stock" id="stock_out_of_stock" <?php echo $stockStatus == 'out_of_stock' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="stock_out_of_stock">
                                    Out of Stock
                                </label>
                            </div>
                        </div>

                        <!-- Categories Filter -->
                        <div class="sidebar_category">
                            <h3>Categories</h3>
                            <ul>
                                <?php foreach ($sidebarMain as $mainCat) : ?>
                                    <li>
                                        <a href="shop.php?category=<?php echo urlencode($mainCat['slug']); ?>">
                                            <?php echo htmlspecialchars($mainCat['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Rating Filter -->
                        <div class="sidebar_rating">
                            <h3>Rating</h3>
                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                <div class="form-check">
                                    <input class="form-check-input rating-filter" type="checkbox"
                                        value="<?php echo $rating; ?>"
                                        id="rating_<?php echo $rating; ?>"
                                        <?php echo in_array($rating, $selectedRatings) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="rating_<?php echo $rating; ?>">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $rating ? '' : '-o'; ?>" style="color: #ffac00;"></i>
                                        <?php endfor; ?>
                                        <?php echo $rating; ?> star or above
                                    </label>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xxl-10 col-lg-8 col-xl-9">
                <div class="product_page_top">
                    <div class="row">
                        <div class="col-4 col-xl-6 col-md-6">
                            <div class="product_page_top_button">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <button class="nav-link active" id="grid-view-tab" data-bs-toggle="tab"
                                            data-bs-target="#grid-view" type="button" role="tab">
                                            <i class="fas fa-th"></i>
                                        </button>
                                        <button class="nav-link" id="list-view-tab" data-bs-toggle="tab"
                                            data-bs-target="#list-view" type="button" role="tab">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                    </div>
                                </nav>
                                <p>Showing <?php echo ($offset + 1); ?>–<?php echo min($offset + $perPage, $totalProducts); ?> of <?php echo $totalProducts; ?> results</p>
                            </div>
                        </div>
                        <div class="col-8 col-xl-6 col-md-6">
                            <ul class="product_page_sorting">
                                <li>
                                    <select class="select_js sort-by" id="sort_by">
                                        <option value="default" <?php echo $sortBy == 'default' ? 'selected' : ''; ?>>Default Sorting</option>
                                        <option value="price_asc" <?php echo $sortBy == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                                        <option value="price_desc" <?php echo $sortBy == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                                        <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                        <option value="popular" <?php echo $sortBy == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                        <option value="rating" <?php echo $sortBy == 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                                    </select>
                                </li>
                                <li>
                                    <select class="select_js show" id="per_page">
                                        <option value="12" <?php echo $perPage == 12 ? 'selected' : ''; ?>>Show: 12</option>
                                        <option value="24" <?php echo $perPage == 24 ? 'selected' : ''; ?>>Show: 24</option>
                                        <option value="48" <?php echo $perPage == 48 ? 'selected' : ''; ?>>Show: 48</option>
                                        <option value="96" <?php echo $perPage == 96 ? 'selected' : ''; ?>>Show: 96</option>
                                    </select>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="nav-tabContent">
                    <!-- Grid View -->
                    <div class="tab-pane fade show active" id="grid-view" role="tabpanel">
                        <div class="row" id="products-grid">
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <div class="col-xxl-3 col-6 col-md-4 col-lg-6 col-xl-4 wow fadeInUp">
                                        <div class="product_item_2 product_item">
                                            <div class="product_img">
                                                <img src="<?= get_product_image($product, 'main') ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid w-100">
                                                <ul class="discount_list">
                                                    <?php if ($product['is_new']): ?>
                                                        <li class="new"> new</li>
                                                    <?php endif; ?>
                                                    <?php if ($product['is_on_sale'] && $product['mrp'] > $product['base_retail_price']): ?>
                                                        <li class="discount"> -<?php echo round((($product['mrp'] - $product['base_retail_price']) / $product['mrp']) * 100); ?>%</li>
                                                    <?php endif; ?>
                                                </ul>
                                                <ul class="btn_list">
                                                    <li>
                                                        <a href="#">
                                                            <img src="assets/images/love_icon_white.svg" class="add-to-wishlist" alt="Love" data-id="<?php echo $product['id']; ?>"
                                                                class="img-fluid">
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="#">
                                                            <img src="assets/images/cart_icon_white.svg" class="add-to-cart" alt="Love" data-id="<?php echo $product['id']; ?>"
                                                                class="img-fluid">
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="product_text">
                                                <a class="title" href="shop_details.php?id=<?= encrypt_id($product['id']) ?>">
                                                    <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                                <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span>
                                                <p class="price">
                                                    <?= pricing_format($product['base_retail_price'], $currency) ?>
                                                    <?php if ($product['mrp'] > $product['base_retail_price']): ?>
                                                        <del><?= pricing_format($product['mrp'], $currency) ?></del>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?php echo $i <= round($product['average_rating']) ? '' : '-o'; ?>"></i>
                                                    <?php endfor; ?>
                                                    <span>(<?php echo $product['review_count']; ?> reviews)</span>
                                                </p>
                                                <?php if (!empty($product['color'])): ?>
                                                    <ul class="color">
                                                        <li style="background: <?php echo getColorCode($product['color']); ?>"></li>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($product['stock_quantity'] <= 0): ?>
                                                <div class="out_of_stock">
                                                    <p>out of stock</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>No Products Found</h3>
                                        <p>Try adjusting your filters or browse other categories.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="row">
                                <div class="pagination_area">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-start mt_50" id="pagination">
                                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="#" data-page="<?php echo $page - 1; ?>">
                                                    <i class="far fa-arrow-left"></i>
                                                </a>
                                            </li>
                                            <?php
                                            $startPage = max(1, $page - 2);
                                            $endPage = min($totalPages, $page + 2);
                                            if ($startPage > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="#" data-page="1">1</a>
                                                </li>
                                                <?php if ($startPage > 2): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($endPage < $totalPages): ?>
                                                <?php if ($endPage < $totalPages - 1): ?>
                                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                                <?php endif; ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="#" data-page="<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                                                </li>
                                            <?php endif; ?>

                                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="#" data-page="<?php echo $page + 1; ?>">
                                                    <i class="far fa-arrow-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- List View -->
                    <div class="tab-pane fade" id="list-view" role="tabpanel">
                        <div class="row" id="products-list">
                            <?php foreach ($products as $product): ?>
                                <div class="col-12">
                                    <div class="product_list_item product_item_2 product_item">
                                        <div class="row align-items-center">
                                            <div class="col-md-5 col-sm-6 col-xxl-4">
                                                <div class="product_img">
                                                    <img src="<?= get_product_image($product, 'main') ?>"
                                                        alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid w-100">
                                                    <ul class="discount_list">
                                                        <?php if ($product['is_new']): ?>
                                                            <li class="new"> new</li>
                                                        <?php endif; ?>
                                                        <?php if ($product['is_on_sale'] && $product['mrp'] > $product['base_retail_price']): ?>
                                                            <li class="discount"> -<?php echo round((($product['mrp'] - $product['base_retail_price']) / $product['mrp']) * 100); ?>%</li>
                                                        <?php endif; ?>
                                                    </ul>
                                                    <ul class="btn_list">
                                                        <li>
                                                            <a href="#">
                                                                <img src="assets/images/love_icon_white.svg" class="add-to-wishlist" alt="Love" data-id="<?php echo $product['id']; ?>"
                                                                    class="img-fluid">
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#">
                                                                <img src="assets/images/cart_icon_white.svg" class="add-to-cart" alt="Love" data-id="<?php echo $product['id']; ?>"
                                                                    class="img-fluid">
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="col-md-7 col-sm-6 col-xxl-8">
                                                <div class="product_text">
                                                    <a class="title"
                                                        href="shop_details.php?id=<?= encrypt_id($product['id']) ?>">
                                                        <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                    <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span>
                                                    <p class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star<?php echo $i <= round($product['average_rating']) ? '' : '-o'; ?>"></i>
                                                        <?php endfor; ?>
                                                        <span>(<?php echo $product['review_count']; ?> reviews)</span>
                                                    </p>
                                                    <p class="price">
                                                        ₹<?php echo number_format($product['base_retail_price'], 2); ?>
                                                        <?php if ($product['mrp'] > $product['base_retail_price']): ?>
                                                            <del>₹<?php echo number_format($product['mrp'], 2); ?></del>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if (!empty($product['color'])): ?>
                                                        <ul class="color">
                                                            <li style="background: <?php echo getColorCode($product['color']); ?>"></li>
                                                        </ul>
                                                    <?php endif; ?>
                                                    <p class="short_description"><?php echo htmlspecialchars(substr($product['short_description'] ?: $product['description'], 0, 150)) . '...'; ?></p>
                                                    <a class="common_btn add-to-cart" href="#" data-id="<?php echo $product['id']; ?>">add to cart <i class="fas fa-long-arrow-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- List View Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="row">
                                <div class="pagination_area">
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-start mt_50" id="pagination-list">
                                            <!-- Same pagination as grid view -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 40px 0;
    }

    .empty-state i {
        font-size: 60px;
        color: #dee2e6;
        margin-bottom: 15px;
    }

    .empty-state h3 {
        margin-bottom: 10px;
        color: #495057;
    }

    .price-inputs {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-top: 10px;
    }

    .price-inputs input {
        width: 80px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    .btn-sm {
        padding: 5px 12px;
        background: #1a685b;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-sm:hover {
        background: #0f4f44;
    }

    .loading {
        text-align: center;
        padding: 40px;
    }

    .loading i {
        font-size: 40px;
        color: #1a685b;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>

<?php include "footer.php"; ?>