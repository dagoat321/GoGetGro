<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = current_user();

if (!$user) {
    header('Location: login.php');
    exit;
}

$allGw             = payment_gateways();
$savedPayMethod    = get_saved_payment_method();
$savedGw           = get_saved_payment_gateway();
$savedUserGateways = get_user_payment_gateways($user['id']);
$addresses         = get_user_addresses($user['id']);
$flashMessage      = null;

$gwOptions = [];
if (!empty($savedUserGateways)) {
    foreach ($savedUserGateways as $g) {
        $k = $g['gateway_key'];
        if (isset($allGw[$k])) $gwOptions[$k] = $allGw[$k];
    }
}
if (empty($gwOptions)) $gwOptions = $allGw;

$stores = [
    'Makati Main Branch',
    'Quezon City Hub',
    'Cebu City Store',
    'Davao Branch',
    'BGC Taguig Store',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if (in_array($action, ['increase','decrease','remove'], true) && $productId > 0) {
        if ($user) {
            $uid = (int)$user['id'];
            if ($action === 'increase') {
                $sStmt = $db->prepare('SELECT stock_quantity FROM products WHERE id = ?');
                $sStmt->bind_param('i', $productId);
                $sStmt->execute();
                $stockRow = $sStmt->get_result()->fetch_assoc();
                $avail = (int)($stockRow['stock_quantity'] ?? 0);
                
                $cStmt = $db->prepare('SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
                $cStmt->bind_param('ii', $uid, $productId);
                $cStmt->execute();
                $cartRow = $cStmt->get_result()->fetch_assoc();
                $curr = (int)($cartRow['quantity'] ?? 0);
                
                if ($curr < $avail) {
                    $db->query("UPDATE cart_items SET quantity = quantity + 1 WHERE user_id = $uid AND product_id = $productId");
                } else {
                    $flashMessage = "Maximum available stock ($avail) reached for this item.";
                }
            } elseif ($action === 'decrease') {
                $db->query("UPDATE cart_items SET quantity = GREATEST(0, quantity - 1) WHERE user_id = $uid AND product_id = $productId");
                $db->query("DELETE FROM cart_items WHERE user_id = $uid AND quantity <= 0");
            } elseif ($action === 'remove') {
                $db->query("DELETE FROM cart_items WHERE user_id = $uid AND product_id = $productId");
            }
        } else {
            if ($action === 'increase') {
                $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            } elseif ($action === 'decrease' && isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]--;
                if ($_SESSION['cart'][$productId] <= 0) unset($_SESSION['cart'][$productId]);
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$productId]);
            }
        }
        header('Location: order-again.php' . (!empty($_GET['order']) ? '?order=' . urlencode($_GET['order']) : ''));
        exit;
    }

    if ($action === 'place-order') {
        $payMethod        = ($_POST['payment_method'] ?? 'online') === 'cod' ? 'cod' : 'online';
        $gw               = $_POST['payment_gateway'] ?? $savedGw;
        $fulfillmentType  = ($_POST['fulfillment_type'] ?? 'delivery') === 'pickup' ? 'pickup' : 'delivery';
        $deliveryType     = $_POST['delivery_type'] ?? 'regular';
        $deliveryAddrId   = isset($_POST['delivery_address_id']) && $_POST['delivery_address_id'] !== '' ? (int)$_POST['delivery_address_id'] : null;
        $deliverySchedule = trim($_POST['delivery_schedule'] ?? '');
        $pickupSchedule   = trim($_POST['pickup_schedule'] ?? '');
        $voucherCode      = strtoupper(trim($_POST['voucher_code'] ?? ''));

        // ── Validate schedule date ────────────────────────────────────────────────
        if ($fulfillmentType === 'delivery' && $deliverySchedule === '') {
            $flashMessage = ' Please select a delivery date and time before placing your order.';
            goto render_page;
        }
        if ($fulfillmentType === 'pickup' && $pickupSchedule === '') {
            $flashMessage = ' Please select a pickup date and time before placing your order.';
            goto render_page;
        }

        // ── Validate stock ───────────────────────────────────────────────────
        $cart2    = get_cart_items($db);
        $items2   = build_cart_order_items($db, $cart2);
        $sub2     = 0.0;
        foreach ($items2 as $it2) $sub2 += $it2['price'] * $it2['quantity'];

        $oa_stockErrors = [];
        foreach ($cart2 as $pid => $qty) {
            $sChk = $db->prepare('SELECT name, stock_quantity FROM products WHERE id = ?');
            $sChk->bind_param('i', $pid);
            $sChk->execute();
            $pRow = $sChk->get_result()->fetch_assoc();
            if ($pRow) {
                $avail = (int)$pRow['stock_quantity'];
                if ($avail <= 0) {
                    $oa_stockErrors[] = "<b>{$pRow['name']}</b> is out of stock.";
                } elseif ($qty > $avail) {
                    $oa_stockErrors[] = "<b>{$pRow['name']}</b>: only $avail in stock (you have $qty in cart).";
                }
            }
        }
        if (!empty($oa_stockErrors)) {
            $flashMessage = ' Cannot place order:<br>' . implode('<br>', $oa_stockErrors) . '<br>Please adjust your cart.';
            goto render_page;
        }

        if ($fulfillmentType === 'pickup' && $payMethod === 'cod' && $sub2 > 999) {
            $flashMessage = ' Pay Over the Counter is only available for orders ₱999 and below.';
            goto render_page;
        }
        // ───────────────────────────────────────────────────────────────────────────

        $deliveryFees = ['regular' => 50.0, 'express' => 150.0, 'priority' => 250.0];
        $delFee       = ($fulfillmentType === 'pickup') ? 0.0 : ($deliveryFees[$deliveryType] ?? 50.0);
        $disc         = 0.0;
        
        if ($voucherCode === 'G3LAUNCH' && $sub2 >= 999) {
            if (has_used_voucher($db, $user['id'], 'G3LAUNCH')) {
                $flashMessage = ' You have already claimed the G3LAUNCH voucher.';
                goto render_page;
            }
            $disc = $sub2 * 0.10;
        } else {
            $voucherCode = '';
        }
        $fulfLabel    = ($fulfillmentType === 'pickup') ? 'Store Pickup' : ucfirst($deliveryType) . ' Delivery';

        save_payment_preference($payMethod, $gw);

        $order = create_order_from_cart($db, [
            'payment_method'      => $payMethod,
            'gateway'             => $gw,
            'fulfillment_type'    => $fulfLabel,
            'delivery_type'       => $deliveryType,
            'delivery_address_id' => $deliveryAddrId,
            'delivery_fee'        => $delFee,
            'discount_amount'     => $disc,
            'voucher_code'        => $voucherCode,
        ]);

        if ($order) {
            try {
                $db->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS email_sent TINYINT(1) DEFAULT 0");
            } catch (Exception $e) {}

            require_once __DIR__ . '/includes/email_helper.php';
            if (!empty($user['email'])) {
                try {
                    $items_details = get_order_items($db, $order['id']);
                    $orderStatusText = ($payMethod === 'online') ? 'Pending' : 'Shipped';
                    $itemsHtml = '';
                    foreach($items_details as $item) {
                        $itemsHtml .= "<tr>
                            <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item['product_name']) . "</td>
                            <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
                            <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>&#8369;" . number_format($item['unit_price'] * $item['quantity'], 2) . "</td>
                        </tr>";
                    }
                    $totalAmount = $order['total'] ?? ($sub2 + $delFee - $disc);
                    $emailSubject = "Order Confirmation - GoGetGro Order #" . $order['order_number'];
                    $emailMessage = "
                    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
                            <h2 style='margin: 0;'>Order Confirmation</h2>
                        </div>
                        <div style='padding: 20px;'>
                            <p>Dear " . htmlspecialchars($user['first_name'] ?? 'Customer') . ",</p>
                            <p>Thank you for shopping with GoGetGro. Your order has been successfully placed.</p>
                            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                                <strong>Order Number:</strong> #" . $order['order_number'] . "<br>
                                <strong>Order Status:</strong> " . $orderStatusText . "<br>
                                <strong>Total Amount:</strong> &#8369;" . number_format($totalAmount, 2) . "
                            </div>
                            <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                                <thead>
                                    <tr>
                                        <th style='padding: 8px; border-bottom: 2px solid #ddd; text-align: left;'>Item</th>
                                        <th style='padding: 8px; border-bottom: 2px solid #ddd; text-align: center;'>Qty</th>
                                        <th style='padding: 8px; border-bottom: 2px solid #ddd; text-align: right;'>Price</th>
                                    </tr>
                                </thead>
                                <tbody>" . $itemsHtml . "</tbody>
                            </table>
                            <p>If you have chosen online payment, please ensure to complete the payment to process your order.</p>
                            <br><p>Best Regards,<br>GoGetGro Support Team</p>
                        </div>
                        <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                            &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
                        </div>
                    </div>";
                    
                    if (send_gogetgro_email($user['email'], $emailSubject, $emailMessage)) {
                        $uStmt = $db->prepare('UPDATE orders SET email_sent = 1 WHERE id = ?');
                        $uStmt->bind_param('i', $order['id']);
                        $uStmt->execute();
                    }
                } catch (Exception $e) {
                    error_log("Error processing order placement email: " . $e->getMessage());
                }
            }

            if ($payMethod === 'online') {
                header('Location: payment_gateway.php?gateway=' . urlencode($gw) . '&orderId=' . $order['id']);
                exit;
            }

            // For COD
            update_order_status($order['id'], 'To Ship');
            reduce_order_stock($db, $order['id']);

            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order placed! Your order ' . $order['order_number'] . ' is confirmed.'];
            header('Location: orderhistory.php?tab=to-ship');
            exit;
        } else {
            $err = "Order creation failed. Cart count: " . count($cart2) . ". ";
            $err .= "Subtotal: " . $sub2 . ". ";
            $flashMessage = $err . 'Please try again or contact support.';
        }
    }
}

