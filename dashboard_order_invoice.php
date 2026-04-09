<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';
require_login();

$page_meta = [
    'title'       => 'Order Invoice | Adidev',
    'description' => 'View your order history and invoices on Adidev. Access detailed information about your past purchases, including order status, payment details, and downloadable invoices for easy reference.',
    'keywords'    => 'order invoice, purchase history, Adidev invoices, order details, payment information, downloadable invoices, Adidev account, order tracking, invoice management',
];

$allPaidOrders = get_user_recent_orders(current_user_id() ?? 0, 'paid', 5);
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
                            <li><a href="#">Order</a></li>
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
            <div class="col-lg-9 wow fadeInRight">
                <div class="dashboard_content mt_100">
                    <h3 class="dashboard_title">Order History</h3>
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
                                    <?php if (!empty($allPaidOrders)) : ?>

                                        <?php foreach ($allPaidOrders as $order) :
                                            $isPaid = ($order['payment_status'] === 'paid');

                                            $statusClass = $isPaid ? 'complete' : 'pending';
                                            $amountClass = $isPaid ? 'paid' : 'pending';

                                            $statusText = strtoupper($order['payment_status']);
                                            $amount = $isPaid ? $order['amount_paid'] : $order['amount_due'];
                                        ?>

                                            <tr>
                                                <td><?= htmlspecialchars($order['order_number']) ?></td>

                                                <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>

                                                <td >
                                                    <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                                </td>

                                                <td>
                                                    <span class="status <?= $amountClass ?>">
                                                        <?= pricing_format($amount, get_user_currency(current_user_id() ?? 0)) ?>
                                                    </span>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else : ?>

                                        <tr>
                                            <td colspan="5" style="text-align: center;">
                                                No recent orders found.
                                            </td>
                                        </tr>

                                    <?php endif; ?>
                                </tbody>
                            </table>
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