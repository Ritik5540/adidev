<?php
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit_mode = ($post_id > 0);
$post_data = null;
$post_categories = [];
$post_tags = [];

// Fetch post data if in edit mode
if ($is_edit_mode) {
    $result = $conn->query("SELECT * FROM posts WHERE id = $post_id");
    if ($result && $result->num_rows > 0) {
        $post_data = $result->fetch_assoc();

        // Fetch post categories
        $cat_result = $conn->query("
            SELECT category_id 
            FROM post_categories 
            WHERE post_id = $post_id
        ");
        while ($row = $cat_result->fetch_assoc()) {
            $post_categories[] = $row['category_id'];
        }

        // Fetch post tags
        $tag_result = $conn->query("
            SELECT t.name 
            FROM tags t
            JOIN post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = $post_id
        ");
        $post_tag_names = [];
        while ($row = $tag_result->fetch_assoc()) {
            $post_tag_names[] = $row['name'];
        }
        $post_tags_string = implode(', ', $post_tag_names);
    } else {
        $is_edit_mode = false;
        header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Post not found");
        exit;
    }
}

/* CATEGORY INSERT */
if (isset($_POST['category_name'])) {
    $name = $conn->real_escape_string($_POST['category_name']);
    $slug = strtolower(str_replace(" ", "-", $name));

    $sql = "INSERT INTO categories (name, slug) VALUES ('$name', '$slug')";

    if ($conn->query($sql)) {
        header("Location: " . $_SERVER['PHP_SELF'] . ($is_edit_mode ? "?id=$post_id&" : "?") . "status=success&message=Category added");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . ($is_edit_mode ? "?id=$post_id&" : "?") . "status=error&message=Failed to add category");
    }
    exit;
}

/* POST INSERT/UPDATE LOGIC */
if (isset($_POST['submitAction']) && ($_POST['submitAction'] == 'create_post' || $_POST['submitAction'] == 'update_post')) {
    /* BASIC DATA */
    $title   = $conn->real_escape_string($_POST['title']);
    $slug    = !empty($_POST['slug'])
        ? $conn->real_escape_string($_POST['slug'])
        : strtolower(str_replace(" ", "-", $title));

    $excerpt = $conn->real_escape_string($_POST['excerpt']);
    $content = $conn->real_escape_string($_POST['content']);

    $meta_title = $conn->real_escape_string($_POST['meta_title']);
    $meta_desc  = $conn->real_escape_string($_POST['meta_description']);
    $keyword    = $conn->real_escape_string($_POST['focus_keyword']);

    $status = $_POST['status'] ?? 'draft';
    $publish_date = $_POST['publish_date'] ?? date('Y-m-d H:i:s');
    $user_id = isset($_POST['author_id']) ? (int)$_POST['author_id'] : 1;

    /* IMAGE UPLOAD */
    $imageName = $post_data['featured_image'] ?? '';
    $imageAlt = $conn->real_escape_string($_POST['image_alt'] ?? '');

    if (!empty($_FILES['featured_image']['name'])) {
        $targetDir = "uploads/blog/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . '_' . basename($_FILES['featured_image']['name']);
        $targetFile = $targetDir . $imageName;

        // Basic validation
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            // Delete old image if exists
            if (!empty($post_data['featured_image']) && file_exists($targetDir . $post_data['featured_image'])) {
                unlink($targetDir . $post_data['featured_image']);
            }

            move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFile);
        }
    }

    if ($_POST['submitAction'] == 'create_post') {
        /* INSERT POST */
        $sql = "INSERT INTO posts 
        (user_id, title, slug, excerpt, content,
        featured_image, image_alt,
        meta_title, meta_description, focus_keyword,
        status, publish_date, created_at, updated_at)

        VALUES
        ('$user_id', '$title', '$slug', '$excerpt', '$content',
        '$imageName', '$imageAlt',
        '$meta_title', '$meta_desc', '$keyword',
        '$status', '$publish_date', NOW(), NOW())";

        if ($conn->query($sql)) {
            $post_id = $conn->insert_id;
            $message = "Post created successfully";
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?status=error&message=Failed to create post");
            exit;
        }
    } else {
        /* UPDATE POST */
        $sql = "UPDATE posts SET 
                user_id = '$user_id',
                title = '$title',
                slug = '$slug',
                excerpt = '$excerpt',
                content = '$content',
                featured_image = '$imageName',
                image_alt = '$imageAlt',
                meta_title = '$meta_title',
                meta_description = '$meta_desc',
                focus_keyword = '$keyword',
                status = '$status',
                publish_date = '$publish_date',
                updated_at = NOW()
                WHERE id = $post_id";

        if ($conn->query($sql)) {
            $message = "Post updated successfully";
        } else {
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=$post_id&status=error&message=Failed to update post");
            exit;
        }
    }

    /* HANDLE CATEGORIES */
    // Remove existing categories
    $conn->query("DELETE FROM post_categories WHERE post_id = $post_id");

    // Add new categories
    if (!empty($_POST['categories'])) {
        foreach ($_POST['categories'] as $cat) {
            $cat = (int)$cat;
            $conn->query("INSERT INTO post_categories (post_id, category_id) VALUES ($post_id, $cat)");
        }
    }

    /* HANDLE TAGS */
    // Remove existing tags
    $conn->query("DELETE FROM post_tags WHERE post_id = $post_id");

    // Add new tags
    if (!empty($_POST['tags'])) {
        $tags = explode(',', $_POST['tags']);

        foreach ($tags as $tag) {
            $tag = trim($conn->real_escape_string($tag));
            if (empty($tag)) continue;

            $slugTag = strtolower(str_replace(" ", "-", $tag));

            // Insert tag if not exists
            $conn->query("INSERT IGNORE INTO tags (name, slug) VALUES ('$tag', '$slugTag')");

            $tag_id = $conn->insert_id;

            if (!$tag_id) {
                $res = $conn->query("SELECT id FROM tags WHERE name='$tag' OR slug='$slugTag' LIMIT 1");
                if ($res && $res->num_rows > 0) {
                    $tag_id = $res->fetch_assoc()['id'];
                }
            }

            if ($tag_id) {
                $conn->query("INSERT INTO post_tags (post_id, tag_id) VALUES ($post_id, $tag_id)");
            }
        }
    }

    header("Location: " . $_SERVER['PHP_SELF'] . ($is_edit_mode ? "?id=$post_id&" : "?") . "status=success&message=" . urlencode($message));
    exit;
}

