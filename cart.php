<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user();

// POST: handle cart quantity changes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action    = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    if ($productId > 0) {
        $stmt = null;
        if ($action === 'increase') {
            // Check stock before increasing
            $sStmt = $db->prepare('SELECT stock_quantity FROM products WHERE id = ?');
            $sStmt->bind_param('i', $productId);
            $sStmt->execute();
            $stockRow = $sStmt->get_result()->fetch_assoc();
            $availableStock = (int)($stockRow['stock_quantity'] ?? 0);

            // Get current cart qty
            $cStmt = $db->prepare('SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
            $cStmt->bind_param('ii', $user['id'], $productId);
            $cStmt->execute();
            $cartRow = $cStmt->get_result()->fetch_assoc();
            $currentQty = (int)($cartRow['quantity'] ?? 0);

            if ($currentQty < $availableStock) {
                $stmt = $db->prepare('UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
            } else {
                $_SESSION['flash'] = ['type' => 'warning', 'msg' => "Maximum available stock ($availableStock) reached for this item."];
            }
        } elseif ($action === 'decrease') {
            $stmt = $db->prepare('UPDATE cart_items SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ? AND quantity > 1');
        } elseif ($action === 'remove') {
            $stmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        }
        if ($stmt) {
            $stmt->bind_param('ii', $user['id'], $productId);
            $stmt->execute();
        }
    }
    header('Location: cart.php');
    exit;
}

$cart          = get_cart_items($db);
$products      = get_products_by_ids($db, array_keys($cart));
$addresses     = get_user_addresses($user['id']);
$savedGateways = get_user_payment_gateways($user['id']);
$allGateways   = payment_gateways();

// Build saved gateway list — fallback to all if none saved
$gatewayOptions = [];
if (!empty($savedGateways)) {
    foreach ($savedGateways as $gw) {
        $key = $gw['gateway_key'];
        if (isset($allGateways[$key])) {
            $gatewayOptions[$key] = $allGateways[$key];
        }
    }
}
if (empty($gatewayOptions)) {
    $gatewayOptions = $allGateways;
}

$stores = [
    'Makati Main Branch',
    'Quezon City Hub',
    'Cebu City Store',
    'Davao Branch',
    'BGC Taguig Store',
];

$subtotal = 0.0;
foreach ($cart as $pid => $qty) {
    if (isset($products[$pid])) {
        $subtotal += (float)$products[$pid]['price'] * $qty;
    }
}

// Build stock issues for JS validation
$cartStockIssues = [];
foreach ($cart as $pid => $qty) {
    if (isset($products[$pid])) {
        $avail = (int)$products[$pid]['stock_quantity'];
        if ($avail <= 0) {
            $cartStockIssues[] = ['name' => $products[$pid]['name'], 'type' => 'oos'];
        } elseif ($qty > $avail) {
            $cartStockIssues[] = ['name' => $products[$pid]['name'], 'type' => 'over', 'qty' => $qty, 'avail' => $avail];
        }
    }
}
$hasCartStockBlock = !empty($cartStockIssues);

$hasUsedG3Launch = has_used_voucher($db, current_user()['id'], 'G3LAUNCH');

