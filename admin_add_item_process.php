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

$name = trim($_POST['name'] ?? '');
$category_slug = trim($_POST['category_slug'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
$image_path = 'images/Group 19.png'; // Default image

if ($name === '' || $category_slug === '' || $price < 0 || $stock_quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/images/';
    $fileName = basename($_FILES['image']['name']);
    
    // Add unique prefix to avoid overwriting
    $uniqueFileName = uniqid() . '_' . $fileName;
    $targetFilePath = $uploadDir . $uniqueFileName;
    
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    // Basic validation
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $image_path = 'images/' . $uniqueFileName;
        } else {
            // Failed to move
        }
    }
}

if (add_product($db, $category_slug, $name, $price, $image_path, $stock_quantity)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

