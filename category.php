<?php

require __DIR__ . '/includes/bootstrap.php';

$categories = get_category_map($db);
$slug = $_GET['cat'] ?? 'frozen-goods';
$category = $categories[$slug] ?? null;
$products = $category ? get_products_by_category($db, $slug) : [];
$pageTitle = 'Category';
$extraCss = ['stylesheet1.css'];
$headerActiveCategorySlug = $slug;
$inlineStyles = <<<'CSS'
        .page-body { max-width: 1400px; margin: 0 auto; padding: 0 15px; }
        .category-banner { background: linear-gradient(135deg, #007a5e 0%, #00a884 100%); padding: 30px 25px 25px; margin-bottom: 25px; border-radius: 0 0 14px 14px; }
        .category-banner h1 { color: white; font-weight: 800; font-size: 1.7rem; margin: 0; }
        .category-banner .breadcrumb-nav { font-size: 0.82rem; color: rgba(255,255,255,0.75); margin-bottom: 8px; }
        .category-banner .breadcrumb-nav a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .category-icon-badge { display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; background: rgba(255,255,255,0.2); border-radius: 12px; font-size: 1.4rem; color: white; margin-bottom: 10px; }
        .product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; padding: 0 20px 40px; }
        .product-grid .product-card { width: 100%; min-height: 350px; border: 1px solid #eee; padding: 15px; border-radius: 10px; }
        .product-grid .product-card img { width: 100%; height: 180px; object-fit: contain; }
        .empty-state { padding: 0 20px 40px; color: #4b5563; }
        @media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .product-grid { grid-template-columns: repeat(1, 1fr); } }
CSS;
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-body">
    <div class="category-banner">
        <div class="breadcrumb-nav">
            <a href="index.php">Home</a> &rsaquo;
            <span><?= htmlspecialchars($category['name'] ?? 'Unknown Category') ?></span>
        </div>
        <div class="category-icon-badge">
            <i class="bi <?= htmlspecialchars($category['icon_class'] ?? 'bi-grid') ?>"></i>
        </div>
        <h1><?= htmlspecialchars($category['name'] ?? 'Unknown Category') ?></h1>
    </div>

    <?php if ($category && $products !== []): ?>
        <div class="product-grid">
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
                        'category.php?cat=<?= urlencode($slug) ?>',
                        '<?= (int) $product['stock_quantity'] ?>'
                    )">ADD TO CART</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($category): ?>
        <div class="empty-state">
            No products are loaded for this category yet. Add more rows to the `products` table if you want this section filled.
        </div>
    <?php else: ?>
        <div class="empty-state">
            The requested category does not exist in the database.
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sideMenuElement = document.getElementById('sideMenu');
        const offcanvasBody = sideMenuElement.querySelector('.offcanvas-body');
        const sideMenu = bootstrap.Offcanvas.getOrCreateInstance(sideMenuElement);

        if (localStorage.getItem('menuState') === 'open') {
            sideMenu.show();
        }

        sideMenuElement.addEventListener('shown.bs.offcanvas', () => {
            localStorage.setItem('menuState', 'open');
            const savedScroll = localStorage.getItem('menuScrollPos');
            if (savedScroll) {
                offcanvasBody.scrollTop = savedScroll;
            }
        });

        sideMenuElement.addEventListener('hidden.bs.offcanvas', () => {
            localStorage.setItem('menuState', 'closed');
        });

        // Save scroll position on scroll
        offcanvasBody.addEventListener('scroll', () => {
            localStorage.setItem('menuScrollPos', offcanvasBody.scrollTop);
        });

        // Ensure scroll position is captured when a link is clicked
        offcanvasBody.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                localStorage.setItem('menuScrollPos', offcanvasBody.scrollTop);
            });
        });
    });
</script>
<?php include __DIR__ . '/includes/quantity_modal.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

