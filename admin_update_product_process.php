<?php

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

$admin = current_admin();
if (!$admin) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$category_slug = trim($_POST['category_slug'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
$image_path = trim($_POST['image_path'] ?? '');

if ($id <= 0 || $name === '' || $category_slug === '' || $price < 0 || $stock_quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Check if category exists
$stmt = $db->prepare('SELECT slug FROM categories WHERE slug = ?');
$stmt->bind_param('s', $category_slug);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
    exit;
}

// Get old name
$stmt = $db->prepare('SELECT name FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$oldName = $stmt->get_result()->fetch_assoc()['name'] ?? '';

if (!$oldName) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

$db->begin_transaction();

try {
    // Update category only for this specific ID
    $stmt1 = $db->prepare('UPDATE products SET category_slug = ? WHERE id = ?');
    $stmt1->bind_param('si', $category_slug, $id);
    $stmt1->execute();

    // Update other details for ALL products sharing the old name
    $stmt2 = $db->prepare('UPDATE products SET name = ?, price = ?, stock_quantity = ?, image_path = ? WHERE name = ?');
    $stmt2->bind_param('sdiss', $name, $price, $stock_quantity, $image_path, $oldName);
    $stmt2->execute();
    
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

