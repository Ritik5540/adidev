 <!--=========================
        FOOTER 2 START
    ==========================-->
 <footer class="footer_2 pt_100" style="background: url(assets/images/footer_2_bg_2.jpg);">
     <div class="container">
         <div class="row justify-content-between">
             <div class="col-xl-3 col-md-6 col-lg-3 wow fadeInUp" data-wow-delay=".7s">
                 <div class="footer_2_logo_area">
                     <a class="footer_logo" href="/">
                         <img src="assets/images/logo_2.png" alt="Adidev Manufacturing Sales And Services Private Limited" class="img-fluid w-100">
                     </a>
                     <p>Adidev Manufacturing Sales And Services Private Limited, a active private limited company, was established on 06 May 2025 in Ranchi, Jharkhand, India.</p>
                     <ul>
                         <li><span>Follow :</span></li>
                         <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                         <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                         <li><a href="#"><i class="fab fa-google-plus-g"></i></a></li>
                         <li><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                     </ul>
                 </div>
             </div>
             <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1s">
                 <div class="footer_link">
                     <h3>Company</h3>
                     <ul>
                         <li><a href="about.php">About us</a></li>
                         <li><a href="contact_us.php">Contact Us</a></li>
                         <li><a href="#">Affiliate</a></li>
                         <li><a href="#">Career</a></li>
                         <li><a href="#">Latest News</a></li>
                     </ul>
                 </div>
             </div>
             <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1.3s">
                 <div class="footer_link">
                     <h3>Category</h3>
                     <ul>
                         <li><a href="category.php">All Categories</a></li>
                         <?php foreach ($footer_sub_categories as $mainId => $subCats) : ?>
                             <?php foreach ($subCats as $subCat) : ?>
                                 <li><a href="shop.php?sub=<?php echo urlencode($subCat['slug']); ?>"><?php echo htmlspecialchars($subCat['name']); ?></a></li>
                             <?php endforeach; ?>
                         <?php endforeach; ?>
                     </ul>
                 </div>
             </div>
             <div class="col-xl-2 col-sm-6 col-md-4 col-lg-2 wow fadeInUp" data-wow-delay="1.6s">
                 <div class="footer_link">
                     <h3>Quick Links</h3>
                     <ul>
                         <li><a href="privacy_policy.php">Privacy Policy</a></li>
                         <li><a href="terms_condition.php">Terms and Condition</a></li>
                         <li><a href="return_policy.php">Return Policy</a></li>
                         <li><a href="faq.php">FAQ's</a></li>
                     </ul>
                 </div>
             </div>
             <div class="col-xl-3 col-sm-6 col-md-4 col-lg-3 wow fadeInUp" data-wow-delay="1.9s">
                 <div class="footer_link footer_logo_area">
                     <h3>Contact Us</h3>
                     <p>It is a long established fact that reader distracted looking layout It is a long established
                         fact.</p>
                     <span>
                         <b><img src="assets/images/location_icon_white.png" alt="Map" class="img-fluid"></b>
                         H1 279, KARTIK ORAON CHOWK HARMU COLONY
                         RANCHI,JHARKHAND,834002, INDIA</span>
                     <span>
                         <b><img src="assets/images/phone_icon_white.png" alt="Call" class="img-fluid"></b>
                         <a href="callto:+91 7369084701
