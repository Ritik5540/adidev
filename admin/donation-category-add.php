<?php
// donation-category-add.php
// Include configuration and database connection
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Include header and sidebar (adjust paths as needed)
include 'layout/header.php';
include 'layout/sidebar.php';

// Determine action (list, add, edit)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$is_edit_mode = ($action == 'edit' && isset($_GET['id']));
$edit_id = $is_edit_mode ? intval($_GET['id']) : 0;

// Initialize variables
$category_data = [];
$error_message = '';
$success_message = '';

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submitDonationCategory'])) {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $target_amount = floatval($_POST['target_amount'] ?? 0);
    $collected_amount = floatval($_POST['collected_amount'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
    }
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/donation-categories/';
            // Create directory if not exists
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $extension;
            $target_path = $upload_dir . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Image uploaded successfully
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Only JPG, PNG, WEBP and GIF images are allowed.";
        }
    } elseif ($is_edit_mode && isset($_POST['existing_image']) && empty($_FILES['image']['name'])) {
        // Keep existing image
        $image_name = $_POST['existing_image'];
    }
    
    // Validate required fields
    if (empty($title)) {
        $error_message = "Title is required.";
    } else {
        if ($is_edit_mode) {
            // Update existing record
            $sql = "UPDATE donation_categories SET 
                    title = ?,
                    slug = ?,
                    description = ?,
                    short_description = ?,
                    target_amount = ?,
                    collected_amount = ?,
                    image = ?,
                    is_featured = ?,
                    status = ?,
                    updated_at = NOW()
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssddsiii", $title, $slug, $description, $short_description, $target_amount, $collected_amount, $image_name, $is_featured, $status, $edit_id);
        } else {
            // Insert new record
            $sql = "INSERT INTO donation_categories (title, slug, description, short_description, target_amount, collected_amount, image, is_featured, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssddsii", $title, $slug, $description, $short_description, $target_amount, $collected_amount, $image_name, $is_featured, $status);
        }
        
        if ($stmt->execute()) {
            $success_message = $is_edit_mode ? "Category updated successfully!" : "Category added successfully!";
            // Redirect to list view after successful operation
            header("Location: donation-category-add.php?status=success&message=" . urlencode($success_message));
            exit;
        } else {
            $error_message = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Get image to delete file
    $result = $conn->query("SELECT image FROM donation_categories WHERE id = $delete_id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image']) && file_exists('uploads/donation-categories/' . $row['image'])) {
            unlink('uploads/donation-categories/' . $row['image']);
        }
    }
    
    $conn->query("DELETE FROM donation_categories WHERE id = $delete_id");
    header("Location: donation-category-add.php?status=success&message=Category+deleted+successfully");
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $toggle_id = intval($_GET['toggle_status']);
    $conn->query("UPDATE donation_categories SET status = NOT status, updated_at = NOW() WHERE id = $toggle_id");
    header("Location: donation-category-add.php?status=success&message=Status+updated");
    exit;
}

// Fetch data for edit mode
if ($is_edit_mode && $edit_id > 0) {
    $result = $conn->query("SELECT * FROM donation_categories WHERE id = $edit_id");
    if ($result && $row = $result->fetch_assoc()) {
        $category_data = $row;
    } else {
        $error_message = "Category not found.";
        $is_edit_mode = false;
    }
}

// Pagination for list view
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_condition = " WHERE title LIKE '%$search%' OR description LIKE '%$search%' OR short_description LIKE '%$search%' ";
}

