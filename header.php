<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// Basic SEO meta defaults (can be overridden per page via $page_meta)
$meta_title       = isset($page_meta['title']) ? (string) $page_meta['title'] : 'Adidev Manufacturing Sales And Services Private Limited';
$meta_description = isset($page_meta['description']) ? (string) $page_meta['description'] : 'Adidev Manufacturing Sales And Services Private Limited - E-commerce platform for manufacturing, wholesale and bulk orders.';
$meta_keywords    = isset($page_meta['keywords']) ? (string) $page_meta['keywords'] : 'Adidev, manufacturing, ecommerce, bulk orders';

// Build a simple current user display name
$current_user_name = is_logged_in() && !empty($_SESSION['user_name'])
    ? $_SESSION['user_name']
    : 'My Account';

$userCurrency = get_user_currency($_SESSION['user_id'] ?? 0);

// Preload categories for header menu
$header_main_categories = get_main_categories_for_menu();
$header_sub_categories  = get_sub_categories_grouped_by_main(
    array_map(
        static fn(array $c): int => (int) $c['id'],
        $header_main_categories
    ),
    50,
    'name',
    'ASC'
);
$footer_sub_categories = get_sub_categories_grouped_by_main(
    array_map(
        static fn(array $c): int => (int) $c['id'],
        $header_main_categories
    ),
    5,
    'RAND()',
    ''
);
$currency = get_user_currency($_SESSION['user_id'] ?? 0);
$trending_sub_categories = get_trending_products_random($limit = 15 , $currency);
$best_selling_products = get_best_selling_products(3);
$new_arrivals = get_new_arrival_products(5 , $currency);
$favorite_products = get_recommended_products(15 , null, $currency);
?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="currency" content="<?php echo htmlspecialchars($userCurrency); ?>">
    <link rel="icon" type="image/png" href="assets/images/favicon.ico">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/mobile_menu.css">
    <link rel="stylesheet" href="assets/css/nice-select.css">
    <link rel="stylesheet" href="assets/css/scroll_button.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/venobox.min.css">
    <link rel="stylesheet" href="assets/css/select2.min.css">
    <link rel="stylesheet" href="assets/css/jquery.pwstabs.css">
    <link rel="stylesheet" href="assets/css/range_slider.css">
    <link rel="stylesheet" href="assets/css/multiple-image-video.css">
    <link rel="stylesheet" href="assets/css/custom_spacing.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<style>
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .custom-toast {
        min-width: 250px;
        margin-bottom: 10px;
        padding: 12px 16px;
        border-radius: 6px;
        color: #fff;
        font-size: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    }

    /* Show animation */
    .custom-toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    /* Types */
    .custom-toast.success {
        background: #28a745;
    }

    .custom-toast.error {
        background: #dc3545;
    }

    .custom-toast.warning {
        background: #ffc107;
        color: #000;
    }

    .custom-toast.info {
        background: #17a2b8;
    }

    .toast-message {
        color: #fff !important;
    }

    /* Close button */
    .toast-close {
        margin-left: 10px;
        cursor: pointer;
        font-weight: bold;
        color: #fff !important;
    }
</style>

