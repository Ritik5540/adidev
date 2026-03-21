<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

$user_id = $_SESSION['user_id'];
$user_data = null;

// Fetch current user data
$result = $conn->query("SELECT * FROM admins WHERE id = $user_id");
if ($result && $result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
} else {
    header("Location: logout.php");
    exit;
}

/* UPDATE PROFILE LOGIC */
if (isset($_POST['updateProfile'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $mobile = $conn->real_escape_string(trim($_POST['mobile'] ?? NULL));

    // Check if email already exists (if changed)
    if ($user_data['email'] != $email) {
        $check_sql = "SELECT id FROM admins WHERE email = '$email' AND id != $user_id";
        $check_result = $conn->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Email already exists");
            exit;
        }
    }

    /* PROFILE IMAGE UPLOAD */
    $profile_image = $user_data['profile_image'] ?? '';

    if (!empty($_FILES['profile_image']['name'])) {
        $targetDir = "uploads/profiles/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
        $targetFile = $targetDir . $imageName;

        // Basic validation
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($imageFileType, $allowedTypes)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Only JPG, JPEG, PNG & GIF files are allowed");
            exit;
        }

        if ($_FILES['profile_image']['size'] > $maxFileSize) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=File size must be less than 2MB");
            exit;
        }

        // Delete old image if exists
        if (!empty($user_data['profile_image']) && file_exists($targetDir . $user_data['profile_image'])) {
            unlink($targetDir . $user_data['profile_image']);
        }

        // Resize image for optimization
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $profile_image = $imageName;

            // Create thumbnail
            createThumbnail($targetFile, $targetDir . 'thumb_' . $imageName, 150, 150);
        }
    }

    /* UPDATE USER PROFILE */
    $sql = "UPDATE admins SET 
            name = '$name',
            email = '$email',
            mobile = '$mobile',
            profile_image = '$profile_image',
            updated_at = NOW()
            WHERE id = $user_id";

    if ($conn->query($sql)) {
        // Update session data
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['profile_image'] = $profile_image;

        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Profile updated successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to update profile");
    }
    exit;
}

/* UPDATE PASSWORD LOGIC */
if (isset($_POST['updatePassword'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate current password
    if (!password_verify($current_password, $user_data['password'])) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Current password is incorrect");
        exit;
    }

    // Validate new password
    if (empty($new_password)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=New password is required");
        exit;
    }

    if (strlen($new_password) < 8) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Password must be at least 8 characters");
        exit;
    }

    if ($new_password !== $confirm_password) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=New passwords do not match");
        exit;
    }

    // Check if new password is same as current
    if (password_verify($new_password, $user_data['password'])) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=New password cannot be same as current password");
        exit;
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    if ($conn->query("UPDATE admins SET password = '$hashed_password', updated_at = NOW() WHERE id = $user_id")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Password updated successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to update password");
    }
    exit;
}

/* REMOVE PROFILE IMAGE */
if (isset($_GET['remove_image'])) {
    $targetDir = "uploads/profiles/";

    if (!empty($user_data['profile_image']) && file_exists($targetDir . $user_data['profile_image'])) {
        unlink($targetDir . $user_data['profile_image']);

        // Also remove thumbnail if exists
        $thumbFile = $targetDir . 'thumb_' . $user_data['profile_image'];
        if (file_exists($thumbFile)) {
            unlink($thumbFile);
        }
    }

    if ($conn->query("UPDATE admins SET profile_image = NULL, updated_at = NOW() WHERE id = $user_id")) {
        $_SESSION['profile_image'] = NULL;
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Profile image removed successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to remove profile image");
    }
    exit;
}