render_page:
$cart       = get_cart_items($db);
$items      = build_cart_order_items($db, $cart);
$subtotal   = 0.0;
foreach ($items as $item) $subtotal += $item['price'] * $item['quantity'];
$sourceOrder = trim($_GET['order'] ?? '');

// Build stock issues for JS validation
$cartStockIssues = [];
foreach ($cart as $pid => $qty) {
    $sChk2 = $db->prepare('SELECT name, stock_quantity FROM products WHERE id = ?');
    $sChk2->bind_param('i', $pid);
    $sChk2->execute();
    $sRow = $sChk2->get_result()->fetch_assoc();
    if ($sRow) {
        $avail = (int)$sRow['stock_quantity'];
        if ($avail <= 0) {
            $cartStockIssues[] = ['name' => $sRow['name'], 'type' => 'oos'];
        } elseif ($qty > $avail) {
            $cartStockIssues[] = ['name' => $sRow['name'], 'type' => 'over', 'qty' => $qty, 'avail' => $avail];
        }
    }
}

$pageTitle = 'Order Again';
$extraCss  = ['stylesheet3.css'];
require_once __DIR__ . '/includes/header.php';

$oaStockWarning = $_SESSION['oa_stock_warning'] ?? null;
unset($_SESSION['oa_stock_warning']);

