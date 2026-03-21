<?php
// Auto logout after 30 minutes of inactivity
$timeout = 30 * 60; // 30 minutes in seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    // Last request was more than 30 minutes ago
    session_unset();     // Unset $_SESSION variable
    session_destroy();   // Destroy session data
    
    // Redirect to logout with timeout message
    header('Location: logout.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();
?>