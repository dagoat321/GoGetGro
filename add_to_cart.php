<?php

require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
$redirect  = $_POST['redirect'] ?? 'index.php';

if ($productId > 0 && $quantity > 0) {
    // Validate against real stock
    $sStmt = $db->prepare('SELECT stock_quantity FROM products WHERE id = ?');
    $sStmt->bind_param('i', $productId);
    $sStmt->execute();
    $row = $sStmt->get_result()->fetch_assoc();
    $stock = (int)($row['stock_quantity'] ?? 0);

    if ($stock <= 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Sorry, this item is out of stock.'];
    } else {
        // Cap quantity at available stock
        $quantity = min($quantity, $stock);
        add_items_to_cart([['product_id' => $productId, 'quantity' => $quantity]]);
        if ($quantity < (int)$_POST['quantity']) {
            $_SESSION['flash'] = ['type' => 'warning', 'msg' => "Only $stock unit(s) available — your quantity was adjusted."];
        }
    }
}

header('Location: ' . $redirect);
exit;

