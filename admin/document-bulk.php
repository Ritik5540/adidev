<?php
// document-bulk.php - Bulk upload with preview table
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// session_start();

include 'layout/header.php';
include 'layout/sidebar.php';

$current_user_id = $_SESSION['user_id'] ?? 1;
$today = date('Y-m-d');
$max_files = 5;
?>

<!-- Main Content -->
<main class="main-content">
    <div class="container">
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-layer-group"></i>
                Bulk Upload Documents (Max <?php echo $max_files; ?>)
            </h2>
            <div class="header-actions">
                <a href="documents.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        
        <div class="form-container">
            <!-- File Selection Section -->
            <div class="file-selection-section">
                <div class="file-upload-box">
                    <label class="form-label required">Select Files (Max <?php echo $max_files; ?>)</label>
                    
                    <div class="bulk-upload-area">
                        <input type="file" 
                               name="bulk_files[]" 
                               id="bulkFileInput" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.zip"
                               multiple
                               style="display: none;">
                        
                        <div class="bulk-upload-preview" id="bulkUploadPreview">
                            <div class="upload-placeholder" onclick="document.getElementById('bulkFileInput').click();">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to select multiple files</p>
                                <span>Select up to <?php echo $max_files; ?> files (max 20MB each)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-help mt-2">Files will be processed in the order selected</div>
                </div>
            </div>

            <!-- Preview Table Section (Hidden by default) -->
            <div id="previewSection" style="display: none;">
                <h3 class="preview-title">
                    <i class="fas fa-eye"></i>
                    Preview & Edit Details
                </h3>
                
                <form method="POST" action="document-submit.php" enctype="multipart/form-data" class="document-form" id="bulkUploadForm">
                    <input type="hidden" name="action" value="add_bulk_preview">
                    
                    <div class="table-container">
                        <table class="preview-table" id="previewTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Type</th>
                                    <th>Title <span class="required-star">*</span></th>
                                    <th>Document Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Dynamic rows will be added here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Common Fields -->
                    <div class="common-fields">
                        <div class="common-fields-grid">
                            <div class="form-group">
                                <label class="form-label">Document Type (for all)</label>
                                <select name="bulk_document_type" class="form-control">
                                    <option value="general">General</option>
                                    <option value="workorder">Work Order</option>
                                    <option value="legal">Legal Document</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Common Description (Optional)</label>
                                <textarea name="bulk_description" 
                                          class="form-control" 
                                          rows="2"
                                          placeholder="This description will be applied to all documents"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="bulkSubmitBtn" disabled>
                            <i class="fas fa-cloud-upload-alt"></i>
                            Upload All (0)
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetSelection()">
                            <i class="fas fa-redo"></i> Select Different Files
                        </button>
                        <a href="documents.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
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
        --danger: #dc3545;
        --danger-light: #f8d7da;
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
    
    .btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .btn-primary:hover:not(:disabled) {
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
        background: var(--danger);
        color: white;
        border: 1px solid var(--danger);
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-danger:hover {
        background: #bb2d3b;
    }
    
    .form-container {
        background: white;
        border: 1px solid var(--border);
        padding: 25px;
        border-radius: 4px;
    }
    
    .file-selection-section {
        margin-bottom: 30px;
    }
    
    .file-upload-box {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .bulk-upload-preview {
        min-height: 120px;
        border: 1px dashed var(--teal);
        background: white;
        margin-top: 10px;
        border-radius: 4px;
    }
    
    .upload-placeholder {
        text-align: center;
        padding: 30px 20px;
        cursor: pointer;
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
    
    .preview-title {
        color: var(--teal);
        margin: 20px 0 15px;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .table-container {
        overflow-x: auto;
        margin-bottom: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
    }
    
    .preview-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }
    
    .preview-table th {
        background: var(--teal-light);
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        color: var(--dark);
        border-bottom: 2px solid var(--teal);
    }
    
    .preview-table td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    
    .preview-table tr:last-child td {
        border-bottom: none;
    }
    
    .preview-table input[type="text"],
    .preview-table input[type="date"] {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
    }
    
    .preview-table input[type="text"]:focus,
    .preview-table input[type="date"]:focus {
        outline: none;
        border-color: var(--teal);
    }
    
    .preview-table input.error {
        border-color: var(--danger);
        background-color: var(--danger-light);
    }
    
    .file-icon-small {
        color: var(--teal);
        font-size: 16px;
        margin-right: 5px;
    }
    
    .file-size {
        color: #5f7d76;
        font-size: 12px;
    }
    
    .badge-type {
        display: inline-block;
        padding: 3px 8px;
        background: var(--gold-light);
        color: var(--dark);
        font-size: 11px;
        font-weight: 600;
        border: 1px solid var(--gold);
        border-radius: 4px;
    }
    
    .required-star {
        color: var(--danger);
        margin-left: 2px;
    }
    
    .common-fields {
        background: #f9fdfc;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .common-fields-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 20px;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--dark);
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
    
    .mt-2 {
        margin-top: 10px;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
    }
    
    @media (max-width: 768px) {
        .common-fields-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const maxFiles = <?php echo $max_files; ?>;
    const maxSize = 20 * 1024 * 1024; // 20MB
    const today = '<?php echo $today; ?>';
    let selectedFiles = [];
    
    document.getElementById('bulkFileInput')?.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Check file count
        if (files.length > maxFiles) {
            Swal.fire({
                title: 'Too Many Files',
                text: `You can only upload up to ${maxFiles} files at once.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            this.value = '';
            return;
        }
        
        // Check file sizes
        let hasLargeFile = false;
        let largeFiles = [];
        files.forEach(file => {
            if (file.size > maxSize) {
                hasLargeFile = true;
                largeFiles.push(file.name);
            }
        });
        
        if (hasLargeFile) {
            Swal.fire({
                title: 'File Too Large',
                html: `The following files exceed 20MB:<br><strong>${largeFiles.join('<br>')}</strong>`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
            this.value = '';
            return;
        }
        
        // Store files and show preview
        selectedFiles = files;
        showPreviewTable(files);
    });
    
    function showPreviewTable(files) {
        // Hide file selection preview
        document.getElementById('bulkUploadPreview').innerHTML = `
            <div style="padding: 15px; text-align: center;">
                <i class="fas fa-check-circle" style="color: var(--teal); font-size: 30px;"></i>
                <p style="margin-top: 10px;"><strong>${files.length}</strong> file(s) selected</p>
                <p style="font-size: 12px; color: #5f7d76;">Proceed to edit details below</p>
            </div>
        `;
        
        // Show preview section
        document.getElementById('previewSection').style.display = 'block';
        
        // Generate table rows
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';
        
        files.forEach((file, index) => {
            // Format file size
            let size = file.size;
            const units = ['B', 'KB', 'MB'];
            let i = 0;
            while (size >= 1024 && i < units.length - 1) {
                size /= 1024;
                i++;
            }
            const formattedSize = size.toFixed(1) + ' ' + units[i];
            
            // Get file extension and icon
            const ext = file.name.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': 'fa-file-pdf', 'doc': 'fa-file-word', 'docx': 'fa-file-word',
                'xls': 'fa-file-excel', 'xlsx': 'fa-file-excel',
                'ppt': 'fa-file-powerpoint', 'pptx': 'fa-file-powerpoint',
                'txt': 'fa-file-alt', 'jpg': 'fa-file-image', 'jpeg': 'fa-file-image',
                'png': 'fa-file-image', 'zip': 'fa-file-archive'
            };
            const iconClass = iconMap[ext] || 'fa-file';
            
            // Generate row
            const row = document.createElement('tr');
            row.id = `file-row-${index}`;
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <i class="fas ${iconClass} file-icon-small"></i>
                    ${file.name}
                </td>
                <td class="file-size">${formattedSize}</td>
                <td><span class="badge-type">${ext.toUpperCase()}</span></td>
                <td>
                    <input type="text" 
                           name="titles[${index}]" 
                           value="Document ${index + 1}" 
                           class="title-input"
                           placeholder="Enter title"
                           data-index="${index}"
                           required>
                    <input type="hidden" name="file_names[${index}]" value="${file.name}">
                    <input type="hidden" name="file_sizes[${index}]" value="${file.size}">
                </td>
                <td>
                    <input type="date" 
                           name="dates[${index}]" 
                           value="${today}"
                           class="date-input">
                </td>
                <td>
                    <button type="button" class="btn btn-danger" onclick="removeFile(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        // Enable submit button
        document.getElementById('bulkSubmitBtn').disabled = false;
        document.getElementById('bulkSubmitBtn').innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload All (${files.length})`;
        
        // Add input validation
        addValidationListeners();
    }
    
    function addValidationListeners() {
        const titleInputs = document.querySelectorAll('.title-input');
        titleInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim() === '') {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
                validateForm();
            });
        });
    }
    
    function validateForm() {
        const titleInputs = document.querySelectorAll('.title-input');
        let allValid = true;
        
        titleInputs.forEach(input => {
            if (input.value.trim() === '') {
                allValid = false;
                input.classList.add('error');
            }
        });
        
        document.getElementById('bulkSubmitBtn').disabled = !allValid;
    }
    
    function removeFile(index) {
        const row = document.getElementById(`file-row-${index}`);
        if (row) {
            row.remove();
            
            // Remove file from selectedFiles array
            selectedFiles.splice(index, 1);
            
            // Renumber remaining rows
            const rows = document.querySelectorAll('#previewTableBody tr');
            rows.forEach((row, newIndex) => {
                row.id = `file-row-${newIndex}`;
                row.cells[0].textContent = newIndex + 1;
                
                // Update input names
                const titleInput = row.querySelector('.title-input');
                titleInput.name = `titles[${newIndex}]`;
                titleInput.dataset.index = newIndex;
                
                const dateInput = row.querySelector('.date-input');
                dateInput.name = `dates[${newIndex}]`;
                
                // Update hidden inputs
                const hiddenInputs = row.querySelectorAll('input[type="hidden"]');
                hiddenInputs[0].name = `file_names[${newIndex}]`;
                hiddenInputs[1].name = `file_sizes[${newIndex}]`;
                
                // Update remove button
                const removeBtn = row.querySelector('.btn-danger');
                removeBtn.setAttribute('onclick', `removeFile(${newIndex})`);
            });
            
            // Update submit button
            const remainingCount = rows.length;
            document.getElementById('bulkSubmitBtn').innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Upload All (${remainingCount})`;
            
            // If no files left, reset selection
            if (remainingCount === 0) {
                resetSelection();
            } else {
                validateForm();
            }
        }
    }
    
    function resetSelection() {
        // Reset file input
        const fileInput = document.getElementById('bulkFileInput');
        fileInput.value = '';
        
        // Reset preview
        document.getElementById('bulkUploadPreview').innerHTML = `
            <div class="upload-placeholder" onclick="document.getElementById('bulkFileInput').click();">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Click to select multiple files</p>
                <span>Select up to ${maxFiles} files (max 20MB each)</span>
            </div>
        `;
        
        // Hide preview section
        document.getElementById('previewSection').style.display = 'none';
        
        // Clear table
        document.getElementById('previewTableBody').innerHTML = '';
        
        // Disable submit button
        document.getElementById('bulkSubmitBtn').disabled = true;
        document.getElementById('bulkSubmitBtn').innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload All (0)';
        
        selectedFiles = [];
    }
    
    document.getElementById('bulkUploadForm')?.addEventListener('submit', function(e) {
        const titleInputs = document.querySelectorAll('.title-input');
        let hasEmptyTitle = false;
        
        titleInputs.forEach(input => {
            if (input.value.trim() === '') {
                hasEmptyTitle = true;
                input.classList.add('error');
            }
        });
        
        if (hasEmptyTitle) {
            e.preventDefault();
            Swal.fire({
                title: 'Missing Titles',
                text: 'Please enter titles for all documents.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Add file data to form before submit
        const formData = new FormData(this);
        
        // Add the actual files
        selectedFiles.forEach((file, index) => {
            formData.append(`actual_files[${index}]`, file);
        });
        
        // Replace form submission with fetch to handle file upload
        e.preventDefault();
        
        Swal.fire({
            title: 'Uploading...',
            html: 'Please wait while your documents are being uploaded.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        fetch('document-submit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'documents.php';
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred during upload.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
</script>

<?php include 'layout/footer.php'; ?>