<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Initialize variables
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit_mode = ($category_id > 0);
$category_data = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Fetch category data if in edit mode
if ($is_edit_mode && $action == 'edit') {
    $result = $conn->query("SELECT * FROM categories WHERE id = $category_id");
    if ($result && $result->num_rows > 0) {
        $category_data = $result->fetch_assoc();
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Category not found");
        exit;
    }
}

/* CATEGORY INSERT/UPDATE LOGIC */
if (isset($_POST['submitCategory'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $description = $conn->real_escape_string(trim($_POST['description'] ?? NULL));

    // Generate slug
    $slug = strtolower(trim($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = strtolower(str_replace(" ", "-", $name));
    }
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    // Check if slug already exists
    $check_sql = "SELECT id FROM categories WHERE slug = '$slug'";
    if ($is_edit_mode) {
        $check_sql .= " AND id != $category_id";
    }
    $check_result = $conn->query($check_sql);

    if ($check_result && $check_result->num_rows > 0) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?action=" . ($is_edit_mode ? "edit&id=$category_id" : "add") . "&status=error&message=Slug already exists");
        exit;
    }

    if ($is_edit_mode && $action == 'edit') {
        /* UPDATE CATEGORY */
        $sql = "UPDATE categories SET 
                name = '$name',
                slug = '$slug',
                description = '$description',
                updated_at = NOW()
                WHERE id = $category_id";

        if ($conn->query($sql)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Category updated successfully");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=edit&id=$category_id&status=error&message=Failed to update category");
        }
    } else {
        /* INSERT CATEGORY */
        $sql = "INSERT INTO categories (name, slug, description, created_at, updated_at) 
                VALUES ('$name', '$slug', '$description', NOW(), NOW())";

        if ($conn->query($sql)) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Category created successfully");
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?action=add&status=error&message=Failed to create category");
        }
    }
    exit;
}

/* DELETE CATEGORY LOGIC */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];

    // Check if category has posts
    $check_posts = $conn->query("SELECT COUNT(*) as post_count FROM post_categories WHERE category_id = $delete_id");
    $post_count = $check_posts->fetch_assoc()['post_count'];

    if ($post_count > 0) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Cannot delete category. It has $post_count posts assigned. Please reassign or delete posts first.");
        exit;
    }

    if ($conn->query("DELETE FROM categories WHERE id = $delete_id")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Category deleted successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to delete category");
    }
    exit;
}

