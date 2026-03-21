<?php
// gallery.php - Manage gallery images
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Include header and sidebar (adjust paths as needed)
include 'layout/header.php';
include 'layout/sidebar.php';

// Determine action (list, add, edit, bulk)
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$is_edit_mode = ($action == 'edit' && isset($_GET['id']));
$edit_id = $is_edit_mode ? intval($_GET['id']) : 0;

// Initialize variables
$gallery_data = [];
$error_message = '';
$success_message = '';

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submitGallery'])) {
        // Single image upload
        $title = trim($_POST['title'] ?? '');
        $gallery_type = trim($_POST['gallery_type'] ?? 'image');
        $description = trim($_POST['description'] ?? '');
        
        // Handle image upload
        $image_path = '';
        $upload_error = '';
        
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
            $file_type = $_FILES['image_file']['type'];
            $file_size = $_FILES['image_file']['size'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_type, $allowed_types)) {
                $upload_error = "Only JPG, PNG, WEBP and GIF images are allowed.";
            } elseif ($file_size > $max_size) {
                $upload_error = "Image size must be less than 5MB.";
            } else {
                $upload_dir = 'uploads/gallery/';
                // Create directory if not exists
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
                $image_path = time() . '_' . uniqid() . '.' . $extension;
                $target_path = $upload_dir . $image_path;
                
                if (!move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
                    $upload_error = "Failed to upload image. Check directory permissions.";
                }
            }
        } elseif ($is_edit_mode && isset($_POST['existing_image']) && empty($_FILES['image_file']['name'])) {
            // Keep existing image
            $image_path = $_POST['existing_image'];
        } elseif (!$is_edit_mode) {
            $upload_error = "Image is required.";
        }
        
        // Validate required fields
        if (empty($title)) {
            $error_message = "Title is required.";
        } elseif (!empty($upload_error)) {
            $error_message = $upload_error;
        } else {
            if ($is_edit_mode) {
                // Update existing record
                $sql = "UPDATE galleries SET 
                        title = ?,
                        gallery_type = ?,
                        image_path = ?,
                        description = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $title, $gallery_type, $image_path, $description, $edit_id);
            } else {
                // Insert new record
                $sql = "INSERT INTO galleries (title, gallery_type, image_path, description, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $title, $gallery_type, $image_path, $description);
            }
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = $is_edit_mode ? "Gallery item updated successfully!" : "Gallery item added successfully!";
                // Redirect to list view after successful operation
                header("Location: gallery.php");
                exit;
            } else {
                $error_message = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['submitBulkGallery'])) {
        // Bulk image upload
        $gallery_type = trim($_POST['bulk_gallery_type'] ?? 'image');
        $base_title = trim($_POST['base_title'] ?? 'Gallery');
        $bulk_description = trim($_POST['bulk_description'] ?? '');
        $max_uploads = 10; // Maximum 10 images
        
        $upload_dir = 'uploads/gallery/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $uploaded_count = 0;
        $failed_count = 0;
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Get current count for auto-numbering
        $count_result = $conn->query("SELECT COUNT(*) as total FROM galleries");
        $count_row = $count_result->fetch_assoc();
        $current_count = $count_row['total'];
        
        if (isset($_FILES['bulk_images'])) {
            $files = $_FILES['bulk_images'];
            $file_count = count($files['name']);
            
            // Limit to 10 files
            $file_count = min($file_count, $max_uploads);
            
            for ($i = 0; $i < $file_count; $i++) {
                if ($files['error'][$i] == 0) {
                    $file_type = $files['type'][$i];
                    $file_size = $files['size'][$i];
                    
                    if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                        $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $image_path = time() . '_' . uniqid() . '.' . $extension;
                        $target_path = $upload_dir . $image_path;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $target_path)) {
                            // Auto-generate title with count
                            $current_count++;
                            $title = $base_title . ' ' . $current_count;
                            
                            // Insert into database
                            $sql = "INSERT INTO galleries (title, gallery_type, image_path, description, created_at) 
                                    VALUES (?, ?, ?, ?, NOW())";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssss", $title, $gallery_type, $image_path, $bulk_description);
                            
                            if ($stmt->execute()) {
                                $uploaded_count++;
                            } else {
                                $failed_count++;
                                // Delete uploaded image if database insert fails
                                unlink($target_path);
                            }
                            $stmt->close();
                        } else {
                            $failed_count++;
                        }
                    } else {
                        $failed_count++;
                    }
                } else {
                    $failed_count++;
                }
            }
            
            if ($uploaded_count > 0) {
                $_SESSION['success_message'] = "Successfully uploaded $uploaded_count images. Failed: $failed_count";
            } else {
                $error_message = "No images were uploaded. Please check file types and sizes.";
            }
            
            header("Location: gallery.php");
            exit;
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Get image to delete file
    $result = $conn->query("SELECT image_path FROM galleries WHERE id = $delete_id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['image_path']) && file_exists('uploads/gallery/' . $row['image_path'])) {
            unlink('uploads/gallery/' . $row['image_path']);
        }
    }
    
    $conn->query("DELETE FROM galleries WHERE id = $delete_id");
    $_SESSION['success_message'] = "Gallery item deleted successfully";
    header("Location: gallery.php");
    exit;
}

