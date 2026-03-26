<?php
date_default_timezone_set('Asia/Kolkata');
include "../admin-config.php";
include "otp-mail.php";
// session_start();


// Check if user is already logged in
if (isset($_SESSION['user_id']) && $page != 'logout') {
    header("Location: dashboard.php");
    exit;
}

// Initialize variables
$error = '';
$success = '';
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Check for remember me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    // Check if token exists in database
    $sql = "SELECT u.* FROM admins u WHERE u.remember_token = ? AND u.status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Regenerate token for security
        $new_token = bin2hex(random_bytes(32));
        $sql_update = "UPDATE admins SET remember_token = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_token, $user['id']);
        $stmt_update->execute();

        // Set new cookie
        setcookie('remember_token', $new_token, time() + (30 * 24 * 60 * 60), '/');

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    }
}

/* LOGIN PROCESS */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Handle login form submission
        $email = $conn->real_escape_string(trim($_POST['email']));
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        // Validate inputs
        if (empty($email) || empty($password)) {
            $error = "Please enter email and password";
        } else {
            // Check if user exists
            $sql = "SELECT * FROM admins WHERE email = '$email' AND status = 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Check if OTP is required
                    if ($user['otp_enabled'] == 1) {
                        // Generate and send OTP
                        $otp = generateOTP();
                        $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                        $sql_update = "UPDATE admins SET otp_value = ? WHERE id = ?";
                        $stmt_update = $conn->prepare($sql_update);
                        $stmt_update->bind_param("si", $otp, $user['id']);
                        $stmt_update->execute();

                        // Store OTP in session for verification
                        $_SESSION['temp_user_id'] = $user['id'];
                        $_SESSION['temp_otp'] = $otp;
                        $_SESSION['temp_otp_expiry'] = $otp_expiry;

                        // Send OTP via email
                        sendOTPEmail($user['email'], $user['name'], $otp);

                        // Log OTP generation
                        logActivity($conn, $user['id'], 'OTP_SENT', "OTP sent to email");

                        // Redirect to OTP verification page
                        header("Location: index.php?page=otp");
                        exit;
                    } else {
                        // Login without OTP
                        completeLogin($conn, $user, $remember);

                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit;
                    }
                } else {
                    // Invalid password
                    $error = "Invalid email or password";
                    logFailedAttempt($conn, $email, 'WRONG_PASSWORD');
                }
            } else {
                // User not found
                $error = "Invalid email or password";
                logFailedAttempt($conn, $email, 'USER_NOT_FOUND');
            }
        }
    } elseif (isset($_POST['verify_otp'])) {
        // Handle OTP verification
        $otp = $_POST['otp'] ?? '';

        if (empty($otp)) {
            $error = "Please enter OTP";
        } elseif (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['temp_otp'])) {
            $error = "OTP session expired. Please login again.";
        } elseif (time() > strtotime($_SESSION['temp_otp_expiry'])) {
            $error = "OTP has expired. Please login again.";
        } elseif ($otp != $_SESSION['temp_otp']) {
            $error = "Invalid OTP";
            logFailedAttempt($conn, $_SESSION['temp_user_id'], 'WRONG_OTP');
        } else {
            // OTP verified successfully
            $user_id = $_SESSION['temp_user_id'];
            $sql = "SELECT * FROM admins WHERE id = '$user_id'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $remember = isset($_SESSION['temp_remember']) ? $_SESSION['temp_remember'] : false;

                // Complete login
                completeLogin($conn, $user, $remember);

                // Clear temporary session data
                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_otp']);
                unset($_SESSION['temp_otp_expiry']);
                unset($_SESSION['temp_remember']);

                // Log successful OTP verification
                logActivity($conn, $user['id'], 'OTP_VERIFIED', "OTP verified successfully");

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "User not found";
            }
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Handle OTP resend
        if (isset($_SESSION['temp_user_id'])) {
            $user_id = $_SESSION['temp_user_id'];
            $sql = "SELECT * FROM admins WHERE id = '$user_id'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Generate new OTP
                $otp = generateOTP();
                $otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                // Update session with new OTP
                $_SESSION['temp_otp'] = $otp;
                $_SESSION['temp_otp_expiry'] = $otp_expiry;

                // Send new OTP
                sendOTPEmail($user['email'], $user['name'], $otp);

                $success = "New OTP has been sent to your email";
                logActivity($conn, $user['id'], 'OTP_RESENT', "OTP resent to email");
            }
        } else {
            $error = "Session expired. Please login again.";
        }
    }
}

function generateOTP()
{
    // Generate 6-digit OTP
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}


