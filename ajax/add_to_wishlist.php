<?php
// ajax/add_to_wishlist.php

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
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

$stmt = db_execute(
    "SELECT id FROM products WHERE id = ? AND is_active = 1 LIMIT 1",
    'i',
    [$product_id]
);

if (!$stmt->get_result()->fetch_assoc()) {
    $stmt->close();
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
$stmt->close();

$stmt = db_execute(
    "INSERT INTO user_wishlist (user_id, product_id, created_at) 
     VALUES (?, ?, NOW())",
    'ii',
    [$user_id, $product_id]
);

if ($stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Added to wishlist'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Already in wishlist'
    ]);
}

$stmt->close();