// Get total records for pagination
$total_result = $conn->query("SELECT COUNT(*) as total FROM donation_categories $search_condition");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch categories for list view
$categories_result = null;
if ($action == 'list') {
    $sql = "SELECT * FROM donation_categories $search_condition ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $categories_result = $conn->query($sql);
}
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status']; ?>">
                <i class="fas fa-<?php echo $_GET['status'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($_GET['message']); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($action == 'list'): ?>
            <!-- List View -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-hand-holding-heart"></i>
                        Donation Categories
                    </h2>
                    <div class="card-actions">
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Category
                        </a>
                    </div>
                </div>
                
                <!-- Search and Filter -->
                <div class="search-bar">
                    <form method="GET" action="">
                        <div class="search-group">
                            <input type="text" 
                                   name="search" 
                                   placeholder="Search categories..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="search-input">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="?" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <?php
                    $total_cats = $conn->query("SELECT COUNT(*) as c FROM donation_categories")->fetch_assoc()['c'];
                    $active_cats = $conn->query("SELECT COUNT(*) as c FROM donation_categories WHERE status = 1")->fetch_assoc()['c'];
                    $featured_cats = $conn->query("SELECT COUNT(*) as c FROM donation_categories WHERE is_featured = 1")->fetch_assoc()['c'];
                    $total_target = $conn->query("SELECT SUM(target_amount) as total FROM donation_categories")->fetch_assoc()['total'];
                    ?>
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-number"><?php echo $total_cats; ?></div>
                        <div class="stat-label">Total Categories</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon posts">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo $active_cats; ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon featured">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number"><?php echo $featured_cats; ?></div>
                        <div class="stat-label">Featured</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon amount">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-number">₹<?php echo number_format($total_target ?? 0); ?></div>
                        <div class="stat-label">Total Target</div>
                    </div>
                </div>
                
                <!-- Categories Table -->
                <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 100px;">Image</th>
                                    <th>Title</th>
                                    <th>Target</th>
                                    <th>Collected</th>
                                    <th>Progress</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $categories_result->fetch_assoc()): 
                                    $progress = ($row['target_amount'] > 0) ? round(($row['collected_amount'] / $row['target_amount']) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if (!empty($row['image'])): ?>
                                                <img src="uploads/donation-categories/<?php echo $row['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($row['title']); ?>"
                                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <div class="no-image">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                                            <small class="text-muted">Slug: <?php echo $row['slug']; ?></small>
                                        </td>
                                        <td>₹<?php echo number_format($row['target_amount'], 2); ?></td>
                                        <td>₹<?php echo number_format($row['collected_amount'], 2); ?></td>
                                        <td>
                                            <div class="progress-container">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                                </div>
                                                <span class="progress-text"><?php echo $progress; ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($row['is_featured']): ?>
                                                <span class="badge badge-featured">
                                                    <i class="fas fa-star"></i> Featured
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Regular</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $row['status'] ? 'badge-success' : 'badge-secondary'; ?>">
                                                <?php echo $row['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?toggle_status=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm <?php echo $row['status'] ? 'btn-warning' : 'btn-success'; ?>"
                                                   title="<?php echo $row['status'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $row['status'] ? 'times' : 'check'; ?>"></i>
                                                </a>
                                                <a href="?action=edit&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirmDelete('<?php echo addslashes($row['title']); ?>')"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                   class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" 
                                   class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h3>No Donation Categories Found</h3>
                        <p>Start by adding your first donation category to begin collecting funds.</p>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Category
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Form -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-<?php echo $is_edit_mode ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $is_edit_mode ? 'Edit Donation Category' : 'Add New Donation Category'; ?>
                    </h2>
                    <div class="card-actions">
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data" id="categoryForm">
                    <input type="hidden" name="submitDonationCategory" value="1">
                    
                    <div class="form-grid">
                        <!-- Left Column - Main Fields -->
                        <div class="form-main">
                            <div class="form-group">
                                <label class="form-label required">Category Title</label>
                                <input type="text" 
                                       name="title" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($category_data['title'] ?? ''); ?>"
                                       placeholder="e.g., Education for Children"
                                       required
                                       id="titleInput">
                                <div class="form-help">Enter a clear, descriptive title for this donation category.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Slug</label>
                                <input type="text" 
                                       name="slug" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($category_data['slug'] ?? ''); ?>"
                                       placeholder="auto-generated if empty"
                                       id="slugInput">
                                <div class="form-help">URL-friendly version of the title. Leave empty to auto-generate.</div>
                                <div class="slug-preview" id="slugPreview"></div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Short Description</label>
                                <input type="text" 
                                       name="short_description" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($category_data['short_description'] ?? ''); ?>"
                                       placeholder="Brief summary (max 500 chars)"
                                       maxlength="500">
                                <div class="form-help">A short description that appears in category cards.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Full Description</label>
                                <textarea name="description" 
                                          class="form-control" 
                                          rows="5"
                                          placeholder="Detailed description of this donation category..."><?php echo htmlspecialchars($category_data['description'] ?? ''); ?></textarea>
                                <div class="form-help">Provide complete details about how donations will be used.</div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Target Amount (₹)</label>
                                    <input type="number" 
                                           name="target_amount" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($category_data['target_amount'] ?? '0.00'); ?>"
                                           step="0.01"
                                           min="0"
                                           id="targetAmount">
                                    <div class="form-help">Goal amount to raise.</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Collected Amount (₹)</label>
                                    <input type="number" 
                                           name="collected_amount" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($category_data['collected_amount'] ?? '0.00'); ?>"
                                           step="0.01"
                                           min="0"
                                           id="collectedAmount">
                                    <div class="form-help">Amount already collected.</div>
                                </div>
                            </div>
                            
                            <!-- Progress preview -->
                            <div class="progress-preview" id="progressPreview" style="display: none;">
                                <label class="form-label">Progress Preview</label>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" id="progressFill" style="width: 0%;"></div>
                                    </div>
                                    <span class="progress-text" id="progressText">0%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Sidebar Fields -->
                        <div class="form-sidebar">
                            <!-- Image Upload -->
                            <div class="sidebar-section">
                                <h4 class="sidebar-section-title">
                                    <i class="fas fa-image"></i>
                                    Category Image
                                </h4>
                                
                                <div class="form-group">
                                    <label class="form-label">Upload Image</label>
                                    <input type="file" 
                                           name="image" 
                                           id="imageInput" 
                                           class="form-control" 
                                           accept="image/jpeg,image/png,image/webp,image/gif">
                                    <div class="form-help">Recommended: 800x600px, max 2MB. JPG, PNG, WEBP or GIF.</div>
                                    
                                    <!-- Image Preview -->
                                    <div class="image-preview-container" id="imagePreviewContainer">
                                        <?php if ($is_edit_mode && !empty($category_data['image'])): ?>
                                            <input type="hidden" name="existing_image" value="<?php echo $category_data['image']; ?>">
                                            <div class="current-image">
                                                <img src="uploads/donation-categories/<?php echo $category_data['image']; ?>" 
                                                     alt="Current image"
                                                     id="currentImage">
                                                <button type="button" class="btn-remove-image" onclick="removeImage()">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder" id="imagePlaceholder">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click to upload or drag and drop</p>
                                            </div>
                                        <?php endif; ?>
                                        <img id="newImagePreview" class="new-image-preview" style="display: none;">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status and Featured -->
                            <div class="sidebar-section">
                                <h4 class="sidebar-section-title">
                                    <i class="fas fa-cog"></i>
                                    Settings
                                </h4>
                                
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <div class="toggle-switch">
                                        <input type="checkbox" 
                                               name="status" 
                                               id="statusToggle" 
                                               value="1"
                                               <?php echo (!isset($category_data['status']) || $category_data['status'] == 1) ? 'checked' : ''; ?>>
                                        <label for="statusToggle" class="toggle-label">
                                            <span class="toggle-on">Active</span>
                                            <span class="toggle-off">Inactive</span>
                                        </label>
                                    </div>
                                    <div class="form-help">Active categories are visible on the donation page.</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Featured</label>
                                    <div class="toggle-switch">
                                        <input type="checkbox" 
                                               name="is_featured" 
                                               id="featuredToggle" 
                                               value="1"
                                               <?php echo ($category_data['is_featured'] ?? 0) == 1 ? 'checked' : ''; ?>>
                                        <label for="featuredToggle" class="toggle-label">
                                            <span class="toggle-on">Featured</span>
                                            <span class="toggle-off">Regular</span>
                                        </label>
                                    </div>
                                    <div class="form-help">Featured categories appear prominently on the homepage.</div>
                                </div>
                            </div>
                            
                            <!-- Metadata (for edit mode) -->
                            <?php if ($is_edit_mode): ?>
                                <div class="sidebar-section">
                                    <h4 class="sidebar-section-title">
                                        <i class="fas fa-info-circle"></i>
                                        Metadata
                                    </h4>
                                    
                                    <div class="meta-info">
                                        <div class="meta-item">
                                            <span class="meta-label">ID:</span>
                                            <span class="meta-value">#<?php echo $category_data['id']; ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Created:</span>
                                            <span class="meta-value"><?php echo date('d M Y, H:i', strtotime($category_data['created_at'])); ?></span>
                                        </div>
                                        <?php if (!empty($category_data['updated_at'])): ?>
                                            <div class="meta-item">
                                                <span class="meta-label">Updated:</span>
                                                <span class="meta-value"><?php echo date('d M Y, H:i', strtotime($category_data['updated_at'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            <?php echo $is_edit_mode ? 'Update Category' : 'Add Category'; ?>
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <?php if ($is_edit_mode): ?>
                            <a href="?delete=<?php echo $category_data['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirmDelete('<?php echo addslashes($category_data['title']); ?>')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Brand colors: #1a685b, #ffac00, #051311 */
    :root {
        --brand-teal: #1a685b;
        --brand-gold: #ffac00;
        --brand-dark: #051311;
        --brand-teal-light: #e0f3f0;
        --brand-gold-light: #fff4e0;
        --brand-dark-light: #1a3a36;
    }
    
    /* Global Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
        background-color: #f4f9f8;
        color: var(--brand-dark);
    }
    
    /* Card Styles */
    .card {
        background: white;
        border-radius: 0px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(5,19,17,0.08);
        border: 1px solid rgba(26,104,91,0.15);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--brand-gold);
    }
    
    .card-title {
        font-size: 24px;
        font-weight: 600;
        color: var(--brand-teal);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .card-title i {
        color: var(--brand-gold);
    }
    
    /* Button Styles */
    .btn {
        padding: 12px 24px;
        border-radius: 10px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        letter-spacing: 0.3px;
    }
    
    .btn-primary {
        background: var(--brand-teal);
        color: white;
        box-shadow: 0 5px 15px rgba(26,104,91,0.3);
    }
    
    .btn-primary:hover {
        background: #0f4f44;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(26,104,91,0.4);
    }
    
    .btn-success {
        background: var(--brand-teal);
        color: white;
    }
    
    .btn-success:hover {
        background: #0f4f44;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: #f0f5f4;
        color: var(--brand-dark);
        border: 1px solid var(--brand-teal);
    }
    
    .btn-secondary:hover {
        background: var(--brand-teal-light);
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
    }
    
    .btn-danger:hover {
        background: #bb2d3b;
        transform: translateY(-2px);
    }
    
    .btn-warning {
        background: var(--brand-gold);
        color: var(--brand-dark);
    }
    
    .btn-warning:hover {
        background: #e69c00;
    }
    
    .btn-outline {
        background: transparent;
        border: 2px solid var(--brand-teal);
        color: var(--brand-teal);
    }
    
    .btn-outline:hover {
        background: var(--brand-teal);
        color: white;
    }
    
    .btn-sm {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    /* Form Styles */
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--brand-dark);
        font-size: 15px;
    }
    
    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 18px;
        border: 2px solid #dde9e6;
        border-radius: 10px;;
        font-size: 15px;
        transition: all 0.3s;
        background: #f9fdfc;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--brand-teal);
        background: white;
        box-shadow: 0 0 0 4px rgba(26,104,91,0.1);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    .form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #5f7d76;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }
    
    /* Alert Styles */
    .alert {
        padding: 16px 24px;
        border-radius: 10px;;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
        animation: slideIn 0.3s ease;
        border-left: 6px solid transparent;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
        background: #e0f3ef;
        color: #0f4f44;
        border-left-color: var(--brand-teal);
    }
    
    .alert-error {
        background: #ffe6e6;
        color: #b02a37;
        border-left-color: #dc3545;
    }
    
    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
        border-radius: 10px;;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }
    
    .data-table thead {
        background: var(--brand-teal);
        color: white;
    }
    
    .data-table th {
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: #ffffff !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .data-table tbody tr {
        border-bottom: 1px solid #e2f0ec;
        transition: all 0.2s;
    }
    
    .data-table tbody tr:hover {
        background-color: var(--brand-teal-light);
    }
    
    .data-table td {
        padding: 16px 20px;
        vertical-align: middle;
    }
    
    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    
    .badge-success {
        background: var(--brand-teal);
        color: white;
    }
    
    .badge-secondary {
        background: #e9ecef;
        color: #495057;
    }
    
    .badge-featured {
        background: var(--brand-gold);
        color: var(--brand-dark);
    }
    
    .badge-featured i {
        margin-right: 4px;
    }
    
    /* Progress Bar Styles */
    .progress-container {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 150px;
    }
    
    .progress-bar {
        flex: 1;
        height: 8px;
        background: #e2f0ec;
        border-radius: 10px;;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--brand-teal) 0%, var(--brand-gold) 100%);
        border-radius: 10px;;
        transition: width 0.3s;
    }
    
    .progress-text {
        font-size: 13px;
        font-weight: 600;
        color: var(--brand-teal);
        min-width: 45px;
    }
    
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(145deg, #ffffff 0%, #f9fdfc 100%);
        padding: 24px;
        border-radius: 10px;;
        text-align: center;
        box-shadow: 0 5px 15px rgba(5,19,17,0.05);
        border: 1px solid rgba(26,104,91,0.15);
        transition: all 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--brand-gold);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
    }
    
    .stat-icon.total {
        background: rgba(26,104,91,0.1);
        color: var(--brand-teal);
    }
    
    .stat-icon.posts {
        background: rgba(255,172,0,0.1);
        color: var(--brand-gold);
    }
    
    .stat-icon.featured {
        background: rgba(255,215,0,0.1);
        color: #d4a500;
    }
    
    .stat-icon.amount {
        background: rgba(5,19,17,0.1);
        color: var(--brand-dark);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--brand-teal);
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #5f7d76;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Image Upload Styles */
    .image-preview-container {
        margin-top: 15px;
        min-height: 200px;
        border: 2px dashed var(--brand-teal);
        border-radius: 10px;;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        background: var(--brand-teal-light);
    }
    
    .image-placeholder {
        text-align: center;
        padding: 40px;
        color: var(--brand-teal);
    }
    
    .image-placeholder i {
        font-size: 48px;
        margin-bottom: 10px;
    }
    
    .current-image {
        position: relative;
        width: 100%;
        height: 100%;
    }
    
    .current-image img {
        width: 100%;
        max-height: 250px;
        object-fit: cover;
    }
    
    .btn-remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .new-image-preview {
        max-width: 100%;
        max-height: 250px;
        object-fit: contain;
    }
    
    /* Search Bar */
    .search-bar {
        margin-bottom: 25px;
    }
    
    .search-group {
        display: flex;
        gap: 10px;
        max-width: 500px;
    }
    
    .search-input {
        flex: 1;
        padding: 12px 20px;
        border: 2px solid #dde9e6;
        border-radius: 10px;
        font-size: 15px;
    }
    
    .search-input:focus {
        outline: none;
        border-color: var(--brand-teal);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 80px;
        color: var(--brand-teal-light);
        margin-bottom: 20px;
    }
    
    .empty-state h3 {
        color: var(--brand-teal);
        margin-bottom: 10px;
        font-size: 24px;
    }
    
    .empty-state p {
        color: #5f7d76;
        max-width: 400px;
        margin: 0 auto 25px;
    }
    
    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    
    .toggle-label {
        display: flex;
        background: #e2f0ec;
        border-radius: 10px;
        padding: 4px;
        cursor: pointer;
        position: relative;
        border: 2px solid var(--brand-teal);
    }
    
    .toggle-label span {
        flex: 1;
        text-align: center;
        padding: 8px 16px;
        border-radius: 30px;
        transition: all 0.3s;
        font-weight: 600;
        font-size: 14px;
    }
    
    .toggle-on {
        background: var(--brand-teal);
        color: white;
    }
    
    .toggle-off {
        background: transparent;
        color: var(--brand-dark);
    }
    
    input:checked + .toggle-label .toggle-on {
        background: var(--brand-teal);
        color: white;
    }
    
    input:checked + .toggle-label .toggle-off {
        background: transparent;
        color: var(--brand-dark);
    }
    
    input:not(:checked) + .toggle-label .toggle-on {
        background: transparent;
        color: var(--brand-dark);
    }
    
    input:not(:checked) + .toggle-label .toggle-off {
        background: #e2f0ec;
        color: var(--brand-dark);
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 30px;
    }
    
    .page-link {
        padding: 10px 18px;
        border: 2px solid var(--brand-teal);
        background: white;
        color: var(--brand-teal);
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.2s;
        font-weight: 600;
    }
    
    .page-link:hover {
        background: var(--brand-teal-light);
    }
    
    .page-link.active {
        background: var(--brand-teal);
        color: white;
        border-color: var(--brand-teal);
    }
    
    /* Sidebar Sections */
    .sidebar-section {
        background: #f9fdfc;
        border-radius: 10px;;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(26,104,91,0.15);
    }
    
    .sidebar-section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--brand-teal);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--brand-gold);
    }
    
    .meta-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .meta-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed #dde9e6;
    }
    
    .meta-label {
        color: #5f7d76;
        font-size: 13px;
    }
    
    .meta-value {
        font-weight: 600;
        color: var(--brand-dark);
    }
    
    .no-image {
        width: 60px;
        height: 60px;
        background: #f0f5f4;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a0c0b9;
    }
    
    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid var(--brand-gold);
    }
    
    /* Progress Preview */
    .progress-preview {
        margin-top: 20px;
        padding: 15px;
        background: #f9fdfc;
        border-radius: 10px;;
        border: 1px solid var(--brand-teal);
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .card {
            padding: 20px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Auto-generate slug from title
    document.getElementById('titleInput')?.addEventListener('input', function() {
        const title = this.value;
        const slugInput = document.getElementById('slugInput');
        const slugPreview = document.getElementById('slugPreview');
        
        if (slugInput.value === '') {
            const slug = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/--+/g, '-')
                .trim();
            slugInput.value = slug;
        }
        
        if (slugPreview) {
            slugPreview.innerHTML = `<span>Preview:</span> <strong>/donate/${slugInput.value || 'category'}</strong>`;
        }
    });
    
    // Update progress preview
    function updateProgress() {
        const target = parseFloat(document.getElementById('targetAmount')?.value) || 0;
        const collected = parseFloat(document.getElementById('collectedAmount')?.value) || 0;
        const progressPreview = document.getElementById('progressPreview');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        
        if (target > 0) {
            const progress = (collected / target) * 100;
            const roundedProgress = Math.min(100, Math.round(progress * 10) / 10);
            
            progressFill.style.width = roundedProgress + '%';
            progressText.textContent = roundedProgress + '%';
            progressPreview.style.display = 'block';
        } else {
            progressPreview.style.display = 'none';
        }
    }
    
    document.getElementById('targetAmount')?.addEventListener('input', updateProgress);
    document.getElementById('collectedAmount')?.addEventListener('input', updateProgress);
    
    // Image preview
    document.getElementById('imageInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const currentImage = document.getElementById('currentImage');
                const placeholder = document.getElementById('imagePlaceholder');
                const newPreview = document.getElementById('newImagePreview');
                
                if (currentImage) currentImage.style.display = 'none';
                if (placeholder) placeholder.style.display = 'none';
                
                newPreview.src = e.target.result;
                newPreview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Remove image
    function removeImage() {
        const currentImage = document.getElementById('currentImage');
        const placeholder = document.getElementById('imagePlaceholder');
        const newPreview = document.getElementById('newImagePreview');
        const fileInput = document.getElementById('imageInput');
        
        if (currentImage) currentImage.style.display = 'none';
        if (newPreview) newPreview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        
        if (fileInput) fileInput.value = '';
    }
    
    // Confirm delete
    function confirmDelete(name) {
        return Swal.fire({
            title: 'Delete Category?',
            html: `Are you sure you want to delete "<strong>${name}</strong>"?<br><br>
                  <small style="color: #dc3545;">This action cannot be undone!</small>`,
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
    
    // Form validation
    document.getElementById('categoryForm')?.addEventListener('submit', function(e) {
        const target = parseFloat(document.getElementById('targetAmount')?.value) || 0;
        const collected = parseFloat(document.getElementById('collectedAmount')?.value) || 0;
        
        if (collected > target) {
            e.preventDefault();
            Swal.fire({
                title: 'Invalid Amount',
                text: 'Collected amount cannot exceed target amount.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        return true;
    });
    
    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    }, 5000);
    
    // Initialize progress preview on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateProgress();
        
        // Focus first input
        const firstInput = document.querySelector('input[name="title"]');
        if (firstInput) firstInput.focus();
        
        // Initialize slug preview
        const slugInput = document.getElementById('slugInput');
        const slugPreview = document.getElementById('slugPreview');
        if (slugInput && slugPreview) {
            slugPreview.innerHTML = `<span>Preview:</span> <strong>/donate/${slugInput.value || 'category'}</strong>`;
        }
    });
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>