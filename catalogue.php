<?php
// catalogue.php
// Product Catalogue Generator with A4 Grid Layout - 12 Products per Page

// Database connection

if($_SERVER['SERVER_NAME'] == 'localhost'){
    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $dbname = '20260307_adidev'; // Change here if your database name differs
}else{
    $host = 'localhost';
    $username = 'u409719797_ecomadidev';
    $password = 'w=hA8A+2';
    $dbname = 'u409719797_ecomadidev'; // Change here if your database name differs
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Company Information
$company = [
    'name' => 'Adidev Manufacturing Sales And Services Pvt. Ltd.',
    'logo' => 'logo.png',
    'address' => 'H1 279, KARTIK ORAON CHOWK HARMU COLONY, RANCHI, JHARKHAND, 834002, INDIA',
    'phone' => '+91 7369084701 | +91 7541865329',
    'email' => 'care@adidevmanufacturing.com | support@adidevmanufacturing.com',
    'website' => 'www.adidevmanufacturing.com',
    'gst' => '20ABCCA8461B1Z9'
];

// Theme Colors
$themeColorOne = '#164580'; // Dark Blue
$themeColorTwo = '#f36c1e'; // Orange

// Fetch all products with their variants
$query = "SELECT 
    p.id,
    p.name as product_name,
    p.sub_category_id,
    sc.name as category_name,
    mc.name as main_category_name,
    p.base_wholesale_price,
    p.mrp,
    p.bulk_min_quantity,
    (SELECT GROUP_CONCAT(CONCAT(v.size, '|', v.wholesale_price) SEPARATOR ';') 
     FROM product_variants v 
     WHERE v.product_id = p.id AND v.is_active = 1) as variant_data
FROM products p
LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
WHERE p.is_active = 1
ORDER BY mc.sort_order, sc.sort_order, p.id";

$stmt = $pdo->query($query);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to parse variant data
function parseVariants($variant_data)
{
    $variants = [];
    if ($variant_data) {
        $parts = explode(';', $variant_data);
        foreach ($parts as $part) {
            list($size, $price) = explode('|', $part);
            $variants[] = ['size' => $size, 'price' => $price];
        }
    }
    return $variants;
}

// Function to format price
function formatPrice($price)
{
    return '₹ ' . number_format($price, 0);
}

// Function to get corner image for page (alternating positions)
function getCornerPosition($page_number)
{
    if ($page_number % 3 == 1) { // Pages 1, 4, 7...
        return 'bottom-left';
    } elseif ($page_number % 3 == 2) { // Pages 2, 5, 8...
        return 'bottom-right';
    } else { // Pages 3, 6, 9...
        return 'top-right';
    }
}

// Split products into pages (12 per page)
$products_per_page = 6;
$total_products = count($products);
$total_pages = ceil($total_products / $products_per_page);
$paginated_products = array_chunk($products, $products_per_page);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Catalogue - <?php echo $company['name']; ?></title>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* A4 Print Styles */
        @page {
            size: A4;
            margin: 0.4in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body {
            background: #f4f4f4;
            padding: 20px;
        }

        .catalogue {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .page {
            page-break-after: always;
            page-break-inside: avoid;
            position: relative;
            min-height: 297mm;
            padding: 12mm 12mm 18mm 12mm;
            background: white;
            overflow: hidden;
        }

        /* Decorative Corner Elements */
        .corner-decoration {
            position: absolute;
            width: 180px;
            height: 180px;
            opacity: 0.08;
            pointer-events: none;
            z-index: 1;
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }

        .corner-bottom-left {
            bottom: 5mm;
            left: 5mm;
            background-image: url('images/corners/corner-design-1.png');
        }

        .corner-bottom-right {
            bottom: 5mm;
            right: 5mm;
            background-image: url('images/corners/corner-design-2.png');
        }

        .corner-top-right {
            top: 5mm;
            right: 5mm;
            background-image: url('images/corners/corner-design-3.png');
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8mm;
            padding-bottom: 3mm;
            border-bottom: 3px solid <?php echo $themeColorTwo; ?>;
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            color: <?php echo $themeColorOne; ?>;
            letter-spacing: 1px;
        }

        .company-name span {
            color: <?php echo $themeColorTwo; ?>;
            font-weight: 400;
        }

        .company-tagline {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-top: 2px;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 8mm;
            left: 12mm;
            right: 12mm;
            font-size: 9px;
            color: #555;
            text-align: center;
            padding-top: 3mm;
            z-index: 2;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 5px;
        }

        .footer-content i {
            color: <?php echo $themeColorTwo; ?>;
            margin-right: 3px;
            width: 14px;
        }

        /* Page Title */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5mm;
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-size: 20px;
            font-weight: 600;
            color: <?php echo $themeColorOne; ?>;
            padding-left: 8px;
            border-left: 4px solid <?php echo $themeColorTwo; ?>;
        }

        .bulk-badge {
            background: <?php echo $themeColorTwo; ?>;
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(243, 108, 30, 0.3);
        }

        .bulk-badge i {
            margin-right: 5px;
        }

        /* Category Stats */
        .category-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 5mm;
            font-size: 11px;
            color: #666;
            position: relative;
            z-index: 2;
        }

        .stat-item i {
            color: <?php echo $themeColorTwo; ?>;
            width: 16px;
            margin-right: 3px;
        }

        /* Product Grid - 3x4 (12 products per page) */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm;
            position: relative;
            z-index: 2;
        }

        .product-card {
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            padding: 4mm 3mm;
            background: white;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            page-break-inside: avoid;
            break-inside: avoid;
            position: relative;
            border-top: 3px solid <?php echo $themeColorTwo; ?>;
        }

        .product-card:hover {
            box-shadow: 0 5px 15px rgba(22, 69, 128, 0.1);
            border-color: <?php echo $themeColorOne; ?>;
        }

        .product-badge {
            background: <?php echo $themeColorOne; ?>;
            color: white;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 0 15px 15px 0;
            display: inline-block;
            margin-bottom: 3mm;
            margin-left: -3mm;
            font-weight: 500;
        }

        .product-badge i {
            margin-right: 4px;
            font-size: 8px;
        }

        .product-name {
            font-size: 14px;
            font-weight: 600;
            color: <?php echo $themeColorOne; ?>;
            margin-bottom: 3mm;
            line-height: 1.4;
            min-height: 40px;
            padding-right: 5px;
        }

        .product-variants {
            margin: 3mm 0;
            background: #f9f9f9;
            padding: 2mm;
            border-radius: 6px;
        }

        .variant-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 2mm;
            display: block;
        }

        .variant-label i {
            color: <?php echo $themeColorTwo; ?>;
            margin-right: 3px;
        }

        .variant-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }

        .variant-tag {
            background: white;
            border: 1px solid #ddd;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 10px;
            color: #555;
            transition: all 0.2s;
        }

        .variant-tag:hover {
            border-color: <?php echo $themeColorTwo; ?>;
            background: #fff5ed;
        }

        .price-info {
            margin-top: 3mm;
            padding-top: 3mm;
            border-top: 1px dashed #eee;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .price-block {
            text-align: left;
        }

        .bulk-price {
            font-size: 16px;
            font-weight: 700;
            color: <?php echo $themeColorTwo; ?>;
        }

        .price-label {
            font-size: 9px;
            color: #888;
            display: block;
        }

        .mrp {
            font-size: 11px;
            color: #999;
            text-decoration: line-through;
        }

        .min-order {
            font-size: 10px;
            color: <?php echo $themeColorOne; ?>;
            background: #f0f4fa;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 3mm;
            font-weight: 500;
        }

        .min-order i {
            margin-right: 3px;
        }

        .action-buttons {
            display: flex;
            gap: 2mm;
            margin-top: 4mm;
        }

        .btn {
            flex: 1;
            padding: 5px 0;
            font-size: 11px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-primary {
            background: <?php echo $themeColorOne; ?>;
            color: white;
        }

        .btn-primary:hover {
            background: #0f3566;
        }

        .btn-outline {
            background: white;
            color: <?php echo $themeColorTwo; ?>;
            border: 1px solid <?php echo $themeColorTwo; ?>;
        }

        .btn-outline:hover {
            background: <?php echo $themeColorTwo; ?>;
            color: white;
        }

        /* Bulk Special Ribbon */
        .bulk-ribbon {
            position: absolute;
            top: -3px;
            right: 5px;
            background: <?php echo $themeColorTwo; ?>;
            color: white;
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 0 0 10px 10px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .bulk-ribbon i {
            margin-right: 3px;
        }

        /* Page Numbers */
        .page-number {
            position: absolute;
            bottom: 5mm;
            right: 12mm;
            font-size: 10px;
            color: #aaa;
            z-index: 2;
        }

        /* Page Progress */
        .page-progress {
            position: absolute;
            bottom: 5mm;
            left: 12mm;
            font-size: 10px;
            color: <?php echo $themeColorOne; ?>;
            z-index: 2;
            background: #f5f5f5;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .page-progress i {
            color: <?php echo $themeColorTwo; ?>;
            margin-right: 4px;
        }

        /* Print Controls */
        .print-controls {
            text-align: right;
            margin-bottom: 20px;
        }

        .print-btn {
            background: <?php echo $themeColorOne; ?>;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(22, 69, 128, 0.3);
        }

        .print-btn i {
            margin-right: 8px;
        }

        .print-btn:hover {
            background: <?php echo $themeColorTwo; ?>;
            transform: translateY(-2px);
        }

        .print-btn.secondary {
            background: <?php echo $themeColorTwo; ?>;
        }

        .print-btn.secondary:hover {
            background: #d55a0f;
        }

        /* Last Page Special Styles */
        .contact-section {
            position: relative;
            z-index: 2;
            max-width: 500px;
            margin: 10mm auto;
            text-align: center;
        }

        .contact-title {
            font-size: 28px;
            color: <?php echo $themeColorOne; ?>;
            margin-bottom: 8mm;
            font-weight: 600;
        }

        .contact-title span {
            color: <?php echo $themeColorTwo; ?>;
        }

        .contact-card {
            background: linear-gradient(135deg, #f8f9fa, white);
            padding: 8mm;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #eee;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .contact-item:last-child {
            border-bottom: none;
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: <?php echo $themeColorOne; ?>;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .contact-icon.orange {
            background: <?php echo $themeColorTwo; ?>;
        }

        .contact-text {
            text-align: left;
            flex: 1;
        }

        .contact-text h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 3px;
        }

        .contact-text p {
            font-size: 16px;
            color: <?php echo $themeColorOne; ?>;
            font-weight: 500;
        }

        .contact-text small {
            font-size: 11px;
            color: #888;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm;
            margin-top: 8mm;
        }

        .benefit-item {
            text-align: center;
            padding: 4mm;
        }

        .benefit-item i {
            font-size: 28px;
            color: <?php echo $themeColorTwo; ?>;
            margin-bottom: 3mm;
        }

        .benefit-item h5 {
            font-size: 13px;
            color: <?php echo $themeColorOne; ?>;
            margin-bottom: 2mm;
        }

        .benefit-item p {
            font-size: 10px;
            color: #666;
        }

        /* Responsive */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .catalogue {
                box-shadow: none;
            }

            .print-controls {
                display: none;
            }

            .product-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .corner-decoration {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="print-controls">
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Catalogue
        </button>
        <button class="print-btn secondary" onclick="window.location.href='?export=pdf'">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>

    <div class="catalogue">
        <?php foreach ($paginated_products as $page_num => $page_products):
            $page_number = $page_num + 1;
            $corner_position = getCornerPosition($page_number);
        ?>

            <!-- Page <?php echo $page_number; ?> of <?php echo $total_pages; ?> -->
            <div class="page">
                <!-- Decorative Corner -->
                <div class="corner-decoration corner-<?php echo $corner_position; ?>"></div>

                <!-- Header -->
                <div class="header">
                    <div class="logo">
                        <img src="logo.png" alt="Adidev Manufacturing Sales And Services Pvt. Ltd." width="90" height="70">
                    </div>
                    <div class="company-info">
                        <div class="company-name">Adidev Manufacturing <span> Sales And Services Pvt. Ltd.</span></div>
                        <div class="company-tagline">
                            <i class="fas fa-star" style="color: <?php echo $themeColorTwo; ?>;"></i>
                            Handcrafted , Textile & FMCG Products
                            <i class="fas fa-star" style="color: <?php echo $themeColorTwo; ?>;"></i>
                        </div>
                    </div>
                </div>

                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-title">
                        <?php
                        // Get unique categories on this page
                        $page_cats = array_unique(array_column($page_products, 'main_category_name'));
                        echo implode(' • ', $page_cats);
                        ?>
                    </div>
                    <div class="bulk-badge">
                        <i class="fas fa-tags"></i> Bulk Rate Available
                    </div>
                </div>

                <!-- Category Stats -->
                <div class="category-stats">
                    <div class="stat-item">
                        <i class="fas fa-cubes"></i>
                        <?php echo count($page_products); ?> Products
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-arrow-up"></i>
                        Min Order: 10 Pcs
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-truck"></i>
                        Free Shipping on ₹10,000+
                    </div>
                </div>

                <!-- Product Grid - 3x4 (12 products) -->
                <div class="product-grid">
                    <?php foreach ($page_products as $product):
                        $variants = parseVariants($product['variant_data']);
                        $has_variants = !empty($variants);
                    ?>
                        <div class="product-card">
                            <?php if ($product['bulk_min_quantity'] <= 15): ?>
                                <div class="bulk-ribbon">
                                    <i class="fas fa-fire"></i> BULK DEAL
                                </div>
                            <?php endif; ?>

                            <div class="product-badge">
                                <i class="fas fa-tag"></i>
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </div>

                            <div class="product-name">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </div>

                            <?php if ($has_variants): ?>
                                <div class="product-variants">
                                    <span class="variant-label">
                                        <i class="fas fa-ruler"></i> Available Sizes:
                                    </span>
                                    <div class="variant-tags">
                                        <?php
                                        $display_variants = array_slice($variants, 0, 4);
                                        foreach ($display_variants as $variant):
                                        ?>
                                            <span class="variant-tag">
                                                <?php echo htmlspecialchars($variant['size']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if (count($variants) > 4): ?>
                                            <span class="variant-tag">+<?php echo count($variants) - 4; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="price-info">
                                    <div class="price-block">
                                        <span class="price-label">Starting from</span>
                                        <span class="bulk-price">
                                            <?php echo formatPrice(min(array_column($variants, 'price'))); ?>
                                        </span>
                                        <span class="gst-inclusive" style="font-size: 10px; color: #999; margin-left: 5px; background:rgb(19, 145, 2); padding: 2px 5px; border-radius: 5px;color: white;"> GST INC. </span>
                                    </div>
                                    <?php if ($product['mrp']): ?>
                                        <span class="mrp"><?php echo formatPrice($product['mrp']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="price-info">
                                    <div class="price-block">
                                        <span class="price-label">Bulk Price</span>
                                        <span class="bulk-price">
                                            <?php echo formatPrice($product['base_wholesale_price']); ?>
                                        </span>
                                        <!-- GST Inclusive -->
                                        <span class="gst-inclusive" style="font-size: 10px; color: #999; margin-left: 5px; background:rgb(19, 145, 2); padding: 2px 5px; border-radius: 5px;color: white;"> GST INC. </span>
                                    </div>
                                    <?php if ($product['mrp'] > $product['base_wholesale_price']): ?>
                                        <span class="mrp"><?php echo formatPrice($product['mrp']); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="min-order">
                                <i class="fas fa-shopping-bag"></i>
                                Min: <?php echo $product['bulk_min_quantity']; ?> pieces
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Add empty cards if less than 12 products (to maintain grid) -->
                    <?php for ($i = count($page_products); $i < $products_per_page; $i++): ?>
                        <div style="visibility: hidden; height: 0; margin: 0; padding: 0;"></div>
                    <?php endfor; ?>
                </div>

                <!-- Bulk Order Note -->
                <div style="
                position: relative;
                z-index: 2;
                background: #fafafa;
                padding: 1.5mm 1mm;
                margin-top: 6mm;
                font-size: 10px;
                border-radius: 0 5px 5px 0;
                color: #555;
                margin-bottom: 4px;
            ">
                    <i class="fas fa-info-circle" style="color: <?php echo $themeColorOne; ?>; margin-right: 5px;"></i>
                    <strong>Bulk Orders:</strong> Mix & match any designs | GST invoice available |
                    <span style="color: <?php echo $themeColorTwo; ?>;">Sample orders accepted</span>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="footer-content">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo $company['address']; ?></span>
                        <span><i class="fas fa-phone-alt"></i> <?php echo $company['phone']; ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo $company['email']; ?></span>
                        <span><i class="fas fa-globe"></i> <?php echo $company['website']; ?></span>
                    </div>
                    <div style="margin-top: 2mm; font-size: 8px; color: #999;">
                        GST: <?php echo $company['gst']; ?> | Prices exclusive of GST | Minimum order: 10 pieces per design
                    </div>
                </div>

                <!-- Page Info -->
                <div class="page-progress">
                    <i class="fas fa-layer-group"></i> Page <?php echo $page_number; ?> of <?php echo $total_pages; ?>
                </div>
                <div class="page-number">
                    <i class="far fa-calendar-alt"></i> <?php echo date('d M Y'); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Last Page: Contact & Order Information -->
        <div class="page">
            <!-- Decorative Corner -->
            <div class="corner-decoration corner-top-right"></div>

            <!-- Header -->
            <div class="header">
                <div class="logo">
                    <img src="logo.png" alt="Adidev Manufacturing Sales And Services Pvt. Ltd." width="90" height="70">
                </div>
                <div class="company-info">
                    <div class="company-name">Adidev Manufacturing <span> Sales And Services Pvt. Ltd.</span></div>
                    <div class="company-tagline">
                        <i class="fas fa-star" style="color: <?php echo $themeColorTwo; ?>;"></i>
                        Your Trusted Bulk Order Partner
                        <i class="fas fa-star" style="color: <?php echo $themeColorTwo; ?>;"></i>
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <div class="contact-title">
                    READY TO <span>ORDER?</span>
                </div>

                <div class="contact-card">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Call Us</h4>
                            <p><?php echo $company['phone']; ?></p>
                            <small>Mon-Sat, 10AM to 7PM</small>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon orange">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="contact-text">
                            <h4>WhatsApp</h4>
                            <p><?php echo $company['phone']; ?></p>
                            <small>Quick replies within 1 hour</small>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p><?php echo $company['email']; ?></p>
                            <small>24x7 support</small>
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon orange">
                            <i class="fas fa-store"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Visit Us</h4>
                            <p><?php echo $company['address']; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Benefits Grid -->
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <i class="fas fa-rupee-sign"></i>
                        <h5>Best Prices</h5>
                        <p>Direct from artisans</p>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-truck"></i>
                        <h5>Fast Delivery</h5>
                        <p>Pan India shipping</p>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-file-invoice"></i>
                        <h5>GST Billing</h5>
                        <p>For business orders</p>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-flask"></i>
                        <h5>Samples</h5>
                        <p>Available on request</p>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-paint-brush"></i>
                        <h5>Customization</h5>
                        <p>Bulk customization</p>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-shield-alt"></i>
                        <h5>Quality Assured</h5>
                        <p>Handpicked products</p>
                    </div>
                </div>
            </div>

            <!-- Order Form Note -->
            <div style="
                position: relative;
                z-index: 2;
                text-align: center;
                margin-top: 8mm;
                padding: 3mm;
                background: #f0f7fa;
                border-radius: 8px;
            ">
                <i class="fas fa-clipboard-list" style="color: <?php echo $themeColorOne; ?>; margin-right: 5px;"></i>
                <strong>Quick Order:</strong> Mention product codes and quantities when contacting us
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="footer-content">
                    <span><i class="fas fa-map-marker-alt"></i> <?php echo $company['address']; ?></span>
                    <span><i class="fas fa-phone-alt"></i> <?php echo $company['phone']; ?></span>
                    <span><i class="fas fa-envelope"></i> <?php echo $company['email']; ?></span>
                </div>
                <div style="margin-top: 2mm;">
                    <i class="fas fa-copyright"></i> <?php echo date('Y'); ?> Hastkala Emporium. All rights reserved.
                </div>
            </div>

            <!-- Page Info -->
            <div class="page-progress">
                <i class="fas fa-check-circle" style="color: <?php echo $themeColorTwo; ?>;"></i> Thank you for your business
            </div>
        </div>
    </div>

    <script>
        // Prevent page breaks inside product cards
        window.onload = function() {
            // Ensure all product cards stay together
            var cards = document.querySelectorAll('.product-card');
            cards.forEach(function(card) {
                card.style.pageBreakInside = 'avoid';
                card.style.breakInside = 'avoid';
            });
        };

        // Inquiry button handler
        document.querySelectorAll('.btn-primary').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productName = e.target.closest('.product-card').querySelector('.product-name').textContent;
                alert('📧 Inquiry sent for: ' + productName + '\nOur team will contact you shortly!');
            });
        });

        // Sample button handler
        document.querySelectorAll('.btn-outline').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productName = e.target.closest('.product-card').querySelector('.product-name').textContent;
                alert('🧪 Sample request submitted for: ' + productName + '\nWe will process your request.');
            });
        });
    </script>
</body>

</html>