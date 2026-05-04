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

$productId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

// 1. Fetch the product data before deleting
$stmt = $db->prepare('
    SELECT p.id, p.name, p.price, p.stock_quantity, p.image_path,
           c.slug AS category_slug, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON c.slug = p.category_slug
    WHERE p.id = ?
');
$stmt->bind_param('i', $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// 2. Move image to deleted/images/
$originalImagePath = $product['image_path'] ?? '';
$archivedImagePath = '';

if ($originalImagePath) {
    $srcFile = __DIR__ . '/' . ltrim($originalImagePath, '/');
    if (file_exists($srcFile)) {
        $ext      = pathinfo($srcFile, PATHINFO_EXTENSION);
        $newName  = 'product_' . $productId . '_' . time() . '.' . $ext;
        $destFile = __DIR__ . '/deleted/images/' . $newName;
        if (copy($srcFile, $destFile)) {
            $archivedImagePath = 'deleted/images/' . $newName;
        }
    }
}

// 3. Insert into deleted_products archive table
$deletedBy = $admin['display_name'] ?? $admin['username'] ?? 'unknown';
$ins = $db->prepare('
    INSERT INTO deleted_products
        (original_id, name, category_slug, category_name, price, stock_quantity, original_image_path, archived_image_path, deleted_by)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$ins->bind_param(
    'issssdiss',
    $product['id'],
    $product['name'],
    $product['category_slug'],
    $product['category_name'],
    $product['price'],
    $product['stock_quantity'],
    $originalImagePath,
    $archivedImagePath,
    $deletedBy
);
$ins->execute();

// 4. Clean up cart_items referencing this product
$d1 = $db->prepare('DELETE FROM cart_items WHERE product_id = ?');
$d1->bind_param('i', $productId);
$d1->execute();

// 5. Delete the product from the main products table
$d2 = $db->prepare('DELETE FROM products WHERE id = ?');
$d2->bind_param('i', $productId);

if ($d2->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product archived and removed.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $db->error]);
}
