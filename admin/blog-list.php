<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Handle actions
if (isset($_GET['action'])) {
    $post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch ($_GET['action']) {
        case 'delete':
            if ($post_id > 0) {
                // Delete categories and tags first
                $conn->query("DELETE FROM post_categories WHERE post_id = $post_id");
                $conn->query("DELETE FROM post_tags WHERE post_id = $post_id");

                // Delete post
                if ($conn->query("DELETE FROM posts WHERE id = $post_id")) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Post deleted successfully");
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to delete post");
                }
                exit;
            }
            break;

        case 'publish':
            if ($post_id > 0) {
                $sql = "UPDATE posts SET status = 'published', publish_date = NOW(), updated_at = NOW() WHERE id = $post_id";
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Post published successfully");
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to publish post");
                }
                exit;
            }
            break;

        case 'draft':
            if ($post_id > 0) {
                $sql = "UPDATE posts SET status = 'draft', updated_at = NOW() WHERE id = $post_id";
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Post moved to draft");
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to update post");
                }
                exit;
            }
            break;

        case 'archive':
            if ($post_id > 0) {
                $sql = "UPDATE posts SET status = 'archived', updated_at = NOW() WHERE id = $post_id";
                if ($conn->query($sql)) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=success&message=Post archived");
                } else {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to archive post");
                }
                exit;
            }
            break;
    }
}

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$author_filter = isset($_GET['author']) ? (int)$_GET['author'] : 0;
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter && in_array($status_filter, ['draft', 'published', 'archived'])) {
    $where_conditions[] = "p.status = '$status_filter'";
}

if ($category_filter > 0) {
    $where_conditions[] = "pc.category_id = $category_filter";
}

if ($author_filter > 0) {
    $where_conditions[] = "p.user_id = $author_filter";
}

