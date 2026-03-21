<?php include "header.php"; ?>
<!--=========================
        BANNER 2 START
    ==========================-->
<section class="banner_2">
    <div class="container">
        <div class="row">
            <div class="col-xl-2  d-none d-xxl-block">
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
            </div>
            <div class="col-xxl-7 col-lg-8">
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
        <div class="row category_2_slider">
            <!-- Sub Categories -->
            <?php if (!empty($header_sub_categories)) : ?>
                <?php foreach ($header_sub_categories as $mainId => $subCats) : ?>
                    <?php foreach ($subCats as $subCat) : ?>
                        <div class="col-2 wow fadeInUp">
                            <a href=" shop.php?sub=<?php echo urlencode($subCat['slug']); ?>" class="category_item">
                                <div class="img">
                                    <?php
                                    $img = !empty($subCat['image'])
                                        ? htmlspecialchars($subCat['image'])
                                        : 'assets/images/category_img_2.png';
                                    ?>
                                    <img src="<?php echo $img; ?>" alt="Category" class="img-fluid w-100">
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
        FLASH SELL 2 START
    ==============================-->
<section class="flash_sell_2 flash_sell mt_95">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xxl-6 col-md-3 col-xl-4">
                <div class="section_heading_2 section_heading">
                    <h3><span>Flash</span> Sell</h3>
                </div>
            </div>
            <div class="col-xxl-6 col-md-9 col-xl-8">
                <div class="d-flex flex-wrap justify-content-end">
                    <div class="simply-countdown simply-countdown-one"></div>
                    <div class="view_all_btn_area">
                        <a class="view_all_btn" href="flash_deals.php">View all</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt_25 flash_sell_2_slider">
            <?php if (!empty($flash_products)) : ?>
                <?php foreach ($flash_products as $flash_product) : ?>
                    <div class="col-xl-1-5 wow fadeInUp">
                        <div class="product_item_2 product_item">
                            <div class="product_img">
                                <img src="assets/images/product_1.png" alt="Product" class="img-fluid w-100">
                                <ul class="discount_list">
                                    <!-- <li class="discount"> <b>-</b> 75%</li> -->
                                    <li class="new"> new</li>
                                </ul>
                                <ul class="btn_list">
                                    <li>
                                        <a href="#">
                                            <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="product_text">
                                <a class="title" href="shop_details.php"><?php echo htmlspecialchars($flash_product['name']); ?></a>
                                <p class="price">₹<?php echo htmlspecialchars($flash_product['base_retail_price']); ?> <del>₹<?php echo htmlspecialchars($flash_product['mrp']); ?></del></p>
                                <?php
                                $reviews = get_product_reviews($flash_product['id']);
                                $rating = get_average_product_rating($flash_product['id']);
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

                                    <span>(<?= (int)get_total_product_reviews_count($flash_product['id']) ?> reviews)</span>
                                </p>
                                <ul class="product_variants_items_box">

                                    <?php
                                    $variants = get_product_variants($flash_product['id']);

                                    $totalVariants = count($variants);

                                    /* show first two variants */
                                    $visible = array_slice($variants, 0, 2);

                                    /* extra count */
                                    $extraCount = $totalVariants > 2 ? $totalVariants - 2 : 0;
                                    ?>

                                    <?php foreach ($visible as $variant): ?>

                                        <li class="product_variants_item">
                                            <a href="shop.php?slug=<?= urlencode($flash_product['slug']) ?>"
                                                title="Size: <?= htmlspecialchars($variant['size']) ?> | Price: ₹<?= htmlspecialchars($variant['retail_price']) ?>">

                                                <img
                                                    src="<?= htmlspecialchars($variant['image'] ?? 'assets/images/product_1.png') ?>"
                                                    alt="<?= htmlspecialchars($variant['size']) ?>"
                                                    loading="lazy"
                                                    width="30"
                                                    height="30">

                                            </a>
                                        </li>

                                    <?php endforeach; ?>


                                    <?php if ($extraCount > 0): ?>

                                        <li class="variant_more">
                                            <a href="shop.php?slug=<?= urlencode($flash_product['slug']) ?>">
                                                +<?= $extraCount ?>
                                            </a>
                                        </li>

                                    <?php endif; ?>

                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="text-center">
                        <h3>No flash products found</h3>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!--============================
        FLASH SELL 2 END
    ==============================-->



<!--============================
        SPECIAL PRODUCT 2 START
    ==============================-->
