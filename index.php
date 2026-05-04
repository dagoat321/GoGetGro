<?php

require __DIR__ . '/includes/bootstrap.php';

$featuredCategories = get_featured_categories($db);
$pageTitle = 'Home';
$extraCss = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active"><img src="images/1.png" alt="Free Delivery"></div>
            <div class="carousel-item"><img src="images/2.png" alt="Fresh deals"></div>
            <div class="carousel-item"><img src="images/3.png" alt="Shop faster"></div>
        </div>
    </div>
</section>

<main class="container">
    <?php foreach ($featuredCategories as $index => $category): ?>
        <?php $products = get_products_by_category($db, $category['slug'], 6); ?>
        <div class="section-header" <?= $index === 0 ? '' : ' style="margin-top: 40px;"' ?>>
            <h2><?= htmlspecialchars($category['name']) ?></h2>
            <a href="category.php?cat=<?= urlencode($category['slug']) ?>" class="see-all">See All</a>
        </div>
        <div class="scroll-wrapper">
            <button class="scroll-btn left" onclick="scrollGrid('section-<?= $index ?>', -300)"><i class="bi bi-chevron-left"></i></button>
            <div class="product-scroll-container" id="section-<?= $index ?>">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <p class="name"><?= htmlspecialchars($product['name']) ?></p>
                        <p class="stock" style="font-size: 0.8rem; color: #666; margin: -5px 0 5px 0; font-weight: 500;">
                            <?php $sq = (int)$product['stock_quantity']; ?>
                            <?php if ($sq <= 0): ?>
                                <span style="color: #dc3545; font-weight: bold;">Out of Stock</span>
                            <?php elseif ($sq <= 5): ?>
                                <span style="color: #e67e22; font-weight: bold;">Low Stock: <?= $sq ?> left</span>
                            <?php else: ?>
                                Stock: <?= $sq ?>
                            <?php endif; ?>
                        </p>
                        <p class="price"><?= htmlspecialchars(money((float) $product['price'])) ?></p>
                        <button class="add-btn" type="button" onclick="openQuantityModal(
                                '<?= (int) $product['id'] ?>', 
                                '<?= addslashes(htmlspecialchars($product['name'])) ?>', 
                                '<?= addslashes(htmlspecialchars(money((float) $product['price']))) ?>', 
                                '<?= addslashes(htmlspecialchars($product['image_path'])) ?>', 
                                'index.php',
                                '<?= (int) $product['stock_quantity'] ?>'
                            )">ADD TO CART</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="scroll-btn right" onclick="scrollGrid('section-<?= $index ?>', 300)"><i class="bi bi-chevron-right"></i></button>
        </div>
    <?php endforeach; ?>
</main>

<?php include __DIR__ . '/includes/quantity_modal.php'; ?>

<script>
    function scrollGrid(containerId, distance) {
        const container = document.getElementById(containerId);
        if (container) {
            container.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }
    }
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
