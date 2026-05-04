<?php

require __DIR__ . '/includes/bootstrap.php';

$query = $_GET['q'] ?? '';
$products = [];

if ($query !== '') {
    $products = search_products($db, $query);
}

$pageTitle = 'Search Results';
$extraCss = ['stylesheet1.css'];
$headerSearchValue = $query;
$headerSearchDisabled = false;
$headerSearchAction = 'search.php';
$headerSearchPlaceholder = 'Search for products...';

$inlineStyles = <<<'CSS'
        .page-body { max-width: 1400px; margin: 0 auto; padding: 0 15px; }
        .search-banner { background: linear-gradient(135deg, #007a5e 0%, #00a884 100%); padding: 30px 25px 25px; margin-bottom: 25px; border-radius: 0 0 14px 14px; }
        .search-banner h1 { color: white; font-weight: 800; font-size: 1.7rem; margin: 0; }
        .search-banner .breadcrumb-nav { font-size: 0.82rem; color: rgba(255,255,255,0.75); margin-bottom: 8px; }
        .search-banner .breadcrumb-nav a { color: rgba(255,255,255,0.85); text-decoration: none; }
        .product-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; padding: 0 20px 40px; }
        .product-grid .product-card { width: 100%; min-height: 350px; border: 1px solid #eee; padding: 15px; border-radius: 10px; background: #fff; }
        .product-grid .product-card img { width: 100%; height: 180px; object-fit: contain; }
        .empty-state { padding: 40px 20px; color: #4b5563; text-align: center; }
        @media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        @media (max-width: 992px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .product-grid { grid-template-columns: repeat(1, 1fr); } }
CSS;

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-body">
    <div class="search-banner">
        <div class="breadcrumb-nav">
            <a href="index.php">Home</a> &rsaquo;
            <span>Search</span>
        </div>
        <h1>Search Results for "<?= htmlspecialchars($query) ?>"</h1>
    </div>

    <?php if ($products !== []): ?>
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
                        'search.php?q=<?= urlencode($query) ?>',
                        '<?= (int) $product['stock_quantity'] ?>'
                    )">ADD TO CART</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <?php if ($query === ''): ?>
                Please enter a search term above.
            <?php else: ?>
                No products found matching "<?= htmlspecialchars($query) ?>".
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/quantity_modal.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

