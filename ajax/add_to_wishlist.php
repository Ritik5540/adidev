<?php
// ajax/add_to_wishlist.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add to wishlist']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists
$stmt = db_execute("SELECT id FROM products WHERE id = ? AND is_active = 1", 'i', [$product_id]);
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
$stmt->close();

// Check if already in wishlist
$stmt = db_execute("SELECT id FROM user_wishlist WHERE user_id = ? AND product_id = ?", 'ii', [$user_id, $product_id]);
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
    exit;
}
$stmt->close();

// Add to wishlist
$stmt = db_execute("INSERT INTO user_wishlist (user_id, product_id, created_at) VALUES (?, ?, NOW())", 'ii', [$user_id, $product_id]);

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
}
$stmt->close();
?>