<section class="special_product_2 pt_85">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-sm-9">
                <div class="section_heading_2 section_heading">
                    <h3>Our <span>Spatial</span> Brand Products</h3>
                </div>
            </div>
            <div class="col-xl-6 col-sm-3">
                <div class="view_all_btn_area">
                    <a class="view_all_btn" href=" shop.php">View all</a>
                </div>
            </div>
        </div>

        <div class="row pt_15">
            <div class="col-xl-12">
                <div class="row">
                    <?php if (!empty($special_products)) : ?>
                        <?php foreach ($special_products as $special_product) : ?>
                            <div class="col-md-6 wow fadeInUp">
                                <div class="special_product_item">
                                    <div class="special_product_img">
                                        <img src="assets/images/product_30.png" alt="product" class="img-fluid w-100">
                                        <span class="discount">save $<?php echo htmlspecialchars($special_product['mrp'] - $special_product['base_retail_price']); ?></span>
                                    </div>
                                    <div class="special_product_text">
                                        <a class="title" href="shop_details.php"><?php echo htmlspecialchars($special_product['name']); ?></a>
                                        <?php
                                        $rating = get_average_product_rating($special_product['id']);
                                        $fullStars = floor($rating);
                                        $halfStar  = ($rating - $fullStars) >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        ?>
                                        <span class="rating">
                                            <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor; ?>

                                            <?php if ($halfStar): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php endif; ?>

                                            <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <p>₹<?php echo htmlspecialchars($special_product['base_retail_price']); ?> <del>₹<?php echo htmlspecialchars($special_product['mrp']); ?></del></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="col-12">
                            <div class="text-center">
                                <h3>No special products found</h3>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!--============================
        SPECIAL PRODUCT 2 END
    ==============================-->


<!--============================
        TRENDING PRODUCT 2 START
    ==============================-->