function completeLogin($conn, $user, $remember = false)
{
    // Update last login
    $conn->query("UPDATE admins SET last_login = NOW(), login_attempts = 0 WHERE id = {$user['id']}");

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['profile_image'] = $user['profile_image'];
    $_SESSION['login_time'] = time();

    // Set remember me cookie
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days

        // Store token in database
        $conn->query("UPDATE admins SET remember_token = '$token' WHERE id = {$user['id']}");

        // Set cookie
        setcookie('remember_token', $token, $expiry, '/');
    }

    // Log successful login
    logActivity($conn, $user['id'], 'LOGIN_SUCCESS', "User logged in successfully");
}

function logActivity($conn, $user_id, $action, $details = '')
{
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $email = '';

    // Get user email if user_id is provided
    if ($user_id) {
        $result = $conn->query("SELECT email FROM admins WHERE id = $user_id");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $email = $user['email'];
        }
    }

    $sql = "INSERT INTO login_logs (user_id, email, action, ip_address, user_agent, status, details) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $status = (strpos($action, 'SUCCESS') !== false || strpos($action, 'VERIFIED') !== false) ? 'success' : 'failed';
    $stmt->bind_param("issssss", $user_id, $email, $action, $ip_address, $user_agent, $status, $details);
    $stmt->execute();
}

function logFailedAttempt($conn, $identifier, $reason)
{
    $email = is_numeric($identifier) ? '' : $identifier;
    $user_id = is_numeric($identifier) ? $identifier : null;

    logActivity($conn, $user_id, 'LOGIN_FAILED', $reason);

    // Update user's failed attempt count
    if ($user_id) {
        $conn->query("UPDATE admins SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = $user_id");
    } elseif ($email) {
        $conn->query("UPDATE admins SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE email = '$email'");
    }
}

