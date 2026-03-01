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
                                <li><a href="#">Reviews</a></li>
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
                <div class="col-lg-9 wow fadeInRight">
                    <div class="col-md-9">
  <div class="table-responsive">
  <table class="table table-bordered table-hover align-middle text-nowrap">
    <thead class="table-light">
      <tr>
        <th>Invoice Number</th>
        <th>Payment Method</th>
        <th>Download Invoice</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>Cash</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-16 05:30:42</td>
      </tr>
      <tr>
        <td>2</td>
        <td>Online</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-12 15:49:27</td>
      </tr>
      <tr>
        <td>3</td>
        <td>Cash</td>
        <td>
          <a href="https://example.com/invoice/107" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-12 05:32:01</td>
      </tr>
      <tr>
        <td>4</td>
          <td>Cash</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-11 03:58:00</td>
      </tr>
      <tr>
        <td>5</td>
          <td>Cash</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-10 10:46:19</td>
      </tr>
      <tr>
        <td>6</td>
        <td>Online</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-10 10:43:42</td>
      </tr>
      <tr>
        <td>7</td>
      <td>Online</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-10 10:40:36</td>
      </tr>
      <tr>
        <td>8</td>
        <td>Cash</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-10 10:35:34</td>
      </tr>
      <tr>
        <td>9</td>
      <td>Online</td>
        <td>
          <a href="download-invoices.php" target="_blank" class="btn btn-primary btn-sm">Download Invoice</a>
        </td>
        <td>2025-06-10 08:04:37</td>
      </tr>
    </tbody>
  </table>
</div>
   </div>
        </div>
    </section>
    <!--============================
        DSHBOARD END
    =============================-->

<?php include "footer.php"; ?>