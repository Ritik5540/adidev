<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Initialize variables
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit_mode = ($user_id > 0);
$user_data = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Fetch user data if in edit mode
if ($is_edit_mode && $action == 'edit') {
    $result = $conn->query("SELECT * FROM admins WHERE id = $user_id");
    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        // Don't show password
        unset($user_data['password']);
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=User not found");
        exit;
    }
}

/* USER INSERT/UPDATE LOGIC */
if (isset($_POST['submitUser'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $mobile = $conn->real_escape_string(trim($_POST['mobile'] ?? NULL));
    $role = $conn->real_escape_string(trim($_POST['role'] ?? 'author'));
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    // Check if email already exists (for new users or if email changed)
    if (!$is_edit_mode || ($is_edit_mode && $user_data['email'] != $email)) {
        $check_sql = "SELECT id FROM admins WHERE email = '$email'";
        if ($is_edit_mode) {
            $check_sql .= " AND id != $user_id";
        }
        $check_result = $conn->query($check_sql);

        if ($check_result && $check_result->num_rows > 0) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=" . ($is_edit_mode ? "edit&id=$user_id" : "add") . "&status=error&message=Email already exists");
            exit;
        }
    }

    if ($is_edit_mode && $action == 'edit') {
        /* UPDATE USER */
        $sql = "UPDATE admins SET 
                name = '$name',
                email = '$email',
                mobile = '$mobile',
                role = '$role',
                status = $status
                WHERE id = $user_id";

        if ($conn->query($sql)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=User updated successfully");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=$user_id&status=error&message=Failed to update user");
        }
    } else {
        /* INSERT USER - Password required for new users */
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=add&status=error&message=Password is required");
            exit;
        }

        if ($password !== $confirm_password) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=add&status=error&message=Passwords do not match");
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, mobile, password, role, status, created_at) 
                VALUES ('$name', '$email', '$mobile', '$hashed_password', '$role', $status, NOW())";

        if ($conn->query($sql)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=User created successfully");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=add&status=error&message=Failed to create user");
        }
    }
    exit;
}

/* DELETE USER LOGIC */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];

    // Prevent deleting own account
    $current_user_id = $_SESSION['user_id'] ?? 0;

    if ($delete_id == $current_user_id) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Cannot delete your own account");
        exit;
    }

    if ($conn->query("DELETE FROM admins WHERE id = $delete_id")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=User deleted successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to delete user");
    }
    exit;
}

/* BULK DELETE USERS */
if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'delete' && isset($_POST['selected_users'])) {
    $selected_ids = array_map('intval', $_POST['selected_users']);
    $ids_string = implode(',', $selected_ids);

    $current_user_id = $_SESSION['user_id'] ?? 0;

    if (in_array($current_user_id, $selected_ids)) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Cannot delete your own account");
        exit;
    }

    if ($conn->query("DELETE FROM admins WHERE id IN ($ids_string)")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=" . count($selected_ids) . " users deleted successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to delete users");
    }
    exit;
}

/* BULK STATUS UPDATE */
if (isset($_POST['bulk_action']) && in_array($_POST['bulk_action'], ['activate', 'deactivate']) && isset($_POST['selected_users'])) {
    $selected_ids = array_map('intval', $_POST['selected_users']);
    $ids_string = implode(',', $selected_ids);
    $new_status = $_POST['bulk_action'] == 'activate' ? 1 : 0;

    if ($conn->query("UPDATE admins SET status = $new_status WHERE id IN ($ids_string)")) {
        $action_text = $_POST['bulk_action'] == 'activate' ? 'activated' : 'deactivated';
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=" . count($selected_ids) . " users $action_text successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to UPDATE admins");
    }
    exit;
}

