<?php

require __DIR__ . '/includes/bootstrap.php';

$admin = current_admin();

if (!$admin) {
    header('Location: login_admin.php');
    exit;
}

if (($admin['role'] ?? '') !== 'owner') {
    header('Location: adminview.php');
    exit;
}

$adminName           = $admin['display_name'] ?? 'Owner';
$inventoryProducts   = get_admin_inventory_products($db);
$inventoryCategories = get_categories($db);
$totalCategories     = count($inventoryCategories);
$totalProducts       = count($inventoryProducts);
$totalUnits          = 0;
$lowStockItems       = 0;

foreach ($inventoryProducts as $product) {
    $quantity       = (int) ($product['stock_quantity'] ?? 0);
    $totalUnits    += $quantity;
    if ($quantity <= 5) $lowStockItems++;
}

// ─── Real DB data ────────────────────────────────────────────────────────────
$allOrdersRaw    = get_all_orders_admin($db);
$allUsersRaw     = get_all_users_admin($db);
$revSummary      = get_admin_revenue_summary($db);
$weeklySalesRaw  = get_weekly_sales_admin($db);
$monthlySalesRaw = get_monthly_sales_admin($db);
$topProductsRaw  = get_top_products_admin($db, 5);

// Status map for orders
$orderStatusCounts = ['To Pay' => 0, 'To Ship' => 0, 'Completed' => 0, 'Cancelled' => 0];
foreach ($allOrdersRaw as $o) {
    $s = $o['status'] ?? '';
    if (isset($orderStatusCounts[$s])) $orderStatusCounts[$s]++;
}

// Build JS-ready arrays
$jsOrders = array_map(function($o) {
    return [
        'id'          => '#' . $o['order_number'],
        'customer'    => $o['full_name'] ?: $o['username'] ?: 'Guest',
        'items'       => (int)$o['item_count'],
        'total'       => '₱' . number_format((float)$o['total_amount'], 2),
        'status'      => strtolower(str_replace(' ', '_', $o['status'])),
        'statusLabel' => $o['status'],
        'date'        => date('M j, Y', strtotime($o['created_at'])),
        'db_id'       => (int)$o['id'],
    ];
}, $allOrdersRaw);

$jsUsers = array_map(function($u) {
    return [
        'id'       => (int)$u['id'],
        'name'     => $u['full_name'] ?: $u['username'],
        'username' => $u['username'],
        'joined'   => date('M j, Y', strtotime($u['created_at'])),
        'orders'   => (int)$u['order_count'],
        'spent'    => '₱' . number_format((float)$u['total_spent'], 2),
        'status'   => 'active',
    ];
}, $allUsersRaw);

$jsWeekly  = $weeklySalesRaw;
$jsMonthly = $monthlySalesRaw;