$pageTitle = 'Cart & Checkout';
$extraCss  = ['stylesheet3.css'];
require_once __DIR__ . '/includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="content-wrapper">
    <div class="container my-5">

        <?php if (isset($_SESSION['flash'])): ?>
            <?php $flash = $_SESSION['flash'];
            unset($_SESSION['flash']); ?>
            <div id="cartFlash" class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                <?= $flash['msg'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>
                setTimeout(() => {
                    const a = document.getElementById('cartFlash');
                    if (a) {
                        const b = bootstrap.Alert.getOrCreateInstance(a);
                        b.close();
                    }
                }, 6000);
            </script>
        <?php endif; ?>

        <form id="cartActionForm" method="POST" action="cart.php" style="display:none;">
            <input type="hidden" name="action" id="cart_action_input">
            <input type="hidden" name="product_id" id="cart_product_id_input">
        </form>
        <script>
            function submitCartAction(action, productId) {
                document.getElementById('cart_action_input').value = action;
                document.getElementById('cart_product_id_input').value = productId;
                document.getElementById('cartActionForm').submit();
            }
        </script>

        <form action="place_order.php" method="POST" id="checkoutForm">
            <div class="row g-4">

                <!-- ── LEFT: Items + Fulfillment ── -->
                <div class="col-lg-8">
                    <h1 class="summary-title mb-4">CHECKOUT</h1>

                    <!-- CART SUMMARY -->
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Order Summary (<?= cart_items_count() ?> items)</h5>
                            <div class="item-list">
                                <?php if (empty($cart)): ?>
                                    <p class="text-muted">Your cart is empty. <a href="index.php" class="text-success">Continue Shopping</a></p>
                                <?php else: ?>
                                    <?php foreach ($cart as $pid => $qty):
                                        if (!isset($products[$pid])) continue;
                                        $p = $products[$pid];
                                        $stockQty = (int)$p['stock_quantity'];
                                        $overStock = $qty > $stockQty;
                                    ?>
                                        <div class="cart-item d-flex align-items-center py-3 border-bottom gap-3">
                                            <img src="<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold small"><?= htmlspecialchars($p['name']) ?></h6>
                                                <small class="text-muted"><?= money((float)$p['price']) ?> each</small>
                                                <?php if ($stockQty <= 0): ?>
                                                    <div><span class="badge bg-danger mt-1">Out of Stock</span></div>
                                                <?php elseif ($stockQty <= 5): ?>
                                                    <div><span class="badge bg-warning text-dark mt-1">Low Stock: <?= $stockQty ?> left</span></div>
                                                <?php elseif ($overStock): ?>
                                                    <div><span class="badge bg-warning text-dark mt-1">Only <?= $stockQty ?> available</span></div>
                                                <?php endif; ?>
                                                <div class="d-inline-flex align-items-center gap-2 mt-1">
                                                    <button type="button" class="btn btn-sm btn-outline-success rounded-3" style="width:28px;height:28px;padding:0;" onclick="submitCartAction('decrease', <?= (int)$pid ?>)">-</button>
                                                    <strong class="<?= $overStock ? 'text-danger' : '' ?>"><?= (int)$qty ?></strong>
                                                    <button type="button" class="btn btn-sm btn-outline-success rounded-3" style="width:28px;height:28px;padding:0;" onclick="submitCartAction('increase', <?= (int)$pid ?>)" <?= $qty >= $stockQty ? 'disabled title="Max stock reached"' : '' ?>>+</button>
                                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 fw-semibold ms-2" onclick="submitCartAction('remove', <?= (int)$pid ?>)">Remove</button>
                                                </div>
                                            </div>
                                            <div class="fw-bold"><?= money((float)$p['price'] * $qty) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- FULFILLMENT TABS -->
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <ul class="nav nav-pills nav-fill p-2 bg-light rounded-top-4" id="fulfillmentTabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active rounded-pill py-2 fw-semibold" id="delivery-tab"
                                        data-bs-toggle="tab" data-bs-target="#delivery-panel" type="button"
                                        onclick="switchFulfillment('delivery')">
                                        <i class="bi bi-truck me-1"></i> Delivery
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-pill py-2 fw-semibold" id="pickup-tab"
                                        data-bs-toggle="tab" data-bs-target="#pickup-panel" type="button"
                                        onclick="switchFulfillment('pickup')">
                                        <i class="bi bi-shop me-1"></i> Store Pickup
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content p-4" id="fulfillmentContent">

                                <!-- DELIVERY PANEL -->
                                <div class="tab-pane fade show active" id="delivery-panel" role="tabpanel">
                                    <input type="hidden" name="fulfillment_type" id="fulfillment_type_input" value="delivery">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Delivery Type</label>
                                            <select name="delivery_type" id="delivery_type_select" class="form-select rounded-pill" onchange="updateTotal()">
                                                <option value="regular" data-fee="50">Regular Delivery &mdash; &#8369;50</option>
                                                <option value="express" data-fee="150">Express Delivery &mdash; &#8369;150</option>
                                                <option value="priority" data-fee="250">Priority Delivery &mdash; &#8369;250</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Delivery Schedule</label>
                                            <input type="text" name="delivery_schedule" class="form-control rounded-pill datetimepicker"
                                                placeholder="Select Date &amp; Time" autocomplete="off">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Delivery Address</label>
                                            <select name="delivery_address_id" class="form-select rounded-pill">
                                                <?php foreach ($addresses as $addr): ?>
                                                    <option value="<?= (int)$addr['id'] ?>">
                                                        <?= htmlspecialchars($addr['label'] . ': ' . $addr['address_line']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (empty($addresses)): ?>
                                                    <option value="">No addresses saved &mdash; add one in your profile.</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="same_billing"
                                                    name="same_billing" checked onchange="toggleBilling()">
                                                <label class="form-check-label small" for="same_billing">
                                                    Billing address is the same as delivery address
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="billing_address_container">
                                            <label class="form-label small fw-bold">Billing Address</label>
                                            <select name="billing_address_id" class="form-select rounded-pill">
                                                <?php foreach ($addresses as $addr): ?>
                                                    <option value="<?= (int)$addr['id'] ?>">
                                                        <?= htmlspecialchars($addr['label'] . ': ' . $addr['address_line']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <?php if (empty($addresses)): ?>
                                                    <option value="">No addresses saved.</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- PICKUP PANEL -->
                                <div class="tab-pane fade" id="pickup-panel" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Select Store</label>
                                            <select name="pickup_store" class="form-select rounded-pill">
                                                <?php foreach ($stores as $s): ?>
                                                    <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Pickup Schedule</label>
                                            <input type="text" name="pickup_schedule" class="form-control rounded-pill datetimepicker"
                                                placeholder="Select Date &amp; Time" autocomplete="off">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── RIGHT: Payment + Summary ── -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:90px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Payment</h5>

                            <!-- Voucher -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Voucher Code</label>
                                <div class="input-group">
                                    <input type="text" id="voucher_input" class="form-control rounded-start-pill"
                                        placeholder="Enter code" autocomplete="off">
                                    <input type="hidden" name="voucher_code" id="voucher_code_hidden" value="">
                                    <button class="btn btn-outline-success rounded-end-pill px-3" type="button"
                                        onclick="applyVoucher()">Apply</button>
                                </div>
                                <div id="voucher_msg" class="small mt-2" aria-live="polite"></div>
                            </div>

                            <hr>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Payment Method</label>
                                <select name="payment_method" id="pay_method_select" class="form-select rounded-pill" onchange="toggleGateway()">
                                    <option value="online" <?= get_saved_payment_method() !== 'cod' ? 'selected' : '' ?>>Online Payment</option>
                                    <option id="cod_option" value="cod" <?= get_saved_payment_method() === 'cod' ? 'selected' : '' ?>>Cash on Delivery (COD)</option>
                                </select>
                                <div id="pay_info" class="small text-muted mt-1 px-2 d-none"></div>
                            </div>

                            <!-- Gateway Selector -->
                            <div id="gateway_container" class="<?= get_saved_payment_method() === 'cod' ? 'd-none' : '' ?> mb-4">
                                <label class="form-label small fw-bold">Select Payment Gateway</label>
                                <select name="payment_gateway" class="form-select rounded-pill" id="gateway_select">
                                    <?php
                                    $currentSavedGw = get_saved_payment_gateway();
                                    foreach ($gatewayOptions as $key => $name): ?>
                                        <option value="<?= $key ?>" <?= $key === $currentSavedGw ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    You will be redirected to your chosen gateway to complete payment.
                                    <?php if (empty($savedGateways)): ?>
                                        <a href="profile.php" class="text-success">Set preferred gateways in your profile.</a>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <hr>

                            <!-- Order Summary -->
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Subtotal</span>
                                <span id="summary_subtotal"><?= money($subtotal) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1" id="delivery_fee_row">
                                <span>Delivery Fee</span>
                                <span id="summary_delivery"><?= money(50) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1 text-danger d-none" id="discount_row">
                                <span>Voucher Discount</span>
                                <span id="summary_discount">-&#8369;0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold h5 mt-3">
                                <span>Total</span>
                                <span id="summary_total"><?= money($subtotal + 50) ?></span>
                            </div>

                            <?php
                            $btnBg = $hasCartStockBlock ? '#dc3545' : '#007a5e';
                            $btnHoverBg = $hasCartStockBlock ? '#b02a37' : '#005a46';
                            ?>
                            <button type="button"
                                id="confirmOrderBtn"
                                style="background:<?= $btnBg ?>;color:#fff;border:none;border-radius:50px;padding:14px 20px;font-weight:700;font-size:1rem;width:100%;margin-top:20px;cursor:pointer;transition:background 0.2s;"
                                onmouseover="this.style.background='<?= $btnHoverBg ?>'" onmouseout="this.style.background='<?= $btnBg ?>'"
                                onclick="<?= empty($cart) ? 'showEmptyCartMsg()' : 'validateAndOrder()' ?>"
                                <?= $hasCartStockBlock && !empty($cart) ? '' : (empty($cart) ? 'disabled' : '') ?>>
                                <?= $hasCartStockBlock ? '&#9888; Fix Stock Issues First' : 'CONFIRM ORDER' ?>
                            </button>
                            <?php if ($hasCartStockBlock): ?>
                                <p class="small text-danger text-center mt-2 mb-0">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    Some items have stock issues. Adjust quantities or remove items.
                                </p>
                            <?php endif; ?>
                            <!-- Empty cart toast -->
                            <div id="emptyCartToast" style="display:none;margin-top:12px;background:#dc3545;color:#fff;border-radius:10px;padding:12px 16px;font-size:.9rem;font-weight:600;text-align:center;">
                                <i class="bi bi-cart-x me-2"></i>Your cart is empty!
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</main>

<style>
    .payment-method-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 2px solid #dde7e2;
        border-radius: 50px;
        padding: 10px 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .payment-method-pill:hover {
        border-color: var(--primary-green);
        background: #f0faf6;
    }

    .payment-method-pill.selected {
        border-color: var(--primary-green);
        background: #e8f7f0;
        color: var(--primary-green);
    }

    .payment-method-pill input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary-green);
    }

    @keyframes voucherPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, .5);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .voucher-applied {
        animation: voucherPulse 0.8s ease 2;
        border: 2px solid #28a745 !important;
        background: #f0fff4;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #voucher_msg.show {
        animation: slideIn 0.3s ease forwards;
    }
