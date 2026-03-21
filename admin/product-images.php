<?php
// product-images.php - Manage Product Images
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    $_SESSION['error_message'] = "Invalid product ID";
    header("Location: products.php");
    exit;
}

// Fetch product details
$product = null;
$sql = "SELECT p.*, sc.name as sub_category_name, mc.name as main_category_name 
        FROM products p
        LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE p.id = $product_id";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    $_SESSION['error_message'] = "Product not found";
    header("Location: products.php");
    exit;
}

// Handle Main Image Update
if (isset($_POST['update_main_image'])) {
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/main/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old main image
        if (!empty($product['main_image']) && file_exists($upload_dir . $product['main_image'])) {
            unlink($upload_dir . $product['main_image']);
        }
        
        $file_ext = pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION);
        $main_image = time() . '_main_' . rand(1000, 9999) . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image)) {
            $conn->query("UPDATE products SET main_image = '$main_image' WHERE id = $product_id");
            $_SESSION['success_message'] = "Main image updated successfully";
        } else {
            $_SESSION['error_message'] = "Failed to upload main image";
        }
    } else {
        $_SESSION['error_message'] = "Please select an image to upload";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Hover Image Update
if (isset($_POST['update_hover_image'])) {
    if (isset($_FILES['hover_image']) && $_FILES['hover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/hover/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Delete old hover image
        if (!empty($product['hover_image']) && file_exists($upload_dir . $product['hover_image'])) {
            unlink($upload_dir . $product['hover_image']);
        }
        
        $file_ext = pathinfo($_FILES['hover_image']['name'], PATHINFO_EXTENSION);
        $hover_image = time() . '_hover_' . rand(1000, 9999) . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['hover_image']['tmp_name'], $upload_dir . $hover_image)) {
            $conn->query("UPDATE products SET hover_image = '$hover_image' WHERE id = $product_id");
            $_SESSION['success_message'] = "Hover image updated successfully";
        } else {
            $_SESSION['error_message'] = "Failed to upload hover image";
        }
    } else {
        $_SESSION['error_message'] = "Please select an image to upload";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Thumbnail Image Update
if (isset($_POST['update_thumbnail_image'])) {
    if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/products/thumbnail/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Check if thumbnail column exists, if not, we can add it or store in product_images
        // For now, we'll store in product_images table with type 'thumbnail'
        
        // Delete old thumbnail
        $old_thumbnail = $conn->query("SELECT id, image_url FROM product_images WHERE product_id = $product_id AND image_type = 'thumbnail'");
        if ($old_thumbnail && $old_thumbnail->num_rows > 0) {
            $old = $old_thumbnail->fetch_assoc();
            if (file_exists($upload_dir . $old['image_url'])) {
                unlink($upload_dir . $old['image_url']);
            }
            $conn->query("DELETE FROM product_images WHERE id = " . $old['id']);
        }
        
        $file_ext = pathinfo($_FILES['thumbnail_image']['name'], PATHINFO_EXTENSION);
        $thumbnail_image = time() . '_thumbnail_' . rand(1000, 9999) . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['thumbnail_image']['tmp_name'], $upload_dir . $thumbnail_image)) {
            $conn->query("INSERT INTO product_images (product_id, image_url, image_type, sort_order) 
                         VALUES ($product_id, '$thumbnail_image', 'thumbnail', 0)");
            $_SESSION['success_message'] = "Thumbnail image updated successfully";
        } else {
            $_SESSION['error_message'] = "Failed to upload thumbnail image";
        }
    } else {
        $_SESSION['error_message'] = "Please select an image to upload";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Gallery Images Upload
if (isset($_POST['upload_gallery'])) {
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = '../uploads/products/gallery/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get current max sort order
        $max_sort = $conn->query("SELECT MAX(sort_order) as max FROM product_images WHERE product_id = $product_id AND image_type = 'gallery'")->fetch_assoc()['max'];
        $sort_start = ($max_sort !== NULL) ? $max_sort + 1 : 0;
        
        $uploaded = 0;
        $failed = 0;
        
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_ext, $allowed)) {
                    $gallery_name = time() . '_gallery_' . $key . '_' . rand(1000, 9999) . '.' . $file_ext;
                    
                    if (move_uploaded_file($tmp_name, $upload_dir . $gallery_name)) {
                        $conn->query("INSERT INTO product_images (product_id, image_url, image_type, sort_order, created_at) 
                                     VALUES ($product_id, '$gallery_name', 'gallery', " . ($sort_start + $key) . ", NOW())");
                        $uploaded++;
                    } else {
                        $failed++;
                    }
                } else {
                    $failed++;
                }
            }
        }
        
        if ($uploaded > 0) {
            $_SESSION['success_message'] = "$uploaded gallery image(s) uploaded successfully" . ($failed > 0 ? " ($failed failed)" : "");
        } else {
            $_SESSION['error_message'] = "No images were uploaded. Please check file types (JPG, PNG, GIF, WEBP only)";
        }
    } else {
        $_SESSION['error_message'] = "Please select images to upload";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Delete Gallery Image
if (isset($_GET['delete_gallery'])) {
    $image_id = intval($_GET['delete_gallery']);
    
    // Get image details
    $img_result = $conn->query("SELECT image_url, image_type FROM product_images WHERE id = $image_id AND product_id = $product_id");
    if ($img_row = $img_result->fetch_assoc()) {
        $upload_dir = '../uploads/products/';
        if ($img_row['image_type'] == 'gallery') {
            $upload_dir .= 'gallery/';
        } elseif ($img_row['image_type'] == 'thumbnail') {
            $upload_dir .= 'thumbnail/';
        }
        
        // Delete file from server
        if (file_exists($upload_dir . $img_row['image_url'])) {
            unlink($upload_dir . $img_row['image_url']);
        }
        
        // Delete from database
        $conn->query("DELETE FROM product_images WHERE id = $image_id AND product_id = $product_id");
        $_SESSION['success_message'] = "Gallery image deleted successfully";
    } else {
        $_SESSION['error_message'] = "Image not found";
    }
    
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Delete Main Image
if (isset($_GET['delete_main'])) {
    if (!empty($product['main_image'])) {
        $upload_dir = '../uploads/products/main/';
        if (file_exists($upload_dir . $product['main_image'])) {
            unlink($upload_dir . $product['main_image']);
        }
        $conn->query("UPDATE products SET main_image = NULL WHERE id = $product_id");
        $_SESSION['success_message'] = "Main image deleted successfully";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Delete Hover Image
if (isset($_GET['delete_hover'])) {
    if (!empty($product['hover_image'])) {
        $upload_dir = '../uploads/products/hover/';
        if (file_exists($upload_dir . $product['hover_image'])) {
            unlink($upload_dir . $product['hover_image']);
        }
        $conn->query("UPDATE products SET hover_image = NULL WHERE id = $product_id");
        $_SESSION['success_message'] = "Hover image deleted successfully";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Delete Thumbnail Image
if (isset($_GET['delete_thumbnail'])) {
    $thumbnail_result = $conn->query("SELECT id, image_url FROM product_images WHERE product_id = $product_id AND image_type = 'thumbnail'");
    if ($thumbnail_row = $thumbnail_result->fetch_assoc()) {
        $upload_dir = '../uploads/products/thumbnail/';
        if (file_exists($upload_dir . $thumbnail_row['image_url'])) {
            unlink($upload_dir . $thumbnail_row['image_url']);
        }
        $conn->query("DELETE FROM product_images WHERE id = " . $thumbnail_row['id']);
        $_SESSION['success_message'] = "Thumbnail image deleted successfully";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Handle Update Image Order
if (isset($_POST['update_order'])) {
    if (isset($_POST['image_order']) && is_array($_POST['image_order'])) {
        foreach ($_POST['image_order'] as $id => $order) {
            $id = intval($id);
            $order = intval($order);
            $conn->query("UPDATE product_images SET sort_order = $order WHERE id = $id AND product_id = $product_id");
        }
        $_SESSION['success_message'] = "Image order updated successfully";
    }
    header("Location: product-images.php?id=" . $product_id);
    exit;
}

// Fetch gallery images
$gallery_images = [];
$gallery_result = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id AND image_type = 'gallery' ORDER BY sort_order");
if ($gallery_result) {
    while ($row = $gallery_result->fetch_assoc()) {
        $gallery_images[] = $row;
    }
}

// Fetch thumbnail image
$thumbnail_image = null;
$thumbnail_result = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id AND image_type = 'thumbnail' LIMIT 1");
if ($thumbnail_result && $thumbnail_result->num_rows > 0) {
    $thumbnail_image = $thumbnail_result->fetch_assoc();
}

// NOW include header and sidebar
include 'layout/header.php';
include 'layout/sidebar.php';

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
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

        <!-- Header -->
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-images"></i>
                Manage Images: <?php echo htmlspecialchars($product['name']); ?>
                <span class="product-code">#<?php echo $product['product_code']; ?></span>
            </h2>
            <div class="header-actions">
                <a href="product-view.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View Product
                </a>
                <a href="product-edit.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>
        </div>

        <!-- Image Management Grid -->
        <div class="image-management">
            
            <!-- Main Image Section -->
            <div class="image-section">
                <h3><i class="fas fa-star"></i> Main Image</h3>
                <div class="image-preview-container">
                    <?php if (!empty($product['main_image']) && file_exists('../uploads/products/main/' . $product['main_image'])): ?>
                        <div class="current-image-wrapper">
                            <img src="../uploads/products/main/<?php echo $product['main_image']; ?>" alt="Main Image" class="main-image-preview">
                            <div class="image-actions">
                                <a href="?id=<?php echo $product_id; ?>&delete_main=1" class="btn-icon delete" onclick="return confirmDelete('main image')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No main image uploaded</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                        <input type="hidden" name="update_main_image" value="1">
                        <div class="file-input-group">
                            <input type="file" name="main_image" accept="image/*" required>
                            <button type="submit" class="btn btn-primary">Upload Main Image</button>
                        </div>
                        <small>Recommended size: 800x800px. Accepted formats: JPG, PNG, GIF, WEBP</small>
                    </form>
                </div>
            </div>
            
            <!-- Hover Image Section -->
            <div class="image-section">
                <h3><i class="fas fa-mouse-pointer"></i> Hover Image</h3>
                <div class="image-preview-container">
                    <?php if (!empty($product['hover_image']) && file_exists('../uploads/products/hover/' . $product['hover_image'])): ?>
                        <div class="current-image-wrapper">
                            <img src="../uploads/products/hover/<?php echo $product['hover_image']; ?>" alt="Hover Image" class="hover-image-preview">
                            <div class="image-actions">
                                <a href="?id=<?php echo $product_id; ?>&delete_hover=1" class="btn-icon delete" onclick="return confirmDelete('hover image')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No hover image uploaded</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                        <input type="hidden" name="update_hover_image" value="1">
                        <div class="file-input-group">
                            <input type="file" name="hover_image" accept="image/*" required>
                            <button type="submit" class="btn btn-primary">Upload Hover Image</button>
                        </div>
                        <small>Appears when mouse hovers over product. Recommended size: 800x800px</small>
                    </form>
                </div>
            </div>
            
            <!-- Thumbnail Image Section -->
            <div class="image-section">
                <h3><i class="fas fa-thumbtack"></i> Thumbnail Image <?= $thumbnail_image['image_url']; ?></h3>
                <div class="image-preview-container">
                    <?php if ($thumbnail_image && file_exists('../uploads/products/thumbnail/' . $thumbnail_image['image_url'])): ?>
                        <div class="current-image-wrapper">
                            <img src="../uploads/products/thumbnail/<?php echo $thumbnail_image['image_url']; ?>" alt="Thumbnail Image" class="thumbnail-image-preview">
                            <div class="image-actions">
                                <a href="?id=<?php echo $product_id; ?>&delete_thumbnail=1" class="btn-icon delete" onclick="return confirmDelete('thumbnail image')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                            <p>No thumbnail image uploaded</p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                        <input type="hidden" name="update_thumbnail_image" value="1">
                        <div class="file-input-group">
                            <input type="file" name="thumbnail_image" accept="image/*" required>
                            <button type="submit" class="btn btn-primary">Upload Thumbnail</button>
                        </div>
                        <small>Small image for listings and cards. Recommended size: 200x200px</small>
                    </form>
                </div>
            </div>
            
            <!-- Gallery Images Section -->
            <div class="image-section full-width">
                <h3><i class="fas fa-images"></i> Gallery Images</h3>
                
                <!-- Upload New Gallery Images -->
                <div class="gallery-upload">
                    <form method="POST" enctype="multipart/form-data" class="gallery-upload-form">
                        <input type="hidden" name="upload_gallery" value="1">
                        <div class="file-input-group">
                            <input type="file" name="gallery_images[]" accept="image/*" multiple required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Images
                            </button>
                        </div>
                        <small>You can select multiple images. Accepted formats: JPG, PNG, GIF, WEBP</small>
                    </form>
                </div>
                
                <!-- Gallery Images List -->
                <?php if (!empty($gallery_images)): ?>
                    <form method="POST" class="gallery-order-form">
                        <input type="hidden" name="update_order" value="1">
                        <div class="gallery-grid">
                            <?php foreach ($gallery_images as $index => $image): ?>
                                <div class="gallery-item" data-id="<?php echo $image['id']; ?>">
                                    <div class="gallery-image-wrapper">
                                        <img src="../uploads/products/gallery/<?php echo $image['image_url']; ?>" alt="Gallery Image">
                                        <div class="gallery-overlay">
                                            <a href="?id=<?php echo $product_id; ?>&delete_gallery=<?php echo $image['id']; ?>" 
                                               class="btn-icon delete" 
                                               onclick="return confirmDelete('gallery image')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <div class="sort-handle">
                                                <i class="fas fa-arrows-alt"></i>
                                                <input type="number" name="image_order[<?php echo $image['id']; ?>]" 
                                                       value="<?php echo $image['sort_order']; ?>" 
                                                       class="sort-order-input" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Order
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <h3>No Gallery Images</h3>
                        <p>Upload images to create a product gallery.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Image Usage Tips -->
            <div class="image-tips">
                <h4><i class="fas fa-lightbulb"></i> Image Tips</h4>
                <ul>
                    <li><strong>Main Image:</strong> Primary product image shown in listings and product page</li>
                    <li><strong>Hover Image:</strong> Alternative image shown when mouse hovers over product</li>
                    <li><strong>Thumbnail Image:</strong> Small image used in cards, search results, and related products</li>
                    <li><strong>Gallery Images:</strong> Additional product views, angles, and lifestyle images</li>
                    <li><strong>Recommended Size:</strong> 800x800px for main/hover, 200x200px for thumbnails</li>
                    <li><strong>File Types:</strong> JPG, PNG, GIF, WEBP (max 5MB each)</li>
                    <li><strong>Drag & Drop:</strong> Use the arrows to reorder gallery images (higher number = higher position)</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<style>
    /* Image Management Styles */
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --gold-light: #fff2d6;
        --gray-bg: #f5f7f6;
        --border: #d4e0dd;
    }

    .main-content {
        padding: 25px;
    }

    .container {
        background: white;
        padding: 20px;
        border-radius: 8px;
    }

    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--gold);
        flex-wrap: wrap;
        gap: 10px;
    }

    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .page-title i {
        color: var(--gold);
    }

    .product-code {
        font-size: 14px;
        color: var(--gold);
        background: var(--gold-light);
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: normal;
    }

    .image-management {
        margin-top: 20px;
    }

    .image-section {
        background: white;
        border: 1px solid var(--border);
        border-radius: 8px;
        margin-bottom: 25px;
        overflow: hidden;
    }

    .image-section.full-width {
        grid-column: span 2;
    }

    .image-section h3 {
        background: var(--teal-light);
        padding: 12px 20px;
        margin: 0;
        font-size: 16px;
        color: var(--teal);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-section h3 i {
        color: var(--gold);
    }

    .image-preview-container {
        padding: 20px;
    }

    .current-image-wrapper {
        text-align: center;
        margin-bottom: 20px;
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .main-image-preview,
    .hover-image-preview,
    .thumbnail-image-preview {
        max-width: 300px;
        max-height: 300px;
        object-fit: contain;
        border: 2px solid var(--border);
        border-radius: 8px;
        padding: 10px;
        background: var(--gray-bg);
    }

    .image-actions {
        margin-top: 10px;
    }

    .image-upload-form {
        border-top: 1px solid var(--border);
        padding-top: 20px;
        margin-top: 10px;
    }

    .file-input-group {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .file-input-group input[type="file"] {
        flex: 1;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 14px;
    }

    .gallery-upload {
        padding: 20px;
        border-bottom: 1px solid var(--border);
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
        padding: 20px;
    }

    .gallery-item {
        position: relative;
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        background: var(--gray-bg);
        transition: transform 0.2s;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .gallery-image-wrapper {
        position: relative;
    }

    .gallery-image-wrapper img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        display: block;
    }

    .gallery-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.7);
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .sort-handle {
        display: flex;
        align-items: center;
        gap: 8px;
        background: white;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .sort-handle i {
        cursor: move;
        color: var(--teal);
    }

    .sort-order-input {
        width: 50px;
        padding: 4px;
        border: 1px solid var(--border);
        border-radius: 4px;
        text-align: center;
        font-size: 12px;
    }

    .gallery-order-form {
        margin-top: 0;
    }

    .no-image {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }

    .no-image i {
        font-size: 48px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: var(--gray-bg);
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

    .image-tips {
        background: var(--teal-light);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .image-tips h4 {
        margin: 0 0 10px 0;
        color: var(--teal);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .image-tips ul {
        margin: 0;
        padding-left: 20px;
    }

    .image-tips li {
        margin-bottom: 8px;
        color: var(--dark);
        line-height: 1.5;
    }

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
        background: var(--gray-bg);
        color: var(--dark);
        border: 1px solid var(--border);
    }

    .btn-secondary:hover {
        background: #e9ecef;
        border-color: var(--teal);
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
        margin: 0 2px;
    }

    .btn-icon.delete {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
    }

    .btn-icon.delete:hover {
        background: #c82333;
    }

    .form-actions {
        padding: 15px 20px;
        background: var(--gray-bg);
        border-top: 1px solid var(--border);
        text-align: right;
    }

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

    small {
        display: block;
        color: #6c757d;
        font-size: 11px;
        margin-top: 8px;
    }

    @media (max-width: 768px) {
        .file-input-group {
            flex-direction: column;
            align-items: stretch;
        }
        
        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        }
        
        .header-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .main-image-preview,
        .hover-image-preview,
        .thumbnail-image-preview {
            max-width: 100%;
        }
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
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(imageType) {
        return Swal.fire({
            title: 'Delete Image?',
            html: `Are you sure you want to delete this ${imageType}?`,
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
    
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.style.display = 'none', 300);
        });
    }, 4000);
    
    // Preview image before upload
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = this.closest('.image-section').querySelector('.current-image-wrapper img');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                }.bind(this);
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // Sortable functionality for gallery items (optional - you can implement drag and drop)
    // This is a simple version - you can enhance with SortableJS library
</script>

<?php include 'layout/footer.php'; ?>