// Fetch data for edit mode
if ($is_edit_mode && $edit_id > 0) {
    $result = $conn->query("SELECT * FROM galleries WHERE id = $edit_id");
    if ($result && $row = $result->fetch_assoc()) {
        $gallery_data = $row;
    } else {
        $error_message = "Gallery item not found.";
        $is_edit_mode = false;
    }
}

// Pagination for list view
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12; // Show 12 items per page (grid layout)
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_condition = " WHERE title LIKE '%$search%' OR description LIKE '%$search%' OR gallery_type LIKE '%$search%' ";
}

// Filter by type
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
if (!empty($type_filter) && empty($search_condition)) {
    $type_filter = $conn->real_escape_string($type_filter);
    $search_condition = " WHERE gallery_type = '$type_filter' ";
} elseif (!empty($type_filter)) {
    $type_filter = $conn->real_escape_string($type_filter);
    $search_condition .= " AND gallery_type = '$type_filter' ";
}

// Get total records for pagination
$total_result = $conn->query("SELECT COUNT(*) as total FROM galleries $search_condition");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch gallery items for list view
$gallery_items = [];
if ($action == 'list') {
    $sql = "SELECT * FROM galleries $search_condition ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $gallery_items[] = $row;
        }
    }
}

// Get unique gallery types for filter dropdown
$types_result = $conn->query("SELECT DISTINCT gallery_type FROM galleries ORDER BY gallery_type");
$gallery_types = [];
if ($types_result) {
    while ($row = $types_result->fetch_assoc()) {
        $gallery_types[] = $row['gallery_type'];
    }
}