">+91 7369084701
                         </a>
                     </span>
                     <span>
                         <b><img src="assets/images/mail_icon_white.png" alt="Mail" class="img-fluid"></b>
                         <a href="mailto:care@adidevmanufacturing.com">care@adidevmanufacturing.com</a>
                     </span>
                 </div>
             </div>
         </div>
         <div class="row">
             <div class="col-12">
                 <div class="footer_copyright mt_75">
                     <p>Copyright @ <b>Adidev Manufacturing Sales And Services Private Limited</b> 2025. All right reserved.</p>
                     <ul class="payment">
                         <li>Payment by :</li>
                         <li>
                             <img src="assets/images/footer_payment_icon_1.jpg" alt="payment"
                                 class="img-fluid w-100">
                         </li>
                         <li>
                             <img src="assets/images/footer_payment_icon_2.jpg" alt="payment"
                                 class="img-fluid w-100">
                         </li>
                         <li>
                             <img src="assets/images/footer_payment_icon_3.jpg" alt="payment"
                                 class="img-fluid w-100">
                         </li>
                     </ul>
                 </div>
             </div>
         </div>
     </div>
 </footer>
 <!--=========================
        FOOTER 2 END
    ==========================-->


 <!--==========================
        SCROLL BUTTON START
    ===========================-->
 <div class="progress-wrap">
     <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
         <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
     </svg>
 </div>
 <!--==========================
        SCROLL BUTTON END
    ===========================-->

 <!--jquery library js-->
 <script src="assets/js/jquery-3.7.1.min.js"></script>
 <script>
     function updateCounts() {
         $.ajax({
             url: 'ajax/get_cart_count.php',
             type: 'GET',
             dataType: 'json',
             cache: false,
             success: function(res) {
                 if (res.success) {
                     $('.cart-count').text(res.cart_count || 0);
                     $('.wishlist-count').text(res.wishlist_count || 0);
                 }
             },
             error: function() {
                 console.warn('Count fetch failed');
             }
         });
     }

     updateCounts();

     function pricing_format(amount, currency = 'INR') {

         amount = parseFloat(amount || 0);

         if (currency === 'USD') {
             // US format
             return '$' + amount.toLocaleString('en-US', {
                 minimumFractionDigits: 2,
                 maximumFractionDigits: 2
             });
         }

         // Default INR
         return '₹' + amount.toLocaleString('en-IN', {
             minimumFractionDigits: 2,
             maximumFractionDigits: 2
         });
     }

     // Remove from wishlist
     $(document).on('click', '.remove-from-wishlist', function() {
         let id = $(this).data('id');

         $.post('ajax/remove_from_wishlist.php', {
             product_id: id
         }, function(res) {
             if (res.success) {
                 location.reload();
             }
         }, 'json');
     });

     // Remove item from add to cart
     $(document).on('click', '.remove-from-cart', function() {
         let id = $(this).data('id');

         $.post('ajax/remove_from_cart.php', {
             cart_item_id: id
         }, function(res) {
             if (res.success) {
                 location.reload();
             }
         }, 'json');
     });
 </script>
 <!--bootstrap js-->
 <script src="assets/js/bootstrap.bundle.min.js"></script>
 <!--font-awesome js-->
 <script src="assets/js/Font-Awesome.js"></script>
 <!--counter js-->
 <script src="assets/js/jquery.waypoints.min.js"></script>
 <script src="assets/js/jquery.countup.min.js"></script>
 <!--nice select js-->
 <script src="assets/js/jquery.nice-select.min.js"></script>
 <!--select 2 js-->
 <script src="assets/js/select2.min.js"></script>
 <!--simply countdown js-->
 <script src="assets/js/simplyCountdown.js"></script>
 <!--slick slider js-->
 <script src="assets/js/slick.min.js"></script>
 <!--venobox js-->
 <script src="assets/js/venobox.min.js"></script>
 <!--wow js-->
 <script src="assets/js/wow.min.js"></script>
 <!--marquee js-->
 <script src="assets/js/jquery.marquee.min.js"></script>
 <!--pws tabs js-->
 <script src="assets/js/jquery.pwstabs.min.js"></script>
 <!--scroll button js-->
 <script src="assets/js/scroll_button.js"></script>
 <!--youtube background js-->
 <script src="assets/js/jquery.youtube-background.min.js"></script>
 <!--range slider js-->
 <script src="assets/js/range_slider.js"></script>
 <!--sticky sidebar js-->
 <script src="assets/js/sticky_sidebar.js"></script>
 <!--multiple image upload js-->
 <script src="assets/js/multiple-image-video.js"></script>
 <!--main/custom js-->
 <script src="assets/js/product.js"></script>
 <script src="assets/js/custom.js"></script>

 </body>

 </html>