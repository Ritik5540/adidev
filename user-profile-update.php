<?php
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/catalog_functions.php';

    // Check if user is logged in
    if (!is_logged_in()) {
        redirect('sign_in.php');
    }

    $user_id = current_user_id() ?? 0;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $currency = $_POST['currency'] ?? 'USD';

        // Basic validation
        if (empty($name) || empty($email)) {
            $_SESSION['profile_update_error'] = 'Name and email are required.';
            redirect('my-profile.php');
        }

        // Update user details
        $update_query = "UPDATE users SET display_name = ?, email = ?, currency = ? WHERE id = ?";
        $stmt = db_execute($update_query, 'sssi', [$name, $email, $currency, $user_id]);

        if ($stmt->affected_rows >= 0) {
            $_SESSION['profile_update_success'] = 'Profile updated successfully.';
        } else {
            $_SESSION['profile_update_error'] = 'Failed to update profile. Please try again.';
        }

        redirect('my-profile.php');
    } else {
        redirect('my-profile.php');
    }
?>