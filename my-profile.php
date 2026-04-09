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
                            <li><a href="#">My Profile</a></li>
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
                                    <a href="invoice.php">
                                        <span>

                                        </span>
                                        Invoice
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
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 wow fadeInRight">
                <!-- Main Content -->

                <div class="flex-grow-1 p-4">
                    <section id="panel-profile" role="tabpanel" class="w-100">
                        <div class="bg-white p-4 rounded shadow-sm">
                            <h1 class="h1 fw-bold mb-3 text-dark">My Profile</h1>
                            <h4 class="h3 fw-semibold mb-3 text-secondary">Contact Us</h4>
                            <div class="mb-3 text-muted fs-4">
                                <p>
                                    Interactively are is our support the services sucking web-readiness.
                                </p>
                            </div>
                            <?php
                                $user_id = current_user_id() ?? 0;
                                $userDetails = get_user_details($user_id); 
                            ?>
                            <form action="user-profile-update.php" method="post">
                                <div class="mb-3">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg"
                                        placeholder="Enter Name" name="name" value="<?= isset($userDetails['display_name']) ? $userDetails['display_name'] : '' ?>" />
                                </div>
                                <div class="mb-3">
                                    <input
                                        type="email"
                                        class="form-control form-control-lg"
                                        placeholder="Email" name="email" value="<?= isset($userDetails['email']) ? $userDetails['email'] : '' ?>" />
                                    <input type="hidden" name="user_id" value="<?= isset($userDetails['id']) ? $userDetails['id'] : '' ?>" />
                                </div>
                                <div class="mb-3">
                                    <select class="form-control form-control-lg" id="currency" name="currency" disabled>
                                        <option value="INR" <?php echo $userDetails['currency'] === 'INR' ? 'selected' : ''; ?>>INR</option>
                                        <option value="USD" <?php echo $userDetails['currency'] === 'USD' ? 'selected' : ''; ?>>USD</option>
                                    </select>
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg">
                                    Send Message
                                </button>
                            </form>

                        </div>
                    </section>
                </div>
            </div>
</section>
<!--============================
        DSHBOARD END
    =============================-->
<?php include "footer.php"; ?>