if (!empty($search_query)) {
    $where_conditions[] = "(p.title LIKE '%$search_query%' OR p.excerpt LIKE '%$search_query%' OR p.content LIKE '%$search_query%')";
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Get total count for pagination
$count_sql = "SELECT COUNT(DISTINCT p.id) as total 
              FROM posts p
              LEFT JOIN post_categories pc ON p.id = pc.post_id
              $where_sql";
$count_result = $conn->query($count_sql);
$total_posts = $count_result->fetch_assoc()['total'];

// Pagination
$per_page = 10;
$total_pages = ceil($total_posts / $per_page);
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Get posts with pagination
$sqlblog = "SELECT p.*, 
               u.name as author_name,
               GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') as categories,
               GROUP_CONCAT(DISTINCT pc.category_id) as category_ids
        FROM posts p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN post_categories pc ON p.id = pc.post_id
        LEFT JOIN categories c ON pc.category_id = c.id
        $where_sql
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT $offset, $per_page";

$resultBlog = $conn->query($sqlblog);

// echo $sql; die();

// Get categories for filter dropdown
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name");

// Get authors for filter dropdown
$authors_result = $conn->query("SELECT DISTINCT u.id, u.name 
                               FROM admins u 
                               JOIN posts p ON u.id = p.user_id 
                               ORDER BY u.name");
?>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>
<style>
    .alert {
        padding: 12px 20px;
        margin: 15px 0;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @keyframes slideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .filter-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-label {
        font-weight: 500;
        color: #555;
        font-size: 14px;
    }

    .filter-select,
    .filter-input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #4a90e2;
        box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        grid-column: 1 / -1;
        margin-top: 10px;
    }

    .table-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .table-badge.published {
        background-color: #d4edda;
        color: #155724;
    }

    .table-badge.draft {
        background-color: #fff3cd;
        color: #856404;
    }

    .table-badge.archived {
        background-color: #e2e3e5;
        color: #383d41;
    }

    .table-badge.pending {
        background-color: #cce5ff;
        color: #004085;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .data-table thead {
        background-color: #f8f9fa;
    }

    .data-table th {
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
    }

    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #dee2e6;
    }

    .data-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-actions {
        display: flex;
        gap: 8px;
    }

    .action-icon-btn {
        padding: 6px 10px;
        border: none;
        background: none;
        cursor: pointer;
        border-radius: 4px;
        color: #6c757d;
        transition: all 0.2s;
    }

    .action-icon-btn:hover {
        background-color: #f8f9fa;
        color: #495057;
    }

    .action-icon-btn.danger:hover {
        background-color: #f8d7da;
        color: #721c24;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        padding: 20px;
    }

    .page-link {
        padding: 8px 16px;
        border: 1px solid #dee2e6;
        background: #fff;
        color: #007bff;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
    }

    .page-link.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .page-link.disabled {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .dropdown-menu {
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 160px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        z-index: 100;
        padding: 5px 0;
    }

    .dropdown-menu:hover .dropdown-content {
        display: block;
    }

    .dropdown-item {
        display: block;
        padding: 8px 16px;
        text-decoration: none;
        color: #333;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        font-size: 14px;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item.danger {
        color: #dc3545;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .stat-number {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #6c757d;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-published .stat-number {
        color: #28a745;
    }

    .stat-draft .stat-number {
        color: #ffc107;
    }

    .stat-archived .stat-number {
        color: #6c757d;
    }

    .stat-total .stat-number {
        color: #007bff;
    }

    .mobile-actions {
        display: none;
    }

    @media (max-width: 768px) {
        .filter-form {
            grid-template-columns: 1fr;
        }

        .table-actions {
            flex-wrap: wrap;
        }

        .data-table {
            font-size: 14px;
        }

        .data-table th,
        .data-table td {
            padding: 8px;
        }

        .desktop-actions {
            display: none;
        }

        .mobile-actions {
            display: block;
        }
    }
    /* =========================
   RESPONSIVE UPGRADE
========================= */

/* Tablet */
@media (max-width: 992px) {

  .filter-container {
    padding: 15px;
  }

  .filter-form {
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
  }

  .stats-container {
    grid-template-columns: repeat(2, 1fr);
  }

  .stat-number {
    font-size: 26px;
  }
}

/* Mobile */
@media (max-width: 768px) {

  /* Filters stack nicely */
  .filter-form {
    grid-template-columns: 1fr;
  }

  .filter-actions {
    flex-direction: column;
  }

  .filter-actions button {
    width: 100%;
  }

  /* Stats responsive */
  .stats-container {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  /* Table scroll */
  .data-table {
    display: block;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
  }

  .data-table th,
  .data-table td {
    padding: 10px;
    font-size: 14px;
  }

  /* ACTIONS — ALWAYS VISIBLE */
  .table-actions {
    flex-wrap: nowrap;
    justify-content: flex-start;
    gap: 6px;
  }

  .action-icon-btn {
    padding: 8px;
    font-size: 14px;
  }

  /* SHOW BOTH ACTION TYPES */
  .desktop-actions {
    display: flex !important;
  }

  .mobile-actions {
    display: flex !important;
    gap: 6px;
  }

  /* Dropdown safer on mobile */
  .dropdown-content {
    right: auto;
    left: 0;
    min-width: 140px;
  }

  /* Pagination wrap */
  .pagination {
    flex-wrap: wrap;
    gap: 6px;
  }

  .page-link {
    padding: 6px 12px;
    font-size: 13px;
  }
}

/* Small Phones */
@media (max-width: 480px) {

  .filter-container {
    padding: 12px;
  }

  .stat-card {
    padding: 15px;
  }

  .stat-number {
    font-size: 22px;
  }

  .stat-label {
    font-size: 12px;
  }

  .data-table {
    font-size: 13px;
  }

  .action-icon-btn {
    padding: 6px;
  }

  .page-link {
    padding: 5px 10px;
    font-size: 12px;
  }
}

</style>

<!-- Main Content -->
<main class="main-content">
    <div class="form-container">
        <!-- Alert -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status']; ?>">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-20px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-container">
            <?php
            $total_result = $conn->query("SELECT COUNT(*) as count FROM posts");
            $published_result = $conn->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
            $draft_result = $conn->query("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'");
            $archived_result = $conn->query("SELECT COUNT(*) as count FROM posts WHERE status = 'archived'");

            $total = $total_result->fetch_assoc()['count'];
            $published = $published_result->fetch_assoc()['count'];
            $draft = $draft_result->fetch_assoc()['count'];
            $archived = $archived_result->fetch_assoc()['count'];
            ?>
            <div class="stat-card stat-total">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
            <div class="stat-card stat-published">
                <div class="stat-number"><?php echo $published; ?></div>
                <div class="stat-label">Published</div>
            </div>
            <div class="stat-card stat-draft">
                <div class="stat-number"><?php echo $draft; ?></div>
                <div class="stat-label">Draft</div>
            </div>
            <div class="stat-card stat-archived">
                <div class="stat-number"><?php echo $archived; ?></div>
                <div class="stat-label">Archived</div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="filter-container">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="published" <?php echo $status_filter == 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="archived" <?php echo $status_filter == 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Category</label>
                    <select name="category" class="filter-select">
                        <option value="0">All Categories</option>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Author</label>
                    <select name="author" class="filter-select">
                        <option value="0">All Authors</option>
                        <?php while ($author = $authors_result->fetch_assoc()): ?>
                            <option value="<?php echo $author['id']; ?>"
                                <?php echo $author_filter == $author['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Search</label>
                    <input type="text" name="search" class="filter-input"
                        placeholder="Search posts..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                    <a href="post-blog.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> New Post
                    </a>
                </div>
            </form>
        </div>

        <!-- Blog Posts Table -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-newspaper"></i> Blog Posts
                    <small>(Showing <?php echo $total_posts; ?> posts)</small>
                </h3>
            </div>
            <?php if ($resultBlog && $resultBlog->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Categories</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resultBlog->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                        <small class="text-muted"><?php echo substr(htmlspecialchars($row['excerpt']), 0, 100); ?>...</small>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($row['categories'])) {
                                            $cats = explode(', ', $row['categories']);
                                            foreach ($cats as $cat) {
                                                echo '<span class="category-tag">' . htmlspecialchars($cat) . '</span> ';
                                            }
                                        } else {
                                            echo '<span class="text-muted">No categories</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['author_name']); ?></td>
                                    <td>
                                        <span class="table-badge <?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?><br>
                                        <small class="text-muted">
                                            <?php
                                            $time_ago = strtotime($row['created_at']);
                                            $now = time();
                                            $diff = $now - $time_ago;

                                            if ($diff < 60) {
                                                echo 'Just now';
                                            } elseif ($diff < 3600) {
                                                echo floor($diff / 60) . ' min ago';
                                            } elseif ($diff < 86400) {
                                                echo floor($diff / 3600) . ' hours ago';
                                            } else {
                                                echo floor($diff / 86400) . ' days ago';
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="table-actions desktop-actions">
                                            <a href="post-blog.php?id=<?php echo $row['id']; ?>"
                                                class="action-icon-btn" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <!-- Status Actions -->
                                            <?php if ($row['status'] == 'draft'): ?>
                                                <a href="?action=publish&id=<?php echo $row['id']; ?>"
                                                    class="action-icon-btn" title="Publish" onclick="return confirm('Publish this post?')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </a>
                                            <?php elseif ($row['status'] == 'published'): ?>
                                                <a href="?action=draft&id=<?php echo $row['id']; ?>"
                                                    class="action-icon-btn" title="Move to Draft" onclick="return confirm('Move to draft?')">
                                                    <i class="fas fa-save"></i>
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($row['status'] != 'archived'): ?>
                                                <a href="?action=archive&id=<?php echo $row['id']; ?>"
                                                    class="action-icon-btn" title="Archive" onclick="return confirm('Archive this post?')">
                                                    <i class="fas fa-archive"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=draft&id=<?php echo $row['id']; ?>"
                                                    class="action-icon-btn" title="Restore" onclick="return confirm('Restore from archive?')">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                            <?php endif; ?>

                                            <a href="?action=delete&id=<?php echo $row['id']; ?>"
                                                class="action-icon-btn danger" title="Delete"
                                                onclick="return confirmDelete(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>

                                        <!-- Mobile Actions Dropdown -->
                                        <div class="dropdown-menu mobile-actions">
                                            <button class="action-icon-btn">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-content">
                                                <a href="create-edit-post.php?id=<?php echo $row['id']; ?>"
                                                    class="dropdown-item">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>

                                                <?php if ($row['status'] == 'draft'): ?>
                                                    <a href="?action=publish&id=<?php echo $row['id']; ?>"
                                                        class="dropdown-item" onclick="return confirm('Publish this post?')">
                                                        <i class="fas fa-paper-plane"></i> Publish
                                                    </a>
                                                <?php elseif ($row['status'] == 'published'): ?>
                                                    <a href="?action=draft&id=<?php echo $row['id']; ?>"
                                                        class="dropdown-item" onclick="return confirm('Move to draft?')">
                                                        <i class="fas fa-save"></i> Draft
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($row['status'] != 'archived'): ?>
                                                    <a href="?action=archive&id=<?php echo $row['id']; ?>"
                                                        class="dropdown-item" onclick="return confirm('Archive this post?')">
                                                        <i class="fas fa-archive"></i> Archive
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?action=draft&id=<?php echo $row['id']; ?>"
                                                        class="dropdown-item" onclick="return confirm('Restore from archive?')">
                                                        <i class="fas fa-undo"></i> Restore
                                                    </a>
                                                <?php endif; ?>

                                                <a href="?action=delete&id=<?php echo $row['id']; ?>"
                                                    class="dropdown-item danger" onclick="return confirmDelete(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        $query_params = $_GET;
                        unset($query_params['page']);
                        $query_string = http_build_query($query_params);
                        $base_url = $_SERVER['PHP_SELF'] . ($query_string ? '?' . $query_string . '&' : '?');
                        ?>

                        <!-- First Page -->
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo $base_url; ?>page=1" class="page-link">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled">
                                <i class="fas fa-angle-double-left"></i>
                            </span>
                        <?php endif; ?>

                        <!-- Previous Page -->
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo $base_url; ?>page=<?php echo $current_page - 1; ?>" class="page-link">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php else: ?>
                            <span class="page-link disabled">
                                <i class="fas fa-angle-left"></i>
                            </span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        if ($start_page > 1) {
                            echo '<span class="page-link disabled">...</span>';
                        }

                        for ($i = $start_page; $i <= $end_page; $i++):
                            if ($i == $current_page):
                        ?>
                                <span class="page-link active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $i; ?>" class="page-link">
                                    <?php echo $i; ?>
                                </a>
                        <?php endif;
                        endfor; ?>

                        if ($end_page < $total_pages) {
                            echo '<span class="page-link disabled">...</span>' ;
                            }
                            ?>

                            <!-- Next Page -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $current_page + 1; ?>" class="page-link">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-link disabled">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            <?php endif; ?>

                            <!-- Last Page -->
                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?php echo $base_url; ?>page=<?php echo $total_pages; ?>" class="page-link">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="page-link disabled">
                                    <i class="fas fa-angle-double-right"></i>
                                </span>
                            <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-newspaper fa-3x"></i>
                    <h3>No posts found</h3>
                    <p>Try adjusting your filters or create a new post.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function confirmDelete(postId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This post will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?action=delete&id=' + postId;
            }
        });
        return false;
    }

    // Auto-submit filters on change
    document.querySelectorAll('.filter-select').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Search debounce
    let searchTimeout;
    document.querySelector('input[name="search"]').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            this.form.submit();
        }, 500);
    });

    // Quick status change
    document.querySelectorAll('.table-badge').forEach(badge => {
        badge.addEventListener('click', function() {
            const postId = this.closest('tr').dataset.id;
            const currentStatus = this.classList[1];
            const newStatus = prompt('Change status to (published/draft/archived):', currentStatus);

            if (newStatus && ['published', 'draft', 'archived'].includes(newStatus)) {
                window.location.href = `?action=${newStatus}&id=${postId}`;
            }
        });
    });
</script>
</body>

</html>