$hasUsedG3Launch = has_used_voucher($db, current_user()['id'], 'G3LAUNCH');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="content-wrapper">
    <div class="container my-5">

        <?php if ($flashMessage): ?>
            <?php
                $fType = (strpos($flashMessage, '') !== false || strpos($flashMessage, 'Cannot') !== false) ? 'danger' : 'warning';
            ?>
            <div id="oaFlash" class="alert alert-<?= $fType ?> alert-dismissible fade show rounded-3 mb-4">
                <?= $flashMessage ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>setTimeout(()=>{ const a=document.getElementById('oaFlash'); if(a){ const b=bootstrap.Alert.getOrCreateInstance(a); b.close(); }}, 7000);</script>
        <?php endif; ?>

        <?php if ($oaStockWarning): ?>
            <?php
                $warningData = json_decode($oaStockWarning, true);
                $outOfStockItems = $warningData['out_of_stock'] ?? [];
                $lowStockItems   = $warningData['low_stock'] ?? [];

                $popupHtml = '';

                if (!empty($outOfStockItems)) {
                    $popupHtml .= '<p style="font-size:0.82rem;color:#888;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin:0 0 8px;">Removed — Out of Stock</p>';
                    foreach ($outOfStockItems as $oos) {
                        $imgSrc = htmlspecialchars($oos['image']);
                        $itemName = htmlspecialchars($oos['name']);
                        $popupHtml .= '
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;margin-bottom:8px;background:#fff5f5;border:1px solid #ffd6d6;border-radius:10px;">
                            <img src="' . $imgSrc . '" style="width:52px;height:52px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                            <div style="text-align:left;">
                                <div style="font-weight:600;font-size:0.9rem;color:#333;">' . $itemName . '</div>
                                <span style="background:#dc3545;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">Out of Stock</span>
                            </div>
                        </div>';
                    }
                }

                if (!empty($lowStockItems)) {
                    if (!empty($outOfStockItems)) $popupHtml .= '<div style="margin:12px 0;"></div>';
                    $popupHtml .= '<p style="font-size:0.82rem;color:#888;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin:0 0 8px;">Low Stock — Quantity Adjusted</p>';
                    foreach ($lowStockItems as $ls) {
                        $imgSrc = htmlspecialchars($ls['image']);
                        $itemName = htmlspecialchars($ls['name']);
                        $req = (int)$ls['requested'];
                        $avail = (int)$ls['available'];
                        $popupHtml .= '
                        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;margin-bottom:8px;background:#fffbf0;border:1px solid #ffe8a1;border-radius:10px;">
                            <img src="' . $imgSrc . '" style="width:52px;height:52px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                            <div style="text-align:left;flex:1;">
                                <div style="font-weight:600;font-size:0.9rem;color:#333;margin-bottom:4px;">' . $itemName . '</div>
                                <div style="font-size:0.8rem;color:#666;">
                                    <span style="text-decoration:line-through;color:#aaa;">Qty: ' . $req . '</span>
                                    &rarr;
                                    <span style="background:#e67e22;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;">Adjusted to ' . $avail . '</span>
                                </div>
                            </div>
                        </div>';
                    }
                }
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        title: '<span style="font-size:1.1rem;font-weight:700;">&#9888; Stock Notice</span>',
                        html: `<div style="max-height:340px;overflow-y:auto;padding:4px 2px;"><?= addslashes($popupHtml) ?></div>`,
                        icon: 'warning',
                        confirmButtonColor: '#007a5e',
                        confirmButtonText: 'Got it, continue',
                        customClass: { popup: 'swal2-stock-popup' }
                    });
                });
            </script>
        <?php endif; ?>

        <form action="order-again.php" method="POST" id="oaForm">
            <input type="hidden" name="action" id="oaAction" value="place-order">
            <input type="hidden" name="product_id" id="oaProductId" value="0">
            <div class="row g-4">

                <!-- LEFT: Items + Fulfillment -->
                <div class="col-lg-8">
                    <h1 class="summary-title mb-4">ORDER AGAIN</h1>

                    <?php if ($sourceOrder !== ''): ?>
                        <p class="text-muted mb-3">Source order: <?= htmlspecialchars($sourceOrder) ?></p>
                    <?php endif; ?>

                    <!-- CART ITEMS -->
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Cart Items (<?= count($items) ?> items)</h5>
                            <?php if (empty($items)): ?>
                                <div class="text-center py-4">
                                    <h6 class="text-muted mb-2">Your cart is empty</h6>
                                    <p class="text-muted small mb-3">Add products or reorder from order history.</p>
                                    <a href="orderhistory.php" class="btn btn-outline-success rounded-pill px-4">Back to Order History</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="d-flex align-items-center py-3 border-bottom gap-3">
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                             class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-semibold small"><?= htmlspecialchars($item['name']) ?></h6>
                                            <small class="text-muted"><?= money((float)$item['price']) ?> each</small>
                                            <div class="d-inline-flex align-items-center gap-2 mt-1">
                                                <button type="button" class="btn btn-sm btn-outline-success rounded-3" style="width:28px;height:28px;padding:0;" onclick="submitOaAction('decrease', <?= (int)$item['product_id'] ?>)">-</button>
                                                <strong><?= (int)$item['quantity'] ?></strong>
                                                <button type="button" class="btn btn-sm btn-outline-success rounded-3" style="width:28px;height:28px;padding:0;" onclick="submitOaAction('increase', <?= (int)$item['product_id'] ?>)" <?= (int)$item['quantity'] >= (int)$item['stock_quantity'] ? 'disabled title="Max stock reached"' : '' ?>>+</button>
                                                <button type="button" class="btn btn-link btn-sm text-danger p-0 fw-semibold ms-2" onclick="submitOaAction('remove', <?= (int)$item['product_id'] ?>)">Remove</button>
                                            </div>
                                        </div>
                                        <div class="fw-bold"><?= money((float)$item['price'] * (int)$item['quantity']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- FULFILLMENT TABS (same as cart.php) -->
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <ul class="nav nav-pills nav-fill p-2 bg-light rounded-top-4" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active rounded-pill py-2 fw-semibold" id="oa-delivery-tab"
                                        data-bs-toggle="tab" data-bs-target="#oa-delivery-panel" type="button"
                                        onclick="oaSwitchFulfillment('delivery')">
                                        <i class="bi bi-truck me-1"></i> Delivery
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link rounded-pill py-2 fw-semibold" id="oa-pickup-tab"
                                        data-bs-toggle="tab" data-bs-target="#oa-pickup-panel" type="button"
                                        onclick="oaSwitchFulfillment('pickup')">
                                        <i class="bi bi-shop me-1"></i> Store Pickup
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content p-4">
                                <!-- DELIVERY PANEL -->
                                <div class="tab-pane fade show active" id="oa-delivery-panel" role="tabpanel">
                                    <input type="hidden" name="fulfillment_type" id="oa_fulfillment_type" value="delivery">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Delivery Type</label>
                                            <select name="delivery_type" id="oa_delivery_type" class="form-select rounded-pill" onchange="oaUpdateTotal()">
                                                <option value="regular" data-fee="50">Regular Delivery — ₱50</option>
                                                <option value="express" data-fee="150">Express Delivery — ₱150</option>
                                                <option value="priority" data-fee="250">Priority Delivery — ₱250</option>
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
                                                    <option value="">No addresses saved — add one in your profile.</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="oa_same_billing"
                                                    name="same_billing" checked onchange="oaToggleBilling()">
                                                <label class="form-check-label small" for="oa_same_billing">
                                                    Billing address is the same as delivery address
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 d-none" id="oa_billing_container">
                                            <label class="form-label small fw-bold">Billing Address</label>
                                            <select name="billing_address_id" class="form-select rounded-pill">
                                                <?php foreach ($addresses as $addr): ?>
                                                    <option value="<?= (int)$addr['id'] ?>">
                                                        <?= htmlspecialchars($addr['label'] . ': ' . $addr['address_line']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- PICKUP PANEL -->
                                <div class="tab-pane fade" id="oa-pickup-panel" role="tabpanel">
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

                <!-- RIGHT: Payment + Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top:90px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Payment</h5>

                            <!-- Voucher -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Voucher Code</label>
                                <div class="input-group">
                                    <input type="text" id="oa_voucher_input" class="form-control rounded-start-pill"
                                        placeholder="Enter code" autocomplete="off">
                                    <input type="hidden" name="voucher_code" id="oa_voucher_hidden" value="">
                                    <button class="btn btn-outline-success rounded-end-pill px-3" type="button"
                                        onclick="oaApplyVoucher()">Apply</button>
                                </div>
                                <div id="oa_voucher_msg" class="small mt-2"></div>
                            </div>

                            <hr>

                            <!-- Payment Method -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Payment Method</label>
                                <select name="payment_method" id="oa_pay_method" class="form-select rounded-pill" onchange="oaToggleGateway()">
                                    <option value="online" <?= $savedPayMethod !== 'cod' ? 'selected' : '' ?>>Online Payment</option>
                                    <option id="oa_cod_option" value="cod" <?= $savedPayMethod === 'cod' ? 'selected' : '' ?>>Cash on Delivery (COD)</option>
                                </select>
                                <div id="oa_pay_info" class="small text-muted mt-1 px-2 d-none"></div>
                            </div>

                            <!-- Gateway Dropdown -->
                            <div id="oa_gateway_container" class="<?= $savedPayMethod === 'cod' ? 'd-none' : '' ?> mb-4">
                                <label class="form-label small fw-bold">Select Payment Gateway</label>
                                <select name="payment_gateway" class="form-select rounded-pill" id="oa_gateway_select">
                                    <?php foreach ($gwOptions as $key => $name): ?>
                                        <option value="<?= $key ?>" <?= $savedGw === $key ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    You will be redirected to complete payment.
                                </p>
                            </div>

                            <hr>

                            <!-- Summary -->
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Subtotal</span>
                                <span id="oa_subtotal"><?= money($subtotal) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1" id="oa_delivery_fee_row">
                                <span>Delivery Fee</span>
                                <span id="oa_delivery_fee"><?= money(50) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1 text-danger d-none" id="oa_discount_row">
                                <span>Voucher Discount</span>
                                <span id="oa_discount">-₱0.00</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold h5 mt-3">
                                <span>Total</span>
                                <span id="oa_total" style="color: #007a5e;"><?= money($subtotal + 50) ?></span>
                            </div>

                            <?php
                                $hasStockBlock = !empty($cartStockIssues);
                                $btnDisabled = (empty($items) || $hasStockBlock) ? 'disabled' : '';
                            ?>
                            <button type="button"
                                id="confirmOrderBtn"
                                style="background:<?= $hasStockBlock ? '#dc3545' : '#007a5e' ?>;color:#fff;border:none;border-radius:50px;padding:14px 20px;font-weight:700;font-size:1rem;width:100%;margin-top:20px;cursor:pointer;transition:background 0.2s;"
                                onmouseover="this.style.background='<?= $hasStockBlock ? '#b02a37' : '#005a46' ?>'" onmouseout="this.style.background='<?= $hasStockBlock ? '#dc3545' : '#007a5e' ?>'"
                                onclick="oaValidateAndSubmit()"
                                <?= $btnDisabled ?>>
                                <?= $hasStockBlock ? '&#9888; Fix Stock Issues First' : 'PLACE ORDER' ?>
                            </button>
                            <?php if ($hasStockBlock): ?>
                            <p class="small text-danger text-center mt-2 mb-0">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                Some items have stock issues. Remove or adjust quantities.
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</main>

<style>
@keyframes voucherPulse {
    0%   { box-shadow: 0 0 0 0 rgba(40,167,69,.5); }
    70%  { box-shadow: 0 0 0 10px rgba(40,167,69,0); }
    100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
}
.voucher-applied {
    animation: voucherPulse 0.8s ease 2;
    border: 2px solid #28a745 !important;
    background: #f0fff4;
}
</style>

<script>
const oaSubtotal = <?= $subtotal ?>;
let oaDiscount   = 0;
let oaIsPickup   = false;

document.addEventListener('DOMContentLoaded', () => {
    flatpickr('.datetimepicker', {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        minDate: 'today',
        time_24hr: false,
    });
    oaUpdateTotal();
    oaUpdatePaymentOptions();
});

function oaUpdatePaymentOptions() {
    const codOption = document.getElementById('oa_cod_option');
    const paySelect = document.getElementById('oa_pay_method');
    const payInfo   = document.getElementById('oa_pay_info');
    if (!codOption || !paySelect || !payInfo) return;
    
    if (oaIsPickup) {
        if (oaSubtotal <= 999) {
            codOption.innerText = "Pay Over the Counter";
            codOption.disabled = false;
            codOption.title = ""; 
            payInfo.classList.add('d-none');
        } else {
            codOption.innerText = "Pay Over the Counter";
            codOption.disabled = true;
            codOption.title = "Available for orders ₱999 and below.";
            payInfo.innerHTML = '<i class="bi bi-info-circle me-1"></i>Pay Over the Counter is only available for orders ₱999 and below.';
            payInfo.classList.remove('d-none');
            if (paySelect.value === 'cod') {
                paySelect.value = 'online';
                oaToggleGateway();
            }
        }
    } else {
        codOption.innerText = "Cash on Delivery (COD)";
        codOption.disabled = false;
        codOption.title = "";
        payInfo.classList.add('d-none');
    }
}

function oaSwitchFulfillment(type) {
    oaIsPickup = (type === 'pickup');
    document.getElementById('oa_fulfillment_type').value = type;
    oaUpdateTotal();
    oaUpdatePaymentOptions();
}

function oaToggleBilling() {
    const checked = document.getElementById('oa_same_billing').checked;
    document.getElementById('oa_billing_container').classList.toggle('d-none', checked);
}

function oaToggleGateway() {
    const method = document.getElementById('oa_pay_method').value;
    document.getElementById('oa_gateway_container').classList.toggle('d-none', method === 'cod');
}

function oaApplyVoucher() {
    const input  = document.getElementById('oa_voucher_input');
    const code   = input.value.trim().toUpperCase();
    const msg    = document.getElementById('oa_voucher_msg');
    const hidden = document.getElementById('oa_voucher_hidden');

    const hasUsedLaunch = <?= $hasUsedG3Launch ? 'true' : 'false' ?>;

    if (code === 'G3LAUNCH') {
        if (hasUsedLaunch) {
            msg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger">You have already claimed this voucher.</span>';
            input.classList.remove('voucher-applied');
            oaDiscount = 0;
            hidden.value = '';
            oaUpdateTotal();
        } else if (oaSubtotal >= 999) {
            oaDiscount = oaSubtotal * 0.10;
            hidden.value = code;
            input.classList.add('voucher-applied');
            msg.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success fw-semibold">G3Launch Applied! 10% off.</span>';
            oaUpdateTotal();
        } else {
            msg.innerHTML = '<i class="bi bi-exclamation-circle-fill text-warning me-1"></i><span class="text-warning">Minimum spend of ₱999 required.</span>';
        }
    } else {
        msg.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger">Invalid voucher code.</span>';
        input.classList.remove('voucher-applied');
        oaDiscount = 0;
        hidden.value = '';
        oaUpdateTotal();
    }
}

function oaUpdateTotal() {
    const feeRow = document.getElementById('oa_delivery_fee_row');
    let deliveryFee = 0;

    if (oaIsPickup) {
        feeRow.style.display = 'none';
    } else {
        feeRow.style.display = '';
        const sel = document.getElementById('oa_delivery_type');
        if (sel) deliveryFee = parseFloat(sel.options[sel.selectedIndex].dataset.fee || 50);
    }

    const total = oaSubtotal + deliveryFee - oaDiscount;

    document.getElementById('oa_subtotal').innerText     = '₱' + oaSubtotal.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('oa_delivery_fee').innerText = '₱' + deliveryFee.toLocaleString('en-PH', {minimumFractionDigits:2});

    if (oaDiscount > 0) {
        document.getElementById('oa_discount_row').classList.remove('d-none');
        document.getElementById('oa_discount').innerText = '-₱' + oaDiscount.toLocaleString('en-PH', {minimumFractionDigits:2});
    } else {
        document.getElementById('oa_discount_row').classList.add('d-none');
    }

    document.getElementById('oa_total').innerText = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function submitOaAction(action, productId) {
    document.getElementById('oaAction').value = action;
    document.getElementById('oaProductId').value = productId;
    document.getElementById('oaForm').submit();
}

const OA_STOCK_ISSUES = <?= json_encode($cartStockIssues ?? []) ?>;

function oaValidateAndSubmit() {
    // 1. Block if stock issues exist
    if (OA_STOCK_ISSUES.length > 0) {
        let html = '<div style="text-align:left;">';
        OA_STOCK_ISSUES.forEach(i => {
            if (i.type === 'oos') {
                html += `<div style="padding:6px 0;border-bottom:1px solid #f0f0f0;"><span style="background:#dc3545;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-right:6px;">Out of Stock</span><b>${i.name}</b></div>`;
            } else {
                html += `<div style="padding:6px 0;border-bottom:1px solid #f0f0f0;"><span style="background:#e67e22;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:20px;margin-right:6px;">Over Limit</span><b>${i.name}</b> &mdash; you have ${i.qty}, only ${i.avail} available</div>`;
            }
        });
        html += '</div>';
        Swal.fire({ title: 'Cannot Place Order', html, icon: 'error', confirmButtonColor: '#dc3545', confirmButtonText: 'Fix Cart' });
        return;
    }

    // 2. Require date/time selection
    const isPickup = oaIsPickup;
    const deliveryDate = document.querySelector('input[name="delivery_schedule"]')?.value?.trim() ?? '';
    const pickupDate   = document.querySelector('input[name="pickup_schedule"]')?.value?.trim() ?? '';
    const payMethod    = document.getElementById('oa_pay_method')?.value;

    if (isPickup && payMethod === 'cod' && oaSubtotal > 999) {
        Swal.fire({
            icon: 'error',
            title: 'Payment Method Unavailable',
            text: 'Pay Over the Counter is only available for orders ₱999 and below.',
            confirmButtonColor: '#007a5e'
        });
        return;
    }

    if (!isPickup && deliveryDate === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Delivery Date Required',
            text: 'Please select a delivery date and time before placing your order.',
            confirmButtonColor: '#007a5e',
            confirmButtonText: 'OK'
        });
        document.querySelector('input[name="delivery_schedule"]')?.focus();
        return;
    }
    if (isPickup && pickupDate === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Pickup Date Required',
            text: 'Please select a pickup date and time before placing your order.',
            confirmButtonColor: '#007a5e',
            confirmButtonText: 'OK'
        });
        document.querySelector('input[name="pickup_schedule"]')?.focus();
        return;
    }

    // All good — submit as place-order
    document.getElementById('oaAction').value = 'place-order';
    document.getElementById('oaProductId').value = '0';
    document.getElementById('oaForm').submit();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

