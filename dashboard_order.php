<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';
require_login();

$user_id = current_user_id() ?? 0;
$currency = get_user_currency($user_id);

$stmt = db_execute(
    "SELECT o.id, o.order_number, o.status, o.payment_status, o.grand_total, o.amount_paid, o.order_date, o.created_at,
            COUNT(oi.id) AS item_count,
            COUNT(pr.id) AS reviewed_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id AND pr.user_id = o.user_id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC
     LIMIT 100",
    'i',
    [$user_id]
);
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_meta = ['title' => 'My Orders | Adidev'];
include "header.php";
?>

<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>My Account</h1>
                        <ul>
                            <li><a href="index.php"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Order</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard mb_100">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 wow fadeInUp">
                <div class="dashboard_sidebar">
                    <div class="dashboard_sidebar_area">
                        <div class="dashboard_sidebar_user">
                            <div class="img">
                                <img src="assets/images/dashboard_user_img.jpg" alt="dashboard" class="img-fluid w-100">
                            </div>
                            <h3><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'My Account'); ?></h3>
                            <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                        </div>
                        <div class="dashboard_sidebar_menu">
                            <ul>
                                <li>
                                    <p>dashboard</p>
                                </li>
                                <li>
                                    <a href="dashboard.php">
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
                                    <a class="active" href="dashboard_order.php">
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
                                        <th>Payment</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$orders) { ?>
                                        <tr>
                                            <td colspan="6" style="text-align:center; padding:30px;">No orders found.</td>
                                        </tr>
                                    <?php } ?>

                                    <?php foreach ($orders as $order) {
                                        $orderId = (int) $order['id'];
                                        $paymentStatus = (string) ($order['payment_status'] ?? 'pending');
                                        $orderStatus = (string) ($order['status'] ?? 'pending');
                                        $canReview = $paymentStatus === 'paid' && (int) $order['item_count'] > (int) $order['reviewed_count'];
                                        $dateValue = $order['order_date'] ?: $order['created_at'];
                                    ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($dateValue))); ?></td>
                                            <td><span class="<?php echo $orderStatus === 'cancelled' ? 'cancel' : ($paymentStatus === 'paid' ? 'complete' : 'active'); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $orderStatus))); ?></span></td>
                                            <td><?php echo htmlspecialchars(ucfirst($paymentStatus ?: 'pending')); ?></td>
                                            <td><?php echo pricing_format((float) $order['grand_total'], $currency); ?></td>
                                            <td>
                                                <a href="dashboard_order_invoice.php?order_id=<?php echo $orderId; ?>">View</a>
                                                <?php if ($canReview) { ?>
                                                    <a class="review_order" href="dashboard_order_review.php?order_id=<?php echo $orderId; ?>">Review</a>
                                                <?php } elseif ($paymentStatus === 'paid') { ?>
                                                    <span style="display:inline-block; padding:8px 10px; color:#0f5132; font-weight:600;">Reviewed</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>