$jsTopProducts = array_values(array_map(function($p, $i) {
    return [
        'rank'  => $i + 1,
        'name'  => $p['name'] ?? '—',
        'cat'   => $p['category_name'] ?? '—',
        'units' => (int)($p['units_sold'] ?? 0),
        'rev'   => '₱' . number_format((float)($p['revenue'] ?? 0), 2),
    ];
}, $topProductsRaw, array_keys($topProductsRaw)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGetGro | Owner Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-green: #007a5e;
            --light-green: #e8f7f1;
            --hover-green: #f0faf6;
            --dark-green: #005a45;
        }

        body { background-color: #f4f6f5; font-family: 'Segoe UI', sans-serif; }

        /* NAVBAR */
        .navbar {
            background-color: var(--primary-green);
            padding: 0 1.5rem;
            height: 60px;
        }
        .nav-links a { color: #fff; text-decoration: none; font-weight: 500; font-size: 0.9rem; opacity: 0.85; transition: opacity 0.2s; }
        .nav-links a:hover { opacity: 1; }
        .owner-badge {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.35);
        }

        /* SIDEBAR TABS */
        .sidebar-tab {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.88rem;
            color: #555;
            transition: all 0.18s;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            margin-bottom: 3px;
        }
        .sidebar-tab:hover { background: var(--hover-green); color: var(--primary-green); }
        .sidebar-tab.active { background: var(--light-green); color: var(--primary-green); font-weight: 600; border-left: 4px solid var(--primary-green); border-radius: 0 10px 10px 0; padding-left: 10px; }
        .sidebar-tab i { font-size: 1rem; width: 20px; }

        /* SECTION VISIBILITY */
        .admin-section { display: none; }
        .admin-section.active { display: block; }

        /* METRIC CARDS */
        .metric-card {
            background: #fff;
            border: 1px solid #eaeaea;
            border-radius: 14px;
            padding: 18px 20px;
            transition: transform 0.2s;
        }
        .metric-card:hover { transform: translateY(-2px); }
        .metric-card .metric-label { font-size: 0.73rem; color: #888; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .metric-card .metric-value { font-size: 1.7rem; font-weight: 700; color: #1a1a1a; }
        .metric-card .metric-sub { font-size: 0.75rem; color: #aaa; margin-top: 4px; }
        .metric-card .metric-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .icon-green { background: var(--light-green); color: var(--primary-green); }
        .icon-blue  { background: #e8f0fe; color: #3b6fd4; }
        .icon-amber { background: #fff8e1; color: #f59e0b; }
        .icon-red   { background: #fef2f2; color: #e05050; }

        /* TABLE STYLES */
        .admin-table { font-size: 0.84rem; }
        .admin-table thead th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.6px; color: #999; font-weight: 600; border-bottom: 1px solid #eee; padding: 10px 14px; }
        .admin-table tbody td { padding: 11px 14px; vertical-align: middle; border-bottom: 1px solid #f5f5f5; color: #333; }
        .admin-table tbody tr:last-child td { border-bottom: none; }
        .admin-table tbody tr:hover td { background: #fafafa; }

        /* STATUS BADGES */
        .badge-placed    { background: #e8f0fe; color: #3b6fd4; font-size: 0.7rem; padding: 3px 10px; border-radius: 20px; font-weight: 500; }
        .badge-pending   { background: #fff8e1; color: #c77b00; font-size: 0.7rem; padding: 3px 10px; border-radius: 20px; font-weight: 500; }
        .badge-completed { background: var(--light-green); color: var(--primary-green); font-size: 0.7rem; padding: 3px 10px; border-radius: 20px; font-weight: 500; }
        .badge-cancelled { background: #fef2f2; color: #e05050; font-size: 0.7rem; padding: 3px 10px; border-radius: 20px; font-weight: 500; }

        /* SECTION HEADER */
        .section-title { font-size: 1.1rem; font-weight: 700; color: #1a1a1a; margin-bottom: 1.2rem; }

        /* USER AVATAR */
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 600; color: var(--primary-green);
            background: var(--light-green); flex-shrink: 0;
        }

        /* INVENTORY */
        .inv-card {
            border: 1px solid #eee; border-radius: 12px;
            padding: 12px; background: #fff; transition: transform 0.15s;
        }
        .inv-card:hover { transform: translateY(-2px); }
        .inv-img { width: 100%; height: 90px; object-fit: cover; border-radius: 8px; background: var(--light-green); }
        .inv-name { font-size: 0.78rem; font-weight: 500; color: #333; margin: 7px 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.2em; }
        .inv-price { font-size: 0.88rem; font-weight: 700; color: var(--primary-green); }
        .qty-badge { font-size: 0.65rem; background: #f5f5f5; color: #666; padding: 2px 7px; border-radius: 4px; border: 1px solid #ddd; }
        .qty-low { color: #e05050; font-weight: 700; border-color: #fca5a5; background: #fef2f2; }
        .cat-pill { font-size: 0.62rem; background: var(--light-green); color: var(--primary-green); padding: 2px 7px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px; }

        /* SEARCH */
        .search-box { position: relative; }
        .search-box input { padding-left: 36px; border: 1px solid #dde; border-radius: 8px; font-size: 0.84rem; }
        .search-box input:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(0,122,94,0.1); outline: none; }
        .search-box i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 0.85rem; }

        /* TAB FILTERS */
        .filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 1rem; }
        .filter-tab {
            font-size: 0.75rem; padding: 4px 14px; border-radius: 20px;
            border: 1px solid #ddd; background: #fff; color: #666; cursor: pointer; transition: all 0.15s;
        }
        .filter-tab.active { background: var(--primary-green); color: #fff; border-color: var(--primary-green); }
        .filter-tab:hover:not(.active) { border-color: var(--primary-green); color: var(--primary-green); }

        /* REVENUE CHART */
        .rev-bar-wrap { display: flex; align-items: flex-end; gap: 5px; height: 140px; }
        .rev-bar { flex: 1; background: var(--light-green); border-radius: 5px 5px 0 0; cursor: pointer; position: relative; transition: background 0.2s; }
        .rev-bar:hover { background: var(--primary-green); }
        .rev-bar .rtip { display: none; position: absolute; top: -30px; left: 50%; transform: translateX(-50%); background: #222; color: #fff; font-size: 0.67rem; padding: 2px 7px; border-radius: 6px; white-space: nowrap; z-index: 9; }
        .rev-bar:hover .rtip { display: block; }

        /* CARD WRAPPER */
        .panel { background: #fff; border: 1px solid #eaeaea; border-radius: 16px; padding: 22px 24px; margin-bottom: 20px; }

        /* LAYOUT */
        html, body { height: 100%; overflow: hidden; }
        .main-row { height: calc(100vh - 60px); overflow: hidden; }

        /* SIDEBAR COLUMN — no background, just padding so the inner card floats */
        .sidebar-col {
            height: 100%;
            overflow-y: auto;
            padding: 16px 8px;
            background: #f4f6f5;
            border-right: 1px solid #e8e8e8;
        }
        .sidebar-col::-webkit-scrollbar { width: 0; }

        /* THE CARD INSIDE THE SIDEBAR */
        .sidebar-inner {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 14px;
            padding: 16px 10px;
        }

        .content-col { height: 100%; overflow-y: auto; padding: 20px; background: #f4f6f5; }
        .content-col::-webkit-scrollbar { width: 5px; }
        .content-col::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        /* INVENTORY */
        #inv-grid { max-height: 560px; overflow-y: auto; }
        #inv-grid::-webkit-scrollbar { width: 5px; }
        #inv-grid::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        /* REMOVE PRODUCT SEARCH CARDS */
        .remove-result-card { border: 1px solid #f0f0f0; background: #fff; cursor: pointer; transition: background 0.15s, border-color 0.15s; border-radius: 10px; }
        .remove-result-card:hover { background: #fff3f3; border-color: #f5c6cb; }
        .selected-remove-card { background: #fff3f3 !important; border-color: #f5c6cb !important; outline: 2px solid #dc3545; }

        @media (max-width: 768px) {
            .metric-value { font-size: 1.3rem !important; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar shadow-sm">
    <div class="d-flex align-items-center justify-content-between w-100">
        <div class="d-flex align-items-center gap-3">
            <img src="images/Group 46.png" alt="GoGetGro Logo" height="36" onerror="this.style.display='none'">
            <span class="owner-badge">Owner Portal</span>
        </div>
        <nav class="nav-links d-flex gap-4 align-items-center">
            <span class="text-white small opacity-75"><?= htmlspecialchars($adminName) ?></span>
            <a href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
        </nav>
    </div>
</header>

<main style="padding:0;">
<div class="row g-0 main-row">

    <!-- SIDEBAR -->
    <div class="col-lg-2 col-md-3 sidebar-col">
        <div class="sidebar-inner">
            <p style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:#bbb;padding:0 6px;margin-bottom:8px;">Navigation</p>
            <button class="sidebar-tab active" data-section="overview">
                <i class="bi bi-grid-1x2"></i> Overview
            </button>
            <button class="sidebar-tab" data-section="inventory">
                <i class="bi bi-box-seam"></i> Inventory
            </button>
            <button class="sidebar-tab" data-section="orders">
                <i class="bi bi-receipt"></i> Orders
            </button>
            <button class="sidebar-tab" data-section="revenue">
                <i class="bi bi-graph-up-arrow"></i> Revenue
            </button>
            <button class="sidebar-tab" data-section="users">
                <i class="bi bi-people"></i> Users
            </button>
            <hr class="my-2 opacity-25">
            <button class="sidebar-tab text-danger" onclick="logout()">
                <i class="bi bi-box-arrow-right"></i> Sign Out
            </button>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="col-lg-10 col-md-9 content-col">

        <!-- ═══════════════ OVERVIEW ═══════════════ -->
        <div id="section-overview" class="admin-section active">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div>
                    <p class="text-muted mb-0" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;">Owner Portal</p>
                    <h4 class="fw-bold mb-0" style="color:#1a1a1a;">Hello, <?= htmlspecialchars($adminName) ?>! <span style="font-size:1.3rem;"></span></h4>
                    <p class="text-muted small mb-0">Here's what's happening today.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="metric-label">Categories</p>
                                <p class="metric-value"><?= number_format($totalCategories) ?></p>
                                <p class="metric-sub">Active categories</p>
                            </div>
                            <div class="metric-icon icon-green"><i class="bi bi-grid-1x2"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="metric-label">Products</p>
                                <p class="metric-value"><?= number_format($totalProducts) ?></p>
                                <p class="metric-sub">Active product records</p>
                            </div>
                            <div class="metric-icon icon-blue"><i class="bi bi-box-seam"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="metric-label">Stock Units</p>
                                <p class="metric-value"><?= number_format($totalUnits) ?></p>
                                <p class="metric-sub">Total quantity on hand</p>
                            </div>
                            <div class="metric-icon icon-amber"><i class="bi bi-stack"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="metric-label">Low Stock Items</p>
                                <p class="metric-value text-danger"><?= number_format($lowStockItems) ?></p>
                                <p class="metric-sub">Products with 5 or fewer left</p>
                            </div>
                            <div class="metric-icon icon-red"><i class="bi bi-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="section-title mb-0">Weekly Sales</p>
                            <small class="text-muted">This week</small>
                        </div>
                        <div class="rev-bar-wrap mb-1" id="weekly-bars"></div>
                        <div class="d-flex gap-1" id="weekly-labels" style="font-size:0.65rem;color:#bbb;"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="panel h-100">
                        <p class="section-title">Order Breakdown</p>
                        <div id="order-donut" class="d-flex flex-column gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Quick View -->
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="section-title mb-0">Recent Orders</p>
                    <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="switchSection('orders')">View All</button>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody id="recent-orders-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════ INVENTORY ═══════════════ -->
        <div id="section-inventory" class="admin-section">
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Inventory Management</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addItemModalOwner">
                            <i class="bi bi-plus-lg me-1"></i> Add New Product
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#removeItemModal">
                            <i class="bi bi-trash me-1"></i> Remove Product
                        </button>
                        <span id="inv-count" class="badge bg-light text-dark border fw-normal"></span>
                    </div>
                </div>

                <!-- Add Product Modal -->
                <div class="modal fade" id="addItemModalOwner" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="addItemFormOwner" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="category_slug" class="form-select" required>
                                            <option value="" disabled selected>Select a category</option>
                                            <?php foreach ($inventoryCategories as $cat): ?>
                                                <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Price (PHP)</label>
                                            <input type="number" step="0.01" min="0" name="price" class="form-control" required>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label">Stock Qty</label>
                                            <input type="number" step="1" min="0" name="stock_quantity" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Remove Product Modal -->
                <div class="modal fade" id="removeItemModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Remove Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">Search for the product you want to remove, then click it to select before confirming.</p>
                                <div class="position-relative mb-3">
                                    <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;"></i>
                                    <input type="text" id="inv-remove-search" class="form-control ps-5 rounded-pill" placeholder="Search by product name or category..." autocomplete="off">
                                </div>
                                <div id="inv-remove-results" style="max-height:320px;overflow-y:auto;">
                                    <div class="text-center text-muted py-4 small" id="inv-remove-placeholder"><i class="bi bi-search me-1"></i>Start typing to find a product</div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" id="inv-confirm-remove-btn" class="btn btn-danger rounded-pill px-4" onclick="confirmInvRemove()" disabled>
                                    <i class="bi bi-trash me-1"></i>Remove Selected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-tabs mb-2" id="inv-cat-tabs"></div>

                <!-- Search -->
                <div class="search-box mb-3" style="position:relative;">
                    <i class="bi bi-funnel" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#666;"></i>
                    <input type="text" id="inv-search" class="form-control" placeholder="Filter items..." style="padding-left:38px;">
                </div>

                <!-- Grid -->
                <div class="row g-3" id="inv-grid"></div>

                <div id="inv-pagination" class="text-center mt-4" style="display:none;">
                    <hr class="text-muted opacity-25 mb-4">
                    <button id="inv-show-more" class="btn btn-success px-5 rounded-pill fw-bold" onclick="invShowMore()">Show More</button>
                    <button id="inv-show-less" class="btn btn-outline-secondary px-5 rounded-pill fw-bold" style="display:none;" onclick="invShowLess()">Show Less</button>
                </div>
            </div>
        </div>

        <!-- ═══════════════ ORDERS ═══════════════ -->
        <div id="section-orders" class="admin-section">
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0">Order Management</h4>
                    <div class="filter-tabs mb-0" id="order-filter-tabs">
                        <button class="filter-tab active" data-status="all">All</button>
                        <button class="filter-tab" data-status="To Pay">To Pay</button>
                        <button class="filter-tab" data-status="To Ship">To Ship</button>
                        <button class="filter-tab" data-status="Completed">Completed</button>
                        <button class="filter-tab" data-status="Cancelled">Cancelled</button>
                    </div>
                </div>
                <div class="search-box mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="order-search" class="form-control form-control-sm" placeholder="Search by customer or order ID...">
                </div>
                <div class="table-responsive" style="max-height:480px;overflow-y:auto;border:1px solid #eee;border-radius:10px;">
                    <table class="table admin-table mb-0">
                        <thead class="sticky-top bg-light"><tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody id="orders-table-body"></tbody>
                    </table>
                </div>
                <div id="orders-empty" class="text-center text-muted py-5" style="display:none;">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>No orders found.
                </div>
                <div id="orders-pagination" class="text-center pt-3" style="display:none;">
                    <hr class="opacity-25">
                    <button id="orders-show-more" class="btn btn-success rounded-pill px-5 fw-bold me-2" onclick="ordersShowMore()">Show More</button>
                    <button id="orders-show-less" class="btn btn-outline-secondary rounded-pill px-5 fw-bold" onclick="ordersShowLess()" style="display:none;">Show Less</button>
                </div>
            </div>
        </div>

        <!-- ═══════════════ REVENUE ═══════════════ -->
        <div id="section-revenue" class="admin-section">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <p class="metric-label">Total Sales</p>
                        <p class="metric-value">₱<?= number_format((float)($revSummary['total_sales'] ?? 0), 2) ?></p>
                        <p class="metric-sub">All time</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <p class="metric-label">This Month</p>
                        <p class="metric-value">₱<?= number_format((float)($revSummary['this_month'] ?? 0), 2) ?></p>
                        <p class="metric-sub"><?= date('F Y') ?></p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <p class="metric-label">This Week</p>
                        <p class="metric-value">₱<?= number_format((float)($revSummary['this_week'] ?? 0), 2) ?></p>
                        <p class="metric-sub">Current week</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-card">
                        <p class="metric-label">Avg. Order Value</p>
                        <p class="metric-value">₱<?= number_format((float)($revSummary['avg_order'] ?? 0), 2) ?></p>
                        <p class="metric-sub">Per order</p>
                    </div>
                </div>
            </div>

            <div class="panel mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="section-title mb-0">Monthly Revenue (<?= date('Y') ?>)</p>
                </div>
                <div class="rev-bar-wrap mb-1" id="monthly-bars"></div>
                <div class="d-flex gap-1" id="monthly-labels" style="font-size:0.65rem;color:#bbb;"></div>
            </div>

            <div class="panel">
                <p class="section-title">Top Selling Products</p>
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr></thead>
                        <tbody id="top-products-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══════════════ USERS ═══════════════ -->
        <div id="section-users" class="admin-section">
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0">User Management</h4>
                    <span class="badge bg-light text-dark border fw-normal" id="user-count"></span>
                </div>
                <div class="search-box mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="user-search" class="form-control form-control-sm" placeholder="Search users...">
                </div>
                <div class="table-responsive" style="max-height:480px;overflow-y:auto;border:1px solid #eee;border-radius:10px;">
                    <table class="table admin-table mb-0">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>User</th>
                                <th>Username</th>
                                <th>Joined</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Status</th>
                                <th>History</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body"></tbody>
                    </table>
                </div>
            </div>

            <div id="order-history-panel" class="panel" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="section-title mb-0" id="history-title">Order History</p>
                    <button class="btn btn-sm btn-icon" onclick="closeHistory()" title="Close"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                    <table class="table admin-table mb-0">
                        <thead class="sticky-top bg-white"><tr><th>Order ID</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody id="history-body"></tbody>
                    </table>
                </div>
                <div id="history-pagination" class="text-center pt-3" style="display:none;">
                    <hr class="opacity-25">
                    <button id="history-show-more" class="btn btn-success rounded-pill px-4 fw-bold me-2" onclick="historyShowMore()">Show More</button>
                    <button id="history-show-less" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" onclick="historyShowLess()" style="display:none;">Show Less</button>
                </div>
            </div>
        </div>

    </div><!-- /content-col -->
</div><!-- /main-row -->
</main>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select class="form-select" id="edit-category" name="category_slug" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit-price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" step="1" min="0" class="form-control" id="edit-qty" name="stock_quantity" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image Path</label>
                        <input type="text" class="form-control" id="edit-img" name="image_path" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitEditProduct()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restock Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="restock-id">
                <label class="form-label">Quantity to Add</label>
                <input type="number" id="restock-amount" class="form-control" value="10" min="1" step="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmRestock()">Restock</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ─── LIVE DB DATA (PHP-injected) ─── */
const ORDERS        = <?= json_encode($jsOrders,      JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const USERS         = <?= json_encode($jsUsers,       JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const WEEKLY_SALES  = <?= json_encode($jsWeekly,      JSON_UNESCAPED_UNICODE) ?>;
const MONTHLY_SALES = <?= json_encode($jsMonthly,     JSON_UNESCAPED_UNICODE) ?>;
const TOP_PRODUCTS  = <?= json_encode($jsTopProducts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;

const CATEGORIES = <?= json_encode(array_map(static function(array $c): array {
    return ['slug' => $c['slug'], 'name' => $c['name']];
}, $inventoryCategories), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

const INV_PRODUCTS = <?= json_encode(array_map(static function(array $p): array {
    return [
        'id'    => (int)$p['id'],
        'img'   => $p['image_path'],
        'name'  => $p['name'],
        'price' => money((float)$p['price']),
        'cat'   => $p['category_name'],
        'slug'  => $p['category_slug'],
        'qty'   => (int)$p['stock_quantity'],
    ];
}, $inventoryProducts), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

let invActiveSlug = 'all';
let invItems = [...INV_PRODUCTS];

/* ─── SIDEBAR NAVIGATION ─── */
document.querySelectorAll('.sidebar-tab[data-section]').forEach(btn => {
    btn.addEventListener('click', () => switchSection(btn.dataset.section));
});

function switchSection(name) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-tab[data-section]').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + name).classList.add('active');
    document.querySelector(`.sidebar-tab[data-section="${name}"]`).classList.add('active');
}

/* ─── STATUS BADGE ─── */
function statusBadge(s, label) {
    const lbl = label || s;
    const sl  = (s || '').toLowerCase().replace(/\s/g, '_');
    const map = {
        'to_pay':     'badge-pending',
        'to_ship':    'badge-placed',
        'to_receive': 'badge-placed',
        'completed':  'badge-completed',
        'cancelled':  'badge-cancelled'
    };
    return `<span class="${map[sl] || 'badge-placed'}">${lbl}</span>`;
}

/* ─── OVERVIEW: WEEKLY CHART ─── */
function buildWeeklyChart() {
    const max = Math.max(...WEEKLY_SALES.map(d => d.val));
    const wrap = document.getElementById('weekly-bars');
    const labWrap = document.getElementById('weekly-labels');
    wrap.innerHTML = ''; labWrap.innerHTML = '';
    WEEKLY_SALES.forEach(d => {
        const pct = Math.round((d.val / max) * 100);
        wrap.innerHTML += `<div class="rev-bar" style="height:${pct}%;"><span class="rtip">₱${d.val.toLocaleString()}</span></div>`;
        labWrap.innerHTML += `<div style="flex:1;text-align:center;">${d.day}</div>`;
    });
}

/* ─── OVERVIEW: ORDER DONUT ─── */
function buildOrderDonut() {
    const counts = { 'To Pay': 0, 'To Ship': 0, 'Completed': 0, 'Cancelled': 0 };
    ORDERS.forEach(o => { if (counts[o.statusLabel] !== undefined) counts[o.statusLabel]++; });
    const total  = ORDERS.length || 1;
    const colors = { 'To Pay': '#f59e0b', 'To Ship': '#3b6fd4', 'Completed': '#007a5e', 'Cancelled': '#e05050' };
    document.getElementById('order-donut').innerHTML = Object.entries(counts).map(([s, c]) => {
        const pct = Math.round((c / total) * 100);
        return `
        <div class="d-flex align-items-center gap-2 justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div style="width:10px;height:10px;border-radius:50%;background:${colors[s]};flex-shrink:0;"></div>
                <span style="font-size:0.82rem;">${s}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div style="width:80px;height:6px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
                    <div style="width:${pct}%;height:100%;background:${colors[s]};border-radius:4px;"></div>
                </div>
                <span style="font-size:0.78rem;color:#666;min-width:20px;text-align:right;">${c}</span>
            </div>
        </div>`;
    }).join('');
}

/* ─── OVERVIEW: RECENT ORDERS ─── */
function buildRecentOrders() {
    document.getElementById('recent-orders-body').innerHTML = ORDERS.slice(0, 6).map(o => `
        <tr>
            <td style="font-family:monospace;font-size:0.8rem;">${o.id}</td>
            <td>${o.customer}</td>
            <td>${o.items} items</td>
            <td class="fw-bold" style="color:var(--primary-green);">${o.total}</td>
            <td>${statusBadge(o.status)}</td>
            <td style="color:#aaa;font-size:0.8rem;">${o.date}</td>
        </tr>`).join('');
}

/* ─── INVENTORY ─── */
const INV_PAGE_SIZE = 18;
let invFullView = false;

function buildInvCatTabs() {
    const tabs = document.getElementById('inv-cat-tabs');
    tabs.innerHTML = `<button class="filter-tab active" data-cat="all">All</button>`;
    CATEGORIES.forEach(c => {
        tabs.innerHTML += `<button class="filter-tab" data-cat="${c.slug}">${c.name}</button>`;
    });
    tabs.addEventListener('click', e => {
        const btn = e.target.closest('.filter-tab');
        if (!btn) return;
        tabs.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        invActiveSlug = btn.dataset.cat;
        invFullView = false;
        renderInvGrid();
    });
}


function renderInvGrid() {
    const q = document.getElementById('inv-search').value.toLowerCase();
    const items = invItems.filter(p =>
        (invActiveSlug === 'all' || p.slug === invActiveSlug) &&
        p.name.toLowerCase().includes(q)
    );
    const grid = document.getElementById('inv-grid');
    const pag  = document.getElementById('inv-pagination');
    const more = document.getElementById('inv-show-more');
    const less = document.getElementById('inv-show-less');
    document.getElementById('inv-count').textContent = `${items.length} items`;
    if (!items.length) {
        grid.innerHTML = `<div class="col-12 text-center text-muted py-5">No products found.</div>`;
        pag.style.display = 'none'; return;
    }
    const display = invFullView ? items : items.slice(0, INV_PAGE_SIZE);
    grid.innerHTML = display.map(p => {
        const isLow = p.qty <= 5;
        return `
        <div class="col-6 col-md-4 col-xl-2">
            <div class="inv-card d-flex flex-column">
                <span class="cat-pill">${p.cat}</span>
                <img src="${p.img}" alt="${p.name}" class="inv-img mt-1"
                     onerror="this.src='https://placehold.co/200x110/e8f7f1/007a5e?text=Product'">
                <p class="inv-name" title="${p.name}">${p.name}</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <span class="inv-price">${p.price}</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="qty-badge ${isLow ? 'qty-low' : ''}" id="qty-${p.id}">Qty: ${p.qty}</span>
                        <button class="btn btn-sm btn-outline-success p-1 py-0" onclick="editProduct(${p.id})">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
    pag.style.display  = items.length > INV_PAGE_SIZE ? 'block' : 'none';
    more.style.display = invFullView ? 'none' : 'inline-block';
    less.style.display = invFullView ? 'inline-block' : 'none';
}

function invShowMore() { invFullView = true;  renderInvGrid(); }
function invShowLess() { invFullView = false; renderInvGrid(); }

/* ─── INV REMOVE MODAL ─── */
let invSelectedRemoveId   = null;
let invSelectedRemoveName = '';

function clearInvRemoveSelection() {
    invSelectedRemoveId = null;
    document.getElementById('inv-confirm-remove-btn').disabled = true;
    document.querySelectorAll('.remove-result-card').forEach(c => c.classList.remove('selected-remove-card'));
}

function selectInvProductToRemove(id, name, img, cat) {
    invSelectedRemoveId   = id;
    invSelectedRemoveName = name;
    document.getElementById('inv-confirm-remove-btn').disabled = false;
    document.querySelectorAll('.remove-result-card').forEach(c => c.classList.remove('selected-remove-card'));
    document.getElementById('inv-remove-card-' + id)?.classList.add('selected-remove-card');
}

document.getElementById('inv-remove-search').addEventListener('input', function() {
    const q    = this.value.trim().toLowerCase();
    const list = document.getElementById('inv-remove-results');
    const ph   = document.getElementById('inv-remove-placeholder');
    if (!q) { list.innerHTML = ''; list.appendChild(ph); ph.classList.remove('d-none'); clearInvRemoveSelection(); return; }
    const matches = invItems.filter(i => i.name.toLowerCase().includes(q) || i.cat.toLowerCase().includes(q));
    if (!matches.length) { list.innerHTML = '<div class="text-center text-muted py-4 small">No products found</div>'; clearInvRemoveSelection(); return; }
    function highlight(text) {
        const idx = text.toLowerCase().indexOf(q);
        if (idx === -1) return text;
        return text.slice(0, idx) + '<mark class="p-0 bg-warning">' + text.slice(idx, idx + q.length) + '</mark>' + text.slice(idx + q.length);
    }
    list.innerHTML = matches.map(p => `
        <div class="remove-result-card d-flex align-items-center gap-3 px-3 py-2 mb-1"
             id="inv-remove-card-${p.id}" role="button"
             onclick="selectInvProductToRemove(${p.id}, '${p.name.replace(/'/g,"\\'").replace(/"/g,'&quot;')}', '${p.img}', '${p.cat}')">
            <img src="${p.img}" onerror="this.src='https://placehold.co/40x40?text=?'" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;">
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate" style="font-size:.9rem;">${highlight(p.name)}</div>
                <div class="text-muted" style="font-size:.75rem;">${highlight(p.cat)} &bull; ${p.price}</div>
            </div>
            <i class="bi bi-chevron-right text-muted" style="font-size:.75rem;"></i>
        </div>`).join('');
    if (invSelectedRemoveId) document.getElementById('inv-remove-card-' + invSelectedRemoveId)?.classList.add('selected-remove-card');
});

document.getElementById('removeItemModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('inv-remove-search').value = '';
    document.getElementById('inv-remove-results').innerHTML = '<div class="text-center text-muted py-4 small" id="inv-remove-placeholder"><i class="bi bi-search me-1"></i>Start typing to find a product</div>';
    clearInvRemoveSelection();
});

function confirmInvRemove() {
    if (!invSelectedRemoveId) return;
    Swal.fire({
        title: `Remove "${invSelectedRemoveName}"?`,
        text: "This will archive the product and remove it from the store.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('id', invSelectedRemoveId);
            fetch('admin_remove_item_process.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('activeTab', 'inventory');
                        Swal.fire({
                            icon: 'success',
                            title: 'Removed!',
                            text: 'Product has been archived and removed.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                    else Swal.fire('Error', data.message, 'error');
                })
                .catch(() => Swal.fire('Error', 'Network error', 'error'));
        }
    });
}

function updateQty(id, action) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', action);
    fetch('admin_update_qty_process.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(`qty-${id}`);
                if (el) {
                    el.textContent = 'Qty: ' + data.newQty;
                    el.classList.toggle('qty-low', data.newQty <= 5);
                    const item = invItems.find(i => i.id === id);
                    if (item) item.qty = data.newQty;
                    renderInvGrid();
                }
            } else alert(data.message);
        });
}

let restockModalInstance = null;
function restockProduct(id) {
    document.getElementById('restock-id').value = id;
    document.getElementById('restock-amount').value = 10;
    if (!restockModalInstance) restockModalInstance = new bootstrap.Modal(document.getElementById('restockModal'));
    restockModalInstance.show();
}

function confirmRestock() {
    const id     = document.getElementById('restock-id').value;
    const amount = parseInt(document.getElementById('restock-amount').value, 10);
    if (isNaN(amount) || amount <= 0) { alert('Please enter a valid positive number.'); return; }
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'restock');
    formData.append('amount', amount);
    fetch('admin_update_qty_process.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const item = invItems.find(i => i.id == id);
                if (item) { item.qty = data.newQty; renderInvGrid(); }
                restockModalInstance.hide();
            } else alert(data.message);
        })
        .catch(() => alert('Network error'));
}

function removeProduct(productId, productName) {
    if (!confirm(`Are you sure you want to remove "${productName}"?`)) return;
    const formData = new FormData();
    formData.append('id', productId);
    fetch('admin_remove_item_process.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) { alert('Product removed successfully!'); location.reload(); }
            else alert('Error: ' + data.message);
        })
        .catch(() => alert('An unexpected error occurred.'));
}

document.getElementById('inv-search').addEventListener('input', renderInvGrid);

/* ─── ORDERS ─── */
const ORDERS_PAGE_SIZE = 15;
let ordersFullView    = false;
let activeOrderStatus = 'all';

document.getElementById('order-filter-tabs').addEventListener('click', e => {
    const btn = e.target.closest('.filter-tab');
    if (!btn) return;
    document.querySelectorAll('#order-filter-tabs .filter-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeOrderStatus = btn.dataset.status;
    renderOrdersTable();
});
document.getElementById('order-search').addEventListener('input', renderOrdersTable);

function renderOrdersTable() {
    const q = document.getElementById('order-search').value.toLowerCase();
    const filtered = ORDERS.filter(o =>
        (activeOrderStatus === 'all' || o.statusLabel === activeOrderStatus) &&
        (o.customer.toLowerCase().includes(q) || o.id.toLowerCase().includes(q))
    );
    const body  = document.getElementById('orders-table-body');
    const empty = document.getElementById('orders-empty');
    const pag   = document.getElementById('orders-pagination');
    const more  = document.getElementById('orders-show-more');
    const less  = document.getElementById('orders-show-less');
    if (!filtered.length) { body.innerHTML = ''; empty.style.display = 'block'; pag.style.display = 'none'; return; }
    empty.style.display = 'none';
    const display  = ordersFullView ? filtered : filtered.slice(0, ORDERS_PAGE_SIZE);
    const statuses = ['To Pay', 'To Ship', 'Completed', 'Cancelled'];
    body.innerHTML = display.map(o => `
        <tr>
            <td style="font-family:monospace;font-size:0.8rem;">${o.id}</td>
            <td>${o.customer}</td>
            <td>${o.items} items</td>
            <td class="fw-bold" style="color:var(--primary-green);">${o.total}</td>
            <td>${statusBadge(o.status, o.statusLabel)}</td>
            <td style="color:#aaa;font-size:0.8rem;">${o.date}</td>
            <td>
                <select class="form-select form-select-sm" style="font-size:0.75rem;width:auto;"
                    onchange="updateOrderStatus(${o.db_id}, this.value, this)">
                    ${statuses.map(s => `<option value="${s}" ${o.statusLabel === s ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
            </td>
        </tr>`).join('');
    pag.style.display  = filtered.length > ORDERS_PAGE_SIZE ? 'block' : 'none';
    more.style.display = ordersFullView ? 'none' : 'inline-block';
    less.style.display = ordersFullView ? 'inline-block' : 'none';
}

function ordersShowMore() { ordersFullView = true;  renderOrdersTable(); }
function ordersShowLess() { ordersFullView = false; renderOrdersTable(); }

function updateOrderStatus(dbId, newStatus, selectEl) {
    selectEl.disabled = true;
    fetch('update_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${dbId}&status=${encodeURIComponent(newStatus)}`
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const order = ORDERS.find(o => o.db_id === dbId);
            if (order) { order.statusLabel = newStatus; order.status = newStatus.toLowerCase().replace(/\s/g, '_'); }
            buildOrderDonut(); buildRecentOrders();
        } else alert('Error: ' + (data.message || 'Could not update status'));
        selectEl.disabled = false;
    }).catch(() => { alert('Network error'); selectEl.disabled = false; });
}

/* ─── REVENUE ─── */
function buildMonthlyChart() {
    const max = Math.max(...MONTHLY_SALES.map(d => d.v), 1);
    const wrap    = document.getElementById('monthly-bars');
    const labWrap = document.getElementById('monthly-labels');
    wrap.innerHTML = ''; labWrap.innerHTML = '';
    MONTHLY_SALES.forEach(d => {
        const pct = d.v ? Math.round((d.v / max) * 100) : 4;
        const col = d.v > 0 ? 'var(--light-green)' : '#f0f0f0';
        wrap.innerHTML    += `<div class="rev-bar" style="height:${pct}%;background:${col};">${d.v > 0 ? `<span class="rtip">₱${d.v.toLocaleString()}</span>` : ''}</div>`;
        labWrap.innerHTML += `<div style="flex:1;text-align:center;">${d.m}</div>`;
    });
}

function buildTopProducts() {
    document.getElementById('top-products-body').innerHTML = TOP_PRODUCTS.map(p => `
        <tr>
            <td><span style="font-size:0.78rem;font-weight:700;color:var(--primary-green);">#${p.rank}</span></td>
            <td>${p.name}</td>
            <td><span class="cat-pill">${p.cat}</span></td>
            <td>${p.units}</td>
            <td class="fw-bold" style="color:var(--primary-green);">${p.rev}</td>
        </tr>`).join('');
}

/* ─── USERS ─── */
document.getElementById('user-search').addEventListener('input', renderUsersTable);

function renderUsersTable() {
    const q = document.getElementById('user-search').value.toLowerCase();
    const filtered = USERS.filter(u =>
        u.name.toLowerCase().includes(q) || u.username.toLowerCase().includes(q)
    );
    document.getElementById('user-count').textContent = `${filtered.length} users`;
    document.getElementById('users-table-body').innerHTML = filtered.map(u => {
        const initials  = u.name.split(' ').map(n => n[0]).slice(0, 2).join('');
        const statusCls = u.status === 'active' ? 'badge-completed' : 'badge-cancelled';
        return `
        <tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    <div class="user-avatar">${initials}</div>
                    <span>${u.name}</span>
                </div>
            </td>
            <td style="color:#888;font-size:0.8rem;">@${u.username}</td>
            <td style="color:#aaa;font-size:0.8rem;">${u.joined}</td>
            <td>${u.orders}</td>
            <td class="fw-bold" style="color:var(--primary-green);">${u.spent}</td>
            <td><span class="${statusCls}">${u.status.charAt(0).toUpperCase() + u.status.slice(1)}</span></td>
            <td>
                <button class="btn btn-outline-success btn-sm" style="font-size:0.73rem;padding:2px 10px;border-radius:20px;"
                    onclick="showHistory(${u.id}, '${u.name.replace(/'/g, "\\'")}')">
                    <i class="bi bi-clock-history me-1"></i>History
                </button>
            </td>
        </tr>`;
    }).join('');
}

const HISTORY_PAGE_SIZE = 10;
let historyAllData  = [];
let historyFullView = false;

function renderHistoryTable() {
    const body    = document.getElementById('history-body');
    const pag     = document.getElementById('history-pagination');
    const more    = document.getElementById('history-show-more');
    const less    = document.getElementById('history-show-less');
    const display = historyFullView ? historyAllData : historyAllData.slice(0, HISTORY_PAGE_SIZE);
    if (!display.length) {
        body.innerHTML    = `<tr><td colspan="5" class="text-center text-muted py-3">No orders yet.</td></tr>`;
        pag.style.display = 'none'; return;
    }
    body.innerHTML = display.map(o => `
        <tr>
            <td style="font-family:monospace;font-size:0.8rem;">#${o.order_number}</td>
            <td>${o.item_count} items</td>
            <td class="fw-bold" style="color:var(--primary-green);">&#8369;${parseFloat(o.total_amount).toFixed(2)}</td>
            <td>${statusBadge('', o.status)}</td>
            <td style="color:#aaa;font-size:0.8rem;">${o.created_at.split(' ')[0]}</td>
        </tr>`).join('');
    pag.style.display  = historyAllData.length > HISTORY_PAGE_SIZE ? 'block' : 'none';
    more.style.display = historyFullView ? 'none' : 'inline-block';
    less.style.display = historyFullView ? 'inline-block' : 'none';
}

function historyShowMore() { historyFullView = true;  renderHistoryTable(); }
function historyShowLess() { historyFullView = false; renderHistoryTable(); }

function showHistory(userId, name) {
    historyFullView = false;
    historyAllData  = [];
    const panel = document.getElementById('order-history-panel');
    const body  = document.getElementById('history-body');
    document.getElementById('history-title').textContent = `${name}'s Order History`;
    document.getElementById('history-pagination').style.display = 'none';
    body.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm text-success"></div> Loading...</td></tr>`;
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    fetch(`get_user_orders_admin.php?user_id=${userId}`)
        .then(r => r.json())
        .then(history => { historyAllData = history; renderHistoryTable(); })
        .catch(() => { body.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Error loading history.</td></tr>`; });
}

function closeHistory() {
    document.getElementById('order-history-panel').style.display = 'none';
}

/* ─── EDIT PRODUCT ─── */
function buildInvCatSelect() {
    const select = document.getElementById('edit-category');
    if (!select) return;
    select.innerHTML = CATEGORIES.map(c => `<option value="${c.slug}">${c.name}</option>`).join('');
}

function editProduct(id) {
    const product = invItems.find(p => p.id === id);
    if (!product) return;
    document.getElementById('edit-id').value    = product.id;
    document.getElementById('edit-name').value  = product.name;
    document.getElementById('edit-category').value = product.slug;
    document.getElementById('edit-price').value = parseFloat(product.price.replace(/[^\d.]/g, ''));
    document.getElementById('edit-qty').value   = product.qty;
    document.getElementById('edit-img').value   = product.img;
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function submitEditProduct() {
    fetch('admin_update_product_process.php', { method: 'POST', body: new FormData(document.getElementById('editProductForm')) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('activeTab', 'inventory');
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            }
            else Swal.fire('Error', data.message, 'error');
        })
        .catch(() => Swal.fire('Error', 'An unexpected error occurred.', 'error'));
}

/* ─── LOGOUT ─── */
function logout() {
    if (confirm('Are you sure you want to logout?')) window.location.href = 'admin_logout.php';
}

/* ─── INIT ─── */
buildWeeklyChart();
buildOrderDonut();
buildRecentOrders();
buildInvCatTabs();
buildInvCatSelect();
renderInvGrid();
renderOrdersTable();
buildMonthlyChart();
buildTopProducts();
document.getElementById('user-count').textContent = `${USERS.length} users`;
renderUsersTable();

const savedTab = localStorage.getItem('activeTab');
if (savedTab) {
    switchSection(savedTab);
    localStorage.removeItem('activeTab');
}

/* ─── ADD PRODUCT ─── */
document.getElementById('addItemFormOwner').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('[type=submit]');
    btn.disabled = true; btn.textContent = 'Adding...';
    fetch('admin_add_item_process.php', { method: 'POST', body: new FormData(this) })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                localStorage.setItem('activeTab', 'inventory');
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'Product added successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            }
            else { Swal.fire('Error', data.message, 'error'); btn.disabled = false; btn.textContent = 'Add Product'; }
        })
        .catch(() => { Swal.fire('Error', 'Network error', 'error'); btn.disabled = false; btn.textContent = 'Add Product'; });
});
</script>
</body>
</html>