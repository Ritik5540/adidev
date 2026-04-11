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

$product_id = (int) ($_POST['product_id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

$stmt = db_execute(
    "SELECT id, name, product_code, base_retail_price, usd_base_retail_price, main_image, stock_quantity, track_inventory 
     FROM products 
     WHERE id = ? AND is_active = 1 
     LIMIT 1",
    'i',
    [$product_id]
);

$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

if (!empty($product['track_inventory']) && $product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

$user_id   = current_user_id();
$session_id = session_id();
$currency = get_user_currency($user_id); // Default to USD if not set

// Get or create cart (optimized)
$stmt = db_execute(
    "SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1",
    'i',
    [$user_id]
);

$cart = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cart) {
    $cart_id = (int) $cart['id'];
} else {
    $stmt = db_execute(
        "INSERT INTO carts (user_id, session_id, status, created_at) 
         VALUES (?, ?, 'active', NOW())",
        'is',
        [$user_id, $session_id]
    );
    $cart_id = $stmt->insert_id;
    $stmt->close();
}

$stmt = db_execute(
    "SELECT id, quantity, unit_price FROM cart_items 
     WHERE cart_id = ? AND product_id = ? 
     LIMIT 1",
    'ii',
    [$cart_id, $product_id]
);

$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if($currency === 'USD') {
    $product['base_retail_price'] = $product['usd_base_retail_price'];
}

if ($item) {
    $new_qty = $item['quantity'] + $quantity;

    // minimum 1 hona chahiye
    if ($new_qty < 1) {
        $new_qty = 1;
    }

    $new_total_price = $item['unit_price'] * $new_qty;

    db_execute(
        "UPDATE cart_items 
         SET quantity = ?, total_price = ?, updated_at = NOW() 
         WHERE id = ?",
        'idi',
        [$new_qty, $new_total_price, $item['id']]
    );
} else {
    db_execute(
        "INSERT INTO cart_items 
        (cart_id, product_id, quantity, unit_price, total_price, product_name, product_sku, product_image, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        'iiiddsss',
        [
            $cart_id,
            $product_id,
            $quantity,
            $product['base_retail_price'],
            $product['base_retail_price'] * $quantity,
            $product['name'],
            $product['product_code'],
            $product['main_image']
        ]
    );
}

// After insert/update cart_items
update_cart_totals($cart_id , $currency);

echo json_encode([
    'success' => true,
    'message' => 'Added to cart',
    'cart_id' => $cart_id
]);