/* DELETE POST LOGIC */
if (isset($_GET['delete_post']) && $_GET['delete_post'] == '1' && $is_edit_mode) {
    // Delete categories
    $conn->query("DELETE FROM post_categories WHERE post_id = $post_id");

    // Delete tags
    $conn->query("DELETE FROM post_tags WHERE post_id = $post_id");

    // Delete post
    if ($conn->query("DELETE FROM posts WHERE id = $post_id")) {
        header("Location: posts.php?status=success&message=Post deleted successfully");
    } else {
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$post_id&status=error&message=Failed to delete post");
    }
    exit;
}
?>

<?php include 'layout/header.php'; ?>
<?php include 'layout/sidebar.php'; ?>
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
    }

    .close {
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .alert {
        padding: 12px 20px;
        margin: 15px 0;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
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

    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sidebar-section {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .checkbox-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-preview-container {
        margin-top: 15px;
        text-align: center;
    }

    .image-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        border: 1px solid #ddd;
        padding: 5px;
        background: #f8f9fa;
    }

    .image-preview-placeholder {
        width: 100%;
        height: 200px;
        background: #f8f9fa;
        border: 2px dashed #ddd;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 14px;
    }

    .delete-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .delete-btn:hover {
        background-color: #c82333;
    }

    .form-actions-top {
        display: flex;
        gap: 10px;
        align-items: center;
    }
</style>
<!-- Main Content -->
<main class="main-content">
    <div class="form-container">
        <!-- Form Header -->
        <div class="form-header">
            <h2 class="form-title">
                <i class="fas fa-pen-fancy"></i>
                <?php echo $is_edit_mode ? 'Edit Blog Post' : 'Create New Blog Post'; ?>
            </h2>
            <div class="form-actions-top">
                <?php if ($is_edit_mode): ?>
                    <button class="btn btn-outline" type="button" onclick="previewPost()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button class="delete-btn" type="button" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline" type="button">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                <?php endif; ?>
                <button class="btn btn-secondary" type="submit" form="postForm" name="status" value="draft">
                    <i class="fas fa-save"></i> <?php echo $is_edit_mode ? 'Update Draft' : 'Save Draft'; ?>
                </button>
                <button class="btn btn-success" type="submit" form="postForm" name="status" value="published">
                    <i class="fas fa-paper-plane"></i> <?php echo $is_edit_mode ? 'Update & Publish' : 'Publish'; ?>
                </button>
            </div>
        </div>

        <!-- Alert -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status']; ?>">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) alert.style.display = 'none';
                    window.history.replaceState({}, document.title,
                        "<?php echo $is_edit_mode ? $_SERVER['PHP_SELF'] . '?id=' . $post_id : $_SERVER['PHP_SELF']; ?>");
                }, 5000);
            </script>
        <?php endif; ?>

        <!-- Form Grid -->
        <div class="form-grid">
            <form id="postForm" action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submitAction" value="<?php echo $is_edit_mode ? 'update_post' : 'create_post'; ?>">
                <?php if ($is_edit_mode): ?>
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <?php endif; ?>

                <div>
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-info-circle"></i>
                            Basic Information
                        </h3>

                        <div class="form-group">
                            <label class="form-label">
                                Post Title
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" name="title"
                                placeholder="Enter your blog post title here..."
                                value="<?php echo $post_data['title'] ?? ''; ?>" required>
                            <div class="form-help">Create a compelling title that captures attention</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Permalink / URL Slug
                                <span class="optional">(optional)</span>
                            </label>
                            <input type="text" class="form-input" name="slug"
                                placeholder="auto-generated-from-title"
                                value="<?php echo $post_data['slug'] ?? ''; ?>">
                            <div class="form-help">URL: https://yoursite.com/blog/<strong>your-slug-here</strong></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Excerpt / Short Description
                            </label>
                            <textarea class="form-input" name="excerpt"
                                placeholder="Write a brief summary of your post..."><?php echo $post_data['excerpt'] ?? ''; ?></textarea>
                            <div class="form-help">This will appear in post previews and search results (150-160 characters recommended)</div>
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-align-left"></i>
                            Post Content
                        </h3>

                        <div class="form-group">
                            <label class="form-label">
                                Content
                                <span class="required">*</span>
                            </label>
                            <textarea class="form-input" name="content" id="content"
                                placeholder="Start writing your amazing content here..."
                                required><?php echo $post_data['content'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-image"></i>
                            Featured Image
                        </h3>

                        <div class="form-group">
                            <label class="form-label">Upload Featured Image</label>
                            <input type="file" name="featured_image" id="featured_image" class="image-input" accept="image/*">
                            <div class="form-help">Recommended size: 1200x628px. Max file size: 2MB.</div>

                            <div class="image-preview-container">
                                <?php if ($is_edit_mode && !empty($post_data['featured_image'])): ?>
                                    <img src="uploads/blog/<?php echo $post_data['featured_image']; ?>"
                                        alt="Current featured image" class="image-preview" id="currentImagePreview">
                                <?php else: ?>
                                    <div class="image-preview-placeholder" id="imagePlaceholder">
                                        <i class="fas fa-image fa-3x"></i>
                                        <p>No image selected</p>
                                    </div>
                                <?php endif; ?>
                                <img id="newImagePreview" class="image-preview" style="display: none;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Image Alt Text <span class="optional">(SEO)</span></label>
                            <input type="text" name="image_alt" class="form-input"
                                placeholder="Describe the image for accessibility and SEO"
                                value="<?php echo $post_data['image_alt'] ?? ''; ?>">
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-search"></i>
                            SEO Settings
                        </h3>

                        <div class="form-group">
                            <label class="form-label">Meta Title</label>
                            <input type="text" class="form-input" name="meta_title"
                                placeholder="SEO optimized title (60 characters max)"
                                value="<?php echo $post_data['meta_title'] ?? ''; ?>">
                            <div class="form-help"><?php echo strlen($post_data['meta_title'] ?? ''); ?> / 60 characters</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Meta Description</label>
                            <textarea class="form-input" name="meta_description"
                                placeholder="SEO meta description (160 characters max)"
                                style="min-height: 80px;"><?php echo $post_data['meta_description'] ?? ''; ?></textarea>
                            <div class="form-help"><?php echo strlen($post_data['meta_description'] ?? ''); ?> / 160 characters</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Focus Keyword</label>
                            <input type="text" class="form-input" name="focus_keyword"
                                placeholder="Main keyword for this post"
                                value="<?php echo $post_data['focus_keyword'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Category -->
                    <div class="sidebar-section">
                        <h4 class="sidebar-section-title">
                            <i class="fas fa-folder"></i>
                            Categories
                        </h4>

                        <div class="checkbox-group">
                            <?php
                            $res = $conn->query("SELECT * FROM categories ORDER BY name");
                            if ($res && $res->num_rows > 0):
                                while ($row = $res->fetch_assoc()):
                                    $checked = in_array($row['id'], $post_categories) ? 'checked' : '';
                            ?>
                                    <div class="checkbox-item">
                                        <input type="checkbox" id="cat-<?php echo $row['id']; ?>"
                                            name="categories[]" value="<?php echo $row['id']; ?>" <?php echo $checked; ?> onclick="onlyOne(this)">
                                        <label for="cat-<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </label>
                                    </div>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <p>No categories found. Add one using the button below.</p>
                            <?php endif; ?>
                        </div>

                        <button type="button" class="btn btn-outline" style="width: 100%; margin-top: 15px;"
                            onclick="openCategoryModal()">
                            <i class="fas fa-plus"></i> Add New Category
                        </button>
                    </div>

                    <!-- Tags -->
                    <div class="sidebar-section">
                        <h4 class="sidebar-section-title">
                            <i class="fas fa-tags"></i>
                            Tags
                        </h4>
                        <div class="form-group">
                            <input type="text" class="form-input" name="tags" id="tags"
                                placeholder="Add tags separated by commas"
                                value="<?php echo $post_tags_string ?? ''; ?>">
                            <div class="form-help">Press comma or enter to add tags</div>
                        </div>
                    </div>

                    <!-- Author & Status -->
                    <div class="sidebar-section">
                        <h4 class="sidebar-section-title">
                            <i class="fas fa-user-edit"></i>
                            Publishing
                        </h4>

                        <div class="form-group">
                            <label class="form-label">Author</label>
                            <select class="form-select" name="author_id">
                                <?php
                                $res = $conn->query("SELECT id, name FROM admins ORDER BY name");
                                while ($row = $res->fetch_assoc()):
                                    $selected = ($is_edit_mode && $post_data['user_id'] == $row['id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="draft" <?php echo (($post_data['status'] ?? 'draft') == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo (($post_data['status'] ?? 'draft') == 'published') ? 'selected' : ''; ?>>Published</option>
                                <option value="archived" <?php echo (($post_data['status'] ?? 'draft') == 'archived') ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Publish Date</label>
                            <input type="datetime-local" name="publish_date" class="form-input"
                                value="<?php echo isset($post_data['publish_date']) ? date('Y-m-d\TH:i', strtotime($post_data['publish_date'])) : date('Y-m-d\TH:i'); ?>">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
function onlyOne(checkbox) {
    let checkboxes = document.getElementsByName('input[name="categories[]"]');

    checkboxes.forEach((item) => {
        if (item !== checkbox) {
            item.checked = false;
        }
    });
}
</script>
<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCategoryModal()">&times;</span>
        <h3>Create Category</h3>
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="category_name"
                    placeholder="Enter category name"
                    required class="form-input">
            </div>
            <button type="submit" class="btn btn-success">
                Add Category
            </button>
        </form>
    </div>
</div>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content', {
        height: 400,
        toolbar: [{
                name: 'document',
                items: ['Source', '-', 'Preview', 'Print']
            },
            {
                name: 'clipboard',
                items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
            },
            {
                name: 'editing',
                items: ['Find', 'Replace', '-', 'SelectAll']
            },
            '/',
            {
                name: 'basicstyles',
                items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
            },
            {
                name: 'paragraph',
                items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
            },
            {
                name: 'links',
                items: ['Link', 'Unlink', 'Anchor']
            },
            {
                name: 'insert',
                items: ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak']
            },
            '/',
            {
                name: 'styles',
                items: ['Styles', 'Format', 'Font', 'FontSize']
            },
            {
                name: 'colors',
                items: ['TextColor', 'BGColor']
            },
            {
                name: 'tools',
                items: ['Maximize', 'ShowBlocks']
            }
        ]
    });

    // Modal functions
    function openCategoryModal() {
        document.getElementById("categoryModal").style.display = "block";
    }

    function closeCategoryModal() {
        document.getElementById("categoryModal").style.display = "none";
    }

    // Close when clicking outside
    window.onclick = function(e) {
        let modal = document.getElementById("categoryModal");
        if (e.target == modal) {
            modal.style.display = "none";
        }
    }

    // Auto-generate slug from title
    document.querySelector('input[name="title"]').addEventListener('input', function(e) {
        const slugInput = document.querySelector('input[name="slug"]');
        if (!slugInput.value) {
            const slug = e.target.value
                .toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
        }
    });

    // Image preview functionality
    document.getElementById('featured_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
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
    });

    // Character counters
    document.querySelector('input[name="meta_title"]').addEventListener('input', function(e) {
        const counter = e.target.parentElement.querySelector('.form-help');
        counter.textContent = e.target.value.length + ' / 60 characters';
    });

    document.querySelector('textarea[name="meta_description"]').addEventListener('input', function(e) {
        const counter = e.target.parentElement.querySelector('.form-help');
        counter.textContent = e.target.value.length + ' / 160 characters';
    });

    // Delete confirmation
    function confirmDelete() {
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
                window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $post_id; ?>&delete_post=1';
            }
        });
    }

    // Preview post
    function previewPost() {
        const form = document.getElementById('postForm');
        const formData = new FormData(form);

        // Open preview in new window
        const previewWindow = window.open('', '_blank');
        previewWindow.document.write('<html><head><title>Post Preview</title></head><body>');
        previewWindow.document.write('<h1>' + document.querySelector('input[name="title"]').value + '</h1>');
        previewWindow.document.write('<div>' + CKEDITOR.instances.content.getData() + '</div>');
        previewWindow.document.write('</body></html>');
    }

    // Auto-save draft (optional)
    let autoSaveTimeout;
    document.getElementById('postForm').addEventListener('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            if (confirm('Save as draft?')) {
                document.querySelector('button[name="status"][value="draft"]').click();
            }
        }, 30000); // Auto-save after 30 seconds of inactivity
    });
</script>
</body>

</html>