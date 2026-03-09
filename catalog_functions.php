<?php
// Catalog and product related helper functions for Adidev

require_once __DIR__ . '/config.php';

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
function get_sub_categories_grouped_by_main(array $mainCategoryIds, int $limit = 12 , string $orderBy = 'name', string $orderDirection = 'ASC'): array
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

/**
 * Get products for listing (shop, category, home sections).
 *
 * Supported filters:
 * - 'sub_category_id' => int
 * - 'is_featured'     => bool
 * - 'is_new'          => bool
 * - 'is_on_sale'      => bool
 *
 * @param array<string,mixed> $filters
 * @param int                 $limit
 * @param int                 $offset
 *
 * @return array<int,array<string,mixed>>
 */
function get_products(array $filters = [], int $limit = 12, int $offset = 0): array
{
    $where  = ['is_active = 1'];
    $params = [];
    $types  = '';

    if (!empty($filters['sub_category_id'])) {
        $where[]  = 'sub_category_id = ?';
        $params[] = (int) $filters['sub_category_id'];
        $types   .= 'i';
    }


    if (!empty($filters['is_featured'])) {
        $where[] = 'is_featured = 1';
    }

    if (!empty($filters['is_new'])) {
        $where[] = 'is_new = 1';
    }

    if (!empty($filters['is_on_sale'])) {
        $where[] = 'is_on_sale = 1';
    }

    $whereSql = implode(' AND ', $where);

    $sql = "SELECT id, name, slug, short_description, mrp, base_retail_price, average_rating, review_count
            FROM products
            WHERE {$whereSql}
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";

    $types   .= 'ii';
    $params[] = $limit;
    $params[] = $offset;

    $stmt = db_execute($sql, $types, $params);

    $result   = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    return $products;
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
function get_trending_every_sub_categories_5_products_randomly(): array
{
    $products = [];

    $stmt = db_execute("
        SELECT id 
        FROM sub_categories
        WHERE is_active = 1
        ORDER BY RAND()
        LIMIT 5
    ");

    $result = $stmt->get_result();
    $subCategories = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (!$subCategories) {
        return [];
    }

    $subIds = array_column($subCategories, 'id');

    foreach ($subIds as $subId) {

        $stmt = db_execute("
            SELECT 
                p.*,
                sc.name AS sub_category_name,
                mc.name AS main_category_name
            FROM products p
            JOIN sub_categories sc ON sc.id = p.sub_category_id
            JOIN main_categories mc ON mc.id = sc.main_category_id
            WHERE 
                p.is_active = 1
                AND sc.is_active = 1
                AND p.sub_category_id = ?
            ORDER BY RAND()
            LIMIT 3
        ", 'i', [$subId]);

        $result = $stmt->get_result();
        $rows   = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $products = array_merge($products, $rows);
    }
    shuffle($products);
    return $products;
}
