<?php
// ajax/get_products.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../catalog_functions.php';

header('Content-Type: application/json');

// Get filter parameters
$filters = [
    'min_price' => isset($_GET['min_price']) ? (float) $_GET['min_price'] : 0,
    'max_price' => isset($_GET['max_price']) ? (float) $_GET['max_price'] : 100000,
    'colors' => isset($_GET['colors']) && $_GET['colors'] ? explode(',', $_GET['colors']) : [],
    'ratings' => isset($_GET['ratings']) && $_GET['ratings'] ? explode(',', $_GET['ratings']) : [],
    'stock_status' => isset($_GET['stock']) ? $_GET['stock'] : '',
    'sort_by' => isset($_GET['sort']) ? $_GET['sort'] : 'default',
    'limit' => isset($_GET['per_page']) ? (int) $_GET['per_page'] : 12,
    'offset' => isset($_GET['page']) ? ((int) $_GET['page'] - 1) * (int) $_GET['per_page'] : 0,
    'search' => isset($_GET['search']) ? $_GET['search'] : ''
];

// Add category filters
if (isset($_GET['sub']) && !empty($_GET['sub'])) {
    $slug = $_GET['sub'];
    $stmt = db_execute("SELECT id FROM sub_categories WHERE slug = ? AND is_active = 1 LIMIT 1", 's', [$slug]);
    $result = $stmt->get_result();
    $sub = $result->fetch_assoc();
    $stmt->close();
    if ($sub) {
        $filters['sub_category_id'] = $sub['id'];
    }
} elseif (isset($_GET['category']) && !empty($_GET['category'])) {
    $slug = $_GET['category'];
    $stmt = db_execute("SELECT id FROM main_categories WHERE slug = ? AND is_active = 1 LIMIT 1", 's', [$slug]);
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();
    if ($category) {
        $filters['main_category_id'] = $category['id'];
    }
}

$user_id = current_user_id() ?? 0;
$currency = get_user_currency($user_id);

// Get products
$products = get_products($filters , $currency);
$total = get_products_count($filters);
$totalPages = ceil($total / ($filters['limit']));
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Format products for JSON response with null checks
$formattedProducts = [];
foreach ($products as $product) {
    $formattedProducts[] = [
        'id' => (int) $product['id'],
        'name' => htmlspecialchars($product['name'] ?? ''),
        'slug' => $product['slug'] ?? '',
        'short_description' => htmlspecialchars($product['short_description'] ?? ''),
        'description' => htmlspecialchars($product['description'] ?? ''),
        'retail_price' => (float) ($product['base_retail_price'] ?? 0),
        'mrp' => (float) ($product['mrp'] ?? 0),
        'main_image' => !empty($product['main_image']) ? './uploads/products/main/' . $product['main_image'] : get_product_image($product, 'main'),
        'average_rating' => (float) ($product['average_rating'] ?? 0),
        'review_count' => (int) ($product['review_count'] ?? 0),
        'stock_quantity' => (int) ($product['stock_quantity'] ?? 0),
        'is_new' => (bool) ($product['is_new'] ?? false),
        'is_on_sale' => (bool) ($product['is_on_sale'] ?? false),
        'color' => !empty($product['color']) ? $product['color'] : null,
        'productsss' => $product,
        // 'image_url' => get_product_image($product, 'main'),
        'encrypted_id' => encrypt_id($product['id']),
        'price' => (float) ($product['base_retail_price'] ?? 0),
        'avg_rating' => (float) ($product['average_rating'] ?? 0),
    ];
}

echo json_encode([
    'success' => true,
    'products' => $formattedProducts,
    'total' => $total,
    'total_pages' => $totalPages,
    'current_page' => $currentPage,
    'per_page' => $filters['limit']
]);
?>