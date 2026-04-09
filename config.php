<?php
// Core configuration and database bootstrap for Adidev E‑Commerce

// --- Database connection settings (adjust for your local setup) ---
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $DB_HOST = '127.0.0.1';
    $DB_USER = 'root';
    $DB_PASS = '';
    $DB_NAME = '20260307_adidev'; // Change here if your database name differs
} else {
    $DB_HOST = 'localhost';
    $DB_USER = 'u409719797_ecomadidev';
    $DB_PASS = 'w=hA8A+2';
    $DB_NAME = 'u409719797_ecomadidev'; // Change here if your database name differs
}

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_errno) {
    // In production you may want to log this instead of displaying it
    die('Database connection failed: ' . htmlspecialchars($mysqli->connect_error));
}

$mysqli->set_charset('utf8mb4');

// --- Session handling & security ---

// Use a custom session name for this application
session_name('adidev_session');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto logout after 1 hour of inactivity
define('ADIDEV_SESSION_TIMEOUT', 60 * 60); // 1 hour

// Checkout/payment session lifetime (5 minutes)
define('ADIDEV_CHECKOUT_TIMEOUT', 5 * 60);
define('ADIDEV_ENCRYPTION_KEY', '*zou3b_D]RhEOz{jDP(N6B<KKz+I9!iK'); // change this

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > ADIDEV_SESSION_TIMEOUT) {
    // Session expired due to inactivity
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    session_start();
}

$_SESSION['last_activity'] = time();

// --- Simple helper to safely prepare & execute queries using MySQLi prepared statements ---

/**
 * Prepare and execute a MySQLi prepared statement.
 *
 * @param string $sql   SQL with ? placeholders
 * @param string $types MySQLi bind_param types string (e.g. 'si')
 * @param array  $params Parameters to bind by reference
 *
 * @return mysqli_stmt
 */
function db_execute(string $sql, string $types = '', array $params = []): mysqli_stmt
{
    global $mysqli;

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die('Failed to prepare statement: ' . htmlspecialchars($mysqli->error));
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        die('Failed to execute statement: ' . htmlspecialchars($stmt->error));
    }

    return $stmt;
}

/**
 * Get currently logged in user ID or null.
 *
 * @return int|null
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

/**
 * Check if a user is logged in.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return current_user_id() !== null;
}

/**
 * Redirect helper.
 *
 * @param string $url
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Require the user to be logged in to access a page.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $redirectTo = $_SERVER['REQUEST_URI'] ?? 'dashboard.php';
        redirect('sign_in.php?redirect=' . urlencode($redirectTo));
    }
}

/**
 * Start or refresh the short-lived checkout session window.
 */
function start_checkout_session(): void
{
    $_SESSION['checkout_started_at'] = time();
    $_SESSION['checkout_token']      = bin2hex(random_bytes(16));

    setcookie(
        'adidev_checkout',
        $_SESSION['checkout_token'],
        [
            'expires'  => time() + ADIDEV_CHECKOUT_TIMEOUT,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Ensure the current checkout/payment request is within the 5 minute window.
 * If not valid, redirect back to cart.
 */
function ensure_checkout_session_valid(): void
{
    $startedAt = $_SESSION['checkout_started_at'] ?? null;
    $token     = $_SESSION['checkout_token'] ?? null;
    $cookie    = $_COOKIE['adidev_checkout'] ?? null;

    $expired = false;

    if (!$startedAt || !$token || !$cookie) {
        $expired = true;
    } elseif (!hash_equals($token, $cookie)) {
        $expired = true;
    } elseif ((time() - (int) $startedAt) > ADIDEV_CHECKOUT_TIMEOUT) {
        $expired = true;
    }

    if ($expired) {
        unset($_SESSION['checkout_started_at'], $_SESSION['checkout_token']);
        setcookie('adidev_checkout', '', time() - 3600, '/');
        redirect('cart.php?checkout_timeout=1');
    }
}