// Handle logout
if ($page == 'logout') {
    session_destroy();
    setcookie('remember_token', '', time() - 3600, '/');
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page == 'otp' ? 'Verify OTP' : 'Login'; ?> - CMS Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
            background: linear-gradient(145deg, #1565c0 0%, #0d2a6b 45%, #ff6b35fa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* main container */
        .login-container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            background: #ffffff;
            border-radius: 0px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        /* ---------- LEFT FORM ---------- */
        .login-form-section {
            flex: 1;
            padding: 60px 50px;
            background: #ffffff;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
        }

        .logo i {
            font-size: 34px;
            color: #1565c0;
            /* primary blue */
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #0a0a2a;
            /* deep dark */
            letter-spacing: -0.3px;
        }

        .login-header {
            margin-bottom: 40px;
        }

        .login-header h2 {
            font-size: 32px;
            color: #0a0a2a;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #3a3a6a;
            /* muted navy-gray */
            font-size: 15px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #0a0a2a;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #1565c0;
            /* blue icon */
            font-size: 16px;
            opacity: 0.8;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            border: 2px solid #c5d4f0;
            /* soft blue-gray */
            border-radius: 16px;
            font-size: 15px;
            transition: all 0.2s ease;
            outline: none;
            background: #f5f7fd;
            color: #0a0a2a;
        }

        .form-input:focus {
            border-color: #ff6b35;
            /* vibrant orange */
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.15);
        }

        .form-input:focus+.input-icon {
            color: #ff6b35;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1565c0;
            /* blue checkbox */
        }

        .checkbox-wrapper label {
            font-size: 14px;
            color: #1565c0;
            font-weight: 500;
            cursor: pointer;
        }

        .forgot-password {
            color: #1565c0;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #ff6b35;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: #1565c0;
            /* solid blue – primary */
            color: #ffffff;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(21, 101, 192, 0.3);
        }

        .login-btn:hover {
            background: #ff6b35;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(255, 107, 53, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
            background: #e85a24;
        }

        /* alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 50px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert i {
            font-size: 18px;
        }

        .alert-error {
            background: #ffe3d9;
            color: #b13e2d;
            border-left: 6px solid #b13e2d;
        }

        .alert-success {
            background: #e3eeff;
            color: #1565c0;
            border-left: 6px solid #1565c0;
        }

        .alert-info {
            background: #ffe8f0;
            color: #c2185b;
            border-left: 6px solid #e91e8c;
        }

        /* OTP styles */
        .otp-container {
            text-align: center;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin: 30px 0;
        }

        .otp-input {
            width: 54px;
            height: 64px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            border: 2px solid #c5d4f0;
            border-radius: 20px;
            outline: none;
            transition: 0.15s;
            background: #f5f7fd;
            color: #0a0a2a;
        }

        .otp-input:focus {
            border-color: #ff6b35;
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.25);
            background: #ffffff;
        }

        .otp-input.filled {
            border-color: #1565c0;
            background: rgba(21, 101, 192, 0.08);
        }

        .timer {
            color: #1565c0;
            font-size: 15px;
            font-weight: 500;
        }

        .timer.expired {
            color: #c05a3a;
        }

        .resend-link {
            color: #1565c0;
            cursor: pointer;
            font-weight: 600;
            text-decoration: underline 2px transparent;
            transition: all 0.2s;
        }

        .resend-link:hover {
            color: #ff6b35;
            text-decoration-color: #ff6b35;
        }

        .resend-link.disabled {
            color: #9bb3d8;
            cursor: default;
            text-decoration: none;
        }

        .back-to-login {
            margin-top: 25px;
        }

        .back-link {
            color: #1565c0;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 2px solid transparent;
        }

        .back-link:hover {
            color: #ff6b35;
            border-bottom-color: #ff6b35;
        }

        /* demo otp box */
        .demo-otp-box {
            background: #f0f4fd;
            border: 2px solid #1565c0;
            border-radius: 20px;
            padding: 20px;
            margin-top: 25px;
            text-align: center;
        }

        .demo-otp-box h4 {
            color: #1565c0;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 700;
        }

        .demo-otp {
            font-size: 28px;
            font-weight: 800;
            color: #ff6b35;
            letter-spacing: 6px;
            background: #0a0a2a;
            display: inline-block;
            padding: 8px 25px;
            border-radius: 60px;
            margin: 10px 0 6px;
        }

        .demo-note {
            font-size: 13px;
            color: #3a4a7a;
        }

        /* password toggle */
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #1565c0;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: #ff6b35;
        }

        /* loading spinner */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.4);
            border-top: 3px solid #ff6b35;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ---------- RIGHT INFO PANEL (deep navy + blue + orange + pink) ---------- */
        .info-section {
            flex: 1;
            background: #0a0a2a;
            /* deep navy background */
            padding: 60px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .info-section::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(21, 101, 192, 0.5) 0%, transparent 70%);
            border-radius: 50%;
            animation: softPulse 10s infinite alternate ease-in-out;
        }

        .info-section::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(233, 30, 140, 0.2) 0%, transparent 70%);
            /* pink glow */
            border-radius: 50%;
            animation: softPulse2 14s infinite alternate;
        }

        @keyframes softPulse {
            0% {
                transform: scale(1);
                opacity: 0.4;
            }

            100% {
                transform: scale(1.3);
                opacity: 0.7;
            }
        }

        @keyframes softPulse2 {
            0% {
                transform: scale(1) translate(0, 0);
                opacity: 0.2;
            }

            100% {
                transform: scale(1.5) translate(-20px, -20px);
                opacity: 0.5;
            }
        }

        .info-content {
            position: relative;
            z-index: 3;
        }

        .info-icon {
            font-size: 70px;
            margin-bottom: 30px;
            color: #ff6b35;
            /* orange icon */
            background: rgba(21, 101, 192, 0.25);
            width: 100px;
            height: 100px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-content h2 {
            font-size: 36px;
            margin-bottom: 20px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
        }

        .info-content h2 span {
            color: #ff6b35;
            /* orange accent */
            border-bottom: 4px solid #e91e8c;
            /* pink underline */
        }

        .info-content p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 35px;
            opacity: 0.85;
            color: rgba(255, 255, 255, 0.9);
        }

        .features-list {
            list-style: none;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 22px;
            font-size: 16px;
            font-weight: 500;
            color: white;
        }

        .features-list li i {
            font-size: 22px;
            background: #1565c0;
            /* blue badge */
            width: 48px;
            height: 48px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ff6b35;
            /* orange icon on blue badge */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        /* responsive */
        @media (max-width: 800px) {
            .login-container {
                flex-direction: column;
            }

            .info-section {
                display: none;
            }

            .login-form-section {
                padding: 45px 30px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Form Section -->
        <div class="login-form-section">
            <div class="logo">
                <i class="fas fa-layer-group"></i>
                <h1>Agriculture CMS Panel</h1>
            </div>

            <?php if ($page == 'otp'): ?>
                <!-- OTP Verification Page -->
                <div class="login-header">
                    <h2>Verify OTP</h2>
                    <p>Enter the 6-digit code sent to your email</p>
                </div>

                <!-- Alert Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Demo OTP Box (for testing) -->
                <?php if (isset($_SESSION['demo_otp'])): ?>
                    <div class="demo-otp-box">
                        <p class="demo-note">Sent to: <?php echo $_SESSION['demo_email']; ?></p>
                        <p class="demo-note">In production, this would be sent via email</p>
                    </div>
                <?php endif; ?>

                <div class="otp-container">
                    <form method="POST" action="">
                        <div class="form-group">
                            <div class="otp-inputs">
                                <?php for ($i = 0; $i < 6; $i++): ?>
                                    <input type="text"
                                        class="otp-input"
                                        maxlength="1"
                                        data-index="<?php echo $i; ?>"
                                        oninput="moveToNext(this, <?php echo $i; ?>)"
                                        onkeydown="handleBackspace(this, <?php echo $i; ?>, event)"
                                        autocomplete="off">
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="otp" id="otpValue">
                        </div>

                        <div class="timer" id="timer">
                            OTP expires in: <span id="timeLeft">05:00</span>
                        </div>
                        <br>
                        <div class="form-group">
                            <button type="submit" name="verify_otp" class="login-btn">
                                <i class="fas fa-check-circle"></i>
                                Verify OTP
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="">
                        <div class="form-group">
                            <button type="submit" name="resend_otp" class="login-btn" style="background: #6c757d;">
                                <i class="fas fa-redo"></i>
                                Resend OTP
                            </button>
                        </div>
                    </form>

                    <div class="back-to-login">
                        <a href="/" class="back-link">
                            <i class="fas fa-arrow-left"></i> Back to Login
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Login Page -->
                <div class="login-header">
                    <h2>Welcome Back!</h2>
                    <p>Please login to your account to continue</p>
                </div>

                <!-- Alert Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email"
                                name="email"
                                class="form-input"
                                placeholder="Enter your email"
                                required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password"
                                name="password"
                                id="password"
                                class="form-input"
                                placeholder="Enter your password"
                                required>
                            <i class="fas fa-lock input-icon"></i>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="login" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Dashboard
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Right Side - Info Panel -->
        <div class="info-section">
            <div class="info-content">
                <div class="info-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h2>Secure Login System</h2>
                <p>Our advanced security system protects your account with OTP verification and login monitoring.</p>

                <ul class="features-list">
                    <li>
                        <i class="fas fa-shield-alt"></i>
                        <span>Two-Factor Authentication</span>
                    </li>
                    <li>
                        <i class="fas fa-history"></i>
                        <span>Login Activity Tracking</span>
                    </li>
                    <li>
                        <i class="fas fa-lock"></i>
                        <span>Secure Session Management</span>
                    </li>
                    <li>
                        <i class="fas fa-bell"></i>
                        <span>Real-time Security Alerts</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        <?php if ($page == 'otp'): ?>
            // OTP Auto-focus and navigation
            function moveToNext(input, index) {
                const otpInputs = document.querySelectorAll('.otp-input');

                // Only allow numbers
                input.value = input.value.replace(/[^0-9]/g, '');

                // Move to next input if current is filled
                if (input.value.length === 1 && index < 5) {
                    otpInputs[index + 1].focus();
                }

                // Update hidden OTP value
                updateOTPValue();
            }

            function handleBackspace(input, index, event) {
                if (event.key === 'Backspace' && input.value === '' && index > 0) {
                    const otpInputs = document.querySelectorAll('.otp-input');
                    otpInputs[index - 1].focus();
                }

                // Update hidden OTP value
                updateOTPValue();
            }

            function updateOTPValue() {
                const otpInputs = document.querySelectorAll('.otp-input');
                let otp = '';

                otpInputs.forEach(input => {
                    otp += input.value;
                    if (input.value) {
                        input.classList.add('filled');
                    } else {
                        input.classList.remove('filled');
                    }
                });

                document.getElementById('otpValue').value = otp;
            }

            // Auto-focus first OTP input on load
            document.addEventListener('DOMContentLoaded', function() {
                const firstOtpInput = document.querySelector('.otp-input');
                if (firstOtpInput) {
                    firstOtpInput.focus();
                }

                // Start OTP timer
                startTimer();
            });

            // OTP Timer
            function startTimer() {
                let timeLeft = 5 * 60; // 5 minutes in seconds
                const timerElement = document.getElementById('timer');
                const timeLeftElement = document.getElementById('timeLeft');
                const resendLink = document.querySelector('.resend-link');

                const timer = setInterval(function() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;

                    timeLeftElement.textContent =
                        minutes.toString().padStart(2, '0') + ':' +
                        seconds.toString().padStart(2, '0');

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        timerElement.classList.add('expired');
                        timeLeftElement.textContent = 'Expired';
                        if (resendLink) {
                            resendLink.classList.remove('disabled');
                        }
                    }

                    timeLeft--;
                }, 1000);
            }
        <?php endif; ?>

        // Form submission loading state
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('.login-btn');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.innerHTML = '<div class="spinner"></div> Processing...';
                }
            });
        });

        // Auto-focus email input on login page load
        <?php if ($page != 'otp'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const emailInput = document.querySelector('input[name="email"]');
                if (emailInput && !emailInput.value) {
                    emailInput.focus();
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>