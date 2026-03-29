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
                            <h1> frequently asked question</h1>
                            <ul>
                                <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                                <li><a href="#">FAQ's</a></li>
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


    <!--=========================
        FAQ PAGE START
    ==========================-->
<section class="faq_page mt_50 mb_100">
    <div class="container">
        <p class="text-end">Last Updated: <?php echo date('F j, Y', strtotime('-2 days')); ?></p>
        <div class="accordion" id="accordionExample">
            <div class="row align-items-center">
                <div class="col-xxl-12 col-lg-12 mt_50 wow fadeInRight">
                    
                    <h6 class="faq_sub_title">General questions</h6>
                    <h3 class="faq_title">Frequently Asked Questions</h3>

                    <!-- Order -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne">
                                How do I place an order?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Select your desired products, add them to your cart, proceed to checkout, fill in your shipping details, and complete the payment. You will receive an order confirmation via email or SMS.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo">
                                What payment methods are accepted?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>We accept UPI, credit/debit cards, net banking, and wallet payments. Cash on Delivery (COD) may be available for selected locations.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shipping -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree">
                                What are the shipping charges and delivery time?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Shipping charges may vary based on your location and order value. Orders above a certain amount may qualify for free shipping. Delivery usually takes 3–7 business days depending on your area.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tracking -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFour">
                                How can I track my order?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Once your order is shipped, you will receive tracking details via email or SMS. You can use the tracking link to monitor your shipment in real-time.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Return -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseFive">
                                What is your return and refund policy?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>We offer returns for damaged, defective, or incorrect products within 7 days of delivery. Refunds are processed after inspection and may take 5–7 business days.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancel -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSix">
                                Can I cancel my order?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>Yes, orders can be canceled before they are shipped. Once shipped, cancellation may not be possible, but you can request a return after delivery.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseSeven">
                                Do I need an account to place an order?
                            </button>
                        </h2>
                        <div id="collapseSeven" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>No, you can place an order as a guest. However, creating an account allows you to track orders and manage your details easily.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseEight">
                                How can I contact customer support?
                            </button>
                        </h2>
                        <div id="collapseEight" class="accordion-collapse collapse"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <p>You can contact our support team via email or through our contact page. We aim to respond within 24 hours.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<?php include "footer.php"; ?>