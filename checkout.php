<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';
require_login();
start_checkout_session();

// SEO for checkout
$page_meta = [
    'title'       => 'Checkout | Adidev',
    'description' => 'Complete your purchase securely on Adidev.',
    'keywords'    => 'Adidev, checkout, payment, order',
];
$user_id   = current_user_id();
$currency  = get_user_currency($user_id);
$checkout_cart = checkout_page_cart_items($user_id);
include "header.php";
?>
<style>
    .common_btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .fa-spinner {
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
<!--=========================
        PAGE BANNER START
    ==========================-->
<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>Checkout</h1>
                        <ul>
                            <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Checkout</a></li>
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
        CHECKOUT START
    =============================-->
<form class="checkout_form_area">
    <section class="checkout_page mt_100 mb_100">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 wow fadeInUp">
                    <div class="checkout_header">
                        <h3>Shipping Information</h3>
                        <p>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            account:
                            <b><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Customer'); ?></b>
                            <a href="logout.php">(logout)</a>
                        </p>
                    </div>
                    <?php
                    // Check if cart is empty                $cart_items = get_cart_items();
                    if (empty($checkout_cart)) {
                        echo '<p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>';
                        include "footer.php";
                        exit;
                    }
                    ?>

                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item border-0">
                            <div id="collapseThree" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionExample">
                                <div class="accordion-body p-0">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Name *</label>
                                                <input type="text" name="customer_name" placeholder="Jhon deo" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Email *</label>
                                                <input type="email" name="customer_email" placeholder="vipin@wciprofile.com" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Phone *</label>
                                                <input type="text" name="customer_phone" placeholder="+965421541845845" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Company name</label>
                                                <input type="text" name="company_name" placeholder="Zenis.com">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Country</label>
                                                <select id="country" name="customer_country" class="select_2">
                                                    <option value="">-- Select Country --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>City</label>
                                                <select id="city" name="customer_city" class="select_2">
                                                    <option value="">-- Select City --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="single_input">
                                                <label>Pin Code / Zip Code *</label>
                                                <input type="text" name="zip" placeholder="1234" required>
                                            </div>
                                        </div>
                                        <div class="col-xl-12">
                                            <div class="single_input">
                                                <label>Address *</label>
                                                <textarea name="address" rows="4" placeholder="Write your address" required></textarea>
                                            </div>
                                        </div>
                                        <input
                                            type="hidden"
                                            name="cart_id"
                                            value="<?= htmlspecialchars($checkout_cart['cart']['cart_id'] ?? '') ?>">

                                        <input
                                            type="hidden"
                                            name="amount"
                                            value="<?= htmlspecialchars($checkout_cart['cart']['grand_total'] ?? 0) ?>">

                                        <input
                                            type="hidden"
                                            name="quantity"
                                            value="<?= htmlspecialchars($checkout_cart['cart']['total_quantity'] ?? 0) ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="single_input">
                            <label>Order notes (optional)</label>
                            <textarea name="order_notes" rows="2" placeholder="Note"></textarea>
                        </div>
                    </div>

                </div>
                <div class="col-lg-4 col-md-9 wow fadeInRight">
                    <div class="cart_page_summary">
                        <h3>Billing summary</h3>

                        <ul id="checkout-items">
                            <?php if (!empty($checkout_cart['items'])): ?>
                                <?php foreach ($checkout_cart['items'] as $item): ?>
                                    <li>
                                        <a class="img" href="#">
                                            <img
                                                src="<?= get_product_image($item, 'main') ?>"
                                                class="img-fluid w-100"
                                                alt="<?= htmlspecialchars($item['name']) ?>">
                                        </a>

                                        <div class="text">
                                            <p class="title"><?= htmlspecialchars($item['name']) ?></p>

                                            <p>
                                                <?= pricing_format($item['unit_price'], $currency) ?>
                                                × <?= (int)$item['quantity'] ?>
                                            </p>

                                            <?php if (!empty($item['color'])): ?>
                                                <p>Color: <?= htmlspecialchars($item['color']) ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($item['size'])): ?>
                                                <p>Size: <?= htmlspecialchars($item['size']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No items in cart</li>
                            <?php endif; ?>
                        </ul>

                        <!-- Cart Summary -->
                        <?php $cart = $checkout_cart['cart'] ?? []; ?>

                        <h6>
                            Subtotal
                            <span id="checkout-subtotal">
                                <?= pricing_format($cart['subtotal'] ?? 0, $currency) ?>
                            </span>
                        </h6>

                        <h6>
                            Shipping
                            <span>
                                <?= pricing_format($cart['shipping_amount'] ?? 0, $currency) ?>
                            </span>
                        </h6>

                        <h6>
                        Discount
                        <span>
                            <?= pricing_format($cart['discount_amount'] ?? 0, $currency) ?>
                        </span>
                    </h6>

                        <h4>
                            Total
                            <span id="checkout-total">
                                <?= pricing_format($cart['grand_total'] ?? 0, $currency) ?>
                            </span>
                        </h4>
                    </div>
                    <div class="checkout_payment">
                        <h3>Payment Method</h3>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="cod" id="payment_cod" checked>
                            <label class="form-check-label" for="payment_cod">
                                Cash on Delivery
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" value="online" id="payment_card">
                            <label class="form-check-label" for="payment_card">
                                Online Payment (Card, UPI, Netbanking)
                            </label>
                        </div>
                        <div class="checkout_card">
                            <p>Online Payment</p>
                            <ul>
                                <li><img src="assets/images/payment-1.jpg" alt="Payment" class="img-fluid w-100"></li>
                                <li><img src="assets/images/payment-3.jpg" alt="Payment" class="img-fluid w-100"></li>
                                <li><img src="assets/images/payment-4.jpg" alt="Payment" class="img-fluid w-100"></li>
                            </ul>
                        </div>
                        <div class="terms">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="termsCheckbox" required>
                                <label class="form-check-label" for="termsCheckbox">
                                    I have read and agree to the website.
                                </label>
                            </div>
                        </div>
                        <!-- ✅ SUBMIT BUTTON -->
                        <button type="submit" class="common_btn">
                            Place Order <i class="fas fa-long-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
</form>
<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="state.js"></script>

<script>
$(document).ready(function() {
    $('.checkout_form_area').on('submit', function(e) {
        e.preventDefault();
        
        if (!$('#termsCheckbox').is(':checked')) {
            alert('Please accept the terms and conditions');
            return false;
        }
        
        var formData = $(this).serialize();
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: 'ajax/place-order.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.payment_required && response.redirect_to_cashfree) {
                        // Direct redirect to Cashfree form
                        window.location.href = response.cashfree_form_url;
                    } else if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    } else {
                        window.location.href = 'thankyou.php?order_id=' + response.order_number;
                    }
                } else {
                    if (response.redirect) {
                        window.location.href = response.redirect_url;
                    } else {
                        alert(response.message || 'Failed to place order. Please try again.');
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred. Please try again.');
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });
});
</script>

<style>
    /* Loading spinner animation */
    .fa-spinner {
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

    /* Button disabled state */
    .common_btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Required field validation */
    input[required],
    select[required],
    textarea[required] {
        transition: border-color 0.3s ease;
    }

    input[required]:invalid,
    select[required]:invalid,
    textarea[required]:invalid {
        border-color: #ff4444;
    }

    /* Empty cart message */
    .empty-cart {
        text-align: center;
        padding: 20px;
        color: #666;
    }
</style>
<!--============================
        CHECKOUT END
    =============================-->
<?php include "footer.php"; ?>