// Function to create thumbnail
function createThumbnail($source, $destination, $width, $height)
{
    $info = getimagesize($source);
    $source_image = null;

    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    $thumbnail = imagecreatetruecolor($width, $height);

    // Preserve transparency for PNG and GIF
    if ($info[2] == IMAGETYPE_PNG || $info[2] == IMAGETYPE_GIF) {
        imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
    }

    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumbnail, $destination, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumbnail, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumbnail, $destination);
            break;
    }

    imagedestroy($source_image);
    imagedestroy($thumbnail);

    return true;
}
?>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>
<style>
    .profile-container {
        margin: 0 auto;
        padding: 20px;
    }

        .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e6ed;
        border-radius: 0px;
        font-size: 15px;
        transition: all 0.3s;
        background: #f8fafc;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #eaeaea;
    }

    .profile-title {
        font-size: 24px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 30px;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }

    .profile-sidebar {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .profile-main {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .profile-image-section {
        text-align: center;
        margin-bottom: 30px;
    }

    .profile-image-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #667eea;
    }

    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 48px;
    }

    .profile-image-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }

    .profile-stats {
        margin-top: 25px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
    }

    .stat-value {
        font-weight: 600;
        color: #333;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
    }

    .badge-admin {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-editor {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
        color: white;
    }

    .badge-author {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }

    .badge-active {
        background: linear-gradient(135deg, #4cd964 0%, #5ac8fa 100%);
        color: white;
    }

    .badge-inactive {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
    }

    .tab-navigation {
        display: flex;
        border-bottom: 2px solid #eaeaea;
        margin-bottom: 25px;
    }

    .tab-button {
        padding: 12px 24px;
        background: none;
        border: none;
        font-size: 15px;
        font-weight: 500;
        color: #666;
        cursor: pointer;
        position: relative;
        transition: all 0.3s;
    }

    .tab-button:hover {
        color: #667eea;
    }

    .tab-button.active {
        color: #667eea;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #667eea;
    }

    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease;
    }

    .tab-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .password-strength {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
        background: #f8f9fa;
    }

    .strength-meter {
        height: 5px;
        background: #ddd;
        border-radius: 3px;
        margin: 5px 0;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        transition: width 0.3s;
    }

    .strength-text {
        font-size: 12px;
        color: #666;
    }

    .password-requirements {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 4px;
        font-size: 13px;
    }

    .requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
    }

    .requirement i {
        font-size: 12px;
    }

    .requirement.met {
        color: #28a745;
    }

    .requirement.unmet {
        color: #dc3545;
    }

    .activity-log {
        margin-top: 25px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #667eea;
    }

    .activity-details {
        flex: 1;
    }

    .activity-text {
        font-size: 14px;
        color: #333;
        margin-bottom: 2px;
    }

    .activity-time {
        font-size: 12px;
        color: #666;
    }

    .two-factor-section {
        margin-top: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .image-preview-container {
        text-align: center;
        margin-top: 15px;
    }

    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 5px;
        background: #f8f9fa;
    }

    .delete-account {
        margin-top: 40px;
        padding: 20px;
        border: 1px solid #ff6b6b;
        border-radius: 8px;
        background: #fff5f5;
    }
