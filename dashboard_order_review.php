<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';
require_login();

$user_id = current_user_id() ?? 0;
$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
$message = '';
$error = '';
$order = null;
$order_items = [];

if ($order_id <= 0) {
    $error = 'Order not found. Please open your order review from your dashboard order history.';
}

if (!$error) {
    $stmt = db_execute(
        "SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$order_id, $user_id]
    );
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        $error = 'Order not found or does not belong to your account.';
    } elseif (($order['payment_status'] ?? '') !== 'paid') {
        $error = 'Reviews are available only after payment is completed.';
    }
}

if (!$error) {
    $stmt = db_execute(
        "SELECT oi.*,
                pr.id AS review_id,
                pr.status AS review_status
         FROM order_items oi
         LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id AND pr.user_id = ?
         WHERE oi.order_id = ?
         ORDER BY oi.id ASC",
        'ii',
        [$user_id, $order_id]
    );
    $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$order_items) {
        $error = 'No order items found for this order.';
    }
}

if (!$error && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reviews']) && is_array($_POST['reviews'])) {
    $submitted = 0;

    foreach ($_POST['reviews'] as $item_id => $review_data) {
        $item_id = (int) $item_id;
        $rating = isset($review_data['rating']) ? (int) $review_data['rating'] : 0;
        $title = trim((string) ($review_data['title'] ?? ''));
        $review_text = trim((string) ($review_data['review'] ?? ''));

        if ($rating < 1 || $rating > 5 || $review_text === '') {
            continue;
        }

        $stmt = db_execute(
            "SELECT oi.product_id
             FROM order_items oi
             LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id AND pr.user_id = ?
             WHERE oi.id = ? AND oi.order_id = ? AND pr.id IS NULL
             LIMIT 1",
            'iii',
            [$user_id, $item_id, $order_id]
        );
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$item || empty($item['product_id'])) {
            continue;
        }

        $stmt = db_execute(
            "INSERT INTO product_reviews
                (product_id, user_id, order_id, order_item_id, rating, title, review, is_verified_purchase, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'pending', NOW(), NOW())",
            'iiiiiss',
            [(int) $item['product_id'], $user_id, $order_id, $item_id, $rating, $title, $review_text]
        );
        $stmt->close();
        $submitted++;
    }

    if ($submitted > 0) {
        $message = 'Thank you! Your review' . ($submitted > 1 ? 's have' : ' has') . ' been submitted and are pending approval.';

        $stmt = db_execute(
            "SELECT oi.*, pr.id AS review_id, pr.status AS review_status
             FROM order_items oi
             LEFT JOIN product_reviews pr ON pr.order_item_id = oi.id AND pr.user_id = ?
             WHERE oi.order_id = ?
             ORDER BY oi.id ASC",
            'ii',
            [$user_id, $order_id]
        );
        $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $error = 'Please provide a valid rating and review for at least one product that has not been reviewed.';
    }
}

$page_meta = ['title' => 'Order Review | Adidev'];
include 'header.php';
?>

<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>Order Review</h1>
                        <ul>
                            <li><a href="index.php"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Order Review</a></li>
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
                    <h3 class="dashboard_title">Order Review <a class="back_btn common_btn" href="dashboard_order.php">Go Back</a></h3>

                    <?php if ($message) { ?>
                        <div class="alert alert-success" style="padding:16px; border-radius:10px; background:#e9f7ef; color:#155724; margin-bottom:20px;">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php } ?>
                    <?php if ($error) { ?>
                        <div class="alert alert-danger" style="padding:16px; border-radius:10px; background:#f8d7da; color:#842029; margin-bottom:20px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php } ?>

                    <?php if (!$error && $order) { ?>
                        <div class="order_review_area dashboard_order_invoice_area">
                            <form method="POST">
                                <div class="dashboard_order_invoice">
                                    <div class="dashboard_invoice_logo_area">
                                        <div class="invoice_logo">
                                            <img src="assets/images/logo_2.png" alt="logo" class="img-fluid w-100">
                                        </div>
                                        <div class="text">
                                            <h2>Order #<?php echo htmlspecialchars($order['order_number'] ?? $order['id']); ?></h2>
                                            <p>Date: <?php echo htmlspecialchars(date('d M Y', strtotime($order['order_date'] ?? $order['created_at']))); ?></p>
                                            <p>Payment: Paid</p>
                                        </div>
                                    </div>

                                    <div class="invoice_table">
                                        <div class="table-responsive">
                                            <table class="table" style="width:100%; border-collapse:collapse; margin-bottom:24px;">
                                                <thead>
                                                    <tr style="background:#f8f9fa;">
                                                        <th style="padding:14px; text-align:left;">Product</th>
                                                        <th style="padding:14px; text-align:left;">Price</th>
                                                        <th style="padding:14px; text-align:left;">Quantity</th>
                                                        <th style="padding:14px; text-align:left;">Review</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order_items as $item) {
                                                        $item_id = (int) $item['id'];
                                                        $alreadyReviewed = !empty($item['review_id']);
                                                    ?>
                                                        <tr>
                                                            <td style="padding:14px; vertical-align:top;">
                                                                <strong><?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?></strong><br>
                                                                <small><?php echo htmlspecialchars($item['product_code'] ?? ''); ?></small>
                                                            </td>
                                                            <td style="padding:14px; vertical-align:top;"><?php echo pricing_format((float)($item['unit_price'] ?? 0), get_user_currency($user_id)); ?></td>
                                                            <td style="padding:14px; vertical-align:top;"><?php echo (int) ($item['quantity'] ?? 1); ?></td>
                                                            <td style="padding:14px; vertical-align:top;">
                                                                <?php if ($alreadyReviewed) { ?>
                                                                    <span style="color:#0f5132; font-weight:600;">Already reviewed (<?php echo htmlspecialchars($item['review_status']); ?>)</span>
                                                                <?php } else { ?>
                                                                    <div style="display:grid; gap:10px;">
                                                                        <label style="font-weight:600;">Rating</label>
                                                                        <select name="reviews[<?php echo $item_id; ?>][rating]" style="padding:10px; min-width:120px; border:1px solid #ddd; border-radius:8px;">
                                                                            <option value="">Select</option>
                                                                            <?php for ($rating = 5; $rating >= 1; $rating--) { ?>
                                                                                <option value="<?php echo $rating; ?>"><?php echo $rating; ?> Star<?php echo $rating > 1 ? 's' : ''; ?></option>
                                                                            <?php } ?>
                                                                        </select>
                                                                        <input type="text" name="reviews[<?php echo $item_id; ?>][title]" placeholder="Review title" style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;">
                                                                        <textarea name="reviews[<?php echo $item_id; ?>][review]" placeholder="Write your review here" rows="4" style="padding:10px; border:1px solid #ddd; border-radius:8px; width:100%;"></textarea>
                                                                    </div>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
                                        <a href="dashboard_order.php" class="common_btn" style="background:#6c757d; color:#fff; padding:12px 20px; border-radius:10px; text-decoration:none;">Back to Orders</a>
                                        <button type="submit" class="common_btn" style="background:#007bff; color:#fff; padding:12px 20px; border:none; border-radius:10px; cursor:pointer;">Submit Reviews</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
