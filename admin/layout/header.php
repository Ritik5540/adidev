<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /adidev/admin");
    exit;
}
include("sessionout.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/dashboard.css">
    <link rel="stylesheet" href="assets/form.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        </div>
        <div class="header-right">

            <!-- Profile Dropdown -->
            <div class="profile-dropdown">
                <div class="profile-trigger">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-info">
                        <div class="profile-name"><?= $_SESSION['user_name'] ?? 'Admin User' ?></div>
                        <div class="profile-role">Administrator</div>
                    </div>
                    <i class="fas fa-chevron-down profile-arrow"></i>
                </div>

                <div class="dropdown-menu">
                    <div class="dropdown-header">
                        <strong><?= $_SESSION['user_name'] ?? 'Admin User' ?></strong>
                        <p><?= $_SESSION['user_email'] ?? 'admin@cmsPanel.com' ?></p>
                    </div>
                    <a href="profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Toggle Button -->
    <button class="menu-toggle" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Add mobile responsive JS -->
    <script src="assets/mobile-responsive.js"></script>