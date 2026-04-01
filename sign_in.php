<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_functions.php';

$login_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login_user($email, $password, $login_errors)) {
        // Redirect to dashboard or home after successful login
        redirect('dashboard.php');
    }
}

// SEO meta for this page
$page_meta = [
    'title'       => 'Sign In | Adidev Manufacturing Sales And Services Private Limited',
    'description' => 'Sign in to your Adidev account to manage orders, wishlist, and bulk inquiries.',
    'keywords'    => 'Adidev, sign in, login, customer account',
];

include "header.php";
?>
 <!--=========================
        SIGN IN PAGE START
    ==========================-->
    <section class="sign_in mt_100 mb_100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xxl-3 col-lg-4 col-xl-4 d-none d-lg-block wow fadeInLeft">
                    <div class="sign_in_img">
                        <img src="assets/images/sign_in_img.jpg" alt="Sign In" class="img-fluid w-100">
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-7 col-xl-6 col-md-10 wow fadeInRight">
                    <div class="sign_in_form">
                        <h3>Sign In to Continue 👋</h3>

                        <?php if (!empty($login_errors)) : ?>
                            <div class="alert alert-danger">
                                <?php foreach ($login_errors as $err) : ?>
                                    <p><?php echo htmlspecialchars($err); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="sign_in.php" novalidate>
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="single_input">
                                        <label>email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            placeholder="example@yourdomain.com"
                                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <div class="single_input">
                                        <label>password</label>
                                        <input
                                            type="password"
                                            name="password"
                                            placeholder="********"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="forgot">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value=""
                                                id="flexCheckDefault">
                                            <label class="form-check-label" for="flexCheckDefault">
                                                Remeber Me
                                            </label>
                                        </div>
                                        <a href="forgot_password.php">Forgot Password ?</a>
                                    </div>
                                </div>
                                <div class="col-xl-12">
                                    <button type="submit" class="common_btn">Sign In <i
                                            class="fas fa-long-arrow-right"></i></button>
                                </div>
                            </div>
                        </form>

                        <p class="dont_account">Already have an account? <a href="sign_up.php">Sign Up</a></p>
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
        SIGN IN PAGE END
    ==========================-->


<?php include "footer.php"; ?>