</style>

<script>
    const subtotal = <?= $subtotal ?>;
    let discount = 0;
    let isPickup = false;

    document.addEventListener('DOMContentLoaded', () => {
        flatpickr('.datetimepicker', {
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            minDate: 'today',
            time_24hr: false,
        });
        updateTotal();
        // Sync payment method pill selection on load
        document.querySelectorAll('input[name="payment_method"]').forEach(r => {
            updatePillStyle(r);
        });
        updatePaymentOptions();
    });

    function updatePaymentOptions() {
        const codOption = document.getElementById('cod_option');
        const paySelect = document.getElementById('pay_method_select');
        const payInfo = document.getElementById('pay_info');
        if (!codOption || !paySelect || !payInfo) return;

        if (isPickup) {
            if (subtotal <= 999) {
                codOption.innerText = "Pay Over the Counter";
                codOption.disabled = false;
                codOption.title = "";
                payInfo.classList.add('d-none');
            } else {
                codOption.innerText = "Pay Over the Counter";
                codOption.disabled = true;
                codOption.title = "Available for orders ₱999 and below.";
                payInfo.innerHTML = '<i class="bi bi-info-circle me-1"></i>Pay Over the Counter requires subtotal ≤ ₱999';
                payInfo.classList.remove('d-none');
                if (paySelect.value === 'cod') {
                    paySelect.value = 'online';
                    toggleGateway();
                }
            }
        } else {
            codOption.innerText = "Cash on Delivery (COD)";
            codOption.disabled = false;
            codOption.title = "";
            payInfo.classList.add('d-none');
        }
    }

    function switchFulfillment(type) {
        isPickup = (type === 'pickup');
        document.getElementById('fulfillment_type_input').value = type;
        updateTotal();
        updatePaymentOptions();
    }

    function toggleBilling() {
        const checked = document.getElementById('same_billing').checked;
        document.getElementById('billing_address_container').classList.toggle('d-none', checked);
    }

    function toggleGateway() {
        const method = document.getElementById('pay_method_select').value;
        document.getElementById('gateway_container').classList.toggle('d-none', method === 'cod');
    }

    function applyVoucher() {
        const input = document.getElementById('voucher_input');
        const code = input.value.trim().toUpperCase();
        const msg = document.getElementById('voucher_msg');
        const hidden = document.getElementById('voucher_code_hidden');

        const hasUsedLaunch = <?= $hasUsedG3Launch ? 'true' : 'false' ?>;

        msg.className = 'small mt-2';

        if (code === 'G3LAUNCH') {
            if (hasUsedLaunch) {
                msg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger">You have already claimed this voucher.</span>';
                msg.classList.add('show');
                input.classList.remove('voucher-applied');
                discount = 0;
                hidden.value = '';
                updateTotal();
            } else if (subtotal >= 999) {
                discount = subtotal * 0.10;
                hidden.value = code;
                input.classList.add('voucher-applied');
                msg.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">G3Launch Applied! 10% off your order.</span>';
                msg.classList.add('show');
                updateTotal();
                // Confetti-like bounce on the total
                const tot = document.getElementById('summary_total');
                tot.style.transition = 'color 0.3s';
                tot.style.color = '#28a745';
                setTimeout(() => tot.style.color = '', 1500);
            } else {
                msg.innerHTML = '<i class="bi bi-exclamation-circle-fill text-warning me-1"></i><span class="text-warning">Minimum spend of ₱999 required.</span>';
                msg.classList.add('show');
            }
        } else {
            msg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger">Invalid voucher code.</span>';
            msg.classList.add('show');
            input.classList.remove('voucher-applied');
            discount = 0;
            hidden.value = '';
            updateTotal();
        }
    }

    function updateTotal() {
        const deliveryFeeRow = document.getElementById('delivery_fee_row');
        let deliveryFee = 0;

        if (isPickup) {
            deliveryFeeRow.style.display = 'none';
        } else {
            deliveryFeeRow.style.display = '';
            const sel = document.getElementById('delivery_type_select');
            if (sel) {
                deliveryFee = parseFloat(sel.options[sel.selectedIndex].dataset.fee || 50);
            }
        }

        const total = subtotal + deliveryFee - discount;

        document.getElementById('summary_subtotal').innerText = '₱' + subtotal.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        document.getElementById('summary_delivery').innerText = '₱' + deliveryFee.toLocaleString('en-PH', {
            minimumFractionDigits: 2
        });

        if (discount > 0) {
            document.getElementById('discount_row').classList.remove('d-none');
            document.getElementById('summary_discount').innerText = '-₱' + discount.toLocaleString('en-PH', {
                minimumFractionDigits: 2
            });
        } else {
            document.getElementById('discount_row').classList.add('d-none');
        }

        document.getElementById('summary_total').innerText = '₱' + total.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function showEmptyCartMsg() {
        const toast = document.getElementById('emptyCartToast');
        if (toast) {
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
        return false;
    }

    const CART_STOCK_ISSUES = <?= json_encode($cartStockIssues) ?>;

    function validateAndOrder() {
        // 1. Block if stock issues
        if (CART_STOCK_ISSUES.length > 0) {
            let html = '<div style="text-align:left;">';
            CART_STOCK_ISSUES.forEach(i => {
                if (i.type === 'oos') {
                    html += `<div style="padding:8px 0;border-bottom:1px solid #f0f0f0;">
                    <span style="background:#dc3545;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-right:6px;">Out of Stock</span>
                    <b>${i.name}</b>
                </div>`;
                } else {
                    html += `<div style="padding:8px 0;border-bottom:1px solid #f0f0f0;">
                    <span style="background:#e67e22;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-right:6px;">Over Limit</span>
                    <b>${i.name}</b> &mdash; you have ${i.qty}, only ${i.avail} available
                </div>`;
                }
            });
            html += '</div>';
            Swal.fire({
                title: 'Cannot Place Order',
                html,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Fix Cart'
            });
            return;
        }

        // 2. Require schedule date
        const fulfillmentType = document.getElementById('fulfillment_type_input')?.value ?? 'delivery';
        const deliveryDate = document.querySelector('input[name="delivery_schedule"]')?.value?.trim() ?? '';
        const pickupDate = document.querySelector('input[name="pickup_schedule"]')?.value?.trim() ?? '';
        const payMethod = document.getElementById('pay_method_select')?.value;

        if (fulfillmentType === 'pickup' && payMethod === 'cod' && subtotal > 999) {
            Swal.fire({
                icon: 'error',
                title: 'Payment Method Unavailable',
                text: 'Pay Over the Counter is only available for orders ₱999 and below.',
                confirmButtonColor: '#007a5e'
            });
            return;
        }

        if (fulfillmentType === 'delivery' && deliveryDate === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Delivery Date Required',
                text: 'Please select a delivery date and time before placing your order.',
                confirmButtonColor: '#007a5e',
                confirmButtonText: 'OK'
            }).then(() => {
                document.querySelector('input[name="delivery_schedule"]')?.focus();
            });
            return;
        }
        if (fulfillmentType === 'pickup' && pickupDate === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Pickup Date Required',
                text: 'Please select a pickup date and time before placing your order.',
                confirmButtonColor: '#007a5e',
                confirmButtonText: 'OK'
            }).then(() => {
                document.querySelector('input[name="pickup_schedule"]')?.focus();
            });
            return;
        }

        // All good — submit
        document.getElementById('checkoutForm').submit();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>