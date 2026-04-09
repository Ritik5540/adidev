<?php include "header.php"; ?>
<!--=========================
        BANNER 2 START
    ==========================-->
<section class="banner_2" style="background-image: url('./assets/images/hero-background.jpg') !important; background-size: cover !important; background-position: center !important;">
    <div class="container">
        <div class="row">
            <!-- <div class="col-xl-2  d-none d-xxl-block">
                <ul class="menu_cat_item">
                    <?php foreach ($header_main_categories as $mainCat) : ?>
                        <li>
                            <a href="shop.php?category=<?php echo urlencode($mainCat['slug']); ?>">
                                <span>
                                    <?php
                                    $iconPath = !empty($mainCat['icon'])
                                        ? htmlspecialchars($mainCat['icon'])
                                        : 'assets/images/category_list_icon_1.png';
                                    ?>
                                    <img src="<?php echo $iconPath; ?>" alt="category" class="img-fluid">
                                </span>
                                <?php echo htmlspecialchars($mainCat['name']); ?>
                            </a>
                            <?php
                            $mainId = (int) $mainCat['id'];
                            $subForMain = $header_sub_categories[$mainId] ?? [];
                            if (!empty($subForMain)) :
                            ?>
                                <ul class="menu_cat_droapdown">
                                    <?php foreach ($subForMain as $subCat) : ?>
                                        <li>
                                            <a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>">
                                                <?php echo htmlspecialchars($subCat['name']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    <li class="all_category">
                        <a href="category.php">View All Categories <i class="far fa-arrow-right"></i></a>
                    </li>
                </ul>
            </div> -->
            <div class="col-xxl-9 col-lg-8">
                <div class="banner_content">
                    <div class="row banner_2_slider">
                        <div class="col-xl-12">
                            <div class="banner_slider_2 wow fadeInUp"
                                style="background: url(assets/images/slider_1.jpg);">
                                <div class="banner_slider_2_text">
                                    <h3>New arrivals of 2025</h3>
                                    <h1>Where Fashion Meets Individuality</h1>
                                    <a class="common_btn" href="shop_details.php">shop now <i
                                            class="fas fa-long-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="banner_slider_2 wow fadeInUp"
                                style="background: url(assets/images/slider_2.jpg);">
                                <div class="banner_slider_2_text">
                                    <h3>Trending of this month</h3>
                                    <h1>make your fashion look more changing</h1>
                                    <a class="common_btn" href="shop_details.php">shop now <i
                                            class="fas fa-long-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="banner_slider_2 wow fadeInUp"
                                style="background: url(assets/images/slider_3.jpg);">
                                <div class="banner_slider_2_text">
                                    <h3>Best selling of 2025</h3>
                                    <h1>Discover ypur Best fitting Clothes</h1>
                                    <a class="common_btn" href="shop_details.php">shop now <i
                                            class="fas fa-long-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-lg-4 col-sm-12 col-md-12">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="banner_2_add wow fadeInUp"
                            style="background: url(assets/images/banner_3_add_bg_1.jpg);">
                            <div class="text">
                                <h4>Summer Offer</h4>
                                <h2>Make Your Fashion Story Unique Every Day</h2>
                                <a class="common_btn" href="shop_details.php">shop now <i
                                        class="fas fa-long-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
        BANNER 2 END
    ==========================-->


<!--============================
        FEATURES START
    ==============================-->
<section class="features mt_20">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-md-6 wow fadeInUp">
                <div class="features_item purple">
                    <div class="icon">
                        <img src="assets/images/feature-icon_1.svg" alt="feature">
                    </div>
                    <div class="text">
                        <h3>Return & refund</h3>
                        <p>Money back guarantee</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 wow fadeInUp">
                <div class="features_item green">
                    <div class="icon">
                        <img src="assets/images/feature-icon_3.svg" alt="feature">
                    </div>
                    <div class="text">
                        <h3>Quality Support</h3>
                        <p>Always online 24/7</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 wow fadeInUp">
                <div class="features_item orange">
                    <div class="icon">
                        <img src="assets/images/feature-icon_2.svg" alt="feature">
                    </div>
                    <div class="text">
                        <h3>Secure Payment</h3>
                        <p>30% off by subscribing</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 wow fadeInUp">
                <div class="features_item">
                    <div class="icon">
                        <img src="assets/images/feature-icon_4.svg" alt="feature">
                    </div>
                    <div class="text">
                        <h3>Daily Offers</h3>
                        <p>20% off by subscribing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--============================
        FEATURES END
    ==============================-->


<!--============================
        CATEGORY 2 START
    ==============================-->
<section class="category category_2 mt_55">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xxl-6 col-md-3 col-xl-4">
                <div class="section_heading_2 section_heading">
                    <h3><span>Categories</span> List</h3>
                </div>
            </div>
            <div class="col-xxl-6 col-md-9 col-xl-8">
                <div class="d-flex flex-wrap justify-content-end">
                    <div class="view_all_btn_area">
                        <a class="view_all_btn" href="category.php">View all</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row category_2_slider">
            <!-- Sub Categories -->
            <?php if (!empty($header_sub_categories)) : ?>
                <?php foreach ($header_sub_categories as $mainId => $subCats) : ?>
                    <?php foreach ($subCats as $subCat) : ?>
                        <div class="col-2 wow fadeInUp">
                            <a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>" class="category_item">
                                <div class="img">
                                    <?php
                                    $img = !empty($subCat['image'])
                                        ? htmlspecialchars($subCat['image'])
                                        : 'assets/images/subcat/apparel-men.png';
                                    ?>
                                    <img src="./uploads/categories/subcategories/<?php echo $img; ?>" alt="Category" class="img-fluid w-100">
                                </div>
                                <h3><?php echo htmlspecialchars($subCat['name']); ?></h3>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="text-center">
                        <h3>No sub categories found</h3>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!--============================
        CATEGORY 2 END
    ==============================-->

<!--============================
        TRENDING PRODUCT 2 START
    ==============================-->
<section class="trending_product_2 mt_90">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xxl-6 col-md-3 col-xl-4">
                <div class="section_heading_2 section_heading">
                    <h3><span>Trending</span> Products</h3>
                </div>
            </div>
            <div class="col-xxl-6 col-md-9 col-xl-8">
                <div class="d-flex flex-wrap justify-content-end">
                    <div class="view_all_btn_area">
                        <a class="view_all_btn" href="shop.php">View all</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
            $user_id = current_user_id() ?? 0;
            $currency = get_user_currency($user_id);
        ?>
        <div class="row wow fadeInUp">
            <div class="col-12">
                <div class="product_tabs pws_tabs_list">
                    <div class="row">
                        <?php if (!empty($trending_sub_categories)) : ?>
                            <?php foreach ($trending_sub_categories as $sub_category_product) : ?>
                                <div class="col-xl-1-5 col-6 col-md-4 col-xl-3">
                                    <div class="product_item_2 product_item">
                                        <div class="product_img">
                                            <img src="<?= get_product_image($sub_category_product, 'main') ?>" alt="Product"
                                                class="img-fluid w-100">
                                            <ul class="discount_list">
                                                <li class="new"> <?php echo htmlspecialchars($sub_category_product['sub_category_name']); ?></li>
                                            </ul>
                                            <ul class="btn_list">
                                                <li>
                                                    <a href="shop.php?slug=<?= urlencode($sub_category_product['slug']) ?>">
                                                        <img src="assets/images/love_icon_white.svg" data-id="<?php echo $sub_category_product['id']; ?>" alt="Love"
                                                            class="img-fluid add-to-wishlist">
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="shop.php?slug=<?= urlencode($sub_category_product['slug']) ?>">
                                                        <img src="assets/images/cart_icon_white.svg" data-id="<?php echo $sub_category_product['id']; ?>" alt="Cart"
                                                            class="img-fluid add-to-cart">
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="product_text">
                                            <a class="title" href="shop_details.php?id=<?= encrypt_id($sub_category_product['id']) ?>"><?php echo htmlspecialchars($sub_category_product['name']); ?></a> <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span>
                                            <p class="price"><?php echo pricing_format($sub_category_product['base_retail_price'], $currency); ?> <del><?php echo pricing_format($sub_category_product['mrp'], $currency); ?></del></p>   
                                            <?php
                                            $reviews = get_product_reviews($sub_category_product['id']);
                                            $rating = get_average_product_rating($sub_category_product['id']);
                                            $fullStars = floor($rating);
                                            $halfStar  = ($rating - $fullStars) >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            ?>
                                            <p class="rating">
                                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>

                                                <?php if ($halfStar): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php endif; ?>

                                                <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                                    <i class="far fa-star"></i>
                                                <?php endfor; ?>

                                                <span>(<?= (int)get_total_product_reviews_count($sub_category_product['id']) ?> reviews)</span>
                                            </p>
                                            <!-- <ul class="product_variants_items_box">

                                                <?php
                                                $variants = get_product_variants($sub_category_product['id']);

                                                $totalVariants = count($variants);

                                                /* show first two variants */
                                                $visible = array_slice($variants, 0, 2);

                                                /* extra count */
                                                $extraCount = $totalVariants > 2 ? $totalVariants - 2 : 0;
                                                ?>

                                                <?php #foreach ($visible as $variant): ?>

                                                    <li class="product_variants_item">
                                                        <a href="shop.php?slug=<?php #urlencode($sub_category_product['slug']) ?>"
                                                            title="Size: <?php #htmlspecialchars($variant['size']) ?> | Price: ₹<?php #htmlspecialchars($variant['retail_price']) ?>">

                                                            <img
                                                                src="<?php #htmlspecialchars($variant['image'] ?? 'assets/images/product_1.png') ?>"
                                                                alt="<?php #htmlspecialchars($variant['size']) ?>"
                                                                loading="lazy"
                                                                width="30"
                                                                height="30">

                                                        </a>
                                                    </li>

                                                <?php #endforeach; ?>


                                                <?php #if ($extraCount > 0): ?>

                                                    <li class="variant_more">
                                                        <a href="shop.php?slug=<?php #urlencode($sub_category_product['slug']) ?>">
                                                            +<?php #$extraCount ?>
                                                        </a>
                                                    </li>

                                                <?php #endif; ?>

                                            </ul> -->
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <h3>No products found</h3>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>
<!--============================
        TRENDING PRODUCT 2 END
    ==============================-->

<!--================================
        BEST SELLING PRODUCT 2 START
    ==================================-->
<!-- <section class="best_selling_product_2 mt_95">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-sm-9">
                <div class="section_heading_2 section_heading">
                    <h3>Our <span>Best</span> Selling Products</h3>
                </div>
            </div>
            <div class="col-xl-6 col-sm-3">
                <div class="view_all_btn_area">
                    <a class="view_all_btn" href="shop.php">View all</a>
                </div>
            </div>
        </div>
        <div class="row mt_15">
            <div class="col-xl-7">
                <div class="row">
                    <?php #if (!empty($best_selling_products)) : ?>
                        <?php #foreach ($best_selling_products as $product) : ?>
                            <div class="col-xl-4 col-sm-6 col-md-4 wow fadeInUp">
                                <div class="best_selling_product_item">
                                    <img src="<?php #get_product_image($product, 'main', 24) ?>" alt="best sell"
                                        class="img-fluid w-100">
                                    <div class="text">
                                        <a class="title" href="shop_details.php?id=<?php #encrypt_id($product['id']) ?>"><?php #echo htmlspecialchars($product['name']); ?></a>
                                        <p class="price">₹<?php #echo htmlspecialchars($product['base_retail_price']); ?> <del>₹<?php #echo htmlspecialchars($product['mrp']); ?></del></p>
                                        <a class="buy_btn" href="shop_details.php?id=<?php #encrypt_id($product['id']) ?>">buy now <i
                                                class="far fa-arrow-up"></i></a>
                                    </div>
                                </div>
                            </div>
                        <?php #endforeach; ?>
                    <?php #else : ?>
                        <div class="col-12">
                            <div class="text-center">
                                <h3>No products found</h3>
                            </div>
                        </div>
                    <?php #endif; ?>
                </div>
            </div>
            <div class="col-xl-5 wow fadeInRight">
                <div class="best_selling_product_item_large">
                    <img src="assets/images/best_sell_pro_img_4.jpg" alt="best sell" class="img-fluid w-100">
                    <div class="text">
                        <a class="title" href="shop_details.php">Best Sales Discount And Offers</a>
                        <p class="price">₹89.00 <del>₹12.00</del></p>
                        <a class="common_btn" href="shop_details.php">buy now <i
                                class="fas fa-long-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!--================================
        BEST SELLING PRODUCT 2 END
    ==================================-->


<!--================================
        NEW ARRIVAL 2 START
    ==================================-->
<section class="new_arrival_2 mt_95">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-sm-9">
                <div class="section_heading_2 section_heading">
                    <h3>Our <span>New</span> arrival Products</h3>
                </div>
            </div>
            <div class="col-xl-6 col-sm-3">
                <div class="view_all_btn_area">
                    <a class="view_all_btn" href="shop.php">View all</a>
                </div>
            </div>
        </div>
        <div class="row mt_15">
            <?php if (!empty($new_arrivals)) : ?>

                <?php foreach ($new_arrivals as $product) : ?>
                    <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                        <div class="product_item_2 product_item">

                            <div class="product_img">
                                <img src="<?= get_product_image($product, 'main', 30) ?>" alt="Product" class="img-fluid w-100">

                                <ul class="discount_list">
                                    <li class="new">new</li>
                                </ul>

                                <ul class="btn_list">
                                    <li>
                                        <a href="wishlist.php?slug=<?= urlencode($product['slug']) ?>">
                                            <img src="assets/images/love_icon_white.svg" data-id="<?php echo $product['id']; ?>" class="img-fluid add-to-wishlist">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="cart.php?slug=<?= urlencode($product['slug']) ?>">
                                            <img src="assets/images/cart_icon_white.svg" data-id="<?php echo $product['id']; ?>" class="img-fluid add-to-cart">
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="product_text">
                                <a class="title" href="shop_details.php?id=<?= encrypt_id($product['id']) ?>">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                                <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span>
                                <p class="price">
                                    <?= pricing_format($product['base_retail_price'], $currency) ?>
                                    <del><?= pricing_format($product['mrp'], $currency) ?></del>
                                </p>

                                <?php
                                $rating = get_average_product_rating($product['id']);
                                $fullStars = floor($rating);
                                $halfStar  = ($rating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                ?>

                                <p class="rating">
                                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>

                                    <?php if ($halfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php endif; ?>

                                    <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>

                                    <span>(<?= (int)get_total_product_reviews_count($product['id']) ?> reviews)</span>
                                </p>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else : ?>
                <div class="col-12">
                    <div class="text-center">
                        <h3>No products found</h3>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!--================================
        NEW ARRIVAL 2 END
    ==================================-->


<!--============================
        FAVOURITE PRODUCT 2 START
    ==============================-->
<section class="favourite_product_2 mt_100">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-4 wow fadeInLeft">
                <div class="bundle_product_banner">
                    <img src="assets/images/favourite_pro_2_banner_img.png" alt="bundle" class="img-fluid">
                    <div class="text">
                        <h4>This Spring On Apple <span>Up To 50K Off</span></h4>
                        <p>Limited Time Offer</p>
                        <a class="common_btn" href="shop_details.php">shop now <i class="fas fa-long-arrow-right"
                                aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-8">
                <div class="row">
                    <div class="col-xl-8">
                        <div class="section_heading_2 section_heading">
                            <h3>Our <span>Favorite</span> Style Product</h3>
                        </div>
                    </div>
                    <div class="row mt_40 favourite_product_2_slider">
                        <?php if (!empty($favorite_products)) : ?>
                            <?php foreach ($favorite_products as $product) : ?>
                                <div class="col-xl-1-5 col-6 col-md-4 col-xl-3">
                                    <div class="product_item_2 product_item">

                                        <div class="product_img">
                                            <img src="<?= get_product_image($product, 'main', 30) ?>"
                                                alt="Product" class="img-fluid w-100">

                                            <ul class="discount_list">
                                                <li class="new">new</li>
                                            </ul>

                                            <ul class="btn_list">
                                                <li>
                                                    <a href="wishlist.php?slug=<?= urlencode($product['slug']) ?>">
                                                        <img src="assets/images/love_icon_white.svg"  data-id="<?php echo $product['id']; ?>" class="img-fluid add-to-wishlist">
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="cart.php?slug=<?= urlencode($product['slug']) ?>">
                                                        <img src="assets/images/cart_icon_white.svg" data-id="<?php echo $product['id']; ?>" class="img-fluid add-to-cart">
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="product_text">
                                            <a class="title" href="shop_details.php?id=<?= encrypt_id($product['id']) ?>">
                                                <?= htmlspecialchars($product['name']) ?>
                                            </a>
                                            <span style="font-size:11px; background:#198754; color:#ffffff; padding:2px 6px; border-radius:4px;">GST Inc.</span>
                                            <p class="price">
                                                <?= pricing_format($product['base_retail_price'], $currency) ?>
                                                 <del><?= pricing_format($product['mrp'], $currency) ?></del>
                                            </p>

                                            <?php
                                            $rating = get_average_product_rating($product['id']);
                                            $fullStars = floor($rating);
                                            $halfStar  = ($rating - $fullStars) >= 0.5;
                                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                            ?>

                                            <p class="rating">
                                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>

                                                <?php if ($halfStar): ?>
                                                    <i class="fas fa-star-half-alt"></i>
                                                <?php endif; ?>

                                                <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                                    <i class="far fa-star"></i>
                                                <?php endfor; ?>

                                                <span>(<?= (int)get_total_product_reviews_count($product['id']) ?> reviews)</span>
                                            </p>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php else : ?>
                            <div class="col-12">
                                <div class="text-center">
                                    <h3>No products found</h3>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</section>
<br>
<!--============================
        FAVOURITE PRODUCT 2 END
    ==============================-->
<?php include "footer.php"; ?>