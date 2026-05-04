<?php
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

$paymentMethod    = ($_POST['payment_method'] ?? 'cod') === 'online' ? 'online' : 'cod';
$paymentGateway   = $_POST['payment_gateway'] ?? get_saved_payment_gateway();
$fulfillmentType  = ($_POST['fulfillment_type'] ?? 'delivery') === 'pickup' ? 'pickup' : 'delivery';
$deliveryType     = $_POST['delivery_type'] ?? 'regular';
$deliveryAddrId   = isset($_POST['delivery_address_id']) && $_POST['delivery_address_id'] !== '' ? (int)$_POST['delivery_address_id'] : null;
$billingAddrId    = isset($_POST['billing_address_id']) && $_POST['billing_address_id'] !== '' ? (int)$_POST['billing_address_id'] : null;
$deliverySchedule = trim($_POST['delivery_schedule'] ?? '');
$pickupSchedule   = trim($_POST['pickup_schedule'] ?? '');
$pickupStore      = $_POST['pickup_store'] ?? '';
$voucherCode      = strtoupper(trim($_POST['voucher_code'] ?? ''));

// ── Validate schedule date ──────────────────────────────────────────────────
if ($fulfillmentType === 'delivery' && $deliverySchedule === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => ' Please select a delivery date and time before placing your order.'];
    header('Location: cart.php');
    exit;
}
if ($fulfillmentType === 'pickup' && $pickupSchedule === '') {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => ' Please select a pickup date and time before placing your order.'];
    header('Location: cart.php');
    exit;
}
// ───────────────────────────────────────────────────────────────────────────

// Calculate subtotal
$cart     = get_cart_items($db);
$products = get_products_by_ids($db, array_keys($cart));
$subtotal = 0.0;
foreach ($cart as $pid => $qty) {
    if (isset($products[$pid])) {
        $subtotal += (float)$products[$pid]['price'] * $qty;
    }
}

// Delivery fee (zero for pickup)
$deliveryFees = ['regular' => 50.0, 'express' => 150.0, 'priority' => 250.0];
$deliveryFee  = ($fulfillmentType === 'pickup') ? 0.0 : ($deliveryFees[$deliveryType] ?? 50.0);

// ── Pre-order stock validation ──────────────────────────────────────────────
$stockErrors    = [];
$stockWarnings  = [];
foreach ($cart as $pid => $qty) {
    if (!isset($products[$pid])) continue;
    $availStock = (int)$products[$pid]['stock_quantity'];
    $pName      = $products[$pid]['name'];
    if ($availStock <= 0) {
        $stockErrors[] = "<b>$pName</b> is out of stock and was removed.";
        // Remove from cart
        $delStmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?');
        $uid = (int)current_user()['id'];
        $delStmt->bind_param('ii', $uid, $pid);
        $delStmt->execute();
    } elseif ($qty > $availStock) {
        $stockWarnings[] = "<b>$pName</b>: Requested $qty, available $availStock. Adjusted.";
        // Cap to available stock
        $capStmt = $db->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?');
        $uid = (int)current_user()['id'];
        $capStmt->bind_param('iii', $availStock, $uid, $pid);
        $capStmt->execute();
    }
}

if (!empty($stockErrors)) {
    $msg = implode('<br>', array_merge($stockErrors, $stockWarnings));
    $msg .= '<br>Please review your cart.';
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => $msg];
    header('Location: cart.php');
    exit;
}
if (!empty($stockWarnings)) {
    $_SESSION['flash'] = ['type' => 'warning', 'msg' => implode('<br>', $stockWarnings) . '<br>Quantities were adjusted. Please review and confirm.'];
    header('Location: cart.php');
    exit;
}
// Recalculate subtotal after any adjustments
$cart     = get_cart_items($db);
$products = get_products_by_ids($db, array_keys($cart));
$subtotal = 0.0;
foreach ($cart as $pid => $qty) {
    if (isset($products[$pid])) {
        $subtotal += (float)$products[$pid]['price'] * $qty;
    }
}
// ────────────────────────────────────────────────────────────────────────────

if ($fulfillmentType === 'pickup' && $paymentMethod === 'cod' && $subtotal > 999) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Pay Over the Counter is only available for orders ₱999 and below.'];
    header('Location: cart.php');
    exit;
}

// Voucher
$discountAmount = 0.0;
if ($voucherCode === 'G3LAUNCH' && $subtotal >= 999) {
    if (has_used_voucher($db, current_user()['id'], 'G3LAUNCH')) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => ' You have already claimed the G3LAUNCH voucher.'];
        header('Location: cart.php');
        exit;
    }
    $discountAmount = $subtotal * 0.10;
} else {
    $voucherCode = ''; // Clear if not valid
}

// Fulfillment label
$fulfillmentLabel = ($fulfillmentType === 'pickup') ? 'Store Pickup' : ucfirst($deliveryType) . ' Delivery';

$order = create_order_from_cart($db, [
    'payment_method'      => $paymentMethod,
    'gateway'             => $paymentGateway,
    'fulfillment_type'    => $fulfillmentLabel,
    'delivery_type'       => $deliveryType,
    'delivery_address_id' => $deliveryAddrId,
    'delivery_fee'        => $deliveryFee,
    'discount_amount'     => $discountAmount,
    'voucher_code'        => $voucherCode,
]);

try {
    $db->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS email_sent TINYINT(1) DEFAULT 0");
} catch (Exception $e) {
    error_log("Failed to alter orders table: " . $e->getMessage());
}

if (!$order) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Failed to place order. Your cart may be empty.'];
    header('Location: cart.php');
    exit;
}

save_payment_preference($paymentMethod, $paymentGateway);

require_once __DIR__ . '/includes/email_helper.php';
$user = current_user();
if ($order && $user && !empty($user['email']) && empty($order['email_sent'])) {
    try {
        $items = get_order_items($db, $order['id']);
        $orderStatusText = ($paymentMethod === 'online') ? 'Pending' : 'Shipped';
        
        $itemsHtml = '';
        foreach($items as $item) {
            $itemsHtml .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item['product_name']) . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>&#8369;" . number_format($item['unit_price'] * $item['quantity'], 2) . "</td>
            </tr>";
        }
        
        $totalAmount = $order['total_amount'] ?? ($subtotal + $deliveryFee - $discountAmount);
        
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
                    <tbody>
                        " . $itemsHtml . "
                    </tbody>
                </table>
                
                <p>If you have chosen online payment, please ensure to complete the payment to process your order.</p>
                <br>
                <p>Best Regards,<br>GoGetGro Support Team</p>
            </div>
            <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
            </div>
        </div>
        ";
        
        if (send_gogetgro_email($user['email'], $emailSubject, $emailMessage)) {
            $uStmt = $db->prepare('UPDATE orders SET email_sent = 1 WHERE id = ?');
            $uStmt->bind_param('i', $order['id']);
            $uStmt->execute();
        }
    } catch (Exception $e) {
        error_log("Error processing order placement email: " . $e->getMessage());
    }
}

if ($paymentMethod === 'online') {
    header('Location: payment_gateway.php?gateway=' . urlencode($paymentGateway) . '&orderId=' . $order['id']);
    exit;
}

// For COD
update_order_status($order['id'], 'To Ship');
reduce_order_stock($db, $order['id']);

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order placed! Your order ' . $order['order_number'] . ' is confirmed.'];
header('Location: orderhistory.php?tab=to-ship');
exit;

