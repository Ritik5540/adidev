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
                         <h1>Cart View</h1>
                         <ul>
                             <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                             <li><a href="#">Cart View</a></li>
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
        CART PAGE START
    =============================-->
 <section class="cart_page mt_100 mb_100">
     <div class="container">
         <div class="row">
             <div class="col-lg-8 wow fadeInUp">
                 <div class="cart_table_area">
                     <div class="table-responsive">
                         <table class="table">
                             <thead>
                                 <tr>
                                     <th class="cart_page_checkbox">
                                         <div class="form-check">
                                             <input class="form-check-input" type="checkbox" value=""
                                                 id="flexCheckDefault">
                                         </div>
                                     </th>
                                     <th class="cart_page_img">Product image </th>
                                     <th class="cart_page_details">Product Details</th>
                                     <th class="cart_page_price">Unit Price</th>
                                     <th class="cart_page_quantity">Quantity</th>
                                     <th class="cart_page_total">Subtotal</th>
                                     <th class="cart_page_action">action</th>
                                 </tr>
                             </thead>
                         </table>
                     </div>
                     <div class="table-responsive">
                         <table class="table">
                             <tbody>
                                 <?php $user_id = current_user_id() ?? 0;
                                    $currency = get_user_currency($user_id);
                                    ?>
                                 <?php if (!empty($cart_items)) : ?>

                                     <?php foreach ($cart_items as $item) : ?>

                                         <tr data-product-id="<?= (int)$item['product_id'] ?>">

                                             <!-- checkbox -->
                                             <td class="cart_page_checkbox">
                                                 <div class="form-check">
                                                     <input class="form-check-input cart-check" type="checkbox">
                                                 </div>
                                             </td>

                                             <!-- image -->
                                             <td class="cart_page_img">
                                                 <div class="img">
                                                     <img src="<?= get_product_image($item, 'main') ?>"
                                                         class="img-fluid w-100">
                                                 </div>
                                             </td>

                                             <!-- details -->
                                             <td class="cart_page_details">
                                                 <a class="title" href="shop_details.php?id=<?= encrypt_id((int)$item['product_id']) ?>">
                                                     <?= htmlspecialchars($item['name']) ?>
                                                 </a>

                                                 <p>
                                                     <?= pricing_format($item['unit_price'], $currency) ?>
                                                 </p>

                                                 <?php if (!empty($item['color'])) : ?>
                                                     <span><b>Color:</b> <?= htmlspecialchars($item['color']) ?></span>
                                                 <?php endif; ?>

                                                 <?php if (!empty($item['size'])) : ?>
                                                     <span><b>Size:</b> <?= htmlspecialchars($item['size']) ?></span>
                                                 <?php endif; ?>
                                             </td>

                                             <!-- price -->
                                             <td class="cart_page_price">
                                                 <h3 class="unit-price" data-price="<?= $item['unit_price'] ?>">
                                                     <?= pricing_format($item['unit_price'], $currency) ?>
                                                 </h3>
                                             </td>

                                             <!-- quantity -->
                                             <td class="cart_page_quantity">
                                                 <div class="details_qty_input">
                                                     <button class="minus">-</button>

                                                     <input type="number"
                                                         class="qty-input"
                                                         value="<?= (int)$item['quantity'] ?>"
                                                         min="1">

                                                     <button class="plus">+</button>
                                                 </div>
                                             </td>

                                             <!-- total -->
                                             <td class="cart_page_total">
                                                 <h3 class="row-total">
                                                     <?= pricing_format($item['unit_price'] * $item['quantity'], $currency) ?>
                                                 </h3>
                                             </td>

                                             <!-- remove -->
                                             <td class="cart_page_action">
                                                 <a href="#" data-id="<?php echo $item['id']; ?>" class="remove-from-cart">
                                                     <i class="fal fa-times"></i> Remove
                                                 </a>
                                             </td>

                                         </tr>

                                     <?php endforeach; ?>

                                 <?php else : ?>
                                     <tr>
                                         <td colspan="7" style="text-align:center;">Cart empty</td>
                                     </tr>
                                 <?php endif; ?>
                             </tbody>
                         </table>
                     </div>
                 </div>
             </div>
             <div class="col-lg-4 col-md-9 wow fadeInRight">
                 <div id="sticky_sidebar">
                     <div class="cart_page_summary">
                         <h3>Billing summary</h3>

                         <!-- dynamic items -->
                         <ul id="billing-items"></ul>

                         <h6>subtotal <span id="billing-subtotal">₹0.00</span></h6>
                         <h4>Total <span id="billing-total">₹0.00</span></h4>
                     </div>
                     <div class="cart_summary_btn">
                         <a class="common_btn continue_shopping" href="shop.php">Contiue shopping</a>
                         <a class="common_btn" href="checkout.php">checkout <i
                                 class="fas fa-long-arrow-right"></i></a>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </section>
 <script>
     function calculateCart() {

         let subtotal = 0;
         let billingHTML = '';
         let cartData = [];
         let currency = '<?= $currency ?>';

         let rows = document.querySelectorAll("tbody tr");

         let anyChecked = [...rows].some(row => {
             let cb = row.querySelector(".cart-check");
             return cb && cb.checked;
         });

         rows.forEach(row => {

             let check = row.querySelector(".cart-check");
             let qtyInput = row.querySelector(".qty-input");
             let priceEl = row.querySelector(".unit-price");

             if (!qtyInput || !priceEl) return;

             let price = parseFloat(priceEl.dataset.price);
             let qty = parseInt(qtyInput.value) || 1;

             let rowTotal = price * qty;

             // update row total
             let totalEl = row.querySelector(".row-total");
             if (totalEl) {
                 totalEl.innerText = pricing_format(rowTotal, currency);
             }

             // default: all OR selected
             if (!anyChecked || (check && check.checked)) {

                 subtotal += rowTotal;

                 let name = row.querySelector(".title")?.innerText || '';
                 let img = row.querySelector("img")?.src || '';
                 let color = row.querySelector("span")?.innerText || '';

                 // 👉 STORE DATA
                 cartData.push({
                     name: name,
                     image: img,
                     price: price,
                     quantity: qty,
                     total: rowTotal,
                     color: color
                 });

                 billingHTML += `
                <li>
                    <a class="img" href="#">
                        <img src="${img}" class="img-fluid w-100">
                    </a>
                    <div class="text">
                        <p class="title">${name}</p>
                        <p>${pricing_format(price, currency)} × ${qty}</p>
                        <p>${color}</p>
                    </div>
                </li>
            `;
             }

         });

         // 👉 SAVE TO LOCAL STORAGE
         localStorage.setItem("checkout_cart", JSON.stringify({
             items: cartData,
             subtotal: subtotal,
             total: subtotal
         }));

         // update UI
         document.getElementById("billing-items").innerHTML = billingHTML;
         document.getElementById("billing-subtotal").innerText = pricing_format(subtotal, currency);
         document.getElementById("billing-total").innerText = pricing_format(subtotal, currency);
     }


     // EVENTS
     document.addEventListener("click", function(e) {

         let qtyBox = e.target.closest(".details_qty_input");
         if (!qtyBox) return;

         let input = qtyBox.querySelector(".qty-input");
         let row = e.target.closest("tr");

         // IMPORTANT: product_id pass karo (HTML me add karna padega)
         let productId = row.getAttribute("data-product-id");

         let qty = parseInt(input.value || 1);

         // PLUS
         if (e.target.closest(".plus")) {
             qty++;
             input.value = qty;
             updateCart(productId, 1); // increment by 1
         }

         // MINUS
         if (e.target.closest(".minus")) {
             if (qty > 1) {
                 qty--;
                 input.value = qty;
                 updateCart(productId, -1); // decrement by 1
             }
         }
     });

     // AJAX FUNCTION
     function updateCart(productId, qtyChange) {
         fetch("ajax/add_to_cart.php", {
                 method: "POST",
                 headers: {
                     "Content-Type": "application/x-www-form-urlencoded"
                 },
                 body: `product_id=${productId}&quantity=${qtyChange}`
             })
             .then(res => res.json())
             .then(data => {
                 if (data.success) {
                     calculateCart(); // UI update
                 } else if (data.redirect) {
                     window.location.href = data.redirect_url;
                 } else {
                     alert(data.message);
                 }
             })
             .catch(err => console.error(err));
     }

     document.addEventListener("input", function(e) {
         if (e.target.classList.contains("qty-input")) {

             if (!e.target.value || e.target.value < 1) {
                 e.target.value = 1;
             }

             calculateCart();
         }
     });

     // LOAD DEFAULT
     window.addEventListener("load", calculateCart);
 </script>
 <!--============================
        CART PAGE END
    =============================-->
 <?php include "footer.php"; ?>