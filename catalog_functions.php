<?php
// Catalog and product related helper functions for Adidev

use FontLib\Font;

require_once __DIR__ . '/config.php';

function get_user_currency(int $user_id): string
{
    $stmt = db_execute('SELECT currency FROM users WHERE id = ? LIMIT 1', 'i', [$user_id]);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['currency'] ?? 'INR';
}

function get_user_details(int $user_id): ?array
{
    $stmt = db_execute('SELECT id, first_name, last_name, email, phone, display_name, currency FROM users WHERE id = ? LIMIT 1', 'i', [$user_id]);
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

function pricing_format($amount, $currency = 'INR'): string
{
    if ($currency === 'USD') {
        return '$ ' . number_format($amount, 2);
    }
    // Default to INR formatting
    return '₹ ' . number_format($amount, 2);
}

/**
 * Get active main categories that should appear in menus.
 *
 * @return array<int,array<string,mixed>>
 */
function get_main_categories_for_menu(): array
{
    $categories = [];

    $stmt = db_execute(
        'SELECT id, name, slug, icon, banner_image, thumbnail_image, meta_title, meta_description, meta_keywords
         FROM main_categories
         WHERE is_active = 1 AND show_in_menu = 1
         ORDER BY sort_order, name'
    );

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();

    return $categories;
}

/**
 * Get active sub categories grouped by their main category id.
 *
 * @param int[] $mainCategoryIds
 *
 * @return array<int,array<int,array<string,mixed>>>
 */
function get_sub_categories_grouped_by_main(array $mainCategoryIds, int $limit = 12, string $orderBy = 'name', string $orderDirection = 'ASC'): array
{
    if (!$mainCategoryIds) {
        return [];
    }

    $mainCategoryIds = array_map('intval', $mainCategoryIds);

    $placeholders = implode(',', array_fill(0, count($mainCategoryIds), '?'));

    $sql = "
        SELECT id, main_category_id, name, slug, icon, image, banner_image
        FROM sub_categories
        WHERE is_active = 1
        AND show_in_menu = 1
        AND main_category_id IN ($placeholders)
        ORDER BY $orderBy $orderDirection
        LIMIT ?
    ";

    $types = str_repeat('i', count($mainCategoryIds)) . 'i';

    $params = array_merge($mainCategoryIds, [$limit]);

    // IMPORTANT: pass params as array
    $stmt = db_execute($sql, $types, $params);

    $result  = $stmt->get_result();
    $grouped = [];

    while ($row = $result->fetch_assoc()) {
        $grouped[(int)$row['main_category_id']][] = $row;
    }

    $stmt->close();

    return $grouped;
}

/**
 * Get a primary image URL for a product.
 *
 * @param int $productId
 *
 * @return string|null
 */
function get_primary_image_for_product(int $productId): ?string
{
    // Try product_images table first
    $stmt = db_execute(
        'SELECT image_url
         FROM product_images
         WHERE product_id = ? AND is_active = 1
         ORDER BY is_primary DESC, sort_order ASC, id ASC
         LIMIT 1',
        'i',
        [$productId]
    );

    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();

    if ($row && !empty($row['image_url'])) {
        return $row['image_url'];
    }

    // Fallback to main_image column on products
    $stmt2 = db_execute(
        'SELECT main_image
         FROM products
         WHERE id = ?',
        'i',
        [$productId]
    );

    $result2 = $stmt2->get_result();
    $row2    = $result2->fetch_assoc();
    $stmt2->close();

    if ($row2 && !empty($row2['main_image'])) {
        return $row2['main_image'];
    }

    return null;
}

function get_products($filters = [], $currency = 'INR')
{
    // 🔥 Currency based column select
    $priceColumn = ($currency === 'USD')
        ? 'p.usd_base_retail_price'
        : 'p.base_retail_price';

    $mrpColumn = ($currency === 'USD')
        ? 'p.usd_mrp'
        : 'p.mrp';

    $conditions = ['p.is_active = 1'];
    $params = [];
    $types = '';

    if (isset($filters['sub_category_id'])) {
        $conditions[] = 'p.sub_category_id = ?';
        $params[] = $filters['sub_category_id'];
        $types .= 'i';
    }

    if (isset($filters['main_category_id'])) {
        $conditions[] = 'sc.main_category_id = ?';
        $params[] = $filters['main_category_id'];
        $types .= 'i';
    }

    if (isset($filters['is_featured']) && $filters['is_featured']) {
        $conditions[] = 'p.is_featured = 1';
    }

    if (isset($filters['is_new']) && $filters['is_new']) {
        $conditions[] = 'p.is_new = 1';
    }

    if (isset($filters['min_price']) && $filters['min_price'] > 0) {
        $conditions[] = 'p.base_retail_price >= ?';
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if (isset($filters['max_price']) && $filters['max_price'] < 100000) {
        $conditions[] = 'p.base_retail_price <= ?';
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    if (!empty($filters['colors'])) {
        $placeholders = implode(',', array_fill(0, count($filters['colors']), '?'));
        $conditions[] = "p.color IN ($placeholders)";
        foreach ($filters['colors'] as $color) {
            $params[] = $color;
            $types .= 's';
        }
    }

    $conditions[] = 'p.main_image IS NOT NULL';
    $conditions[] = "p.main_image != ''";

    // Sort order
    $orderBy = 'p.id DESC';
    switch ($filters['sort_by'] ?? 'default') {
        case 'price_asc':
            $orderBy = 'p.base_retail_price ASC';
            break;
        case 'price_desc':
            $orderBy = 'p.base_retail_price DESC';
            break;
        case 'newest':
            $orderBy = 'p.created_at DESC';
            break;
        case 'popular':
            $orderBy = 'p.total_sold DESC, p.id DESC';
            break;
        case 'rating':
            $orderBy = 'p.average_rating DESC, p.review_count DESC';
            break;
        case 'random':
            $orderBy = 'RAND()';
            break;
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $limit = isset($filters['limit']) ? (int) $filters['limit'] : 12;
    $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

    $sql = "SELECT 
                p.*,
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY $orderBy
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $products;
}

/**
 * Get products count with filters
 */
function get_products_count($filters = [])
{
    $conditions = ['p.is_active = 1'];
    $params = [];
    $types = '';

    if (isset($filters['sub_category_id'])) {
        $conditions[] = 'p.sub_category_id = ?';
        $params[] = $filters['sub_category_id'];
        $types .= 'i';
    }

    if (isset($filters['main_category_id'])) {
        $conditions[] = 'sc.main_category_id = ?';
        $params[] = $filters['main_category_id'];
        $types .= 'i';
    }

    if (isset($filters['is_featured']) && $filters['is_featured']) {
        $conditions[] = 'p.is_featured = 1';
    }

    if (isset($filters['min_price']) && $filters['min_price'] > 0) {
        $conditions[] = 'p.base_retail_price >= ?';
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if (isset($filters['max_price']) && $filters['max_price'] < 100000) {
        $conditions[] = 'p.base_retail_price <= ?';
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "SELECT COUNT(*) as total 
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            $whereClause";

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['total'];
}

/**
 * Get trending products (most viewed/popular)
 * @param int $limit Number of products to return
 * @return array
 */
function get_trending_products($limit = 10)
{
    $filters = [
        'sort_by' => 'popular',
        'limit' => $limit
    ];
    return get_products($filters);
}

/**
 * Get best selling products based on total sold
 * If no sold data, pick random products
 * @param int $limit Number of products to return (default 3)
 * @return array
 */
function get_best_selling_products($limit = 3)
{
    // First try to get products with total_sold > 0
    $conditions = ['p.is_active = 1', 'p.total_sold > 0'];
    $params = [];
    $types = '';

    $sql = "SELECT 
                p.*,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            WHERE p.is_active = 1 AND p.total_sold > 0
            ORDER BY p.total_sold DESC, p.id DESC
            LIMIT ?";

    $params[] = $limit;
    $types .= 'i';

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // If we have enough products with sales data, return them
    if (count($products) >= $limit) {
        return $products;
    }

    // If not enough products with sales, get remaining from random products
    $remaining = $limit - count($products);
    $existingIds = array_column($products, 'id');

    $randomConditions = ['p.is_active = 1'];
    $randomParams = [];
    $randomTypes = '';

    if (!empty($existingIds)) {
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $randomConditions[] = "p.id NOT IN ($placeholders)";
        foreach ($existingIds as $id) {
            $randomParams[] = $id;
            $randomTypes .= 'i';
        }
    }

    $whereClause = 'WHERE ' . implode(' AND ', $randomConditions);

    $sql = "SELECT 
                p.*,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY RAND()
            LIMIT ?";

    $randomParams[] = $remaining;
    $randomTypes .= 'i';

    $stmt = db_execute($sql, $randomTypes, $randomParams);
    $result = $stmt->get_result();
    $randomProducts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Merge both arrays
    return array_merge($products, $randomProducts);
}

/**
 * Get new arrival products (is_new = 1 or recent products)
 * @param int $limit Number of products to return (default 5)
 * @return array
 */
function get_new_arrival_products($limit = 5, $currency = 'INR')
{
    // 🔥 Currency based column select
    $priceColumn = ($currency === 'USD')
        ? 'p.usd_base_retail_price'
        : 'p.base_retail_price';

    $mrpColumn = ($currency === 'USD')
        ? 'p.usd_mrp'
        : 'p.mrp';

    // ✅ Common condition (reuse everywhere)
    $baseCondition = "
        p.is_active = 1
        AND p.main_image IS NOT NULL
        AND p.main_image != ''
    ";

    // 🔥 1. Get NEW products
    $sql = "SELECT 
                p.*,
                -- ✅ override same keys (important)
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            WHERE $baseCondition AND p.is_new = 1
            ORDER BY p.created_at DESC
            LIMIT ?";

    $stmt = db_execute($sql, 'i', [$limit]);
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ✅ Enough mil gaya → return
    if (count($products) >= $limit) {
        return $products;
    }

    // 🔥 2. Recent products (last 30 days)
    $remaining = $limit - count($products);
    $existingIds = array_column($products, 'id');

    $conditions = [
        $baseCondition,
        "p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    ];

    $params = [];
    $types  = '';

    if (!empty($existingIds)) {
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $conditions[] = "p.id NOT IN ($placeholders)";
        foreach ($existingIds as $id) {
            $params[] = $id;
            $types .= 'i';
        }
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT 
                p.*,
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $where
            ORDER BY p.created_at DESC
            LIMIT ?";

    $params[] = $remaining;
    $types .= 'i';

    $stmt = db_execute($sql, $types, $params);
    $recentProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 🔥 3. Random products (if still needed)
    if (count($recentProducts) < $remaining) {

        $finalRemaining = $remaining - count($recentProducts);
        $allIds = array_merge($existingIds, array_column($recentProducts, 'id'));

        $conditions = [$baseCondition];
        $params = [];
        $types  = '';

        if (!empty($allIds)) {
            $placeholders = implode(',', array_fill(0, count($allIds), '?'));
            $conditions[] = "p.id NOT IN ($placeholders)";
            foreach ($allIds as $id) {
                $params[] = $id;
                $types .= 'i';
            }
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "SELECT 
                    p.*,
                    {$priceColumn} AS base_retail_price,
                    {$mrpColumn} AS mrp,
                    sc.name AS sub_category_name,
                    mc.name AS main_category_name,
                    mc.category_code
                FROM products p
                LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
                LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
                $where
                ORDER BY RAND()
                LIMIT ?";

        $params[] = $finalRemaining;
        $types .= 'i';

        $stmt = db_execute($sql, $types, $params);
        $randomProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $recentProducts = array_merge($recentProducts, $randomProducts);
    }

    return array_merge($products, $recentProducts);
}

/**
 * Get featured products (is_featured = 1)
 * @param int $limit Number of products to return (default 5)
 * @return array
 */
function get_featured_products($limit = 5)
{
    $filters = [
        'is_featured' => true,
        'limit' => $limit
    ];
    return get_products($filters);
}

/**
 * Get recommended products based on various criteria
 * Returns random products if no specific recommendations
 * @param int $limit Number of products to return (default 15)
 * @param int $excludeId Product ID to exclude (optional)
 * @return array
 */
function get_recommended_products($limit = 15, $excludeId = null, $currency = 'INR')
{
    // 🔥 Currency based column select
    $priceColumn = ($currency === 'USD')
        ? 'p.usd_base_retail_price'
        : 'p.base_retail_price';

    $mrpColumn = ($currency === 'USD')
        ? 'p.usd_mrp'
        : 'p.mrp';

    // ✅ Common base condition
    $baseCondition = "
        p.is_active = 1
        AND p.main_image IS NOT NULL
        AND p.main_image != ''
    ";

    $conditions = [$baseCondition];
    $params = [];
    $types = '';

    if ($excludeId !== null) {
        $conditions[] = 'p.id != ?';
        $params[] = $excludeId;
        $types .= 'i';
    }

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    // 🔥 1. High rated products (4+)
    $sql = "SELECT 
                p.*,
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause AND p.average_rating >= 4
            ORDER BY p.average_rating DESC, p.review_count DESC
            LIMIT ?";

    $params[] = $limit;
    $types .= 'i';

    $stmt = db_execute($sql, $types, $params);
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // ✅ Enough mil gaya
    if (count($products) >= $limit) {
        return $products;
    }

    // 🔥 2. Random fallback
    $remaining = $limit - count($products);
    $existingIds = array_column($products, 'id');

    if ($excludeId !== null && !in_array($excludeId, $existingIds)) {
        $existingIds[] = $excludeId;
    }

    $conditions = [$baseCondition];
    $params = [];
    $types = '';

    if (!empty($existingIds)) {
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $conditions[] = "p.id NOT IN ($placeholders)";
        foreach ($existingIds as $id) {
            $params[] = $id;
            $types .= 'i';
        }
    }

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT 
                p.*,
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY RAND()
            LIMIT ?";

    $params[] = $remaining;
    $types .= 'i';

    $stmt = db_execute($sql, $types, $params);
    $randomProducts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_merge($products, $randomProducts);
}

/**
 * Get best of best products (top rated + best selling)
 * @param int $limit Number of products to return (default 15)
 * @return array
 */
function get_best_of_best_products($limit = 15)
{
    $conditions = ['p.is_active = 1'];
    $params = [];
    $types = '';

    // Get products with high ratings or high sales
    $conditions[] = '(p.average_rating >= 4.5 OR p.total_sold > 10)';

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT 
                p.*,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY p.average_rating DESC, p.total_sold DESC
            LIMIT ?";

    $params[] = $limit;
    $types .= 'i';

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // If we have enough best products, return them
    if (count($products) >= $limit) {
        return $products;
    }

    // If not enough, get random products to fill
    $remaining = $limit - count($products);
    $existingIds = array_column($products, 'id');

    $randomConditions = ['p.is_active = 1'];
    $randomParams = [];
    $randomTypes = '';

    if (!empty($existingIds)) {
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $randomConditions[] = "p.id NOT IN ($placeholders)";
        foreach ($existingIds as $id) {
            $randomParams[] = $id;
            $randomTypes .= 'i';
        }
    }

    $whereClause = 'WHERE ' . implode(' AND ', $randomConditions);

    $sql = "SELECT 
                p.*,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY RAND()
            LIMIT ?";

    $randomParams[] = $remaining;
    $randomTypes .= 'i';

    $stmt = db_execute($sql, $randomTypes, $randomParams);
    $result = $stmt->get_result();
    $randomProducts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return array_merge($products, $randomProducts);
}


/**
 * Get a single product by slug.
 *
 * @param string $slug
 *
 * @return array<string,mixed>|null
 */
function get_product_by_slug(string $slug): ?array
{
    $stmt = db_execute(
        'SELECT p.*, sc.name AS sub_category_name, mc.name AS main_category_name
         FROM products p
         JOIN sub_categories sc ON sc.id = p.sub_category_id
         JOIN main_categories mc ON mc.id = sc.main_category_id
         WHERE p.slug = ? AND p.is_active = 1
         LIMIT 1',
        's',
        [$slug]
    );

    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    return $product ?: null;
}

/**
 * Get variants of a product.
 *
 * @param int $productId
 *
 * @return array<int,array<string,mixed>>
 */
function get_product_variants(int $productId): array
{
    $stmt = db_execute(
        'SELECT id, sku, size, color, retail_price, stock_quantity, is_default
         FROM product_variants
         WHERE product_id = ? AND is_active = 1
         ORDER BY is_default DESC, id ASC',
        'i',
        [$productId]
    );

    $result   = $stmt->get_result();
    $variants = [];
    while ($row = $result->fetch_assoc()) {
        $variants[] = $row;
    }
    $stmt->close();

    return $variants;
}

/**
 * Get Products Reviews
 *
 * @param int $productId
 *
 * @return array<int,array<string,mixed>>
 */
function get_product_reviews(int $productId): array
{
    $stmt = db_execute(
        'SELECT id, product_id, user_id, order_id, order_item_id, rating, title, review, pros, cons, images, video_url, is_verified_purchase, is_anonymous, status, helpful_count, not_helpful_count, admin_response, admin_responded_at, admin_id
        FROM product_reviews
        WHERE product_id = ?
        ORDER BY rating DESC',
        'i',
        [$productId]
    );

    $result = $stmt->get_result();
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt->close();

    return $reviews;
}

/**
 * Get Total Product Reviews Count
 *
 * @param int $productId
 *
 * @return int
 */
function get_total_product_reviews_count(int $productId): int
{
    $stmt = db_execute("SELECT COUNT(*) as total_reviews FROM product_reviews WHERE product_id = ?", 'i', [$productId]);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total_reviews'];
}

/**
 * Get Average Product Rating
 *
 * @param int $productId
 *
 * @return float
 */
function get_average_product_rating(int $productId): float
{
    $stmt = db_execute("SELECT AVG(rating) as average_rating FROM product_reviews WHERE product_id = ?", 'i', [$productId]);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['average_rating'] ?? 0;
}

/**
 * Get Trending Every Sub Categories 5 products randomly
 *
 */
function get_trending_products_random($limit = 15, $currency = 'INR'): array
{

    // 🔥 Currency based column select
    $priceColumn = ($currency === 'USD')
        ? 'p.usd_base_retail_price'
        : 'p.base_retail_price';

    $mrpColumn = ($currency === 'USD')
        ? 'p.usd_mrp'
        : 'p.mrp';

    $stmt = db_execute("
        SELECT 
            p.*,
            -- ✅ override same keys (important)
            {$priceColumn} AS base_retail_price,
            {$mrpColumn} AS mrp,

            sc.name AS sub_category_name,
            mc.name AS main_category_name
        FROM products p
        JOIN sub_categories sc ON sc.id = p.sub_category_id
        JOIN main_categories mc ON mc.id = sc.main_category_id
        WHERE 
            p.is_active = 1
            AND sc.is_active = 1

            -- ✅ only products with image
            AND p.main_image IS NOT NULL
            AND p.main_image != ''

        ORDER BY RAND()
        LIMIT ?
    ", 'i', [$limit]);

    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $products ?: [];
}

/**
 * Get products with filters
 */
function get_productsBySubCat($filters = [], $currency = 'INR')
{
    // 🔥 Currency based column select
    $priceColumn = ($currency === 'USD')
        ? 'p.usd_base_retail_price'
        : 'p.base_retail_price';

    $mrpColumn = ($currency === 'USD')
        ? 'p.usd_mrp'
        : 'p.mrp';

    $conditions = ['p.is_active = 1'];
    $params = [];
    $types = '';

    if (isset($filters['sub_category_id'])) {
        $conditions[] = 'p.sub_category_id = ?';
        $params[] = $filters['sub_category_id'];
        $types .= 'i';
    }

    if (isset($filters['main_category_id'])) {
        $conditions[] = 'sc.main_category_id = ?';
        $params[] = $filters['main_category_id'];
        $types .= 'i';
    }

    if (isset($filters['min_price']) && $filters['min_price'] > 0) {
        $conditions[] = 'p.base_retail_price >= ?';
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if (isset($filters['max_price']) && $filters['max_price'] < 100000) {
        $conditions[] = 'p.base_retail_price <= ?';
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    if (!empty($filters['colors'])) {
        $placeholders = implode(',', array_fill(0, count($filters['colors']), '?'));
        $conditions[] = "p.color IN ($placeholders)";
        foreach ($filters['colors'] as $color) {
            $params[] = $color;
            $types .= 's';
        }
    }

    if (!empty($filters['ratings'])) {
        $ratingConditions = [];
        foreach ($filters['ratings'] as $rating) {
            $ratingConditions[] = "p.average_rating >= ?";
            $params[] = $rating;
            $types .= 'd';
        }
        $conditions[] = '(' . implode(' OR ', $ratingConditions) . ')';
    }

    if (isset($filters['stock_status']) && $filters['stock_status'] == 'in_stock') {
        $conditions[] = 'p.stock_quantity > 0';
    } elseif (isset($filters['stock_status']) && $filters['stock_status'] == 'out_of_stock') {
        $conditions[] = 'p.stock_quantity <= 0';
    }

    // Search
    if (!empty($filters['search'])) {
        $conditions[] = '(p.name LIKE ? OR p.product_code LIKE ? OR p.short_description LIKE ?)';
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $conditions[] = 'p.main_image IS NOT NULL';
    $conditions[] = "p.main_image != ''";

    // Sort order
    $orderBy = 'p.id DESC';
    switch ($filters['sort_by'] ?? 'default') {
        case 'price_asc':
            $orderBy = 'p.base_retail_price ASC';
            break;
        case 'price_desc':
            $orderBy = 'p.base_retail_price DESC';
            break;
        case 'newest':
            $orderBy = 'p.created_at DESC';
            break;
        case 'popular':
            $orderBy = 'p.total_sold DESC';
            break;
        case 'rating':
            $orderBy = 'p.average_rating DESC, p.review_count DESC';
            break;
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $limit = isset($filters['limit']) ? (int) $filters['limit'] : 12;
    $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;

    // Fixed SELECT query - added all missing fields
    $sql = "SELECT 
                p.id,
                p.sub_category_id,
                p.product_code,
                p.name,
                p.slug,
                p.short_description,
                p.description,
                p.product_type,
                p.has_variants,
                p.size,
                p.weight,
                p.dimensions,
                p.material,
                p.color,
                p.base_wholesale_price,
                p.cost_price,
                -- ✅ override same keys (important)
                {$priceColumn} AS base_retail_price,
                {$mrpColumn} AS mrp,
                p.selling_mode,
                p.min_order_quantity,
                p.max_order_quantity,
                p.bulk_min_quantity,
                p.is_bulk_only,
                p.stock_quantity,
                p.low_stock_threshold,
                p.track_inventory,
                p.allow_backorder,
                p.shipping_class,
                p.shipping_weight,
                p.free_shipping,
                p.tax_class,
                p.gst_rate,
                p.main_image,
                p.hover_image,
                p.video_url,
                p.meta_title,
                p.meta_description,
                p.meta_keywords,
                p.is_active,
                p.is_featured,
                p.is_new,
                p.is_on_sale,
                p.is_trending,
                p.is_bulk_item,
                p.bulk_pricing_model,
                p.has_tiered_pricing,
                p.total_sold,
                p.total_revenue,
                p.average_rating,
                p.review_count,
                p.view_count,
                p.created_at,
                p.updated_at,
                p.canonical_url,
                p.search_keywords,
                sc.name AS sub_category_name,
                mc.name AS main_category_name,
                mc.category_code
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
            $whereClause
            ORDER BY $orderBy
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $products;
}


/**
 * Get price range for current filters
 */
function get_price_range($filters = [])
{
    $conditions = ['p.is_active = 1'];
    $params = [];
    $types = '';

    if (isset($filters['sub_category_id'])) {
        $conditions[] = 'p.sub_category_id = ?';
        $params[] = $filters['sub_category_id'];
        $types .= 'i';
    }

    if (isset($filters['main_category_id'])) {
        $conditions[] = 'sc.main_category_id = ?';
        $params[] = $filters['main_category_id'];
        $types .= 'i';
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $sql = "SELECT MIN(p.base_retail_price) as min_price, MAX(p.base_retail_price) as max_price 
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            $whereClause";

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return [
        'min' => $row['min_price'] ?? 0,
        'max' => $row['max_price'] ?? 100000
    ];
}

/**
 * Get product colors with counts
 */
function get_product_colors_with_counts($filters = [])
{
    $conditions = ['p.is_active = 1', 'p.color IS NOT NULL', 'p.color != ""'];
    $params = [];
    $types = '';

    if (isset($filters['sub_category_id'])) {
        $conditions[] = 'p.sub_category_id = ?';
        $params[] = $filters['sub_category_id'];
        $types .= 'i';
    }

    if (isset($filters['main_category_id'])) {
        $conditions[] = 'sc.main_category_id = ?';
        $params[] = $filters['main_category_id'];
        $types .= 'i';
    }

    $whereClause = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT p.color, COUNT(*) as count 
            FROM products p
            LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
            $whereClause
            GROUP BY p.color
            ORDER BY count DESC";

    $stmt = db_execute($sql, $types, $params);
    $result = $stmt->get_result();
    $colors = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $colors;
}

/**
 * Get color code for CSS
 */
function getColorCode($color)
{
    if (empty($color)) {
        return '#6c757d';
    }

    $colorMap = [
        'red' => '#DB4437',
        'green' => '#41CF0F',
        'gray' => '#8e8e8e',
        'orange' => '#ffa500',
        'purple' => '#B615FD',
        'yellow' => '#FFD747',
        'olive' => '#AB9774',
        'dark blue' => '#1C58F2',
        'blue' => '#1C58F2',
        'black' => '#000000',
        'white' => '#FFFFFF',
        'brown' => '#8B4513',
        'pink' => '#FF69B4',
        'navy' => '#000080',
        'maroon' => '#800000',
        'teal' => '#008080',
        'silver' => '#C0C0C0',
        'gold' => '#FFD700',
        'beige' => '#F5F5DC',
        'ivory' => '#FFFFF0',
        'lavender' => '#E6E6FA',
        'coral' => '#FF7F50',
        'turquoise' => '#40E0D0',
        'magenta' => '#FF00FF',
        'cyan' => '#00FFFF',
        'indigo' => '#4B0082',
        'violet' => '#EE82EE',
        'lime' => '#00FF00',
        'aqua' => '#00FFFF',
        'tan' => '#D2B48C',
        'khaki' => '#F0E68C',
        'plum' => '#DDA0DD',
        'orchid' => '#DA70D6',
        'chocolate' => '#D2691E',
        'sienna' => '#A0522D',
        'salmon' => '#FA8072',
        'tomato' => '#FF6347',
        'crimson' => '#DC143C'
    ];

    $colorLower = strtolower(trim($color));

    // Try exact match
    if (isset($colorMap[$colorLower])) {
        return $colorMap[$colorLower];
    }

    // Try partial match
    foreach ($colorMap as $key => $code) {
        if (strpos($colorLower, $key) !== false) {
            return $code;
        }
    }

    return '#6c757d';
}

/**
 * Generate dynamic product image with text only on transparent background
 * @param string $text Product name/title
 * @param int $width Image width
 * @param int $height Image height
 * @param string $textColor Text color (hex)
 * @param int $fontSize Font size in pixels
 * @return string Image data URI
 */
function generate_product_image($text, $width = 500, $height = 500, $textColor = '#1a685b', $fontSize = 30)
{
    // Create truecolor image with alpha channel
    $image = imagecreatetruecolor($width, $height);

    // Enable alpha blending and save alpha
    imagesavealpha($image, true);

    // Make background transparent
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    // Convert text color from hex to RGB
    list($tr, $tg, $tb) = sscanf($textColor, "#%02x%02x%02x");
    $textColorRGB = imagecolorallocate($image, $tr, $tg, $tb);

    // Font file path - use a default system font if TTF not available
    $fontPath = __DIR__ . '/assets/fonts/Thesead.ttf';

    // Fallback fonts if custom font not found
    if (!file_exists($fontPath)) {
        // Try common system fonts
        $systemFonts = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/verdana.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/System/Library/Fonts/Helvetica.ttc',
        ];

        foreach ($systemFonts as $systemFont) {
            if (file_exists($systemFont)) {
                $fontPath = $systemFont;
                break;
            }
        }
    }

    // Prepare text - limit length and wrap
    $maxLength = 30;
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength) . '...';
    }

    // Wrap text into lines based on character count
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = '';
    $maxCharsPerLine = 20;

    foreach ($words as $word) {
        if (strlen($currentLine . ' ' . $word) <= $maxCharsPerLine) {
            $currentLine .= ($currentLine ? ' ' : '') . $word;
        } else {
            if ($currentLine) $lines[] = $currentLine;
            $currentLine = $word;
        }
    }
    if ($currentLine) $lines[] = $currentLine;

    // Calculate text dimensions
    $lineHeight = $fontSize + 10;
    $totalHeight = count($lines) * $lineHeight;
    $startY = ($height - $totalHeight) / 2;

    // Draw each line of text
    foreach ($lines as $index => $line) {
        $y = $startY + ($index * $lineHeight);

        if (file_exists($fontPath)) {
            // Use TrueType font for better quality
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($width - $textWidth) / 2;
            imagettftext($image, $fontSize, 0, $x, $y + $fontSize, $textColorRGB, $fontPath, $line);
        } else {
            // Fallback to built-in font if TTF not available
            $font = 5; // Built-in font size
            $charWidth = imagefontwidth($font);
            $charHeight = imagefontheight($font);
            $textWidth = strlen($line) * $charWidth;
            $x = ($width - $textWidth) / 2;
            $y = $startY + ($index * ($charHeight + 5));
            imagestring($image, $font, $x, $y, $line, $textColorRGB);
        }
    }

    // Output as base64 for direct use
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($imageData);
}

/**
 * Alternative: Generate image with shadow effect for better visibility
 */
function generate_product_image_with_shadow($text, $width = 500, $height = 500, $fontSize = 24, $textColor = '#1a685b', $shadowColor = '#cccccc')
{
    // Create truecolor image with alpha channel
    $image = imagecreatetruecolor($width, $height);

    // Enable alpha blending and save alpha
    imagesavealpha($image, true);

    // Make background transparent
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);

    // Convert colors
    list($tr, $tg, $tb) = sscanf($textColor, "#%02x%02x%02x");
    $textColorRGB = imagecolorallocate($image, $tr, $tg, $tb);

    list($sr, $sg, $sb) = sscanf($shadowColor, "#%02x%02x%02x");
    $shadowColorRGB = imagecolorallocate($image, $sr, $sg, $sb);

    // Font file path
    $fontPath = __DIR__ . '/assets/fonts/Thesead.ttf';

    // Fallback if font not found
    if (!file_exists($fontPath)) {
        $systemFonts = [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/verdana.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ];

        foreach ($systemFonts as $systemFont) {
            if (file_exists($systemFont)) {
                $fontPath = $systemFont;
                break;
            }
        }
    }

    // Prepare text
    $maxLength = 30;
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength) . '...';
    }

    // Wrap text
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = '';
    $maxCharsPerLine = 20;

    foreach ($words as $word) {
        if (strlen($currentLine . ' ' . $word) <= $maxCharsPerLine) {
            $currentLine .= ($currentLine ? ' ' : '') . $word;
        } else {
            if ($currentLine) $lines[] = $currentLine;
            $currentLine = $word;
        }
    }
    if ($currentLine) $lines[] = $currentLine;

    // Calculate dimensions
    $lineHeight = $fontSize + 10;
    $totalHeight = count($lines) * $lineHeight;
    $startY = ($height - $totalHeight) / 2;

    // Draw shadow and text
    $shadowOffset = 2;

    foreach ($lines as $index => $line) {
        $y = $startY + ($index * $lineHeight);

        if (file_exists($fontPath)) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
            $textWidth = $bbox[2] - $bbox[0];
            $x = ($width - $textWidth) / 2;

            // Draw shadow
            imagettftext($image, $fontSize, 0, $x + $shadowOffset, $y + $fontSize + $shadowOffset, $shadowColorRGB, $fontPath, $line);
            // Draw main text
            imagettftext($image, $fontSize, 0, $x, $y + $fontSize, $textColorRGB, $fontPath, $line);
        } else {
            $charWidth = imagefontwidth(5);
            $charHeight = imagefontheight(5);
            $textWidth = strlen($line) * $charWidth;
            $x = ($width - $textWidth) / 2;
            $y = $startY + ($index * ($charHeight + 5));

            // Draw shadow
            imagestring($image, 5, $x + $shadowOffset, $y + $shadowOffset, $line, $shadowColorRGB);
            // Draw main text
            imagestring($image, 5, $x, $y, $line, $textColorRGB);
        }
    }

    // Output
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    return 'data:image/png;base64,' . base64_encode($imageData);
}

/**
 * Get product image with fallback to generated image
 * @param array $product Product data
 * @param string $type Image type (main, hover, thumbnail)
 * @return string Image URL or data URI
 */
function get_product_image($product, $type = 'main', $fontSize = 30)
{
    $imagePath = '';

    if ($type == 'main' && !empty($product['main_image'])) {
        $imagePath = './uploads/products/main/' . $product['main_image'];
    } elseif ($type == 'hover' && !empty($product['hover_image'])) {
        $imagePath = './uploads/products/hover/' . $product['hover_image'];
    } elseif ($type == 'thumbnail' && !empty($product['thumbnail_image'])) {
        $imagePath = './uploads/products/thumbnail/' . $product['thumbnail_image'];
    } elseif ($type == 'gallery' && !empty($product['image_url'])) {
        $imagePath = './uploads/products/gallery/' . $product['image_url'];
    }

    // Check if file exists and is readable
    if (!empty($imagePath) && file_exists($imagePath) && is_readable($imagePath)) {
        return $imagePath;
    }

    // Generate dynamic image with product name
    $productName = $product['name'] ?? 'Product';
    $width = 500;
    $height = 500;

    // Generate different background colors based on product ID for variety
    $colors = ['#1a685b', '#2c7a6b', '#3e8b7b', '#509c8b', '#62ad9b'];
    $bgColor = $colors[($product['id'] ?? 1) % count($colors)];

    return generate_product_image_with_shadow($productName, $width, $height, $fontSize, $bgColor);
}

/**
 * Generate thumbnail image (smaller version)
 */
function get_product_thumbnail($product, $size = 'small')
{
    $imagePath = '';

    if (!empty($product['main_image'])) {
        $imagePath = './uploads/products/main/' . $product['main_image'];
    } elseif (!empty($product['thumbnail_image'])) {
        $imagePath = './uploads/products/thumbnail/' . $product['thumbnail_image'];
    }

    if (!empty($imagePath) && file_exists($imagePath) && is_readable($imagePath)) {
        return $imagePath;
    }

    // Generate smaller dynamic image
    $productName = $product['name'] ?? 'Product';
    $width = $size == 'small' ? 200 : 300;
    $height = $size == 'small' ? 200 : 300;

    $colors = ['#1a685b', '#2c7a6b', '#3e8b7b', '#509c8b', '#62ad9b'];
    $bgColor = $colors[($product['id'] ?? 1) % count($colors)];

    return generate_product_image($productName, $width, $height, $bgColor);
}

function get_user_wishlist_items(int $user_id, string $currency): array
{
    // Normalize currency
    $currency = strtoupper($currency);

    // Price field mapping (future-proof)
    $priceMap = [
        'USD' => 'p.usd_base_retail_price',
        'INR' => 'p.base_retail_price'
    ];

    // Default fallback
    $priceField = $priceMap[$currency] ?? $priceMap['INR'];

    $stmt = db_execute(
        "SELECT 
            p.id,
            p.name,
            {$priceField} AS price,
            p.main_image,
            p.color,
            p.size
         FROM user_wishlist w
         JOIN products p ON w.product_id = p.id
         WHERE w.user_id = ?
         ORDER BY w.created_at DESC",
        'i',
        [$user_id]
    );

    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items ?: [];
}

function render_empty_wishlist(): void
{
    echo '
    <tr>
        <td colspan="6" class="text-center">
            <div class="empty_wishlist">
                <h4>No items in your wishlist</h4>
                <p>Add products you like to your wishlist</p> <br>
                <a href="shop.php" class="common_btn">Continue Shopping</a>
            </div>
        </td>
    </tr>';
}

function get_user_cart(int $user_id): array
{
    $stmt = db_execute(
        "SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.unit_price,
            ci.total_price,
            ci.cart_id,
            p.name,
            p.main_image,
            p.color,
            p.size
         FROM cart_items ci
         JOIN carts c ON ci.cart_id = c.id
         JOIN products p ON ci.product_id = p.id
         WHERE c.user_id = ? AND c.status = 'active'
         ORDER BY ci.id DESC",
        'i',
        [$user_id]
    );

    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items ?: [];
}

function remove_cart_item(int $cart_item_id, int $user_id): bool
{
    // First, get the cart item and verify ownership
    $stmt = db_execute(
        "SELECT ci.cart_id FROM cart_items ci
         JOIN carts c ON ci.cart_id = c.id
         WHERE ci.id = ? AND c.user_id = ? AND c.status = 'active'",
        'ii',
        [$cart_item_id, $user_id]
    );

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return false; // Item not found or not owned by user
    }

    $row = $result->fetch_assoc();
    $cart_id = $row['cart_id'];
    $stmt->close();

    // Delete the cart item
    db_execute("DELETE FROM cart_items WHERE id = ?", 'i', [$cart_item_id]);

    // Update cart totals
    update_cart_totals($cart_id);

    return true;
}

// all cart items for checkout page with product details and cart totals
function checkout_page_cart_items(int $user_id): array
{
    $stmt = db_execute(
        "SELECT 
            ci.id,
            ci.product_id,
            ci.quantity,
            ci.unit_price,
            ci.total_price,
            p.name,
            p.main_image,
            p.color,
            p.size,
            c.total_items,
            c.total_quantity,
            c.subtotal,
            c.discount_amount,
            c.shipping_amount,
            c.tax_amount,
            c.grand_total,
            c.session_id,
            c.id as cart_id
         FROM cart_items ci
         JOIN carts c ON ci.cart_id = c.id
         JOIN products p ON ci.product_id = p.id
         WHERE c.user_id = ? AND c.status = 'active'
         ORDER BY ci.id DESC",
        'i',
        [$user_id]
    );

    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$result) return [];

    $cart = [];
    $items = [];

    foreach ($result as $index => $row) {

        // Cart data (sirf ek baar set karna hai)
        if ($index == 0) {
            $cart = [
                "cart_id"        => $row['cart_id'],
                "session_id"     => $row['session_id'],
                "total_items"     => $row['total_items'],
                "total_quantity"  => $row['total_quantity'],
                "subtotal"        => $row['subtotal'],
                "discount_amount" => $row['discount_amount'],
                "shipping_amount" => $row['shipping_amount'],
                "tax_amount"      => $row['tax_amount'],
                "grand_total"     => $row['grand_total'],
            ];
        }

        // Items array
        $items[] = [
            "id"          => $row['id'],
            "product_id"  => $row['product_id'],
            "quantity"    => $row['quantity'],
            "unit_price"  => $row['unit_price'],
            "total_price" => $row['total_price'],
            "name"        => $row['name'],
            "main_image"  => $row['main_image'],
            "color"       => $row['color'],
            "size"        => $row['size'],
        ];
    }

    return [
        "cart"  => $cart,
        "items" => $items
    ];
}

function render_empty_cart(): void
{
    echo '
    <tr>
        <td colspan="7" class="text-center">
            <div class="empty_cart">
                <h4>Your cart is empty</h4>
                <p>Add some products to continue shopping</p> <br>
                <a href="shop.php" class="common_btn">Continue Shopping</a>
            </div>
        </td>
    </tr>';
}

/**
 * Update cart totals (subtotal, grand_total, etc.)
 * 
 * @param int $cart_id
 * @return void
 */
function update_cart_totals(int $cart_id, string $currency): void
{
    // Get totals from cart_items
    $stmt = db_execute(
        "SELECT 
            COUNT(*) as total_items,
            COALESCE(SUM(quantity), 0) as total_quantity,
            COALESCE(SUM(total_price), 0) as subtotal
         FROM cart_items
         WHERE cart_id = ?",
        'i',
        [$cart_id]
    );

    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $total_items    = (int) $row['total_items'];
    $total_quantity = (int) $row['total_quantity'];
    $subtotal       = (float) $row['subtotal'];

    // Calculate shipping
    $shipping = 0;
    if ($subtotal < 1000) {
        if ($currency === 'USD') {
            $shipping = 5; // Flat $5 shipping for orders under $1000
        } else {
            $shipping = 50; // Flat ₹50 shipping for orders under ₹1000
        }
    }

    // Calculate discount (example logic)
    $discount = 0;
    if ($total_quantity >= 10) {
        $discount = $subtotal * 0.05; // 5% discount for bulk orders
    }

    $tax = 0; // No GST as per your requirement
    $grand_total = $subtotal - $discount + $shipping + $tax;

    // Update cart
    db_execute(
        "UPDATE carts SET
            total_items = ?,
            total_quantity = ?,
            subtotal = ?,
            discount_amount = ?,
            shipping_amount = ?,
            tax_amount = ?,
            grand_total = ?,
            updated_at = NOW()
         WHERE id = ?",
        'iiddiddi',
        [
            $total_items,
            $total_quantity,
            $subtotal,
            $discount,
            $shipping,
            $tax,
            $grand_total,
            $cart_id
        ]
    );
}

function get_user_order_count(int $user_id, string $status = 'all'): int
{
    $query = "SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?";
    $params = [$user_id];
    $types = 'i';

    if ($status !== 'all') {
        $query .= " AND payment_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $stmt = db_execute($query, $types, $params);
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return (int) $row['order_count'];
}

function get_user_recent_orders(int $user_id, string $status = 'all', int $limit = 5): array
{
    $query = "SELECT id, order_number, amount_paid, amount_due, payment_status, created_at FROM orders WHERE user_id = ?";
    $params = [$user_id];
    $types = 'i';

    if ($status !== 'all') {
        $query .= " AND payment_status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $query .= " ORDER BY created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';

    $stmt = db_execute($query, $types, $params);

    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $orders ?: [];
}

function encrypt_id($id)
{
    $key = ADIDEV_ENCRYPTION_KEY; // change this
    $iv = substr(hash('sha256', $key), 0, 16);

    return urlencode(base64_encode(
        openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv)
    ));
}

function decrypt_id($encrypted)
{
    $key = ADIDEV_ENCRYPTION_KEY;
    $iv = substr(hash('sha256', $key), 0, 16);

    return openssl_decrypt(
        base64_decode(urldecode($encrypted)),
        'AES-256-CBC',
        $key,
        0,
        $iv
    );
}
