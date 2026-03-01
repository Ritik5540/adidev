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
                                <h3>Mr. ariful islam</h3>
                                <p>arifulislam@gmail.com</p>
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
                                    <li>
                                        <a href="my-connection.php">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 9.75h4.875a2.625 2.625 0 0 1 0 5.25H12M8.25 9.75 10.5 7.5M8.25 9.75 10.5 12m9-7.243V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" />
                                                </svg>
                                            </span>
                                          My-Connection
                                        </a>
                                    </li>
                                  
                                    <li>
                                        <a href="my-post.php">
                                            <span>
                                               
                                            </span>
                                        My Post
                                        </a>
                                    </li>
                                    <li>
                                        <a href="product-detial.php">
                                            <span>
                                              
                                            </span>
                                          Products
                                        </a>
                                    </li>
                                   
                                    <li>
                                        <a href="invoice.php">
                                            <span>
                                               
                                            </span>
                                         Invoice
                                        </a>
                                    </li>
                                     <li>
                                        <a href="dashboard_wishlist.php">
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
                                        <a href="dashboard_change_password.php">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                </svg>
                                            </span>
                                            Change Password
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                                </svg>
                                            </span>
                                            Logout
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                        <!-- Main Content -->
  
  <div class="bg-white text-dark fs-6">
  <div class="container py-5">
    <!-- Header -->
    <div class="row align-items-start mb-4">
      <div class="col-md-6">
        <div class="bg-danger text-white fw-bold fs-3 px-4 py-2 d-inline-block" style="transform: skewX(-20deg);">
          <span style="display:inline-block; transform: skewX(20deg);">INVOICE</span>
        </div>
      </div>
      <div class="col-md-6 d-flex justify-content-end align-items-center gap-2">
        <div class="bg-dark text-white px-3 py-2" style="width: 220px; transform: skewX(-20deg); font-size: 0.75rem;">
          <div style="transform: skewX(20deg);">
            <div class="d-flex justify-content-between mb-1">
              <span>Invoice No:</span>
              <span class="text-light">#935648</span>
            </div>
            <div class="d-flex justify-content-between">
              <span>Date:</span>
              <span class="text-light">22/03/2023</span>
            </div>
          </div>
        </div>
        <div class="bg-danger d-flex align-items-center justify-content-center" style="width: 120px; height: 60px; transform: skewX(-20deg);">
          <img src="https://storage.googleapis.com/a1aa/image/b0dc4f13-ada5-4d69-6c31-23cdb577316b.jpg" alt="Logo" class="img-fluid" style="transform: skewX(20deg); max-height: 40px;">
        </div>
      </div>
    </div>

    <!-- Address -->
    <div class="row mb-4">
      <div class="col-md-6">
        <p class="fw-bold">Invoiced To:</p>
        <p>Alex Farnandes</p>
        <p>450 E 96th St, Indianapolis, WRHX+8Q</p>
        <p>IN 46240, United States</p>
      </div>
      <div class="col-md-6 text-md-end">
        <p class="fw-bold">Pay To:</p>
        <p>Payment Info:</p>
        <p>Account : 1234 5678 9012</p>
        <p>A/C Name : Alex Farnandes</p>
        <p>Email : info@invar.com</p>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive border border-secondary rounded mb-5">
      <table class="table table-bordered text-nowrap align-middle text-center mb-0">
        <thead class="table-light text-dark">
          <tr>
            <th style="width: 50px;">SL</th>
            <th class="text-start">Item Description</th>
            <th class="text-end" style="width: 100px;">Price</th>
            <th style="width: 70px;">Qty</th>
            <th class="text-end" style="width: 100px;">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>01</td>
            <td class="text-start">Easy Chicken Masala</td>
            <td class="text-end">$25.00</td>
            <td>1</td>
            <td class="text-end">$25.00</td>
          </tr>
          <tr>
            <td>02</td>
            <td class="text-start">Boiled Organic Egg</td>
            <td class="text-end">$10.00</td>
            <td>3</td>
            <td class="text-end">$20.00</td>
          </tr>
          <tr>
            <td>03</td>
            <td class="text-start">Grilled Smoked Chicken</td>
            <td class="text-end">$100.00</td>
            <td>2</td>
            <td class="text-end">$200.00</td>
          </tr>
          <tr>
            <td>04</td>
            <td class="text-start">Party Platter Wings</td>
            <td class="text-end">$150.00</td>
            <td>1</td>
            <td class="text-end">$150.00</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Footer -->
    <div class="row mt-5">
      <div class="col-md-7">
        <p class="fw-bold mb-2">Terms & Conditions</p>
        <p class="mb-2">Authoritatively envisioned business action items through parallel.</p>
        <p><span class="fw-bold">NOTE:</span> This is a computer generated receipt and does not require physical signature.</p>
      </div>
      <div class="col-md-5">
        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-bold">Sub Total:</span>
          <span>$405.00</span>
        </div>
        <div class="d-flex justify-content-between border-bottom py-1">
          <span class="fw-bold">Tax:</span>
          <span>$00.00</span>
        </div>
        <div class="d-flex justify-content-between pt-2 fw-bold">
          <span>Total:</span>
          <span>$405.00</span>
        </div>
      </div>
    </div>

    <!-- Bottom Stripes -->
    <div class="d-flex justify-content-between mt-5">
      <div class="bg-danger" style="width: 180px; height: 24px; transform: skewX(-20deg);"></div>
      <div class="bg-danger" style="width: 120px; height: 24px; transform: skewX(-20deg);"></div>
      <div class="bg-dark" style="width: 180px; height: 24px; transform: skewX(-20deg);"></div>
    </div>
  </div>
</div>
        </div>
    </section>
    <!--============================
        DSHBOARD END
    =============================-->


<?php include "footer.php"; ?>