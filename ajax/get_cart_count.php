<?php
// ajax/get_counts.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

$cart_count = 0;
$wishlist_count = 0;

if (is_logged_in()) {

    $user_id = current_user_id() ?? 0;

    $stmt = db_execute(
        "SELECT COALESCE(SUM(ci.quantity), 0) as total
         FROM cart_items ci
         JOIN carts c ON ci.cart_id = c.id
         WHERE c.user_id = ? AND c.status = 'active'",
        'i',
        [$user_id]
    );

    $cart_count = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    $stmt = db_execute(
        "SELECT COUNT(*) as total 
         FROM user_wishlist 
         WHERE user_id = ?",
        'i',
        [$user_id]
    );

    $wishlist_count = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'cart_count' => $cart_count,
    'wishlist_count' => $wishlist_count
]);