<section class="trending_product_2 mt_90">
    <div class="container">
        <div class="row">
            <div class="col-xl-6">
                <div class="section_heading_2 section_heading mb_15">
                    <h3><span>Trending</span> Products</h3>
                </div>
            </div>
        </div>
        <div class="row wow fadeInUp">
            <div class="col-12">
                <div class="pws_tabs_container pws_tabs_horizontal pws_tabs_horizontal_top pws_slidedown">
                    <ul class="pws_tabs_controll">
                        <!--  Sub Categories limit 5-->
                        <?php foreach ($trending_sub_categories as $mainId => $subCats) : ?>
                            <?php foreach ($subCats as $subCat) : ?>
                                <li><a data-tab-id="tab<?php echo htmlspecialchars($subCat['id']); ?>" class="pws_tab_active"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                    <div class="product_tabs pws_tabs_list">
                        <?php foreach ($trending_sub_categories as $mainId => $subCats) : ?>
                            <?php foreach ($subCats as $subCat) : ?>
                                <div data-pws-tab="tab<?php echo htmlspecialchars($subCat['id']); ?>" data-pws-tab-name="<?php echo htmlspecialchars($subCat['name']); ?>">
                                    <div class="row">
                                        <?php $sub_categories_products = get_products(['sub_category_id' => $subCat['id']], 12, 0); ?>
                                        <?php foreach ($sub_categories_products as $sub_category_product) : ?>
                                            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3">
                                                <div class="product_item_2 product_item">
                                                    <div class="product_img">
                                                        <img src="assets/images/product_7.png" alt="Product"
                                                            class="img-fluid w-100">
                                                        <ul class="discount_list">
                                                            <li class="new"> new</li>
                                                        </ul>
                                                        <ul class="btn_list">
                                                            <li>
                                                                <a href="#">
                                                                    <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                                        class="img-fluid">
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <img src="assets/images/love_icon_white.svg" alt="Love"
                                                                        class="img-fluid">
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="#">
                                                                    <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                                        class="img-fluid">
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="product_text">
                                                        <a class="title" href="shop_details.php"><?php echo htmlspecialchars($sub_category_product['name']); ?></a>
                                                        <p class="price">₹<?php echo htmlspecialchars($sub_category_product['base_retail_price']); ?></p>
                                                        <p class="rating">
                                                            <i class="fas fa-star"></i>
                                                            <i class="fas fa-star"></i>
                                                            <i class="fas fa-star"></i>
                                                            <i class="fas fa-star-half-alt"></i>
                                                            <i class="far fa-star"></i>
                                                            <span>(<?php echo htmlspecialchars($sub_category_product['review_count']); ?> reviews)</span>
                                                        </p>
                                                        <ul class="color">
                                                            <li class="active" style="background:#DB4437"></li>
                                                            <li style="background:#638C34"></li>
                                                            <li style="background:#1C58F2"></li>
                                                            <li style="background:#ffa500"></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
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
<section class="best_selling_product_2 mt_95">
    <div class="container">
        <div class="row">
            <div class="col-xl-6 col-sm-9">
                <div class="section_heading_2 section_heading">
                    <h3>Our <span>Best</span> Selling Products</h3>
                </div>
            </div>
            <div class="col-xl-6 col-sm-3">
                <div class="view_all_btn_area">
                    <a class="view_all_btn" href=" shop.php">View all</a>
                </div>
            </div>
        </div>
        <div class="row mt_15">
            <div class="col-xl-7">
                <div class="row">
                    <div class="col-xl-4 col-sm-6 col-md-4 wow fadeInUp">
                        <div class="best_selling_product_item">
                            <img src="assets/images/best_sell_pro_img_1.jpg" alt="best sell"
                                class="img-fluid w-100">
                            <div class="text">
                                <a class="title" href="shop_details.php">Men's trendy casual shoes</a>
                                <p class="price">₹89.00 <del>₹12.00</del></p>
                                <a class="buy_btn" href="shop_details.php">buy now <i
                                        class="far fa-arrow-up"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-md-4 wow fadeInUp">
                        <div class="best_selling_product_item">
                            <img src="assets/images/best_sell_pro_img_2.jpg" alt="best sell"
                                class="img-fluid w-100">
                            <div class="text">
                                <a class="title" href="shop_details.php">Kid's Western Party Dress</a>
                                <p class="price">₹75.00 <del>₹99.00</del></p>
                                <a class="buy_btn" href="shop_details.php">buy now <i
                                        class="far fa-arrow-up"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-sm-6 col-md-4 wow fadeInUp">
                        <div class="best_selling_product_item">
                            <img src="assets/images/best_sell_pro_img_3.jpg" alt="best sell"
                                class="img-fluid w-100">
                            <div class="text">
                                <a class="title" href="shop_details.php">Men's Casual Winter Jacket</a>
                                <p class="price">₹60.00 <del>₹65.00</del></p>
                                <a class="buy_btn" href="shop_details.php">buy now <i
                                        class="far fa-arrow-up"></i></a>
                            </div>
                        </div>
                    </div>
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
</section>
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
                    <a class="view_all_btn" href=" shop.php">View all</a>
                </div>
            </div>
        </div>
        <div class="row mt_15">
            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                <div class="product_item_2 product_item">
                    <div class="product_img">
                        <img src="assets/images/product_18.png" alt="Product" class="img-fluid w-100">
                        <ul class="discount_list">
                            <li class="new"> new</li>
                        </ul>
                        <ul class="btn_list">
                            <li>
                                <a href="#">
                                    <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="product_text">
                        <a class="title" href="shop_details.php">Full Sleeve Hoodie Jacket</a>
                        <p class="price">₹88.00 </p>
                        <p class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>(20 reviews)</span>
                        </p>
                        <ul class="color">
                            <li class="active" style="background:#DB4437"></li>
                            <li style="background:#638C34"></li>
                            <li style="background:#1C58F2"></li>
                            <li style="background:#ffa500"></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                <div class="product_item_2 product_item">
                    <div class="product_img">
                        <img src="assets/images/product_19.png" alt="Product" class="img-fluid w-100">
                        <ul class="discount_list">
                            <li class="new"> new</li>
                        </ul>
                        <ul class="btn_list">
                            <li>
                                <a href="#">
                                    <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="product_text">
                        <a class="title" href="shop_details.php">Men's premium formal shirt</a>
                        <p class="price">₹46.00</p>
                        <p class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(17 reviews)</span>
                        </p>
                        <ul class="color">
                            <li class="active" style="background:#DB4437"></li>
                            <li style="background:#638C34"></li>
                            <li style="background:#ffa500"></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                <div class="product_item_2 product_item">
                    <div class="product_img">
                        <img src="assets/images/product_20.png" alt="Product" class="img-fluid w-100">
                        <ul class="discount_list">
                            <li class="new"> new</li>
                        </ul>
                        <ul class="btn_list">
                            <li>
                                <a href="#">
                                    <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="product_text">
                        <a class="title" href="shop_details.php">cherry fabric western tops</a>
                        <p class="price">₹46.00</p>
                        <p class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <i class="far fa-star"></i>
                            <span>(22 reviews)</span>
                        </p>
                        <ul class="color">
                            <li class="active" style="background:#DB4437"></li>
                            <li style="background:#638C34"></li>
                            <li style="background:#1C58F2"></li>
                            <li style="background:#ffa500"></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                <div class="product_item_2 product_item">
                    <div class="product_img">
                        <img src="assets/images/product_4.png" alt="Product" class="img-fluid w-100">
                        <ul class="discount_list">
                            <li class="new"> new</li>
                        </ul>
                        <ul class="btn_list">
                            <li>
                                <a href="#">
                                    <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="product_text">
                        <a class="title" href="shop_details.php">Comfortable Sports Sneakers</a>
                        <p class="price">₹75.00</p>
                        <p class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span>(58 reviews)</span>
                        </p>
                        <ul class="color">
                            <li class="active" style="background:#DB4437"></li>
                            <li style="background:#638C34"></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-1-5 col-6 col-md-4 col-xl-3 wow fadeInUp">
                <div class="product_item_2 product_item">
                    <div class="product_img">
                        <img src="assets/images/product_23.png" alt="Product" class="img-fluid w-100">
                        <ul class="discount_list">
                            <li class="new"> new</li>
                        </ul>
                        <ul class="btn_list">
                            <li>
                                <a href="#">
                                    <img src="assets/images/compare_icon_white.svg" alt="Compare" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/love_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <img src="assets/images/cart_icon_white.svg" alt="Love" class="img-fluid">
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="product_text">
                        <a class="title" href="shop_details.php"> Kid's dresses for summer</a>
                        <p class="price">₹70.00</p>
                        <p class="rating">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span>(44 reviews)</span>
                        </p>
                        <ul class="color">
                            <li class="active" style="background:#DB4437"></li>
                            <li style="background:#638C34"></li>
                            <li style="background:#1C58F2"></li>
                            <li style="background:#ffa500"></li>
                        </ul>
                    </div>
                </div>
            </div>
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
                        <div class="col-xl-3 wow fadeInUp">
                            <div class="product_item_2 product_item">
                                <div class="product_img">
                                    <img src="assets/images/product_22.png" alt="Product" class="img-fluid w-100">
                                    <ul class="discount_list">
                                        <li class="new">new</li>
                                    </ul>
                                    <ul class="btn_list">
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/love_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product_text">
                                    <a class="title" href="shop_details.php">cherry fabric western tops</a>
                                    <p class="price">₹54.00</p>
                                    <p class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <i class="far fa-star"></i>
                                        <span>(98 reviews)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 wow fadeInUp">
                            <div class="product_item_2 product_item">
                                <div class="product_img">
                                    <img src="assets/images/product_24.png" alt="Product" class="img-fluid w-100">
                                    <ul class="btn_list">
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/love_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product_text">
                                    <a class="title" href="shop_details.php">Women denim jacket</a>
                                    <p class="price">₹49.00</p>
                                    <p class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <i class="far fa-star"></i>
                                        <span>(44 reviews)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 wow fadeInUp">
                            <div class="product_item_2 product_item">
                                <div class="product_img">
                                    <img src="assets/images/product_23.png" alt="Product" class="img-fluid w-100">
                                    <ul class="discount_list">
                                        <li class="discount"> <b>-</b> 20%</li>
                                    </ul>
                                    <ul class="btn_list">
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/love_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product_text">
                                    <a class="title" href="shop_details.php">Kid's Western Party Dress</a>
                                    <p class="price">₹40.00 <del>₹48.00</del></p>
                                    <p class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <i class="far fa-star"></i>
                                        <span>(20 reviews)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 wow fadeInUp">
                            <div class="product_item_2 product_item">
                                <div class="product_img">
                                    <img src="assets/images/product_25.png" alt="Product" class="img-fluid w-100">
                                    <ul class="btn_list">
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/love_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product_text">
                                    <a class="title" href="shop_details.php">Half Sleeve Jachket for Women</a>
                                    <p class="price">₹60.00</p>
                                    <p class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <i class="far fa-star"></i>
                                        <span>(57 reviews)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 wow fadeInUp">
                            <div class="product_item_2 product_item">
                                <div class="product_img">
                                    <img src="assets/images/product_26.png" alt="Product" class="img-fluid w-100">
                                    <ul class="discount_list">
                                        <li class="discount"> <b>-</b> 58%</li>
                                    </ul>
                                    <ul class="btn_list">
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/compare_icon_white.svg" alt="Compare"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/love_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">
                                                <img src="assets/images/cart_icon_white.svg" alt="Love"
                                                    class="img-fluid">
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="product_text">
                                    <a class="title" href="shop_details.php">Kid's Western Party Dress</a>
                                    <p class="price">₹40.00 <del>₹48.00</del></p>
                                    <p class="rating">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <i class="far fa-star"></i>
                                        <span>(88 reviews)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--============================
        FAVOURITE PRODUCT 2 END
    ==============================-->
<?php include "footer.php"; ?>