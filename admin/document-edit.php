<?php
// document-edit.php - Edit single document
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// session_start();

include 'layout/header.php';
include 'layout/sidebar.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error_message = '';

// Fetch document data
$result = $conn->query("SELECT d.*, u.name as uploader_name 
                        FROM documents d 
                        LEFT JOIN users u ON d.uploaded_by = u.id 
                        WHERE d.id = $id");

if ($result && $row = $result->fetch_assoc()) {
    $document = $row;
} else {
    $_SESSION['error_message'] = "Document not found.";
    header("Location: documents.php");
    exit;
}
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-edit"></i>
                Edit Document
            </h2>
            <div class="header-actions">
                <a href="documents.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="document-submit.php" enctype="multipart/form-data" class="document-form">
                <input type="hidden" name="action" value="edit_single">
                <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                
                <div class="form-row">
                    <!-- Main Form -->
                    <div class="form-main">
                        <div class="form-group">
                            <label class="form-label required">Document Title</label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($document['title']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Document Type</label>
                            <select name="document_type" class="form-control">
                                <?php
                                $types = ['general', 'workorder', 'legal', 'other'];
                                foreach ($types as $type) {
                                    $selected = ($document['document_type'] == $type) ? 'selected' : '';
                                    echo "<option value=\"$type\" $selected>" . ucfirst($type) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"><?php echo htmlspecialchars($document['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Document Date</label>
                            <input type="date" 
                                   name="document_date" 
                                   class="form-control" 
                                   value="<?php echo date('Y-m-d', strtotime($document['created_at'])); ?>">
                        </div>
                    </div>
                    
                    <!-- File Info Sidebar -->
                    <div class="form-sidebar">
                        <div class="file-upload-box">
                            <label class="form-label">Current File</label>
                            
                            <div class="current-file-info">
                                <input type="hidden" name="existing_file" value="<?php echo $document['file_path']; ?>">
                                <input type="hidden" name="existing_file_name" value="<?php echo $document['file_name']; ?>">
                                
                                <div class="file-info-display">
                                    <i class="fas <?php echo getFileIcon($document['file_name']); ?> file-icon-large"></i>
                                    <div class="file-details">
                                        <strong><?php echo htmlspecialchars($document['original_name'] ?? $document['file_name']); ?></strong>
                                        <span><?php echo formatFileSize($document['file_size']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-help" style="margin-top: 15px;">
                                <label class="form-label">Replace File (Optional)</label>
                                <input type="file" 
                                       name="document_file" 
                                       id="fileInput" 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.zip">
                                <div class="form-help">Leave empty to keep current file</div>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-row">
                                <span class="info-label">Document ID:</span>
                                <span class="info-value">#<?php echo $document['id']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Uploaded:</span>
                                <span class="info-value"><?php echo date('d M Y, H:i', strtotime($document['created_at'])); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Uploaded By:</span>
                                <span class="info-value"><?php echo htmlspecialchars($document['uploader_name'] ?? 'System'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" name="submit_edit">
                        <i class="fas fa-save"></i>
                        Update Document
                    </button>
                    <a href="documents.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <a href="documents.php?delete=<?php echo $document['id']; ?>" 
                       class="btn btn-danger"
                       onclick="return confirmDelete('<?php echo addslashes($document['title']); ?>')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    /* Add these styles to your existing styles */
    .current-file-info {
        background: white;
        padding: 15px;
        border: 1px solid var(--border);
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .file-info-display {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .file-icon-large {
        font-size: 32px;
        color: var(--teal);
    }
    
    .file-details {
        flex: 1;
    }
    
    .file-details strong {
        display: block;
        margin-bottom: 5px;
        word-break: break-all;
    }
    
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
    
    .btn-danger {
        background: #dc3545;
        color: white;
        border: 1px solid #dc3545;
    }
    
    .btn-danger:hover {
        background: #bb2d3b;
    }
</style>

<script>
    function confirmDelete(name) {
        return Swal.fire({
            title: 'Delete Document?',
            html: `Are you sure you want to delete "<strong>${name}</strong>"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            return result.isConfirmed;
        });
    }
</script>

<?php
function formatFileSize($bytes) {
    if ($bytes === null || $bytes == 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
        'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
        'txt' => 'fa-file-alt',
        'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image',
        'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive'
    ];
    return isset($icons[$ext]) ? $icons[$ext] : 'fa-file';
}

include 'layout/footer.php';
?>