</style>
<!-- Main Content -->
<main class="main-content">
    <div class="content-section">
        <!-- Alert Messages -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status']; ?>">
                <i class="fas fa-<?php echo $_GET['status'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert) alert.style.display = 'none';
                        const url = new URL(window.location);
                        url.searchParams.delete('status');
                        url.searchParams.delete('message');
                        window.history.replaceState({}, '', url);
                    }, 300);
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Profile Header -->
        <div class="profile-header">
            <h1 class="profile-title">
                <i class="fas fa-user-circle"></i>
                My Profile
            </h1>
            <div class="profile-info">
                <span class="stat-value"><?php echo htmlspecialchars($user_data['name']); ?></span>
                <span class="badge <?php echo 'badge-' . $user_data['role']; ?>">
                    <?php echo ucfirst($user_data['role']); ?>
                </span>
            </div>
        </div>

        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <!-- Profile Image -->
                <div class="profile-image-section">
                    <div class="profile-image-container">
                        <?php if (!empty($user_data['profile_image'])): ?>
                            <img src="uploads/profiles/<?php echo htmlspecialchars($user_data['profile_image']); ?>"
                                alt="Profile Image" class="profile-image">
                        <?php else: ?>
                            <div class="profile-image-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-image-actions">
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('profileImageInput').click()">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                        <?php if (!empty($user_data['profile_image'])): ?>
                            <a href="?remove_image=1" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to remove your profile image?')">
                                <i class="fas fa-trash"></i> Remove Photo
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- User Stats -->
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-label">Member Since</span>
                        <span class="stat-value"><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Account Status</span>
                        <span class="badge <?php echo $user_data['status'] == 1 ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $user_data['status'] == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">User ID</span>
                        <span class="stat-value">#<?php echo $user_data['id']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Last Updated</span>
                        <span class="stat-value"><?php echo isset($user_data['updated_at']) ? date('M d, Y', strtotime($user_data['updated_at'])) : 'Never'; ?></span>
                    </div>
                </div>

                <!-- Quick Links -->
                <div style="margin-top: 30px;">
                    <h4 style="margin-bottom: 15px; color: #333; font-size: 16px;">
                        <i class="fas fa-link"></i> Quick Links
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="dashboard.php" class="btn btn-outline">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="./blog-list.php" class="btn btn-outline">
                            <i class="fas fa-newspaper"></i> My Posts
                        </a>
                        <a href="logout.php" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to logout?')">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <button class="tab-button active" data-tab="profile">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </button>
                    <button class="tab-button" data-tab="password">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button class="tab-button" data-tab="security">
                        <i class="fas fa-shield-alt"></i> Security
                    </button>
                </div>

                <!-- Profile Tab -->
                <div id="profileTab" class="tab-content active">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="updateProfile" value="1">

                        <!-- Hidden file input for profile image -->
                        <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display: none;" onchange="previewProfileImage(this)">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Full Name</label>
                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your full name"
                                    value="<?php echo htmlspecialchars($user_data['name']); ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label class="form-label required">Email Address</label>
                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email"
                                    value="<?php echo htmlspecialchars($user_data['email']); ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel"
                                    name="mobile"
                                    class="form-control"
                                    placeholder="Enter mobile number"
                                    pattern="[0-9]{10}"
                                    maxlength="10"
                                    value="<?php echo htmlspecialchars($user_data['mobile'] ?? ''); ?>">
                                <div class="form-help">Enter 10-digit mobile number</div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">User Role</label>
                                <input type="text"
                                    class="form-control"
                                    value="<?php echo ucfirst($user_data['role']); ?>"
                                    readonly>
                                <div class="form-help">Role cannot be changed from profile</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Profile Image Preview</label>
                            <div class="image-preview-container">
                                <?php if (!empty($user_data['profile_image'])): ?>
                                    <img id="currentImagePreview"
                                        src="uploads/profiles/<?php echo htmlspecialchars($user_data['profile_image']); ?>"
                                        alt="Current profile image" class="image-preview">
                                <?php else: ?>
                                    <div id="imagePlaceholder" class="image-preview" style="width: 200px; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; color: #6c757d;">
                                        <i class="fas fa-user fa-3x"></i>
                                        <p>No image selected</p>
                                    </div>
                                <?php endif; ?>
                                <img id="newImagePreview" class="image-preview" style="display: none;">
                            </div>
                            <div class="form-help">Max file size: 2MB. Supported formats: JPG, PNG, GIF</div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Tab -->
                <div id="passwordTab" class="tab-content">
                    <form method="POST" action="" id="passwordForm">
                        <input type="hidden" name="updatePassword" value="1">

                        <div class="form-group">
                            <label class="form-label required">Current Password</label>
                            <input type="password"
                                name="current_password"
                                id="currentPassword"
                                class="form-control"
                                placeholder="Enter current password"
                                required>
                            <div class="form-help">
                                <a href="#" class="toggle-password" data-target="currentPassword">
                                    <i class="fas fa-eye"></i> Show Password
                                </a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">New Password</label>
                            <input type="password"
                                name="new_password"
                                id="newPassword"
                                class="form-control"
                                placeholder="Enter new password"
                                required
                                oninput="checkPasswordStrength(this.value)">
                            <div class="form-help">
                                <a href="#" class="toggle-password" data-target="newPassword">
                                    <i class="fas fa-eye"></i> Show Password
                                </a>
                            </div>

                            <!-- Password Strength Meter -->
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Password strength: None</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Confirm New Password</label>
                            <input type="password"
                                name="confirm_password"
                                id="confirmPassword"
                                class="form-control"
                                placeholder="Confirm new password"
                                required>
                            <div class="form-help">
                                <a href="#" class="toggle-password" data-target="confirmPassword">
                                    <i class="fas fa-eye"></i> Show Password
                                </a>
                            </div>
                        </div>

                        <!-- Password Requirements -->
                        <div class="password-requirements">
                            <h5 style="margin-bottom: 10px; font-size: 14px;">
                                <i class="fas fa-list-check"></i> Password Requirements:
                            </h5>
                            <div class="requirement" id="reqLength">
                                <i class="fas fa-circle"></i>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="requirement" id="reqUppercase">
                                <i class="fas fa-circle"></i>
                                <span>At least one uppercase letter</span>
                            </div>
                            <div class="requirement" id="reqLowercase">
                                <i class="fas fa-circle"></i>
                                <span>At least one lowercase letter</span>
                            </div>
                            <div class="requirement" id="reqNumber">
                                <i class="fas fa-circle"></i>
                                <span>At least one number</span>
                            </div>
                            <div class="requirement" id="reqSpecial">
                                <i class="fas fa-circle"></i>
                                <span>At least one special character</span>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div id="securityTab" class="tab-content">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Manage your account security settings here.</span>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="two-factor-section">
                        <h4 style="margin-bottom: 15px; color: #333;">
                            <i class="fas fa-mobile-alt"></i> Two-Factor Authentication
                        </h4>
                        <p style="margin-bottom: 15px; color: #666;">
                            Add an extra layer of security to your account by enabling two-factor authentication.
                        </p>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" id="2fa_enabled" name="two_factor" disabled>
                                    <label for="2fa_enabled">
                                        <span class="badge badge-success">Enabled</span>
                                    </label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" id="2fa_disabled" name="two_factor" checked disabled>
                                    <label for="2fa_disabled">
                                        <span class="badge badge-secondary">Disabled</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-help">Two-factor authentication is currently not available.</div>
                        </div>
                    </div>

                    <!-- Session Management -->
                    <div style="margin-top: 30px;">
                        <h4 style="margin-bottom: 15px; color: #333;">
                            <i class="fas fa-laptop"></i> Active Sessions
                        </h4>
                        <div class="activity-log">
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-text">
                                        <strong>Current Session</strong> - <?php echo $_SERVER['HTTP_USER_AGENT']; ?>
                                    </div>
                                    <div class="activity-time">
                                        Started: <?php echo date('M d, Y H:i:s'); ?>
                                    </div>
                                </div>
                                <button class="btn btn-danger btn-sm" disabled>
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Account -->
                    <div class="delete-account">
                        <h4 style="margin-bottom: 15px; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle"></i> Danger Zone
                        </h4>
                        <p style="margin-bottom: 15px; color: #666;">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()" disabled>
                            <i class="fas fa-trash"></i> Delete My Account
                        </button>
                        <div class="form-help" style="margin-top: 10px;">
                            <i class="fas fa-info-circle"></i>
                            Account deletion is disabled. Please contact administrator.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Tab functionality
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and contents
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Show corresponding content
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + 'Tab').classList.add('active');
        });
    });

    // Profile image preview
    function previewProfileImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Hide current image and placeholder
                const currentPreview = document.getElementById('currentImagePreview');
                const placeholder = document.getElementById('imagePlaceholder');
                const newPreview = document.getElementById('newImagePreview');

                if (currentPreview) currentPreview.style.display = 'none';
                if (placeholder) placeholder.style.display = 'none';

                newPreview.src = e.target.result;
                newPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    }

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Password';
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.innerHTML = '<i class="fas fa-eye"></i> Show Password';
            }
        });
    });

    // Password strength checker
    function checkPasswordStrength(password) {
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');

        // Requirements
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        // Update requirement indicators
        updateRequirement('reqLength', hasLength);
        updateRequirement('reqUppercase', hasUppercase);
        updateRequirement('reqLowercase', hasLowercase);
        updateRequirement('reqNumber', hasNumber);
        updateRequirement('reqSpecial', hasSpecial);

        // Calculate strength score
        let score = 0;
        if (hasLength) score++;
        if (hasUppercase) score++;
        if (hasLowercase) score++;
        if (hasNumber) score++;
        if (hasSpecial) score++;

        // Update strength meter
        const percentage = (score / 5) * 100;
        strengthFill.style.width = percentage + '%';

        // Update strength text and color
        let strength = 'None';
        let color = '#dc3545';

        if (score === 0) {
            strength = 'None';
            color = '#dc3545';
        } else if (score <= 2) {
            strength = 'Weak';
            color = '#dc3545';
        } else if (score <= 3) {
            strength = 'Medium';
            color = '#ffc107';
        } else if (score <= 4) {
            strength = 'Strong';
            color = '#28a745';
        } else {
            strength = 'Very Strong';
            color = '#28a745';
        }

        strengthFill.style.background = color;
        strengthText.textContent = 'Password strength: ' + strength;
        strengthText.style.color = color;
    }

    function updateRequirement(elementId, met) {
        const element = document.getElementById(elementId);
        const icon = element.querySelector('i');

        if (met) {
            element.classList.remove('unmet');
            element.classList.add('met');
            icon.className = 'fas fa-check-circle';
            icon.style.color = '#28a745';
        } else {
            element.classList.remove('met');
            element.classList.add('unmet');
            icon.className = 'fas fa-times-circle';
            icon.style.color = '#dc3545';
        }
    }

    // Password form validation
    document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        if (newPassword.length < 8) {
            e.preventDefault();
            Swal.fire({
                title: 'Password Too Short',
                text: 'Password must be at least 8 characters long.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                title: 'Password Mismatch',
                text: 'New passwords do not match.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }

        return true;
    });

    // Confirm account deletion
    function confirmDeleteAccount() {
        Swal.fire({
            title: 'Delete Account?',
            html: `Are you sure you want to delete your account?<br><br>
                      <small class="text-danger">This action is permanent and cannot be undone!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete my account',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to delete account page
                window.location.href = 'delete-account.php';
            }
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check password strength on page load
        const newPasswordInput = document.getElementById('newPassword');
        if (newPasswordInput) {
            checkPasswordStrength(newPasswordInput.value);
        }

        // Mobile number validation
        const mobileInput = document.querySelector('input[name="mobile"]');
        if (mobileInput) {
            mobileInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Auto-focus first input in active tab
        const activeTab = document.querySelector('.tab-content.active');
        if (activeTab) {
            const firstInput = activeTab.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }
    });

    // Image file size validation
    document.getElementById('profileImageInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (file && file.size > maxSize) {
            Swal.fire({
                title: 'File Too Large',
                text: 'File size must be less than 2MB.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            this.value = '';
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (file && !allowedTypes.includes(file.type)) {
            Swal.fire({
                title: 'Invalid File Type',
                text: 'Only JPG, PNG, and GIF files are allowed.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            this.value = '';
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save form in active tab
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const activeTab = document.querySelector('.tab-content.active');
            if (activeTab) {
                const submitButton = activeTab.querySelector('button[type="submit"]');
                if (submitButton) submitButton.click();
            }
        }

        // Escape to cancel
        if (e.key === 'Escape') {
            const cancelButton = document.querySelector('.btn-secondary');
            if (cancelButton) {
                cancelButton.click();
            }
        }
    });
</script>

<?php include 'layout/footer.php'; ?>
</body>

</html>