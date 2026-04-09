<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';
require_login();

$page_meta = [
    'title'       => 'My Account | Adidev',
    'description' => 'View your Adidev account overview, orders, wishlist and profile information.',
    'keywords'    => 'Adidev account, orders, wishlist, profile',
];

$totalOrderCount = 0;
$totalOrderCount = get_user_order_count(current_user_id() ?? 0, 'all');
$totalOrderCompletedCount = get_user_order_count(current_user_id() ?? 0, 'paid');
$recentPaidOrders = get_user_recent_orders(current_user_id() ?? 0, 'all', 5);

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
                        <h1>My Account</h1>
                        <ul>
                            <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Overview</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
        PAGE BANNER START
    ==========================-->


<!--============================
        DSHBOARD START
    =============================-->
<section class="dashboard mb_100">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 wow fadeInUp">
                <div class="dashboard_sidebar">
                    <div class="dashboard_sidebar_area">
                        <div class="dashboard_sidebar_user">
                            <div class="img">
                                <img src="assets/images/dashboard_user_img.jpg" alt="dashboard"
                                    class="img-fluid w-100">
                                <label for="profile_photo"><i class="far fa-camera"></i></label>
                                <input type="file" id="profile_photo" hidden="">
                            </div>
                            <h3><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></h3>
                            <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                        </div>
                        <div class="dashboard_sidebar_menu">
                            <ul>
                                <li>
                                    <p>dashboard</p>
                                </li>
                                <li>
                                    <a class="active" href="dashboard.php">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                            </svg>
                                        </span>
                                        overview
                                    </a>
                                </li>
                                <li>
                                    <a href="dashboard_order_invoice.php">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                        </span>
                                        Order
                                    </a>
                                </li>

                                <!-- <li>
                                    <a href="invoice.php">
                                        <span>

                                        </span>
                                        Invoice
                                    </a>
                                </li> -->
                                <li>
                                    <a href="wishlist.php">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                        </span>
                                        Wishlist
                                    </a>
                                </li>
                                <li>
                                    <a href="my-profile.php">
                                        <span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </span>
                                        My-Profile
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php

            ?>
            <div class="col-lg-9">
                <div class="dashboard_content mt_100">
                    <div class="row">
                        <div class="col-xl-6 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>

                                </div>
                                <h3> <?php echo $totalOrderCount; ?> <span>Total Order</span></h3>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item blue">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                    </svg>

                                </div>
                                <h3> <?php echo $totalOrderCompletedCount; ?> <span>Completed Order</span></h3>
                            </div>
                        </div>
                        <!-- <div class="col-xl-4 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item orange">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>

                                </div>
                                <h3> 28 <span>pending order</span></h3>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item red">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <h3> 12 <span>Canceled Order</span></h3>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item purple">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                </div>
                                <h3> 48 <span>Total Wishlist</span></h3>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 wow fadeInUp">
                            <div class="dashboard_overview_item olive">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                </div>
                                <h3> 26 <span>Total Reviews</span></h3>
                            </div>
                        </div>
                    </div> -->
                        <div class="row mt_25">
                            <div class="col-xl-12 wow fadeInLeft">
                                <div class="dashboard_recent_order">
                                    <h3>Your Recent order</h3>
                                    <div class="dashboard_order_table">
                                        <div class="table-responsive">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Amount</th>
                                                        <!-- <th>Action</th> -->
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                        $user_id = current_user_id() ?? 0;
                                                        $currency = get_user_currency($user_id);
                                                    ?>
                                                    <?php if (count($recentPaidOrders) > 0) : ?>
                                                        <?php foreach ($recentPaidOrders as $order) : ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($order['created_at']))); ?></td>
                                                                <td><?php echo strtoupper($order['payment_status']); ?></td>
                                                                <?php

                                                                if ($order['payment_status'] === 'paid') {
                                                                    echo '<td><span class="status paid">' . pricing_format($order['amount_paid'], $currency) . '</span></td>';
                                                                } else {
                                                                    echo '<td><span class="status pending">' . pricing_format($order['amount_due'], $currency) . '</span></td>';
                                                                    
                                                                }
                                                                ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php else : ?>
                                                        <tr>
                                                            <td colspan="5" style="text-align: center;">No recent orders found.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-xl-5 wow fadeInRight">
                            <div class="dashboard_recent_review">
                                <h3>Your Recent Reviews</h3>
                                <div class="single_review_list_area">
                                    <div class="single_review">
                                        <div class="text">
                                            <h5>
                                                <a class="title" href="shop_details.php">Denim 2 Quarter Pant</a>
                                                <span>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                </span>
                                            </h5>
                                            <p class="date">05 January 2025</p>
                                            <p class="description">Lorem ipsum dolor sit amet,
                                                consectetur adipisicing elit. Delectus
                                                exercitationem accusantium obcaecati quos voluptate
                                                nesciunt facilis itaque.</p>
                                        </div>
                                    </div>
                                    <div class="single_review">
                                        <div class="text">
                                            <h5>
                                                <a class="title" href="shop_details.php">Half Sleeve Tops for
                                                    Women</a>
                                                <span>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="far fa-star" aria-hidden="true"></i>
                                                </span>
                                            </h5>
                                            <p class="date">03 April 2025</p>
                                            <p class="description">Lorem ipsum dolor sit amet,
                                                consectetur adipisicing elit. Delectus
                                                exercitationem accusantium obcaecati quos voluptate.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="single_review">
                                        <div class="text">
                                            <h5>
                                                <a class="title" href="shop_details.php">cherry fabric western
                                                    tops</a>
                                                <span>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                </span>
                                            </h5>
                                            <p class="date">10 March 2025</p>
                                            <p class="description">Lorem ipsum dolor sit amet,
                                                consectetur adipisicing elit. Delectus
                                                exercitationem accusantium obcaecati quos voluptate
                                                nesciunt facilis itaque</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<!--============================
        DSHBOARD END
    =============================-->


<?php include "footer.php"; ?>