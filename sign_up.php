<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_functions.php';

$register_errors = [];
$register_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (register_user($_POST, $register_errors)) {
        $register_success = true;
        // Redirect to dashboard or home after registration
        redirect('dashboard.php');
    }
}

// SEO meta for this page
$page_meta = [
    'title'       => 'Sign Up | Adidev Manufacturing Sales And Services Private Limited',
    'description' => 'Create your Adidev account to place orders, manage addresses, wishlist products and submit bulk inquiries.',
    'keywords'    => 'Adidev, sign up, registration, create account',
];

include "header.php";
?>
<!--=========================
        SIGN UP PAGE START
    ==========================-->
<section class="sign_up mt_100 mb_100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-3 col-lg-4 col-xl-4 d-none d-lg-block wow fadeInLeft">
                <div class="sign_in_img">
                    <img src="assets/images/sign_in_img_2.jpg" alt="Sign In" class="img-fluid w-100">
                </div>
            </div>
            <div class="col-xxl-5 col-lg-8 col-xl-6 col-md-10 wow fadeInRight">
                <div class="sign_in_form">
                    <h3>Sign Up to Continue 👋</h3>

                    <?php if (!empty($register_errors)) : ?>
                        <div class="alert alert-danger">
                            <?php foreach ($register_errors as $err) : ?>
                                <p><?php echo htmlspecialchars($err); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="sign_up.php" novalidate>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="single_input">
                                    <label>first name</label>
                                    <input
                                        type="text"
                                        name="first_name"
                                        placeholder="First name"
                                        value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input">
                                    <label>Last name</label>
                                    <input
                                        type="text"
                                        name="last_name"
                                        placeholder="Last name"
                                        value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="single_input">
                                    <label>email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        placeholder="you@example.com"
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="single_input">
                                    <label>phone</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        placeholder="+91 7369084701"
                                        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input">
                                    <label>password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="********"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input">
                                    <label>cnfirm password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        placeholder="********"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="single_input">
                                    <label>Currency</label>
                                    <select class="form-control form-control-lg" id="currency" name="currency">
                                        <option value="INR">INR</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <button type="submit" class="common_btn">Sign Up <i
                                        class="fas fa-long-arrow-right"></i></button>
                            </div>
                        </div>
                    </form>

                    <p class="dont_account">Already have an account? <a href="sign_in.php">Sign In</a></p>
                    <!-- <p class="or">or</p>
                        <ul>
                            <li>
                                <a href="#">
                                    <span>
                                        <img src="assets/images/google_logo.png" alt="google" class="img-fluid w-100">
                                    </span>
                                    Google
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <span>
                                        <img src="assets/images/fb_logo.png" alt="google" class="img-fluid w-100">
                                    </span>
                                    Facebook
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <span>
                                        <img src="assets/images/twitter_logo.png" alt="google" class="img-fluid w-100">
                                    </span>
                                    Twitter
                                </a>
                            </li>
                        </ul> -->
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
        SIGN UP PAGE END
    ==========================-->

<?php include "footer.php"; ?>