/* TOGGLE STATUS */
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $toggle_id = (int)$_GET['toggle_status'];

    // Get current status
    $result = $conn->query("SELECT status FROM admins WHERE id = $toggle_id");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_status = $row['status'] == 1 ? 0 : 1;

        if ($conn->query("UPDATE admins SET status = $new_status WHERE id = $toggle_id")) {
            $status_text = $new_status == 1 ? 'activated' : 'deactivated';
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=User $status_text successfully");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to update status");
        }
    }
    exit;
}
?>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #f5f7fa;
        color: #333;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }


    .card {
        background: white;
        
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f2f5;
    }

    .card-title {
        font-size: 22px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn {
        padding: 10px 22px;
        border-radius: 0px;
        border: none;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(102, 126, 234, 0.2);
    }

    .btn-success {
        background: linear-gradient(135deg, #4cd964 0%, #5ac8fa 100%);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(76, 217, 100, 0.2);
    }

    .btn-outline {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
    }

    .btn-outline:hover {
        background: #667eea;
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: white;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(255, 107, 107, 0.2);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
    }

    .btn-sm {
        padding: 6px 14px;
        font-size: 13px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
        font-size: 15px;
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

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }

    .form-help {
        margin-top: 6px;
        font-size: 13px;
        color: #718096;
    }

    .required::after {
        content: " *";
        color: #ff6b6b;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 0px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #b1dfbb;
    }

    .alert-error {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 1px solid #f1b0b7;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
        border: 1px solid #abdde5;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 0px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 0px;
        overflow: hidden;
    }

    .table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        border-bottom: 1px solid #f0f2f5;
        transition: all 0.2s;
    }

    .table tbody tr:hover {
        background-color: #f8fafd;
    }

    .table td {
        padding: 10px 20px;
        vertical-align: middle;
    }

    .checkbox-cell {
        width: 50px;
        text-align: center;
    }
    
    .badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #e2e3e5 0%, #d6d8db 100%);
        color: #383d41;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 60px;
        color: #cbd5e0;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        color: #4a5568;
        margin-bottom: 10px;
        font-size: 20px;
    }

    .empty-state p {
        color: #718096;
        max-width: 400px;
        margin: 0 auto 25px;
    }

    .bulk-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 0px;
        border: 1px solid #e2e8f0;
    }

    .bulk-actions select {
        padding: 8px 16px;
        border: 2px solid #e0e6ed;
        border-radius: 0px;
        background: white;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 30px;
    }

    .page-item {
        display: inline-flex;
    }

    .page-link {
        padding: 8px 16px;
        border: 1px solid #dee2e6;
        background: white;
        color: #667eea;
        text-decoration: none;
        border-radius: 0px;
        transition: all 0.2s;
    }

    .page-link:hover {
        background: #f8f9fa;
        border-color: #667eea;
    }

    .page-link.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #667eea;
        text-decoration: none;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .back-link:hover {
        color: #764ba2;
    }

    .slug-preview {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        padding: 10px 15px;
        border-radius: 0px;
        border: 1px solid #e2e8f0;
        margin-top: 5px;
        font-size: 14px;
    }

    .slug-preview span {
        color: #718096;
    }

    .slug-preview strong {
        color: #2d3748;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 0px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
    }

    .stat-icon.total {
        background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
        color: #667eea;
    }

    .stat-icon.posts {
        background: linear-gradient(135deg, #4cd96420 0%, #5ac8fa20 100%);
        color: #4cd964;
    }

    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #718096;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }

        .header {
            padding: 20px;
        }

        .card {
            padding: 20px;
        }

        .table th,
        .table td {
            padding: 12px 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid">
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
                        // Remove query parameters from URL
                        const url = new URL(window.location);
                        url.searchParams.delete('status');
                        url.searchParams.delete('message');
                        window.history.replaceState({}, '', url);
                    }, 300);
                }, 5000);
            </script>
        <?php endif; ?>

        <?php if ($action == 'list'): ?>
            <!-- User List View -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-users"></i>
                        User Management
                    </div>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>

                <?php
                // Pagination
                $limit = 10;
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $offset = ($page - 1) * $limit;

                // Get total pages
                $total_result = $conn->query("SELECT COUNT(*) as total FROM admins");
                $total_rows = $total_result->fetch_assoc()['total'];
                $total_pages = ceil($total_rows / $limit);

                // Fetch users
                $sql = "SELECT * FROM admins 
                           ORDER BY created_at DESC 
                           LIMIT $limit OFFSET $offset";
                $result = $conn->query($sql);

                // Stats
                $active_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE status = 1")->fetch_assoc()['count'];
                $inactive_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE status = 0")->fetch_assoc()['count'];
                $admin_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE role = 'admin'")->fetch_assoc()['count'];
                $editor_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE role = 'editor'")->fetch_assoc()['count'];
                $author_users = $conn->query("SELECT COUNT(*) as count FROM admins WHERE role = 'author'")->fetch_assoc()['count'];
                ?>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_rows; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon total" style="background: linear-gradient(135deg, #4cd96420 0%, #5ac8fa20 100%); color: #4cd964;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-number"><?php echo $active_users; ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon total" style="background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%); color: #667eea;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="stat-number"><?php echo $admin_users; ?></div>
                        <div class="stat-label">Admins</div>
                    </div>
                </div>

                <?php if ($result && $result->num_rows > 0): ?>
                    <!-- Bulk Actions -->
                    <form method="POST" action="" id="bulkForm">
                        <div class="bulk-actions">
                            <select name="bulk_action" class="form-control" style="max-width: 250px;">
                                <option value="">Bulk Actions</option>
                                <option value="activate">Activate</option>
                                <option value="deactivate">Deactivate</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirmBulkAction()">
                                <i class="fas fa-play"></i> Apply
                            </button>
                        </div>

                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="checkbox-cell">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Mobile</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="selected_users[]" value="<?php echo $row['id']; ?>" class="user-checkbox">
                                            </td>
                                            <td>
                                                <strong>#<?php echo $row['id']; ?></strong>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>">
                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo $row['mobile'] ? htmlspecialchars($row['mobile']) : '<span class="text-muted">N/A</span>'; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php
                                                                    if ($row['role'] == 'admin') echo 'badge-primary';
                                                                    elseif ($row['role'] == 'editor') echo 'badge-info';
                                                                    else echo 'badge-secondary';
                                                                    ?>">
                                                    <?php echo ucfirst($row['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['status'] == 1 ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $row['status'] == 1 ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="actions-cell">
                                                <a href="?toggle_status=<?php echo $row['id']; ?>"
                                                    class="btn btn-sm <?php echo $row['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>"
                                                    title="<?php echo $row['status'] == 1 ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $row['status'] == 1 ? 'times' : 'check'; ?>"></i>
                                                </a>
                                                <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirmDeleteUser('<?php echo addslashes($row['name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            if ($start_page > 1): ?>
                                <a href="?page=1" class="page-link">1</a>
                                <?php if ($start_page > 2): ?>
                                    <span class="page-link" style="background: transparent; border: none;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <span class="page-link" style="background: transparent; border: none;">...</span>
                                <?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?>" class="page-link"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h3>No Users Found</h3>
                        <p>You haven't added any users yet. Start by adding your first user.</p>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First User
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit User Form -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-<?php echo $is_edit_mode ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $is_edit_mode ? 'Edit User' : 'Add New User'; ?>
                    </div>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>

                <form method="POST" action="" id="userForm">
                    <input type="hidden" name="submitUser" value="1">

                    <div class="form-grid">
                        <div>
                            <!-- Basic Information -->
                            <div class="form-section">
                                <h3 class="form-section-title">
                                    <i class="fas fa-user-circle"></i>
                                    Basic Information
                                </h3>

                                <div class="form-group">
                                    <label class="form-label required">
                                        Full Name
                                    </label>
                                    <input type="text"
                                        name="name"
                                        class="form-control"
                                        placeholder="Enter full name"
                                        value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>"
                                        required>
                                    <div class="form-help">Enter the user's full name</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label required">
                                        Email Address
                                    </label>
                                    <input type="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Enter email address"
                                        value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                                        required>
                                    <div class="form-help">User will login with this email</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        Mobile Number
                                    </label>
                                    <input type="tel"
                                        name="mobile"
                                        class="form-control"
                                        placeholder="Enter mobile number"
                                        pattern="[0-9]{10}"
                                        maxlength="10"
                                        value="<?php echo htmlspecialchars($user_data['mobile'] ?? ''); ?>">
                                    <div class="form-help">Enter 10-digit mobile number (optional)</div>
                                </div>
                            </div>

                            <!-- Password Section (only for add or if password reset) -->
                            <?php if (!$is_edit_mode): ?>
                                <div class="form-section">
                                    <h3 class="form-section-title">
                                        <i class="fas fa-key"></i>
                                        Password
                                    </h3>

                                    <div class="form-group">
                                        <label class="form-label required">
                                            Password
                                        </label>
                                        <input type="password"
                                            name="password"
                                            id="password"
                                            class="form-control"
                                            placeholder="Enter password"
                                            required>
                                        <div class="form-help">Minimum 8 characters</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label required">
                                            Confirm Password
                                        </label>
                                        <input type="password"
                                            name="confirm_password"
                                            class="form-control"
                                            placeholder="Confirm password"
                                            required>
                                        <div class="form-help">Re-enter the same password</div>
                                    </div>

                                    <div class="form-help">
                                        <i class="fas fa-info-circle"></i>
                                        Password strength: <span id="passwordStrength">None</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="form-section">
                                    <h3 class="form-section-title">
                                        <i class="fas fa-key"></i>
                                        Password
                                    </h3>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span>Password can only be changed by the user from their profile or via password reset.</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div>
                            <!-- Role & Status Settings -->
                            <div class="sidebar-section">
                                <h4 class="sidebar-section-title">
                                    <i class="fas fa-cog"></i>
                                    Role & Status
                                </h4>

                                <div class="form-group">
                                    <label class="form-label required">User Role</label>
                                    <select class="form-select" name="role" required>
                                        <option value="admin" <?php echo (($user_data['role'] ?? 'author') == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                        <option value="editor" <?php echo (($user_data['role'] ?? 'author') == 'editor') ? 'selected' : ''; ?>>Editor</option>
                                        <option value="author" <?php echo (($user_data['role'] ?? 'author') == 'author') ? 'selected' : ''; ?>>Author</option>
                                    </select>
                                    <div class="form-help">
                                        <strong>Admin:</strong> Full access to all features<br>
                                        <strong>Editor:</strong> Can edit and publish content<br>
                                        <strong>Author:</strong> Can create and edit own content
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Account Status</label>
                                    <div class="radio-group">
                                        <div class="radio-item">
                                            <input type="radio" id="status_active" name="status" value="1"
                                                <?php echo (!isset($user_data['status']) || $user_data['status'] == 1) ? 'checked' : ''; ?>>
                                            <label for="status_active">
                                                <span class="badge badge-success">Active</span>
                                            </label>
                                        </div>
                                        <div class="radio-item">
                                            <input type="radio" id="status_inactive" name="status" value="0"
                                                <?php echo (isset($user_data['status']) && $user_data['status'] == 0) ? 'checked' : ''; ?>>
                                            <label for="status_inactive">
                                                <span class="badge badge-secondary">Inactive</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-help">Active users can login, inactive users cannot.</div>
                                </div>

                                <?php if ($is_edit_mode): ?>
                                    <div class="form-group">
                                        <label class="form-label">User ID</label>
                                        <input type="text" class="form-control" value="#<?php echo $user_data['id']; ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Account Created</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo date('d/m/Y H:i:s', strtotime($user_data['created_at'])); ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Last Login</label>
                                        <input type="text" class="form-control" value="Not tracked" readonly>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            <?php echo $is_edit_mode ? 'Update User' : 'Add User'; ?>
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>

                        <?php if ($is_edit_mode):
                            $current_user_id = $_SESSION['user_id'] ?? 0;
                            if ($user_id != $current_user_id):
                        ?>
                                <a href="?delete=<?php echo $user_data['id']; ?>"
                                    class="btn btn-danger"
                                    onclick="return confirmDeleteUser('<?php echo addslashes($user_data['name']); ?>')">
                                    <i class="fas fa-trash"></i> Delete User
                                </a>
                        <?php endif;
                        endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>
<script>
    // Select all checkboxes
    document.getElementById('selectAll')?.addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });

    // Confirm delete
    function confirmDeleteUser(name) {
        return Swal.fire({
            title: 'Delete User?',
            html: `Are you sure you want to delete "<strong>${name}</strong>"?<br><br>
                      <small class="text-danger">This action cannot be undone!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            return result.isConfirmed;
        });
    }

    // Confirm bulk action
    function confirmBulkAction() {
        const form = document.getElementById('bulkForm');
        const action = form.bulk_action.value;
        const selected = document.querySelectorAll('.user-checkbox:checked');

        if (selected.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select at least one user to perform bulk actions.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return false;
        }

        if (action === 'delete') {
            return Swal.fire({
                title: 'Delete Users?',
                text: `Are you sure you want to delete ${selected.length} users? This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete them!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                return result.isConfirmed;
            });
        }

        return true;
    }

    // Password strength checker
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('passwordStrength');

    if (passwordInput && passwordStrength) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 'None';
            let className = '';

            if (password.length > 0) {
                if (password.length < 6) {
                    strength = 'Weak';
                    className = 'password-weak';
                } else if (password.length < 10) {
                    strength = 'Medium';
                    className = 'password-medium';
                } else {
                    // Check for complexity
                    const hasUpperCase = /[A-Z]/.test(password);
                    const hasLowerCase = /[a-z]/.test(password);
                    const hasNumbers = /\d/.test(password);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                    let complexity = 0;
                    if (hasUpperCase) complexity++;
                    if (hasLowerCase) complexity++;
                    if (hasNumbers) complexity++;
                    if (hasSpecial) complexity++;

                    if (complexity >= 3 && password.length >= 12) {
                        strength = 'Very Strong';
                        className = 'password-strong';
                    } else if (complexity >= 2) {
                        strength = 'Strong';
                        className = 'password-strong';
                    } else {
                        strength = 'Medium';
                        className = 'password-medium';
                    }
                }
            }

            passwordStrength.textContent = strength;
            passwordStrength.className = className;
        });
    }

    // Form validation
    document.getElementById('userForm')?.addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="confirm_password"]');

        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                Swal.fire({
                    title: 'Password Mismatch',
                    text: 'Passwords do not match. Please enter the same password in both fields.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                confirmPassword.focus();
                return false;
            }

            if (password.value.length < 8) {
                e.preventDefault();
                Swal.fire({
                    title: 'Password Too Short',
                    text: 'Password must be at least 8 characters long.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                password.focus();
                return false;
            }
        }

        const email = document.querySelector('input[name="email"]');
        if (email && !email.value.includes('@')) {
            e.preventDefault();
            Swal.fire({
                title: 'Invalid Email',
                text: 'Please enter a valid email address.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            email.focus();
            return false;
        }

        return true;
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-focus first input in forms
        const firstInput = document.querySelector('input[name="name"]');
        if (firstInput) firstInput.focus();

        // Quick edit with double click on list items
        document.addEventListener('dblclick', function(e) {
            const row = e.target.closest('tr');
            if (row && row.querySelector('a.btn-outline')) {
                const editLink = row.querySelector('a.btn-outline');
                window.location.href = editLink.href;
            }
        });

        // Mobile number validation
        const mobileInput = document.querySelector('input[name="mobile"]');
        if (mobileInput) {
            mobileInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N to add new user
        if ((e.ctrlKey || e.metaKey) && e.key === 'n' && window.location.search.includes('action=list')) {
            e.preventDefault();
            window.location.href = '?action=add';
        }

        // Escape to go back to list
        if (e.key === 'Escape' && (window.location.search.includes('action=add') || window.location.search.includes('action=edit'))) {
            window.location.href = '?';
        }

        // Ctrl/Cmd + S to save form
        if ((e.ctrlKey || e.metaKey) && e.key === 's' && document.querySelector('form')) {
            e.preventDefault();
            document.querySelector('form button[type="submit"]').click();
        }
    });
</script>

<?php include 'layout/footer.php'; ?>
</body>

</html>