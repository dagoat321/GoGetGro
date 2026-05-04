<?php
require_once __DIR__ . '/bootstrap.php';
$user = current_user();
$categories = get_categories($db);
$headerSearchPlaceholder = $headerSearchPlaceholder ?? 'Search for products...';
$headerSearchValue = $headerSearchValue ?? '';
$headerSearchDisabled = $headerSearchDisabled ?? false;
$headerSearchAction = $headerSearchAction ?? 'search.php';
$headerActiveCategorySlug = $headerActiveCategorySlug ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGetGro | <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Online Grocery' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php else: ?>
        <link href="stylesheet.css" rel="stylesheet">
    <?php endif; ?>
    <?php if (isset($inlineStyles) && $inlineStyles !== ''): ?>
        <style>
<?= $inlineStyles ?>
        </style>
    <?php endif; ?>
    <style>
        .voucher-success {
            animation: glow 1s ease-in-out infinite alternate;
            border: 2px solid #28a745 !important;
        }
        @keyframes glow {
            from { box-shadow: 0 0 5px #28a745; }
            to { box-shadow: 0 0 20px #28a745; }
        }
    </style>
</head>
<body>
    <button class="hamburger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu">
        <i class="bi bi-list"></i>
    </button>

    <div class="offcanvas offcanvas-start" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="sideMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Menu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column">
                <?php foreach ($categories as $navCategory): ?>
                    <a class="nav-link<?= $headerActiveCategorySlug === $navCategory['slug'] ? ' active-category' : '' ?>" href="category.php?cat=<?= urlencode($navCategory['slug']) ?>">
                        <i class="bi <?= htmlspecialchars($navCategory['icon_class']) ?> me-2"></i>
                        <?= htmlspecialchars($navCategory['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo-link">
                <img src="images/Group 46.png" alt="GoGetGro Logo">
            </a>
            <?php if ($headerSearchDisabled): ?>
                <div class="search-container">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="<?= htmlspecialchars($headerSearchPlaceholder) ?>" value="<?= htmlspecialchars($headerSearchValue) ?>" disabled>
                </div>
            <?php else: ?>
                <form class="search-container" action="<?= htmlspecialchars($headerSearchAction ?? '') ?>" method="GET">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" placeholder="<?= htmlspecialchars($headerSearchPlaceholder) ?>" value="<?= htmlspecialchars($headerSearchValue) ?>">
                </form>
            <?php endif; ?>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="help.php">Help</a>
                <?php if ($user): ?>
                    <a href="cart.php">Cart (<?= cart_items_count() ?>)</a>
                    <a href="profile.php"><?= htmlspecialchars($user['username']) ?></a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="signup.php">Sign Up</a>
                    <a href="login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

