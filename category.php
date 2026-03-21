<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/catalog_functions.php';

// SEO meta for category listing page
$page_meta = [
    'title'       => 'All Categories | Adidev',
    'description' => 'Browse all product categories available on Adidev.',
    'keywords'    => 'Adidev, product categories, bulk, wholesale',
];

include "header.php";
?>
<!--=========================
        PAGE BANNER START
    ==========================-->
<section class="page_banner" style="background: url(assets/images/page_banner_bg.jpg);">
    <div class="page_banner_overlay">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page_banner_text wow fadeInUp">
                        <h1>Category</h1>
                        <ul>
                            <li><a href="#"><i class="fal fa-home-lg"></i> Home</a></li>
                            <li><a href="#">Category</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--=========================
        PAGE BANNER START
    ==========================-->


<!--============================
        CATEGORY PAGE START
    =============================-->
<section class="category_page category_2 mt_75 mb_95">
    <div class="container">
        <div class="row">

            <?php if (!empty($header_sub_categories)) : ?>

                <?php foreach ($header_sub_categories as $mainId => $subCats) : ?>

                    <?php if (!empty($subCats)) : ?>
                        <?php foreach ($subCats as $subCat) : ?>

                            <?php
                            $img = !empty($subCat['image'])
                                ? htmlspecialchars($subCat['image'])
                                : $thumb;

                            $name = htmlspecialchars($subCat['name'] ?? 'Category');
                            $slug = urlencode($subCat['slug'] ?? '');
                            ?>

                            <div class="col-xl-2 col-6 col-sm-4 col-md-3 wow fadeInUp">
                                <a href="shop.php?sub=<?= $slug; ?>" class="category_item">
                                    <div class="img">
                                        <img src="./uploads/categories/subcategories/<?= $img; ?>"
                                            alt="<?= $name; ?>"
                                            class="img-fluid w-100">
                                    </div>
                                    <h3><?= $name; ?></h3>
                                </a>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php endforeach; ?>

            <?php else : ?>
                <div class="col-12 text-center">
                    <h3>No sub categories found</h3>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<!--============================
        CATEGORY PAGE END
    =============================-->

<?php include "footer.php"; ?>