<body class="default_home">

    <!--=========================
        HEADER START
    ==========================-->
    <header class="header_2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2">
                    <div class="header_logo_area">
                        <a href="/" class="header_logo">
                            <img src="assets/images/logo_2.png" alt="Adidev Manufacturing Sales And Services Private Limited" class="img-fluid w-100">
                        </a>
                        <div class="mobile_menu_icon d-block d-lg-none" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
                            <span class="mobile_menu_icon"><i class="far fa-stream menu_icon_bar"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xXl-6 col-xl-5 col-lg-5 d-none d-lg-block">
                    <form action="#">
                        <select class="select_2">
                            <option>All Categories</option>
                            <?php foreach ($header_main_categories as $mainCat) : ?>
                                <option value="<?php echo urlencode($mainCat['slug']); ?>"><?php echo htmlspecialchars($mainCat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input">
                            <input type="text" placeholder="Search your product...">
                            <button type="submit"><i class="far fa-search"></i></button>
                        </div>
                    </form>
                </div>
                <div class="col-xXl-4 col-xl-5 col-lg-5 d-none d-lg-flex">
                    <div class="header_support_user d-flex flex-wrap">
                        <div class="header_support">
                            <span class="icon">
                                <i class="far fa-phone-alt"></i>
                            </span>
                            <h3>
                                <span style="color: #000000;"> Contact Us:</span>
                                <a href="callto:+91 7369084701">
                                    <span>+91 7369084701</span>
                                </a>
                            </h3>
                        </div>
                    </div>
                    <div class="topbar_right d-flex flex-wrap align-items-center justify-content-end">
                        <select class="select_js">
                            <option value="INR" <?php echo $userCurrency === 'INR' ? 'selected' : ''; ?>>INR</option>
                            <option value="USD" <?php echo $userCurrency === 'USD' ? 'selected' : ''; ?>>USD</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!--=========================
        HEADER END
    ==========================-->

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!--=========================
        MENU 2 START
    ==========================-->
    <nav class="main_menu_2 main_menu d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex flex-wrap">
                    <div class="main_menu_area">
                        <!-- <div class="menu_category_area">
                            <a href="/" class="menu_logo d-none">
                                <img src="assets/images/logo_2.png" alt="Zenis" class="img-fluid w-100">
                            </a>
                        </div> -->
                        <ul class="menu_item">
                            <li><a href="/"> Home</a></li>
                            <!-- <li>
                                <a href="#">All Products <i class="fas fa-chevron-down"></i></a>
                                <ul class="menu_droapdown">
                                    <li><a href="shop.php">All Products</a></li>
                                    <li><a href="shop_details.php">Product Details</a></li>
                                </ul>
                            </li> -->
                            <!-- <li>
                                <a href="#">Stores <i class="fas fa-chevron-down"></i></a>
                                <ul class="menu_droapdown">
                                    <li><a href="store.php">Store</a></li>
                                    <li><a href="vendor_details.php">Store Details</a></li>
                                    <li><a href="vendor_registration.php">Become a Vendor</a></li>
                                </ul>
                            </li> -->
                            <?php foreach ($header_main_categories as $mainCat) : ?>
                                <li>
                                    <a href="shop.php?category=<?php echo urlencode($mainCat['slug']); ?>">
                                        <span>
                                            <?php
                                            $iconPath = !empty($mainCat['icon'])
                                                ? htmlspecialchars($mainCat['icon'])
                                                : 'assets/images/category_list_icon_1.png';
                                            ?>
                                            <img src="<?php echo $iconPath; ?>" alt="category" class="img-fluid" style="max-width: 25px !important;">
                                        </span><?php echo htmlspecialchars($mainCat['name']); ?> <i class="fas fa-chevron-down"></i></a>
                                    <ul class="menu_droapdown">
                                        <?php foreach ($header_sub_categories[$mainCat['id']] as $subCat) : ?>
                                            <li><a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endforeach; ?>
                            <li>
                                <a href="#">Categories <i class="fas fa-chevron-down"></i></a>
                                <ul class="menu_droapdown">
                                    <?php foreach ($header_sub_categories as $mainId => $subCats) : ?>
                                        <?php foreach ($subCats as $subCat) : ?>
                                            <li><a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>

                            <!-- <li><a href="#">pages <i class="fas fa-chevron-down"></i></a>
                                <ul class="menu_droapdown">
                                    <li><a href="category.php">Category</a></li>
                                    <li><a href="cart.php">cart view</a></li>
                                    <li><a href="wishlist.php">wishlist</a></li>
                                    <li><a href="checkout.php">checkout</a></li>
                                    <li><a href=" payment_succes.php">payment success</a></li>
                                    <li><a href="track_order.php">track order</a></li>
                                    <li><a href=" faq.php">FAQ's</a></li>
                                    <li><a href=" privacy_policy.php">privacy Policy</a></li>
                                    <li><a href=" terms_condition.php">terms and condition</a></li>
                                    <li><a href="return_policy.php">return policy</a></li>
                                    <li><a href="sign_in.php">sign in</a></li>
                                    <li><a href="sign_up.php">sign up</a></li>
                                    <li><a href=" forgot_password.php">forgot password</a></li>
                                    <li><a href="dashboard.php">Dashboard</a></li>
                                </ul>
                            </li> -->
                            <li><a href="about.php">About-Us</a></li>
                            <!-- <li><a href="blog.php">Blogs</a></li> -->
                            <li><a href="contact_us.php">contact</a></li>
                        </ul>
                        <ul class="menu_icon">
                            <li>
                                <a href="wishlist.php">
                                    <b>
                                        <img src="assets/images/love_black.svg" alt="Wishlist" class="img-fluid">
                                    </b>
                                    <span class="wishlist-count">0</span>
                                </a>
                            </li>
                            <li>
                                <!-- <a data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight"
                                    aria-controls="offcanvasRight"> -->
                                <a href="cart.php">
                                    <b>
                                        <img src="assets/images/cart_black.svg" alt="cart" class="img-fluid">
                                    </b>
                                    <span class="cart-count">0</span>
                                </a>
                            </li>
                            <li>
                                <a class="user" href="<?php echo is_logged_in() ? 'dashboard.php' : 'sign_in.php'; ?>">
                                    <b>
                                        <img src="assets/images/user_icon_black.svg" alt="cart" class="img-fluid">
                                    </b>
                                    <h5><?php echo htmlspecialchars($current_user_name); ?></h5>
                                </a>
                                <ul class="user_dropdown">
                                    <li>
                                        <a href="dashboard.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                                            </svg>
                                            Dashboard
                                        </a>
                                    </li>
                                    <!-- <li>
                                        <a href="my-profile.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                            my account
                                        </a>
                                    </li> -->
                                    <!-- <li>
                                        <a href="dashboard_order.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            my order
                                        </a>
                                    </li> -->
                                    <li>
                                        <a href="wishlist.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                            wishlist
                                        </a>
                                    </li>
                                    <li>
                                        <?php if (is_logged_in()) : ?>
                                            <a href="logout.php">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                                </svg>
                                                logout
                                            </a>
                                        <?php else : ?>
                                            <a href="sign_in.php">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 4.5v15m-7.5-7.5h15" />
                                                </svg>
                                                sign in
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php
    $user_id = current_user_id() ?? 0;
    $cart_items = $user_id ? get_user_cart($user_id) : [];

    $subtotal = 0;
    $total_qty = 0;
    ?>
    <div class="mini_cart">
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasRightLabel"> my cart </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i
                        class="far fa-times"></i></button>
            </div>
            <div class="offcanvas-body">
                <ul>
                    <?php if (!empty($cart_items)) : ?>

                        <?php foreach ($cart_items as $item) :
                            $subtotal += ($item['unit_price'] * $item['quantity']);
                            $total_qty += $item['quantity'];
                        ?>
                            <li>
                                <a href="shop_details.php?id=<?= (int)$item['product_id'] ?>" class="cart_img">
                                    <img src="<?= get_product_image($item, 'main') ?>"
                                        class="img-fluid w-100">
                                </a>

                                <div class="cart_text">
                                    <a class="cart_title" href="shop_details.php?id=<?= (int)$item['product_id'] ?>">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>

                                    <p>
                                        ₹<?= number_format($item['unit_price'], 2) ?>
                                        × <?= $item['quantity'] ?>
                                    </p>
                                    <?= $total = $item['unit_price'] * $item['quantity']; ?>
                                    <p><b>Total:</b> ₹<?= number_format($total, 2) ?></p>

                                    <?php if (!empty($item['color'])) : ?>
                                        <span><b>Color:</b> <?= htmlspecialchars($item['color']) ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($item['size'])) : ?>
                                        <span><b>Size:</b> <?= htmlspecialchars($item['size']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <a class="del_icon remove-from-cart"
                                    href="javascript:void(0)"
                                    data-id="<?= $item['id'] ?>">
                                    <i class="fal fa-times"></i>
                                </a>
                            </li>

                        <?php endforeach; ?>

                    <?php else : ?>

                        <li class="text-center">No items in cart</li>

                    <?php endif; ?>
                </ul>
                <h5>sub total <span>₹<?= number_format($subtotal, 2) ?></span></h5>
                <div class="minicart_btn_area">
                    <a class="common_btn" href="cart.php">view cart</a>
                </div>
            </div>
        </div>
    </div>
    <!--=========================
        MENU 2 END
    ==========================-->


    <!--============================
        MOBILE MENU START
    ==============================-->
    <div class="mobile_menu_area">
        <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"><i
                    class="fal fa-times"></i></button>
            <div class="offcanvas-body">
                <ul class="mobile_currency">
                    <li>
                        <select class="select_js">
                            <option value="INR" <?php echo $userCurrency === 'INR' ? 'selected' : ''; ?>>INR</option>
                            <option value="USD" <?php echo $userCurrency === 'USD' ? 'selected' : ''; ?>>USD</option>
                        </select>
                    </li>
                </ul>
                <ul class="mobile_menu_header d-flex flex-wrap">
                    <!-- <li>
                        <a href="compare.php">
                            <b> <img src="assets/images/compare_black.svg" alt="Wishlist" class="img-fluid"> </b>
                            <span>2</span>
                        </a>
                    </li> -->
                    <li>
                        <a href="wishlist.php">
                            <b> <img src="assets/images/love_black.svg" alt="Wishlist" class="img-fluid"></b>
                            <span class="wishlist-count">0</span>
                        </a>
                    </li>
                    <li>
                        <a href="cart.php">
                            <b><img src="assets/images/cart_black.svg" alt="cart" class="img-fluid"></b>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php">
                            <b><img src="assets/images/user_icon_black.svg" alt="cart" class="img-fluid"></b>
                        </a>
                    </li>
                </ul>

                <form class="mobile_menu_search">
                    <input type="text" placeholder="Search">
                    <button type="submit"><i class="far fa-search"></i></button>
                </form>

                <div class="mobile_menu_item_area">
                    <ul class="nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home"
                                aria-selected="true">Categories</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile"
                                aria-selected="false">menu</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel"
                            aria-labelledby="pills-home-tab" tabindex="0">
                            <ul class="main_mobile_menu">
                                <?php foreach ($header_main_categories as $mainCat) : ?>
                                    <li class="mobile_dropdown">
                                        <a href="shop.php?category=<?php echo urlencode($mainCat['slug']); ?>"><?php echo htmlspecialchars($mainCat['name']); ?></a>
                                        <ul class="inner_menu">
                                            <?php foreach ($header_sub_categories[$mainCat['id']] as $subCat) : ?>
                                                <li><a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel"
                            aria-labelledby="pills-profile-tab" tabindex="0">
                            <ul class="main_mobile_menu">
                                <li><a href="/">Home</a></li>
                                <li class="mobile_dropdown">
                                    <a href="shop.php">Products</a>
                                    <ul class="inner_menu">
                                        <li><a href="shop.php">All Products</a></li>
                                        <li><a href="shop_details.php">Product details</a></li>
                                    </ul>
                                </li>
                                <li class="mobile_dropdown">
                                    <a href="shop.php">Categories</a>
                                    <ul class="inner_menu">
                                        <?php foreach ($header_sub_categories as $mainId => $subCats) : ?>
                                            <?php foreach ($subCats as $subCat) : ?>
                                                <li><a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li><a href="about.php">About Us</a></li>
                                <li><a href="blog.php">Blog</a></li>
                                <li><a href="contact_us.php">contact</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--============================
        MOBILE MENU END
    ==============================-->