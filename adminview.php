<?php

require __DIR__ . '/includes/bootstrap.php';

$admin = current_admin();

if (!$admin) {
    header('Location: login_admin.php');
    exit;
}

$adminName = $admin['display_name'] ?? 'Staff';
$inventoryProducts = get_admin_inventory_products($db);
$inventoryCategories = get_categories($db);
$lowStockProducts = array_values(array_filter($inventoryProducts, static function (array $product): bool {
    return (int) ($product['stock_quantity'] ?? 0) <= 5;
}));

usort($lowStockProducts, static function (array $left, array $right): int {
    $quantityCompare = ((int) $left['stock_quantity']) <=> ((int) $right['stock_quantity']);

    if ($quantityCompare !== 0) {
        return $quantityCompare;
    }

    return strcmp($left['name'], $right['name']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoGetGro | Staff Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="stylesheet3.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-green: #007a5e;
            --light-green: #e8f7f1;
            --hover-green: #f0faf6;
            --dark-green: #005a45;
        }

        body { background-color: #f8f9fa; }

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

        /* Sidebar Styling */
        .category-btn { 
            cursor: pointer; 
            transition: all 0.2s; 
            border: none !important;
            border-radius: 8px !important;
            margin-bottom: 4px;
            font-size: 0.85rem;
        }
        .category-btn:hover { background-color: var(--hover-green); color: var(--primary-green); }
        .category-btn.active {
            background-color: var(--light-green) !important;
            color: var(--primary-green) !important;
            font-weight: 600;
            border-left: 4px solid var(--primary-green) !important;
        }

        /* Product Card Styling */
        .admin-card {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 12px;
            background: #fff;
            height: 100%;
            transition: transform 0.2s, shadow 0.2s;
        }
        .admin-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        .product-card-img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: var(--light-green);
        }

        .cat-badge {
            font-size: 0.65rem;
            background: var(--light-green);
            color: var(--primary-green);
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .search-container input {
            border: 1px solid #ced4da !important;
            border-radius: 8px;
            padding-left: 35px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .search-container input:focus {
            border-color: var(--primary-green) !important;
            box-shadow: 0 0 0 0.25rem rgba(0, 122, 94, 0.1);
            outline: none;
        }
        .search-container {
            width: 100%; 
            max-width: 450px; 
            margin-left: auto;
            margin-right: auto;
        }

        #admin-product-list {
            display: flex;
            flex-wrap: wrap;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s ease;
            padding-bottom: 20px;
        }
        .grid-collapsed { max-height: 620px; }
        .grid-expanded { max-height: 800px; }

        #admin-product-list::-webkit-scrollbar { width: 6px; }
        #admin-product-list::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        #admin-product-list::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        #admin-product-list::-webkit-scrollbar-thumb:hover { background: var(--primary-green); }

        #pagination-wrapper {
            position: sticky;
            bottom: 0;
            background: linear-gradient(to top, rgba(255,255,255,1) 70%, rgba(255,255,255,0));
            padding: 20px 0;
            z-index: 10;
            margin-top: -10px;
        }

        .sticky-top { top: 20px; align-self: flex-start; }

        .admin-card .name { 
            font-size: 0.8rem; 
            font-weight: 500;
            color: #444; 
            margin: 0 0 8px; 
            display: block;
            min-height: 2.4em;
            overflow: visible;
            line-height: 1.2;
        }

        .stock-badge {
            font-size: 0.65rem;
            background: #f8f9fa;
            color: #666;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .text-low-stock { color: #dc3545 !important; font-weight: bold; }

        .admin-card .price { font-size: 0.95rem; font-weight: 700; color: var(--primary-green); }

        .custom-scroll::-webkit-scrollbar { width: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 5px;
            background: #f8f9fa;
            border-radius: 20px;
            padding: 2px 5px;
            border: 1px solid #eee;
            margin-left: auto;
        }
        .qty-btn {
            border: none;
            background: white;
            color: var(--primary-green);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .qty-btn:hover { background: var(--primary-green); color: white; }

        /* Remove product search cards */
        .remove-result-card {
            border: 1px solid #f0f0f0;
            background: #fff;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
        }
        .remove-result-card:hover {
            background: #fff3f3;
            border-color: #f5c6cb;
        }
        .selected-remove-card {
            background: #fff3f3 !important;
            border-color: #f5c6cb !important;
            outline: 2px solid #dc3545;
        }
    </style>
</head>
<body>

<header class="navbar shadow-sm">
    <div class="d-flex align-items-center justify-content-between w-100">
        <div class="d-flex align-items-center gap-3">
            <img src="images/Group 46.png" alt="GoGetGro Logo" height="36" onerror="this.style.display='none'">
            <span class="owner-badge">Staff Portal</span>
        </div>
        <nav class="nav-links d-flex gap-4 align-items-center">
            <span class="text-white small opacity-75"><?= htmlspecialchars($adminName) ?></span>
            <a href="#" onclick="logout()"><i class="bi bi-box-arrow-right me-1"></i>Sign Out</a>
        </nav>
    </div>
</header>

<main class="container-fluid px-4 py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Manage Products</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-lg me-1"></i> Add New Product
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#removeItemModal">
                            <i class="bi bi-trash me-1"></i> Remove Product
                        </button>
                        <span id="product-count" class="badge bg-light text-dark border fw-normal"></span>
                    </div>
                </div>

                <!-- Modals -->
                <div class="modal fade" id="addItemModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form id="addItemForm" enctype="multipart/form-data">
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
                                    <input type="text" id="remove-p-search" class="form-control ps-5 rounded-pill" placeholder="Search by product name or category..." autocomplete="off">
                                </div>
                                <div id="remove-results-list" style="max-height:340px;overflow-y:auto;">
                                    <div class="text-center text-muted py-4 small" id="remove-placeholder"><i class="bi bi-search me-1"></i>Start typing to find a product</div>
                                </div>
                                <div id="remove-selected-info" class="mt-3 p-3 rounded-3 d-none" style="background:#fff3f3;border:1px solid #f5c6cb;">
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="remove-sel-img" src="" style="width:48px;height:48px;object-fit:cover;border-radius:8px;" onerror="this.src='https://placehold.co/48x48?text=?'">
                                        <div>
                                            <div class="fw-bold" id="remove-sel-name"></div>
                                            <div class="text-muted small" id="remove-sel-cat"></div>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary ms-auto rounded-pill" onclick="clearRemoveSelection()">Clear</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" id="confirm-remove-btn" class="btn btn-danger rounded-pill px-4" onclick="confirmRemoveSelected()" disabled>
                                    <i class="bi bi-trash me-1"></i>Remove Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search-container mb-4 w-100" style="max-width: 100%; position: relative;">
                    <i class="bi bi-funnel" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #666;"></i>
                    <input type="text" id="admin-search" class="form-control" placeholder="Filter items..." style="padding-left: 38px;">
                </div>

                <div class="row g-3" id="admin-product-list"></div>

                <div id="pagination-wrapper" class="text-center mt-5" style="display: none;">
                    <hr class="text-muted opacity-25 mb-4">
                    <button id="show-more-btn" class="btn btn-success px-5 rounded-pill fw-bold">Show More</button>
                    <button id="show-less-btn" class="btn btn-outline-secondary px-5 rounded-pill fw-bold" style="display: none;">Show Less</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 20px;">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold mb-3">Categories</h5>
                    <div class="custom-scroll" style="max-height: 350px; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="admin-category-list"></ul>
                    </div>
                </div>
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3 text-danger">Low Stock</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light"><tr><th>PRODUCT</th><th class="text-end">QTY</th><th class="text-center">ACTION</th></tr></thead>
                            <tbody style="font-size: 0.85rem;">
                                <!-- Will be rendered via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm">
                    <input type="hidden" id="edit-id" name="id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-category" class="form-label">Category</label>
                        <select class="form-select" id="edit-category" name="category_slug" required>
                            <?php foreach ($inventoryCategories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-price" class="form-label">Price (₱)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="edit-price" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-qty" class="form-label">Stock Quantity</label>
                        <input type="number" step="1" min="0" class="form-control" id="edit-qty" name="stock_quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-img" class="form-label">Image Path</label>
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

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function logout() { if (confirm('Logout?')) window.location.href = 'admin_logout.php'; }

const CATEGORIES = <?= json_encode(array_map(fn($c) => ['slug'=>$c['slug'],'name'=>$c['name']], $inventoryCategories)) ?>;
let allItems = <?= json_encode(array_map(fn($p) => [
    'id' => (int)$p['id'],
    'img' => $p['image_path'],
    'name' => $p['name'],
    'price' => money((float)$p['price']),
    'qty' => (int)$p['stock_quantity'],
    'slug' => $p['category_slug'],
    'catName' => $p['category_name']
], $inventoryProducts)) ?>;

let activeSlug = 'all';
const INITIAL_LIMIT = 18;
let isFullView = false;

function initCategories() {
    const list = document.getElementById('admin-category-list');
    list.innerHTML = `<li class="list-group-item category-btn d-flex justify-content-between align-items-center ${activeSlug==='all'?'active':''}" data-slug="all"><span>All Products</span> <small>${allItems.length}</small></li>`;
    CATEGORIES.forEach(cat => {
        const count = allItems.filter(i => i.slug === cat.slug).length;
        list.innerHTML += `<li class="list-group-item category-btn d-flex justify-content-between align-items-center ${activeSlug===cat.slug?'active':''}" data-slug="${cat.slug}"><span>${cat.name}</span> <small>${count}</small></li>`;
    });
}

function renderProducts(items) {
    const container = document.getElementById('admin-product-list');
    const displayItems = isFullView ? items : items.slice(0, INITIAL_LIMIT);
    document.getElementById('product-count').textContent = `${items.length} items`;
    
    if (!items.length) {
        container.innerHTML = `<div class="col-12 text-center text-muted py-5">No products found.</div>`;
        return;
    }

    container.innerHTML = displayItems.map(p => {
        const isLow = p.qty <= 5;
        return `
        <div class="col-6 col-md-4 col-xl-2">
            <div class="admin-card d-flex flex-column">
                <span class="cat-badge">${p.catName}</span>
                <img src="${p.img}" alt="${p.name}" class="product-card-img" onerror="this.src='https://placehold.co/200x120?text=Product'">
                <p class="name" title="${p.name}">${p.name}</p>
                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <span class="price">${p.price}</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="stock-badge ${isLow?'text-low-stock':''}" id="qty-${p.id}">Qty: ${p.qty}</span>
                        <button class="btn btn-sm btn-outline-success p-1 py-0" onclick="editProduct(${p.id})">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');

    const pag = document.getElementById('pagination-wrapper');
    pag.style.display = items.length > INITIAL_LIMIT ? 'block' : 'none';
    document.getElementById('show-more-btn').style.display = isFullView ? 'none' : 'inline-block';
    document.getElementById('show-less-btn').style.display = isFullView ? 'inline-block' : 'none';
}

function updateQty(id, action) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', action);

    fetch('admin_update_qty_process.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = allItems.find(i => i.id === id);
            if (item) {
                item.qty = data.newQty;
                const el = document.getElementById(`qty-${id}`);
                if (el) {
                    el.textContent = data.newQty;
                    el.classList.toggle('text-low-stock', data.newQty <= 5);
                }
                initCategories();
                renderLowStock();
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    });
}

function renderLowStock() {
    const lowStockBody = document.querySelector('.card .table tbody');
    if (!lowStockBody) return;

    const lowStockItems = allItems
        .filter(i => i.qty <= 5)
        .sort((a, b) => a.qty - b.qty || a.name.localeCompare(b.name))
        .slice(0, 8);

    if (lowStockItems.length === 0) {
        lowStockBody.innerHTML = '<tr><td colspan="3" class="py-3 text-center text-muted">None</td></tr>';
    } else {
        lowStockBody.innerHTML = lowStockItems.map(p => `
            <tr>
                <td title="${p.name}">${p.name}</td>
                <td class="text-end text-danger fw-bold">${p.qty}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.7rem;" onclick="restockProduct(${p.id})">Restock</button>
                </td>
            </tr>
        `).join('');
    }
}

let restockModalInstance = null;
function restockProduct(id) {
    document.getElementById('restock-id').value = id;
    document.getElementById('restock-amount').value = 10;
    if (!restockModalInstance) restockModalInstance = new bootstrap.Modal(document.getElementById('restockModal'));
    restockModalInstance.show();
}

function confirmRestock() {
    const id = document.getElementById('restock-id').value;
    const amount = parseInt(document.getElementById('restock-amount').value, 10);
    if (isNaN(amount) || amount <= 0) {
        alert("Please enter a valid positive number.");
        return;
    }
    const formData = new FormData();
    formData.append('id', id);
    formData.append('action', 'restock');
    formData.append('amount', amount);

    fetch('admin_update_qty_process.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = allItems.find(i => i.id == id);
            if (item) {
                item.qty = data.newQty;
                const el = document.getElementById(`qty-${id}`);
                if (el) {
                    el.textContent = 'Qty: ' + data.newQty;
                    el.classList.toggle('text-low-stock', data.newQty <= 5);
                }
                initCategories();
                renderLowStock();
            }
            restockModalInstance.hide();
            Swal.fire({
                icon: 'success',
                title: 'Restocked!',
                text: 'Product quantity updated.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(() => Swal.fire('Error', 'Network error', 'error'));
}

function confirmRemoveSelected() {
    if (!selectedRemoveId) return;
    Swal.fire({
        title: `Remove "${selectedRemoveName}"?`,
        text: "This will archive the product and remove it from the store.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('id', selectedRemoveId);
            fetch('admin_remove_item_process.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Removed!', 'Product has been archived and removed.', 'success')
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

document.getElementById('addItemForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetch('admin_add_item_process.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json()).then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Added!',
                text: 'Product added successfully.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
});

let selectedRemoveId   = null;
let selectedRemoveName = '';
let selectedRemoveImg  = '';
let selectedRemoveCat  = '';

function clearRemoveSelection() {
    selectedRemoveId = null;
    document.getElementById('remove-selected-info').classList.add('d-none');
    document.getElementById('confirm-remove-btn').disabled = true;
}

function selectProductToRemove(id, name, img, cat) {
    selectedRemoveId   = id;
    selectedRemoveName = name;
    selectedRemoveImg  = img;
    selectedRemoveCat  = cat;

    document.getElementById('remove-sel-name').textContent = name;
    document.getElementById('remove-sel-cat').textContent  = cat;
    document.getElementById('remove-sel-img').src          = img || 'https://placehold.co/48x48?text=?';
    document.getElementById('remove-selected-info').classList.remove('d-none');
    document.getElementById('confirm-remove-btn').disabled = false;

    // Highlight selected card
    document.querySelectorAll('.remove-result-card').forEach(c => c.classList.remove('selected-remove-card'));
    document.getElementById('remove-card-' + id)?.classList.add('selected-remove-card');
}

document.getElementById('remove-p-search').addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    const list = document.getElementById('remove-results-list');
    const ph   = document.getElementById('remove-placeholder');

    if (!q) {
        list.innerHTML = '';
        list.appendChild(ph);
        ph.classList.remove('d-none');
        clearRemoveSelection();
        return;
    }

    const matches = allItems.filter(i =>
        i.name.toLowerCase().includes(q) || i.catName.toLowerCase().includes(q)
    );

    if (!matches.length) {
        list.innerHTML = '<div class="text-center text-muted py-4 small"><i class="bi bi-emoji-frown me-1"></i>No products found</div>';
        clearRemoveSelection();
        return;
    }

    // Highlight matching text helper
    function highlight(text) {
        const idx = text.toLowerCase().indexOf(q);
        if (idx === -1) return text;
        return text.slice(0, idx) + '<mark class="p-0 bg-warning">' + text.slice(idx, idx + q.length) + '</mark>' + text.slice(idx + q.length);
    }

    list.innerHTML = matches.map(p => `
        <div class="remove-result-card d-flex align-items-center gap-3 px-3 py-2 rounded-3 mb-1"
             id="remove-card-${p.id}"
             role="button"
             onclick="selectProductToRemove(${p.id}, '${p.name.replace(/'/g,"\\'").replace(/"/g,'&quot;')}', '${p.img}', '${p.catName}')">
            <img src="${p.img}" onerror="this.src='https://placehold.co/40x40?text=?'" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0;">
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate" style="font-size:.9rem;">${highlight(p.name)}</div>
                <div class="text-muted" style="font-size:.75rem;">${highlight(p.catName)} &bull; ${p.price}</div>
            </div>
            <i class="bi bi-chevron-right text-muted" style="font-size:.75rem;"></i>
        </div>
    `).join('');

    // Re-highlight selected if still in results
    if (selectedRemoveId) {
        document.getElementById('remove-card-' + selectedRemoveId)?.classList.add('selected-remove-card');
    }
});

// Clear search state when modal closes
document.getElementById('removeItemModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('remove-p-search').value = '';
    document.getElementById('remove-results-list').innerHTML = '<div class="text-center text-muted py-4 small" id="remove-placeholder"><i class="bi bi-search me-1"></i>Start typing to find a product</div>';
    clearRemoveSelection();
});

document.getElementById('admin-search').addEventListener('input', refreshView);
document.getElementById('admin-category-list').addEventListener('click', e => {
    const btn = e.target.closest('.category-btn');
    if (!btn) return;
    activeSlug = btn.dataset.slug;
    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    refreshView();
});

document.getElementById('show-more-btn').onclick = () => { isFullView = true; refreshView(); };
document.getElementById('show-less-btn').onclick = () => { isFullView = false; refreshView(); };

function editProduct(id) {
    const product = allItems.find(p => p.id === id);
    if (!product) return;

    document.getElementById('edit-id').value = product.id;
    document.getElementById('edit-name').value = product.name;
    document.getElementById('edit-category').value = product.slug;
    document.getElementById('edit-price').value = parseFloat(product.price.replace(/[^\d.]/g, ''));
    document.getElementById('edit-qty').value = product.qty;
    document.getElementById('edit-img').value = product.img;

    const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    modal.show();
}

function submitEditProduct() {
    const form = document.getElementById('editProductForm');
    const formData = new FormData(form);

    fetch('admin_update_product_process.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Product updated successfully.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'An unexpected error occurred.', 'error');
    });
}

function refreshView() {
    const q = document.getElementById('admin-search').value.toLowerCase();
    const filtered = (activeSlug==='all'?allItems:allItems.filter(i=>i.slug===activeSlug))
        .filter(i => i.name.toLowerCase().includes(q));
    renderProducts(filtered);
}

initCategories();
renderProducts(allItems);
renderLowStock();
</script>
</body>
</html>

