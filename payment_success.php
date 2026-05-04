<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$orderId = $_GET['order_id'] ?? '';

if ($orderId) {
    $order = find_order($orderId);
    if ($order && $order['status'] !== 'To Ship' && $order['status'] !== 'Completed' && $order['status'] !== 'Cancelled') {
        save_payment_preference('online', 'paymongo');
        update_order_status($orderId, 'To Ship');
        reduce_order_stock($db, $orderId);
        
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Payment successful! Your order is now being processed.'];
    }
}

header('Location: orderhistory.php?tab=to-ship');
exit;