/* BULK DELETE CATEGORIES */
if (isset($_POST['bulk_action']) && $_POST['bulk_action'] == 'delete' && isset($_POST['selected_categories'])) {
    $selected_ids = array_map('intval', $_POST['selected_categories']);
    $ids_string = implode(',', $selected_ids);

    // Check if any selected category has posts
    $check_posts = $conn->query("SELECT COUNT(*) as post_count FROM post_categories WHERE category_id IN ($ids_string)");
    $post_count = $check_posts->fetch_assoc()['post_count'];

    if ($post_count > 0) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Cannot delete categories. Some have posts assigned.");
        exit;
    }

    if ($conn->query("DELETE FROM categories WHERE id IN ($ids_string)")) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=" . count($selected_ids) . " categories deleted successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to delete categories");
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
    <div class="form-container">

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
            <!-- Category List View -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-list"></i>
                        All Categories
                    </div>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>

                <?php
                // Pagination
                $limit = 10;
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $offset = ($page - 1) * $limit;

                // Get total pages
                $total_result = $conn->query("SELECT COUNT(*) as total FROM categories");
                $total_rows = $total_result->fetch_assoc()['total'];
                $total_pages = ceil($total_rows / $limit);

                // Fetch categories with post count
                $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM post_categories pc WHERE pc.category_id = c.id) as post_count
                       FROM categories c 
                       ORDER BY c.name ASC 
                       LIMIT $limit OFFSET $offset";
                $result = $conn->query($sql);
                ?>

                <?php if ($result && $result->num_rows > 0): ?>
                    <!-- Bulk Actions -->
                    <form method="POST" action="" id="bulkForm">
                        <div class="bulk-actions">
                            <select name="bulk_action" class="form-control" style="max-width: 200px;">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmBulkAction()">
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
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Posts</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="selected_categories[]" value="<?php echo $row['id']; ?>" class="category-checkbox">
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                            </td>
                                            <td>
                                                <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 0px;"><?php echo htmlspecialchars($row['slug']); ?></code>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars(substr($row['description'] ?? 'No description', 0, 50)); ?>
                                                <?php if (strlen($row['description'] ?? '') > 50): ?>...<?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['post_count'] > 0 ? 'badge-success' : 'badge-secondary'; ?>">
                                                    <?php echo $row['post_count']; ?> posts
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="actions-cell">
                                                <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-outline btn-sm">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="?delete=<?php echo $row['id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirmDeleteCategory('<?php echo addslashes($row['name']); ?>', <?php echo $row['post_count']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
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

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

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
                        <i class="fas fa-folder-open"></i>
                        <h3>No Categories Found</h3>
                        <p>You haven't created any categories yet. Start by adding your first category.</p>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First Category
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Category Form -->
            <a href="?" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-<?php echo $is_edit_mode ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $is_edit_mode ? 'Edit Category' : 'Add New Category'; ?>
                    </div>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="submitCategory" value="1">

                    <div class="form-group">
                        <label class="form-label required">Category Name</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter category name"
                            value="<?php echo htmlspecialchars($category_data['name'] ?? ''); ?>"
                            required
                            oninput="updateSlugPreview(this.value)">
                        <div class="form-help">This name appears on your site.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text"
                            name="slug"
                            id="slug"
                            class="form-control"
                            placeholder="category-slug"
                            value="<?php echo htmlspecialchars($category_data['slug'] ?? ''); ?>"
                            oninput="updateSlugPreview(this.value)">
                        <div class="slug-preview" id="slugPreview">
                            <span>URL:</span>
                            <strong>https://yoursite.com/category/<span id="slugText"><?php echo htmlspecialchars($category_data['slug'] ?? 'your-slug'); ?></span></strong>
                        </div>
                        <div class="form-help">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                            class="form-control"
                            placeholder="Optional description for this category"
                            rows="4"><?php echo htmlspecialchars($category_data['description'] ?? ''); ?></textarea>
                        <div class="form-help">The description is not prominent by default; however, some themes may show it.</div>
                    </div>

                    <div class="form-group" style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            <?php echo $is_edit_mode ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.category-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });

        // Slug generation
        function updateSlugPreview(value) {
            const slugInput = document.getElementById('slug');
            const slugText = document.getElementById('slugText');

            // Only auto-generate slug if slug field is empty or matches old name
            if (!slugInput.value || slugInput.value === slugText.textContent) {
                const slug = value.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
                slugText.textContent = slug || 'your-slug';
            } else {
                slugText.textContent = slugInput.value || 'your-slug';
            }
        }

        // Confirm delete
        function confirmDeleteCategory(name, postCount) {
            if (postCount > 0) {
                Swal.fire({
                    title: 'Cannot Delete Category',
                    text: `"${name}" has ${postCount} posts assigned. Please reassign or delete those posts first.`,
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            return Swal.fire({
                title: 'Delete Category?',
                text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff6b6b',
                cancelButtonColor: '#667eea',
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
            const selected = document.querySelectorAll('.category-checkbox:checked');

            if (selected.length === 0) {
                Swal.fire({
                    title: 'No Categories Selected',
                    text: 'Please select at least one category to perform bulk actions.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#667eea'
                });
                return false;
            }

            if (action === 'delete') {
                return Swal.fire({
                    title: 'Delete Categories?',
                    text: `Are you sure you want to delete ${selected.length} categories? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff6b6b',
                    cancelButtonColor: '#667eea',
                    confirmButtonText: 'Yes, delete them!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    return result.isConfirmed;
                });
            }

            return true;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Update slug preview with current value
            const nameInput = document.querySelector('input[name="name"]');
            if (nameInput) {
                updateSlugPreview(nameInput.value);
            }

            // Auto-focus first input in forms
            const firstInput = document.querySelector('.form-control');
            if (firstInput) firstInput.focus();

            // Character counter for description
            const textarea = document.querySelector('textarea[name="description"]');
            if (textarea) {
                const counter = document.createElement('div');
                counter.className = 'form-help';
                counter.style.textAlign = 'right';
                counter.textContent = `${textarea.value.length} / 500 characters`;
                textarea.parentNode.insertBefore(counter, textarea.nextSibling);

                textarea.addEventListener('input', function() {
                    counter.textContent = `${this.value.length} / 500 characters`;
                });
            }
        });

        // Quick edit with double click on list items
        document.addEventListener('dblclick', function(e) {
            const row = e.target.closest('tr');
            if (row && row.querySelector('a.btn-outline')) {
                const editLink = row.querySelector('a.btn-outline');
                window.location.href = editLink.href;
            }
        });
    </script>
    </body>

    </html>