// Get session messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <!-- Alert Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($action == 'list'): ?>
            <!-- List View - Gallery Grid -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-images"></i>
                    Gallery Management
                </h2>
                <div class="header-actions">
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Image
                    </a>
                    <a href="?action=bulk" class="btn btn-primary" style="background: var(--gold); border-color: var(--gold); color: var(--dark);">
                        <i class="fas fa-layer-group"></i> Bulk Upload (Max 10)
                    </a>
                </div>
            </div>
            
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-box">
                    <span class="stat-label">Total Images</span>
                    <span class="stat-number"><?php echo $total_records; ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Types</span>
                    <span class="stat-number"><?php echo count($gallery_types); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Last Added</span>
                    <span class="stat-number">
                        <?php 
                        $last = $conn->query("SELECT created_at FROM galleries ORDER BY id DESC LIMIT 1");
                        echo ($last && $last->num_rows) ? date('d M', strtotime($last->fetch_assoc()['created_at'])) : 'N/A';
                        ?>
                    </span>
                </div>
            </div>
            
            <!-- Search and Filter Bar -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <div class="search-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="search" 
                               placeholder="Search images..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input">
                    </div>
                    
                    <div class="filter-group">
                        <select name="type" class="filter-select">
                            <option value="">All Types</option>
                            <?php foreach ($gallery_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>" 
                                    <?php echo $type_filter == $type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Apply</button>
                    
                    <?php if (!empty($search) || !empty($type_filter)): ?>
                        <a href="?" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Gallery Grid -->
            <?php if (!empty($gallery_items)): ?>
                <div class="gallery-grid">
                    <?php foreach ($gallery_items as $item): ?>
                        <div class="gallery-card">
                            <div class="card-image">
                                <?php if (!empty($item['image_path']) && file_exists('uploads/gallery/' . $item['image_path'])): ?>
                                    <img src="uploads/gallery/<?php echo $item['image_path']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="image-overlay">
                                    <span class="image-type"><?php echo htmlspecialchars($item['gallery_type']); ?></span>
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <h3 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                
                                <?php if (!empty($item['description'])): ?>
                                    <p class="card-description">
                                        <?php echo htmlspecialchars(substr($item['description'], 0, 60)); ?>
                                        <?php if (strlen($item['description']) > 60): ?>...<?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="card-footer">
                                    <span class="card-date">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                    </span>
                                    
                                    <div class="card-actions">
                                        <a href="?action=edit&id=<?php echo $item['id']; ?>" 
                                           class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $item['id']; ?>" 
                                           class="btn-icon delete" 
                                           onclick="return confirmDelete('<?php echo addslashes($item['title']); ?>')"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?>" 
                               class="page-link">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?>" 
                               class="page-link">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>No Gallery Images Found</h3>
                    <p>Start by adding your first image to the gallery.</p>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Image
                    </a>
                </div>
            <?php endif; ?>
            
        <?php elseif ($action == 'add'): ?>
            <!-- Add Form -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-plus"></i>
                    Add New Gallery Item
                </h2>
                <div class="header-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                </div>
            </div>
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data" class="gallery-form">
                    <input type="hidden" name="submitGallery" value="1">
                    
                    <div class="form-row">
                        <!-- Main Form -->
                        <div class="form-main">
                            <div class="form-group">
                                <label class="form-label required">Title</label>
                                <input type="text" 
                                       name="title" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                       placeholder="Enter image title"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gallery Type</label>
                                <select name="gallery_type" class="form-control">
                                    <option value="image" <?php echo ($_POST['gallery_type'] ?? '') == 'image' ? 'selected' : ''; ?>>Image</option>
                                    <option value="event" <?php echo ($_POST['gallery_type'] ?? '') == 'event' ? 'selected' : ''; ?>>Event</option>
                                    <option value="program" <?php echo ($_POST['gallery_type'] ?? '') == 'program' ? 'selected' : ''; ?>>Program</option>
                                    <option value="news" <?php echo ($_POST['gallery_type'] ?? '') == 'news' ? 'selected' : ''; ?>>News</option>
                                    <option value="video" <?php echo ($_POST['gallery_type'] ?? '') == 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="campaign" <?php echo ($_POST['gallery_type'] ?? '') == 'campaign' ? 'selected' : ''; ?>>Campaign</option>
                                    <option value="achievement" <?php echo ($_POST['gallery_type'] ?? '') == 'achievement' ? 'selected' : ''; ?>>Achievement</option>
                                    <option value="other" <?php echo ($_POST['gallery_type'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <div class="form-help">Categorize your image for better organization.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" 
                                          class="form-control" 
                                          rows="4"
                                          placeholder="Add a description for this image (optional)"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Image Upload Sidebar -->
                        <div class="form-sidebar">
                            <div class="image-upload-box">
                                <label class="form-label required">Image</label>
                                
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" 
                                           name="image_file" 
                                           id="imageInput" 
                                           accept="image/jpeg,image/png,image/webp,image/gif"
                                           style="display: none;">
                                    
                                    <div class="upload-preview" id="uploadPreview">
                                        <div class="upload-placeholder" onclick="document.getElementById('imageInput').click();">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to upload</p>
                                            <span>JPG, PNG, WEBP, GIF (max 5MB)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-help">Recommended aspect ratio: 16:9 or 4:3</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Add to Gallery
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
            
        <?php elseif ($action == 'edit'): ?>
            <!-- Edit Form -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-edit"></i>
                    Edit Gallery Item
                </h2>
                <div class="header-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                </div>
            </div>
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data" class="gallery-form">
                    <input type="hidden" name="submitGallery" value="1">
                    
                    <div class="form-row">
                        <!-- Main Form -->
                        <div class="form-main">
                            <div class="form-group">
                                <label class="form-label required">Title</label>
                                <input type="text" 
                                       name="title" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($gallery_data['title'] ?? ''); ?>"
                                       placeholder="Enter image title"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gallery Type</label>
                                <select name="gallery_type" class="form-control">
                                    <option value="image" <?php echo ($gallery_data['gallery_type'] ?? '') == 'image' ? 'selected' : ''; ?>>Image</option>
                                    <option value="event" <?php echo ($gallery_data['gallery_type'] ?? '') == 'event' ? 'selected' : ''; ?>>Event</option>
                                    <option value="program" <?php echo ($gallery_data['gallery_type'] ?? '') == 'program' ? 'selected' : ''; ?>>Program</option>
                                    <option value="news" <?php echo ($gallery_data['gallery_type'] ?? '') == 'news' ? 'selected' : ''; ?>>News</option>
                                    <option value="video" <?php echo ($gallery_data['gallery_type'] ?? '') == 'video' ? 'selected' : ''; ?>>Video</option>
                                    <option value="campaign" <?php echo ($gallery_data['gallery_type'] ?? '') == 'campaign' ? 'selected' : ''; ?>>Campaign</option>
                                    <option value="achievement" <?php echo ($gallery_data['gallery_type'] ?? '') == 'achievement' ? 'selected' : ''; ?>>Achievement</option>
                                    <option value="other" <?php echo ($gallery_data['gallery_type'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <div class="form-help">Categorize your image for better organization.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="description" 
                                          class="form-control" 
                                          rows="4"
                                          placeholder="Add a description for this image (optional)"><?php echo htmlspecialchars($gallery_data['description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Image Upload Sidebar -->
                        <div class="form-sidebar">
                            <div class="image-upload-box">
                                <label class="form-label required">Image</label>
                                
                                <?php if (!empty($gallery_data['image_path'])): ?>
                                    <input type="hidden" name="existing_image" value="<?php echo $gallery_data['image_path']; ?>">
                                <?php endif; ?>
                                
                                <div class="upload-area" id="uploadArea">
                                    <input type="file" 
                                           name="image_file" 
                                           id="imageInput" 
                                           accept="image/jpeg,image/png,image/webp,image/gif"
                                           style="display: none;">
                                    
                                    <div class="upload-preview" id="uploadPreview">
                                        <?php if (!empty($gallery_data['image_path']) && file_exists('uploads/gallery/' . $gallery_data['image_path'])): ?>
                                            <img src="uploads/gallery/<?php echo $gallery_data['image_path']; ?>" 
                                                 alt="Preview"
                                                 class="preview-img">
                                            <button type="button" class="remove-image" onclick="removeImage()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <div class="upload-placeholder" onclick="document.getElementById('imageInput').click();">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <p>Click to upload</p>
                                                <span>JPG, PNG, WEBP, GIF (max 5MB)</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-help">Leave empty to keep current image</div>
                            </div>
                            
                            <div class="info-box">
                                <div class="info-row">
                                    <span class="info-label">ID:</span>
                                    <span class="info-value">#<?php echo $gallery_data['id']; ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Added:</span>
                                    <span class="info-value"><?php echo date('d M Y, H:i', strtotime($gallery_data['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Update Item
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <a href="?delete=<?php echo $gallery_data['id']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirmDelete('<?php echo addslashes($gallery_data['title']); ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </form>
            </div>
            
        <?php elseif ($action == 'bulk'): ?>
            <!-- Bulk Upload Form -->
            <div class="header-bar">
                <h2 class="page-title">
                    <i class="fas fa-layer-group"></i>
                    Bulk Upload Images (Max 10)
                </h2>
                <div class="header-actions">
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Gallery
                    </a>
                </div>
            </div>
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data" class="gallery-form" id="bulkUploadForm">
                    <input type="hidden" name="submitBulkGallery" value="1">
                    
                    <div class="form-row">
                        <!-- Main Form -->
                        <div class="form-main">
                            <div class="form-group">
                                <label class="form-label required">Base Title</label>
                                <input type="text" 
                                       name="base_title" 
                                       class="form-control" 
                                       value="Gallery"
                                       placeholder="e.g., Gallery, Event, Campaign"
                                       required>
                                <div class="form-help">Images will be auto-numbered: "Base Title 1", "Base Title 2", etc.</div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Gallery Type</label>
                                <select name="bulk_gallery_type" class="form-control">
                                    <option value="image">Image</option>
                                    <option value="event">Event</option>
                                    <option value="program">Program</option>
                                    <option value="news">News</option>
                                    <option value="video">Video</option>
                                    <option value="campaign">Campaign</option>
                                    <option value="achievement">Achievement</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Common Description</label>
                                <textarea name="bulk_description" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="This description will be applied to all uploaded images (optional)"></textarea>
                            </div>
                        </div>
                        
                        <!-- Bulk Upload Sidebar -->
                        <div class="form-sidebar">
                            <div class="image-upload-box">
                                <label class="form-label required">Select Images (Max 10)</label>
                                
                                <div class="bulk-upload-area" id="bulkUploadArea">
                                    <input type="file" 
                                           name="bulk_images[]" 
                                           id="bulkImageInput" 
                                           accept="image/jpeg,image/png,image/webp,image/gif"
                                           multiple
                                           style="display: none;">
                                    
                                    <div class="bulk-upload-preview" id="bulkUploadPreview">
                                        <div class="upload-placeholder" onclick="document.getElementById('bulkImageInput').click();">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>Click to select multiple images</p>
                                            <span>JPG, PNG, WEBP, GIF (max 5MB each, up to 10 images)</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="selected-count" id="selectedCount" style="display: none;">
                                    <span id="countDisplay">0</span> images selected
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="bulkSubmitBtn">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Upload All Images
                        </button>
                        <a href="?" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Brand colors: #1a685b, #ffac00, #051311 */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }
    
    /* Reset and base */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
    }
    
    body {
        background-color: var(--gray-bg);
        color: var(--dark);
    }
    
    .main-content {
        padding: 25px;
    }
    
    /* Header Bar - clean, minimal */
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--gold);
    }
    
    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .page-title i {
        color: var(--gold);
    }
    
    /* Buttons - classic, minimal radius */
    .btn {
        padding: 10px 22px;
        border: none;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border-radius: 4px;
        letter-spacing: 0.3px;
    }
    
    .btn-primary {
        background: var(--teal);
        color: white;
        border: 1px solid var(--teal);
    }
    
    .btn-primary:hover {
        background: #0f4f44;
    }
    
    .btn-secondary {
        background: white;
        color: var(--dark);
        border: 1px solid var(--border);
    }
    
    .btn-secondary:hover {
        background: var(--gray-bg);
        border-color: var(--teal);
    }
    
    .btn-danger {
        background: #dc3545;
        color: white;
        border: 1px solid #dc3545;
    }
    
    .btn-danger:hover {
        background: #bb2d3b;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--teal);
        text-decoration: none;
        border: 1px solid var(--border);
        background: white;
        transition: all 0.2s;
        border-radius: 4px;
    }
    
    .btn-icon:hover {
        background: var(--teal);
        color: white;
        border-color: var(--teal);
    }
    
    .btn-icon.delete:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }
    
    /* Stats Row */
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-box {
        background: white;
        padding: 15px 25px;
        border: 1px solid var(--border);
        flex: 1;
        border-radius: 4px;
    }
    
    .stat-label {
        display: block;
        font-size: 13px;
        color: #5f7d76;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    
    .stat-number {
        font-size: 28px;
        font-weight: 600;
        color: var(--teal);
    }
    
    /* Filter Bar */
    .filter-bar {
        background: white;
        padding: 15px;
        border: 1px solid var(--border);
        margin-bottom: 25px;
        border-radius: 4px;
    }
    
    .filter-form {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .search-group {
        flex: 2;
        min-width: 250px;
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9bb7b0;
    }
    
    .search-input {
        width: 100%;
        padding: 10px 10px 10px 40px;
        border: 1px solid var(--border);
        font-size: 14px;
        background: white;
        border-radius: 4px;
    }
    
    .search-input:focus {
        outline: none;
        border-color: var(--teal);
    }
    
    .filter-group {
        flex: 1;
        min-width: 150px;
    }
    
    .filter-select {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border);
        background: white;
        border-radius: 4px;
    }
    
    /* Gallery Grid */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .gallery-card {
        background: white;
        border: 1px solid var(--border);
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .gallery-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: var(--teal);
    }
    
    .card-image {
        height: 180px;
        background: #f0f5f4;
        position: relative;
        overflow: hidden;
    }
    
    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .no-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a0c0b9;
        font-size: 40px;
        background: #e9f0ee;
    }
    
    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 8px 12px;
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        color: white;
    }
    
    .image-type {
        font-size: 12px;
        background: var(--gold);
        color: var(--dark);
        padding: 3px 10px;
        font-weight: 600;
        border-radius: 4px;
    }
    
    .card-content {
        padding: 15px;
    }
    
    .card-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--teal);
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .card-description {
        font-size: 13px;
        color: #4f6b65;
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }
    
    .card-date {
        font-size: 12px;
        color: #5f7d76;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .card-actions {
        display: flex;
        gap: 8px;
    }
    
    /* Form Container */
    .form-container {
        background: white;
        border: 1px solid var(--border);
        padding: 25px;
        border-radius: 4px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--dark);
    }
    
    .form-label.required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        font-size: 14px;
        background: white;
        border-radius: 4px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--teal);
    }
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #5f7d76;
    }
    
    /* Image Upload Area */
    .image-upload-box {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .upload-area {
        margin-top: 10px;
        margin-bottom: 10px;
    }
    
    .upload-preview {
        min-height: 200px;
        border: 1px dashed var(--teal);
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        border-radius: 4px;
    }
    
    .upload-placeholder {
        text-align: center;
        padding: 40px 20px;
        cursor: pointer;
        width: 100%;
    }
    
    .upload-placeholder i {
        font-size: 40px;
        color: var(--teal);
        margin-bottom: 10px;
    }
    
    .upload-placeholder p {
        color: var(--dark);
        margin-bottom: 5px;
    }
    
    .upload-placeholder span {
        font-size: 12px;
        color: #5f7d76;
    }
    
    .preview-img {
        max-width: 100%;
        max-height: 250px;
        object-fit: contain;
    }
    
    .remove-image {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #dc3545;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 4px;
    }
    
    /* Bulk Upload Styles */
    .bulk-upload-preview {
        min-height: 150px;
        border: 1px dashed var(--teal);
        background: white;
        margin-top: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
    }
    
    .selected-count {
        margin-top: 10px;
        padding: 8px;
        background: var(--teal-light);
        border: 1px solid var(--teal);
        text-align: center;
        font-weight: 600;
        border-radius: 4px;
    }
    
    .selected-count span {
        color: var(--teal);
        font-size: 18px;
    }
    
    /* Info Box */
    .info-box {
        background: #f9fdfc;
        padding: 15px;
        border: 1px solid var(--border);
        margin-top: 20px;
        border-radius: 4px;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px dashed var(--border);
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: #5f7d76;
        font-size: 13px;
    }
    
    .info-value {
        font-weight: 600;
        color: var(--dark);
    }
    
    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    
    /* Alerts */
    .alert {
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 4px solid transparent;
        animation: slideIn 0.3s ease;
        border-radius: 4px;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .alert-success {
        background: #e6f3f0;
        color: #0f4f44;
        border-left-color: var(--teal);
    }
    
    .alert-error {
        background: #ffe6e6;
        color: #b02a37;
        border-left-color: #dc3545;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .empty-state i {
        font-size: 60px;
        color: var(--teal-light);
        margin-bottom: 15px;
    }
    
    .empty-state h3 {
        color: var(--teal);
        margin-bottom: 10px;
        font-size: 20px;
    }
    
    .empty-state p {
        color: #5f7d76;
        margin-bottom: 20px;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 5px;
        margin-top: 30px;
    }
    
    .page-link {
        padding: 8px 14px;
        border: 1px solid var(--border);
        background: white;
        color: var(--teal);
        text-decoration: none;
        transition: all 0.2s;
        border-radius: 4px;
    }
    
    .page-link:hover {
        background: var(--teal-light);
        border-color: var(--teal);
    }
    
    .page-link.active {
        background: var(--teal);
        color: white;
        border-color: var(--teal);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .stats-row {
            flex-direction: column;
        }
        
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Image upload preview
    document.getElementById('imageInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const uploadPreview = document.getElementById('uploadPreview');
                uploadPreview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="preview-img">
                    <button type="button" class="remove-image" onclick="removeImage()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
            }
            reader.readAsDataURL(file);
        }
    });
    
    // Bulk image preview and count
    document.getElementById('bulkImageInput')?.addEventListener('change', function(e) {
        const files = e.target.files;
        const fileCount = files.length;
        
        if (fileCount > 10) {
            Swal.fire({
                title: 'Too Many Files',
                text: 'You can only upload up to 10 images at once.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            this.value = '';
            return;
        }
        
        const countDisplay = document.getElementById('selectedCount');
        const countSpan = document.getElementById('countDisplay');
        countSpan.textContent = fileCount;
        countDisplay.style.display = 'block';
        
        // Show preview of first image
        if (fileCount > 0) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const bulkPreview = document.getElementById('bulkUploadPreview');
                bulkPreview.innerHTML = `
                    <div style="padding: 10px; text-align: center;">
                        <img src="${e.target.result}" style="max-width: 100%; max-height: 150px; object-fit: contain; margin-bottom: 10px;">
                        <p><strong>${fileCount}</strong> image${fileCount > 1 ? 's' : ''} selected</p>
                        <p style="font-size: 12px; color: #5f7d76;">Click to change selection</p>
                    </div>
                `;
                bulkPreview.onclick = function() {
                    document.getElementById('bulkImageInput').click();
                };
            }
            reader.readAsDataURL(files[0]);
        }
    });
    
    // Remove image
    function removeImage() {
        const uploadPreview = document.getElementById('uploadPreview');
        const fileInput = document.getElementById('imageInput');
        
        uploadPreview.innerHTML = `
            <div class="upload-placeholder" onclick="document.getElementById('imageInput').click();">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to upload</p>
                <span>JPG, PNG, WEBP, GIF (max 5MB)</span>
            </div>
        `;
        
        if (fileInput) fileInput.value = '';
    }
    
    // Confirm delete
    function confirmDelete(name) {
        Swal.fire({
            title: 'Delete Item?',
            html: `Are you sure you want to delete "<strong>${name}</strong>"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `?delete=${<?php echo $item['id'] ?? 0; ?>}`;
            }
        });
        return false;
    }
    
    // Auto-hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    }, 4000);
    
    // Form validation for single upload
    document.querySelector('.gallery-form')?.addEventListener('submit', function(e) {
        const action = '<?php echo $action; ?>';
        if (action === 'add') {
            const fileInput = document.getElementById('imageInput');
            if (!fileInput || !fileInput.files.length) {
                e.preventDefault();
                Swal.fire({
                    title: 'Image Required',
                    text: 'Please select an image to upload.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
    
    // Form validation for bulk upload
    document.getElementById('bulkUploadForm')?.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('bulkImageInput');
        if (!fileInput || !fileInput.files.length) {
            e.preventDefault();
            Swal.fire({
                title: 'Images Required',
                text: 'Please select at least one image to upload.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
</script>

<?php include 'layout/footer.php'; ?>