<?php
// Authentication related helper functions for Adidev

require_once __DIR__ . '/config.php';

/**
 * Register a new user.
 *
 * @param array $data   Form data
 * @param array $errors Validation and processing errors (by reference)
 *
 * @return bool True on success, false on failure
 */
function register_user(array $data, array &$errors): bool
{
    global $mysqli;

    $first_name = trim($data['first_name'] ?? '');
    $last_name  = trim($data['last_name'] ?? '');
    $email      = trim($data['email'] ?? '');
    $phone      = trim($data['phone'] ?? '');
    $password   = $data['password'] ?? '';
    $confirm    = $data['confirm_password'] ?? '';
    $currency    = $data['currency'] ?? 'INR';

    if ($first_name === '') {
        $errors[] = 'First name is required.';
    }
    if ($last_name === '') {
        $errors[] = 'Last name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    if (!empty($errors)) {
        return false;
    }

    // Check if email already exists
    $stmt = db_execute('SELECT id FROM users WHERE email = ?', 's', [$email]);
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = 'An account with this email already exists.';
        $stmt->close();
        return false;
    }

    $stmt->close();

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $display_name  = trim($first_name . ' ' . $last_name);
    $ip            = $_SERVER['REMOTE_ADDR'] ?? null;

    $insertSql = 'INSERT INTO users (user_type, first_name, last_name, display_name, email, phone, password_hash, currency, registered_from_ip)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

    $insertParams = [
        'customer',
        $first_name,
        $last_name,
        $display_name,
        $email,
        $phone,
        $password_hash,
        $currency,
        $ip,
    ];

    $insertStmt = db_execute($insertSql, 'sssssssss', $insertParams);

    if ($insertStmt->affected_rows <= 0) {
        $errors[] = 'Failed to create account. Please try again.';
        $insertStmt->close();
        return false;
    }

    $newUserId = $insertStmt->insert_id;
    $insertStmt->close();

    // Log the user in immediately after successful registration
    $_SESSION['user_id']   = (int) $newUserId;
    $_SESSION['user_name'] = $display_name;
    $_SESSION['user_email'] = $email;
    $_SESSION['currency'] = 'INR'; // Default currency, can be made dynamic later

    return true;
}

/**
 * Attempt to log a user in with the given credentials.
 *
 * @param string $email
 * @param string $password
 * @param array  $errors
 *
 * @return bool
 */
function login_user(string $email, string $password, array &$errors): bool
{
    $email = trim($email);

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
        return false;
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
        return false;
    }

    $stmt = db_execute(
        'SELECT id, first_name, last_name, email, password_hash, is_active, is_blocked , currency
         FROM users
         WHERE email = ?',
        's',
        [$email]
    );

    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $errors[] = 'Invalid email or password.';
        return false;
    }

    if ((int) $user['is_active'] === 0 || (int) $user['is_blocked'] === 1) {
        $errors[] = 'Your account is inactive or blocked. Please contact support.';
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        $errors[] = 'Invalid email or password.';
        return false;
    }

    $display_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $ip           = $_SERVER['REMOTE_ADDR'] ?? null;

    $_SESSION['user_id']    = (int) $user['id'];
    $_SESSION['user_name']  = $display_name;
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['currency']   = $user['currency']; // Default currency, can be made dynamic later

    // Update last login info
    db_execute(
        'UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?',
        'si',
        [$ip, (int) $user['id']]
    )->close();

    return true;
}

/**
 * Log the current user out.
 */
function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

