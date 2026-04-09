<?php include "header.php"; ?>
<!--=========================
        PAGE BANNER START
    ==========================-->
<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>Wishlist</h1>
                        <ul>
                            <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Wishlist</a></li>
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
        WISHLIST START
    =============================-->
<section class="wishlist_page cart_page mt_100 mb_100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11">
                <div class="cart_table_area wow fadeInUp">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="cart_page_img">Product image </th>
                                    <th class="cart_page_details">Product Details</th>
                                    <th class="cart_page_price">Unit Price</th>
                                    <th class="cart_page_quantity">Quantity</th>
                                    <th class="cart_page_action">add to cart</th>
                                    <th class="cart_page_action">remove</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <td>
                                    <!-- <h4 class="cart_vendor_name">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                        </svg>
                                        Zapier Gallery
                                    </h4> -->
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <?php
                            $user_id = current_user_id() ?? 0;
                            $currency = get_user_currency($user_id);
                            $wishlist_items = $user_id ? get_user_wishlist_items($user_id , $currency) : [];
                            ?>

                            <tbody>
                                <?php if (!empty($wishlist_items)) : ?>

                                    <?php foreach ($wishlist_items as $product) : ?>
                                        <tr>

                                            <td class="cart_page_img">
                                                <div class="img">
                                                    <img src="<?= get_product_image($product, 'main') ?>"
                                                        alt="<?= htmlspecialchars($product['name']) ?>"
                                                        class="img-fluid w-100">
                                                </div>
                                            </td>

                                            <td class="cart_page_details">
                                                <a class="title" href="shop_details.php?id=<?= (int)$product['id'] ?>">
                                                    <?= htmlspecialchars($product['name']) ?>
                                                </a>

                                                <p><?= pricing_format($product['price'], $currency) ?></p>

                                                <?php if (!empty($product['color'])) : ?>
                                                    <span><b>Color:</b> <?= htmlspecialchars($product['color']) ?></span>
                                                <?php endif; ?>

                                                <?php if (!empty($product['size'])) : ?>
                                                    <span><b>Size:</b> <?= htmlspecialchars($product['size']) ?></span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="cart_page_price">
                                                <h3><?= pricing_format($product['price'], $currency) ?></h3>
                                            </td>

                                            <td class="cart_page_quantity">
                                                <div class="details_qty_input">
                                                    <button class="minus"><i class="fal fa-minus"
                                                            aria-hidden="true"></i></button>
                                                    <input type="number" class="qty-input" value="1" min="1">
                                                    <button class="plus"><i class="fal fa-plus"
                                                            aria-hidden="true"></i></button>
                                                </div>
                                            </td>

                                            <td class="cart_page_action">
                                                <a href="javascript:void(0)"
                                                    class="common_btn add-to-cart-wislisht"
                                                    data-id="<?= (int)$product['id'] ?>">
                                                    add to cart
                                                </a>
                                            </td>

                                            <td class="cart_page_action">
                                                <a href="javascript:void(0)"
                                                    class="common_btn remove-from-wishlist"
                                                    data-id="<?= (int)$product['id'] ?>">
                                                    remove
                                                </a>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>

                                <?php else : ?>

                                    <?php render_empty_wishlist(); ?>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener("click", function(e) {

        // PLUS
        if (e.target.closest(".plus")) {
            const container = e.target.closest(".details_qty_input");
            const input = container.querySelector(".qty-input");

            let val = parseInt(input.value) || 1;
            input.value = val + 1;
        }

        // MINUS
        if (e.target.closest(".minus")) {
            const container = e.target.closest(".details_qty_input");
            const input = container.querySelector(".qty-input");

            let val = parseInt(input.value) || 1;

            if (val > 1) {
                input.value = val - 1;
            }
        }
    });
</script>
<!--============================
        WISHLIST END
    =============================-->

<?php include "footer.php"; ?>