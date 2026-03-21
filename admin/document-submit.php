<?php
// document-submit.php - Central processing for all document actions
include "../admin-config.php";
date_default_timezone_set('Asia/Kolkata');
// session_start();

// display error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$current_user_id = $_SESSION['user_id'] ?? 1;
$upload_dir = '../uploads/documents/';

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Get action
$action = $_POST['action'] ?? '';

// Helper function to validate file
function validateFile($file, $allowed_ext, $max_size = 20 * 1024 * 1024) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed_ext)) {
        return ['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allowed_ext)];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File size must be less than 20MB'];
    }
    
    return ['success' => true];
}

// Allowed extensions
$allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];

// Set response type for AJAX requests
if ($action == 'add_bulk_preview') {
    header('Content-Type: application/json');
}

switch ($action) {
    case 'add_single':
        // Single document upload
        $title = trim($_POST['title'] ?? '');
        $document_type = trim($_POST['document_type'] ?? 'general');
        $description = trim($_POST['description'] ?? '');
        $document_date = $_POST['document_date'] ?? date('Y-m-d');
        
        // Validate
        if (empty($title)) {
            $_SESSION['error_message'] = "Title is required.";
            header("Location: document-add.php");
            exit;
        }
        
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] != 0) {
            $_SESSION['error_message'] = "File is required.";
            header("Location: document-add.php");
            exit;
        }
        
        // Validate file
        $validation = validateFile($_FILES['document_file'], $allowed_ext);
        if (!$validation['success']) {
            $_SESSION['error_message'] = $validation['message'];
            header("Location: document-add.php");
            exit;
        }
        
        // Upload file
        $file = $_FILES['document_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_name = time() . '_' . uniqid() . '.' . $ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Insert into database
            $sql = "INSERT INTO documents (title, document_type, description, file_name, file_path, file_size, original_name, uploaded_by, created_at, document_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
            
            $stmt = $conn->prepare($sql);
            $original_name = $file['name'];
            $file_size = $file['size'];
            $stmt->bind_param("sssssssis", $title, $document_type, $description, $file_name, $file_name, $file_size, $original_name, $current_user_id, $document_date);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Document uploaded successfully!";
            } else {
                $_SESSION['error_message'] = "Database error: " . $conn->error;
                // Delete uploaded file
                unlink($target_path);
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Failed to upload file. Check directory permissions.";
        }
        
        header("Location: documents.php");
        break;
        
    case 'add_bulk_preview':
        // Bulk document upload with individual titles and dates
        $document_type = trim($_POST['bulk_document_type'] ?? 'general');
        $description = trim($_POST['bulk_description'] ?? '');
        $max_files = 5;
        
        $titles = $_POST['titles'] ?? [];
        $dates = $_POST['dates'] ?? [];
        $file_names = $_POST['file_names'] ?? [];
        $file_sizes = $_POST['file_sizes'] ?? [];
        
        $uploaded = 0;
        $failed = 0;
        $errors = [];
        
        // Process each file
        foreach ($titles as $index => $title) {
            $title = trim($title);
            $document_date = $dates[$index] ?? date('Y-m-d');
            $original_name = $file_names[$index] ?? '';
            $file_size = $file_sizes[$index] ?? 0;
            
            if (empty($title)) {
                $failed++;
                $errors[] = "File $original_name: Title is required";
                continue;
            }
            
            // Get the actual file from $_FILES
            if (!isset($_FILES['actual_files'])) {
                $failed++;
                $errors[] = "No files received";
                continue;
            }
            
            $file = [
                'name' => $_FILES['actual_files']['name'][$index],
                'type' => $_FILES['actual_files']['type'][$index],
                'tmp_name' => $_FILES['actual_files']['tmp_name'][$index],
                'error' => $_FILES['actual_files']['error'][$index],
                'size' => $_FILES['actual_files']['size'][$index]
            ];
            
            if ($file['error'] != 0) {
                $failed++;
                $errors[] = "File $original_name: Upload error";
                continue;
            }
            
            // Validate file
            $validation = validateFile($file, $allowed_ext);
            if (!$validation['success']) {
                $failed++;
                $errors[] = "File $original_name: " . $validation['message'];
                continue;
            }
            
            // Upload file
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_name = time() . '_' . uniqid() . '.' . $ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Insert into database
                $sql = "INSERT INTO documents (title, document_type, description, file_name, file_path, file_size, original_name, uploaded_by, created_at, document_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssis", $title, $document_type, $description, $file_name, $file_name, $file_size, $original_name, $current_user_id, $document_date);
                
                if ($stmt->execute()) {
                    $uploaded++;
                } else {
                    $failed++;
                    unlink($target_path);
                    $errors[] = "File $original_name: Database error";
                }
                $stmt->close();
            } else {
                $failed++;
                $errors[] = "File $original_name: Failed to upload";
            }
        }
        
        $response = [];
        if ($uploaded > 0) {
            $response['success'] = true;
            $response['message'] = "Successfully uploaded $uploaded document(s). Failed: $failed";
        } else {
            $response['success'] = false;
            $response['message'] = "No documents were uploaded. " . implode(", ", $errors);
        }
        
        echo json_encode($response);
        break;
        
    case 'edit_single':
        // Edit single document
        $id = intval($_POST['document_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $document_type = trim($_POST['document_type'] ?? 'general');
        $description = trim($_POST['description'] ?? '');
        $document_date = $_POST['document_date'] ?? date('Y-m-d');
        
        if (empty($title) || $id == 0) {
            $_SESSION['error_message'] = "Invalid request.";
            header("Location: documents.php");
            exit;
        }
        
        // Get existing document info
        $result = $conn->query("SELECT * FROM documents WHERE id = $id");
        if (!$result || $result->num_rows == 0) {
            $_SESSION['error_message'] = "Document not found.";
            header("Location: documents.php");
            exit;
        }
        
        $document = $result->fetch_assoc();
        
        // Check if new file uploaded
        if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
            // Validate new file
            $validation = validateFile($_FILES['document_file'], $allowed_ext);
            if (!$validation['success']) {
                $_SESSION['error_message'] = $validation['message'];
                header("Location: document-edit.php?id=$id");
                exit;
            }
            
            // Upload new file
            $file = $_FILES['document_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_name = time() . '_' . uniqid() . '.' . $ext;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Delete old file
                $old_file = $upload_dir . $document['file_path'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
                
                // Update with new file
                $sql = "UPDATE documents SET 
                        title = ?, 
                        document_type = ?, 
                        description = ?,
                        file_name = ?,
                        file_path = ?,
                        file_size = ?,
                        original_name = ?,
                        document_date = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $original_name = $file['name'];
                $file_size = $file['size'];
                $stmt->bind_param("ssssssssi", $title, $document_type, $description, $file_name, $file_name, $file_size, $original_name, $document_date, $id);
                
            } else {
                $_SESSION['error_message'] = "Failed to upload new file.";
                header("Location: document-edit.php?id=$id");
                exit;
            }
        } else {
            // Update without changing file
            $sql = "UPDATE documents SET 
                    title = ?, 
                    document_type = ?, 
                    description = ?,
                    document_date = ?
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $document_type, $description, $document_date, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Document updated successfully!";
        } else {
            $_SESSION['error_message'] = "Database error: " . $conn->error;
        }
        $stmt->close();
        
        header("Location: documents.php");
        break;
        
    default:
        $_SESSION['error_message'] = "Invalid action.";
        header("Location: documents.php");
        break;
}

$conn->close();
exit;
?>