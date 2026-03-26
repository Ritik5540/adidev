<?php
// ajax/remove_from_wishlist.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'redirect' => true,
        'redirect_url' => 'sign_in.php',
        'message' => 'Please login first'
    ]);
    exit;
}

$user_id   = current_user_id();
$product_id = (int) ($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid product'
    ]);
    exit;
}

$stmt = db_execute(
    "SELECT id FROM user_wishlist 
     WHERE user_id = ? AND product_id = ? 
     LIMIT 1",
    'ii',
    [$user_id, $product_id]
);

$wishlistItem = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$wishlistItem) {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found in wishlist'
    ]);
    exit;
}

$stmt = db_execute(
    "DELETE FROM user_wishlist 
     WHERE user_id = ? AND product_id = ?",
    'ii',
    [$user_id, $product_id]
);

if ($stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Removed from wishlist'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to remove item'
    ]);
}

$stmt->close();