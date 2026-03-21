<?php
// ajax/get_cart_count.php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

$session_id = session_id();
$user_id = $_SESSION['user_id'] ?? null;

$count = 0;

if ($user_id) {
    $stmt = db_execute("SELECT SUM(ci.quantity) as total 
                        FROM cart_items ci 
                        JOIN carts c ON ci.cart_id = c.id 
                        WHERE c.user_id = ? AND c.status = 'active'", 'i', [$user_id]);
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $count = (int) $row['total'];
    }
    $stmt->close();
} else {
    $stmt = db_execute("SELECT SUM(ci.quantity) as total 
                        FROM cart_items ci 
                        JOIN carts c ON ci.cart_id = c.id 
                        WHERE c.session_id = ? AND c.status = 'active'", 's', [$session_id]);
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $count = (int) $row['total'];
    }
    $stmt->close();
}

echo json_encode(['success' => true, 'count' => $count]);
?>