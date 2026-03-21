<?php
// ajax/add_to_cart.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
$variant_id = isset($_POST['variant_id']) ? (int) $_POST['variant_id'] : 0;

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Get product details
$stmt = db_execute("SELECT id, name, product_code, base_retail_price, main_image, stock_quantity FROM products WHERE id = ? AND is_active = 1", 'i', [$product_id]);
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

// Check stock
if ($product['stock_quantity'] < $quantity && $product['track_inventory']) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

// Get or create cart
$session_id = session_id();
$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Check if cart exists for user
    $stmt = db_execute("SELECT id FROM carts WHERE user_id = ? AND status = 'active'", 'i', [$user_id]);
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        $cart_id = $cart['id'];
    } else {
        // Create new cart
        $stmt = db_execute("INSERT INTO carts (user_id, session_id, status, created_at) VALUES (?, ?, 'active', NOW())", 'is', [$user_id, $session_id]);
        $cart_id = $stmt->insert_id;
    }
    $stmt->close();
} else {
    // Check if cart exists for session
    $stmt = db_execute("SELECT id FROM carts WHERE session_id = ? AND status = 'active'", 's', [$session_id]);
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $cart = $result->fetch_assoc();
        $cart_id = $cart['id'];
    } else {
        // Create new cart
        $stmt = db_execute("INSERT INTO carts (session_id, status, created_at) VALUES (?, 'active', NOW())", 's', [$session_id]);
        $cart_id = $stmt->insert_id;
    }
    $stmt->close();
}

// Check if item already in cart
$stmt = db_execute("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))", 'iii', [$cart_id, $product_id, $variant_id, $variant_id]);
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $cart_item = $result->fetch_assoc();
    $new_quantity = $cart_item['quantity'] + $quantity;
    $stmt = db_execute("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?", 'ii', [$new_quantity, $cart_item['id']]);
} else {
    // Add new item
    $stmt = db_execute(
        "INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price, total_price, product_name, product_sku, product_image, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        'iiiddsss',
        [$cart_id, $product_id, $variant_id, $quantity, $product['base_retail_price'], $product['base_retail_price'] * $quantity, $product['name'], $product['product_code'], $product['main_image']]
    );
}

if ($stmt->affected_rows > 0 || $stmt->insert_id > 0) {
    echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_id' => $cart_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
}
$stmt->close();
