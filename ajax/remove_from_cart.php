<?php
// ajax/add_to_cart.php

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

$cart_item_id = (int) ($_POST['cart_item_id'] ?? 0);

if ($cart_item_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
    exit;
}

$user_id   = current_user_id();
$session_id = session_id();
$removed = remove_cart_item($cart_item_id , $user_id);

echo json_encode([
    'success' => $removed,
    'message' => $removed ? 'Removed from cart' : 'Failed to remove from cart',
]);