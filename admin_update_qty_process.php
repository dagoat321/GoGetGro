<?php
require __DIR__ . '/includes/bootstrap.php';

$admin = current_admin();
if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action    = $_POST['action'] ?? '';

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

if ($action === 'increase') {
    $stmt = $db->prepare('UPDATE products p JOIN products p2 ON p.name = p2.name SET p.stock_quantity = p.stock_quantity + 1 WHERE p2.id = ?');
} elseif ($action === 'decrease') {
    $stmt = $db->prepare('UPDATE products p JOIN products p2 ON p.name = p2.name SET p.stock_quantity = GREATEST(0, p.stock_quantity - 1) WHERE p2.id = ?');
} elseif ($action === 'restock') {
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 10;
    if ($amount <= 0) $amount = 10; // Fallback
    $stmt = $db->prepare('UPDATE products p JOIN products p2 ON p.name = p2.name SET p.stock_quantity = p.stock_quantity + ? WHERE p2.id = ?');
    $stmt->bind_param('ii', $amount, $productId);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

if ($action !== 'restock') {
    $stmt->bind_param('i', $productId);
}

if ($stmt->execute()) {
    // Get updated quantity
    $stmt2 = $db->prepare('SELECT stock_quantity FROM products WHERE id = ?');
    $stmt2->bind_param('i', $productId);
    $stmt2->execute();
    $newQty = $stmt2->get_result()->fetch_assoc()['stock_quantity'];
    
    echo json_encode(['success' => true, 'newQty' => $newQty]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $db->error]);
}

