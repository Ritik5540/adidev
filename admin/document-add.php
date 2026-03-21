<?php
// document-add.php - Add single document
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// session_start();

include 'layout/header.php';
include 'layout/sidebar.php';

$current_user_id = $_SESSION['user_id'] ?? 1;
$error_message = '';
$today = date('Y-m-d');
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-plus"></i>
                Add New Document
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
            <form method="POST" action="document-submit.php" enctype="multipart/form-data" class="document-form" id="singleUploadForm">
                <input type="hidden" name="action" value="add_single">
                
                <div class="form-row">
                    <!-- Main Form -->
                    <div class="form-main">
                        <div class="form-group">
                            <label class="form-label required">Document Title</label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                   placeholder="Enter descriptive title"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Document Type</label>
                            <select name="document_type" class="form-control">
                                <option value="general">General</option>
                                <option value="workorder">Work Order</option>
                                <option value="premium">Premium Document</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description (Optional)</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Add a brief description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Document Date</label>
                            <input type="date" 
                                   name="document_date" 
                                   class="form-control" 
                                   value="<?php echo $today; ?>">
                            <div class="form-help">Default is today's date</div>
                        </div>
                    </div>
                    
                    <!-- File Upload Sidebar -->
                    <div class="form-sidebar">
                        <div class="file-upload-box">
                            <label class="form-label required">Document File</label>
                            
                            <div class="upload-area">
                                <input type="file" 
                                       name="document_file" 
                                       id="fileInput" 
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.zip"
                                       style="display: none;" 
                                       required>
                                
                                <div class="upload-preview" id="uploadPreview">
                                    <div class="upload-placeholder" onclick="document.getElementById('fileInput').click();">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to choose file</p>
                                        <span>PDF, DOC, DOCX, XLS, XLSX, PPT, TXT, JPG, PNG, ZIP (max 20MB)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-help">Maximum file size: 20MB</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" name="submit_single">
                        <i class="fas fa-upload"></i>
                        Upload Document
                    </button>
                    <a href="documents.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    :root {
        --teal: #1a685b;
        --gold: #ffac00;
        --dark: #051311;
        --teal-light: #e6f3f0;
        --border: #d4e0dd;
    }
    
    .main-content {
        padding: 25px;
    }
    
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
        background: white;
        color: var(--dark);
        border: 1px solid var(--border);
    }
    
    .btn-secondary:hover {
        background: #f5f7f6;
        border-color: var(--teal);
    }
    
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
    
    .form-help {
        margin-top: 6px;
        font-size: 12px;
        color: #5f7d76;
    }
    
    .file-upload-box {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .upload-preview {
        min-height: 150px;
        border: 1px dashed var(--teal);
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
        border-radius: 4px;
    }
    
    .upload-placeholder {
        text-align: center;
        padding: 30px 20px;
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
        font-size: 11px;
        color: #5f7d76;
    }
    
    .file-info {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        width: 100%;
    }
    
    .file-icon-large {
        font-size: 36px;
        color: var(--teal);
    }
    
    .file-details {
        flex: 1;
    }
    
    .file-details strong {
        display: block;
        margin-bottom: 5px;
        color: var(--dark);
    }
    
    .remove-file {
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
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    
    .alert {
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-left: 4px solid transparent;
        border-radius: 4px;
    }
    
    .alert-error {
        background: #ffe6e6;
        color: #b02a37;
        border-left-color: #dc3545;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('fileInput')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 20 * 1024 * 1024) {
                Swal.fire({
                    title: 'File Too Large',
                    text: 'File size must be less than 20MB.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                this.value = '';
                return;
            }
            
            const ext = file.name.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': 'fa-file-pdf', 'doc': 'fa-file-word', 'docx': 'fa-file-word',
                'xls': 'fa-file-excel', 'xlsx': 'fa-file-excel',
                'ppt': 'fa-file-powerpoint', 'pptx': 'fa-file-powerpoint',
                'txt': 'fa-file-alt', 'jpg': 'fa-file-image', 'jpeg': 'fa-file-image',
                'png': 'fa-file-image', 'zip': 'fa-file-archive'
            };
            const iconClass = iconMap[ext] || 'fa-file';
            
            let size = file.size;
            const units = ['B', 'KB', 'MB'];
            let i = 0;
            while (size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }
            const formattedSize = size.toFixed(1) + ' ' + units[i];
            
            const uploadPreview = document.getElementById('uploadPreview');
            uploadPreview.innerHTML = `
                <div class="file-info">
                    <i class="fas ${iconClass} file-icon-large"></i>
                    <div class="file-details">
                        <strong>${file.name}</strong>
                        <span>${formattedSize}</span>
                    </div>
                    <button type="button" class="remove-file" onclick="removeFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
    });
    
    function removeFile() {
        document.getElementById('uploadPreview').innerHTML = `
            <div class="upload-placeholder" onclick="document.getElementById('fileInput').click();">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to choose file</p>
                <span>PDF, DOC, DOCX, XLS, XLSX, PPT, TXT, JPG, PNG, ZIP (max 20MB)</span>
            </div>
        `;
        document.getElementById('fileInput').value = '';
    }
    
    document.getElementById('singleUploadForm')?.addEventListener('submit', function(e) {
        const fileInput = document.getElementById('fileInput');
        if (!fileInput || !fileInput.files.length) {
            e.preventDefault();
            Swal.fire({
                title: 'File Required',
                text: 'Please select a file to upload.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
</script>

<?php include 'layout/footer.php'; ?>