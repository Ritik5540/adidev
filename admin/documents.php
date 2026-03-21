<?php
// documents.php - Main document listing page
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// session_start();

include 'layout/header.php';
include 'layout/sidebar.php';

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Get file to delete
    $result = $conn->query("SELECT file_path FROM documents WHERE id = $delete_id");
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['file_path']) && file_exists('../uploads/documents/' . $row['file_path'])) {
            unlink('../uploads/documents/' . $row['file_path']);
        }
    }
    
    $conn->query("DELETE FROM documents WHERE id = $delete_id");
    $_SESSION['success_message'] = "Document deleted successfully";
    header("Location: documents.php");
    exit;
}

// Handle download
if (isset($_GET['download'])) {
    $download_id = intval($_GET['download']);
    $result = $conn->query("SELECT file_path, file_name, original_name FROM documents WHERE id = $download_id");
    if ($row = $result->fetch_assoc()) {
        $file = '../uploads/documents/' . $row['file_path'];
        if (file_exists($file)) {
            // Update download count if you have that field
            $download_name = !empty($row['original_name']) ? $row['original_name'] : $row['file_name'];
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $download_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$date_from = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

$conditions = [];
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $conditions[] = "(title LIKE '%$search%' OR description LIKE '%$search%' OR file_name LIKE '%$search%')";
}
if (!empty($type_filter)) {
    $type_filter = $conn->real_escape_string($type_filter);
    $conditions[] = "document_type = '$type_filter'";
}
if (!empty($date_from)) {
    $conditions[] = "DATE(created_at) >= '$date_from'";
}
if (!empty($date_to)) {
    $conditions[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Get total records
$total_result = $conn->query("SELECT COUNT(*) as total FROM documents $where_clause");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch documents
$documents = [];
$sql = "SELECT d.*, u.name as uploader_name 
        FROM documents d 
        LEFT JOIN users u ON d.uploaded_by = u.id 
        $where_clause 
        ORDER BY d.id DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $documents[] = $row;
    }
}

// Get unique document types for filter
$types_result = $conn->query("SELECT DISTINCT document_type FROM documents ORDER BY document_type");
$document_types = [];
if ($types_result) {
    while ($row = $types_result->fetch_assoc()) {
        $document_types[] = $row['document_type'];
    }
}

// Get total storage size
$size_result = $conn->query("SELECT SUM(file_size) as total FROM documents");
$total_size = $size_result->fetch_assoc()['total'] ?? 0;

// Get session messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Format file size function
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

// Get file icon
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'pdf' => 'fa-file-pdf',
        'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel',
        'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
        'txt' => 'fa-file-alt',
        'jpg' => 'fa-file-image', 'jpeg' => 'fa-file-image', 'png' => 'fa-file-image', 'gif' => 'fa-file-image',
        'zip' => 'fa-file-archive', 'rar' => 'fa-file-archive', '7z' => 'fa-file-archive'
    ];
    return isset($icons[$ext]) ? $icons[$ext] : 'fa-file';
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

        <!-- Header -->
        <div class="header-bar">
            <h2 class="page-title">
                <i class="fas fa-folder-open"></i>
                Document Management
            </h2>
            <div class="header-actions">
                <a href="document-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Document
                </a>
                <a href="document-bulk.php" class="btn btn-primary" style="background: var(--gold); border-color: var(--gold); color: var(--dark);">
                    <i class="fas fa-layer-group"></i> Bulk Upload (Max 5)
                </a>
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-box">
                <span class="stat-label">Total Documents</span>
                <span class="stat-number"><?php echo $total_records; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Total Size</span>
                <span class="stat-number"><?php echo formatFileSize($total_size); ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Document Types</span>
                <span class="stat-number"><?php echo count($document_types); ?></span>
            </div>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="" class="filter-form">
                <div class="search-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           name="search" 
                           placeholder="Search documents..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <?php foreach ($document_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" 
                                <?php echo $type_filter == $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($type)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="filter-select" placeholder="From">
                </div>
                
                <div class="filter-group">
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="filter-select" placeholder="To">
                </div>
                
                <button type="submit" class="btn btn-primary">Apply</button>
                
                <?php if (!empty($search) || !empty($type_filter) || !empty($date_from) || !empty($date_to)): ?>
                    <a href="?" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Documents Table -->
        <?php if (!empty($documents)): ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File</th>
                            <th>Title / Filename</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): 
                            $file_icon = getFileIcon($doc['file_name'] ?? '');
                            $file_exists = !empty($doc['file_path']) && file_exists('../uploads/documents/' . $doc['file_path']);
                        ?>
                            <tr>
                                <td>#<?php echo $doc['id']; ?></td>
                                <td class="file-icon-cell">
                                    <i class="fas <?php echo $file_icon; ?>"></i>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($doc['title']); ?></strong>
                                    <div class="file-name">
                                        <small><?php echo htmlspecialchars($doc['original_name'] ?? $doc['file_name']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-type">
                                        <?php echo htmlspecialchars(ucfirst($doc['document_type'] ?? 'general')); ?>
                                    </span>
                                </td>
                                <td><?php echo formatFileSize($doc['file_size']); ?></td>
                                <td><?php echo htmlspecialchars($doc['uploader_name'] ?? 'System'); ?></td>
                                <td>
                                    <span class="date-cell">
                                        <i class="far fa-calendar-alt"></i>
                                        <?php echo date('d/m/Y', strtotime($doc['created_at'])); ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($file_exists): ?>
                                        <a href="?download=<?php echo $doc['id']; ?>" 
                                           class="btn-icon" 
                                           title="Download"
                                           target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="document-edit.php?id=<?php echo $doc['id']; ?>" 
                                       class="btn-icon" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $doc['id']; ?>" 
                                       class="btn-icon delete" 
                                       onclick="return confirmDelete('<?php echo addslashes($doc['title']); ?>')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($type_filter) ? '&type='.urlencode($type_filter) : ''; ?><?php echo !empty($date_from) ? '&date_from='.urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to='.urlencode($date_to) : ''; ?>" 
                           class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-file-upload"></i>
                <h3>No Documents Found</h3>
                <p>Upload your first document to get started.</p>
                <a href="document-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Document
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
    /* Copy the styles from your original file */
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
        background: var(--gray-bg);
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
    
    .table-container {
        background: white;
        border: 1px solid var(--border);
        overflow-x: auto;
        border-radius: 4px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }
    
    .data-table thead {
        background: var(--teal-light);
        border-bottom: 2px solid var(--teal);
    }
    
    .data-table th {
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        color: var(--dark);
        text-transform: uppercase;
    }
    
    .data-table td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    
    .file-icon-cell {
        width: 40px;
        text-align: center;
        font-size: 20px;
        color: var(--teal);
    }
    
    .file-name {
        color: #5f7d76;
        font-size: 12px;
        margin-top: 3px;
    }
    
    .badge-type {
        display: inline-block;
        padding: 4px 10px;
        background: var(--gold-light);
        color: var(--dark);
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--gold);
        border-radius: 4px;
    }
    
    .date-cell {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #5f7d76;
    }
    
    .actions-cell {
        white-space: nowrap;
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
    
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        .stats-row {
            flex-direction: column;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.style.display = 'none', 300);
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ?>   