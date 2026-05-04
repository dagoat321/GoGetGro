<?php

function get_categories(mysqli $db): array
{
    $result = $db->query('SELECT slug, name, icon_class, home_featured, home_sort FROM categories ORDER BY home_featured DESC, home_sort ASC, name ASC');

    return $result->fetch_all(MYSQLI_ASSOC);
}

function get_category_map(mysqli $db): array
{
    $map = [];

    foreach (get_categories($db) as $category) {
        $map[$category['slug']] = $category;
    }

    return $map;
}

function get_featured_categories(mysqli $db): array
{
    $stmt = $db->prepare('SELECT slug, name, icon_class, home_sort FROM categories WHERE home_featured = 1 ORDER BY home_sort ASC');
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_products_by_category(mysqli $db, string $categorySlug, ?int $limit = null): array
{
    if ($limit !== null) {
        $stmt = $db->prepare('SELECT id, name, price, image_path, category_slug, stock_quantity FROM products WHERE category_slug = ? ORDER BY id ASC LIMIT ?');
        $stmt->bind_param('si', $categorySlug, $limit);
    } else {
        $stmt = $db->prepare('SELECT id, name, price, image_path, category_slug, stock_quantity FROM products WHERE category_slug = ? ORDER BY id ASC');
        $stmt->bind_param('s', $categorySlug);
    }

    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_products_by_ids(mysqli $db, array $ids): array
{
    if ($ids === []) {
        return [];
    }

    $ids = array_values(array_map('intval', $ids));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $db->prepare("SELECT id, name, price, image_path, category_slug, stock_quantity FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();

    $products = [];

    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $product) {
        $products[(int) $product['id']] = $product;
    }

    return $products;
}

function find_user_by_login(mysqli $db, string $login): ?array
{
    $stmt = $db->prepare('SELECT id, full_name, username, email, password_hash, created_at FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

function create_user(mysqli $db, string $fullName, string $username, string $email, string $password): bool
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $fullName, $username, $email, $passwordHash);

    return $stmt->execute();
}

function find_admin_by_username(mysqli $db, string $username): ?array
{
    $stmt = $db->prepare('SELECT id, username, display_name, role, password_hash FROM admin_accounts WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    return $admin ?: null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_user']);
}

function current_admin(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function admin_dashboard_path(?array $admin = null): string
{
    $admin ??= current_admin();

    if (($admin['role'] ?? '') === 'owner') {
        return 'realadminview.php';
    }

    return 'adminview.php';
}

function cart_items_count(): int
{
    if (is_logged_in()) {
        global $db;
        $user = current_user();
        $stmt = $db->prepare('SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int) ($result['total'] ?? 0);
    }
    return array_sum($_SESSION['cart'] ?? []);
}

function get_cart_items(mysqli $db): array
{
    if (is_logged_in()) {
        $user = current_user();
        $stmt = $db->prepare('SELECT product_id, quantity FROM cart_items WHERE user_id = ?');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $cart = [];
        foreach ($result as $row) {
            $cart[(int) $row['product_id']] = (int) $row['quantity'];
        }
        return $cart;
    }
    return $_SESSION['cart'] ?? [];
}

function add_to_db_cart(mysqli $db, int $userId, int $productId, int $quantity): bool
{
    $stmt = $db->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
    $stmt->bind_param('iii', $userId, $productId, $quantity);
    return $stmt->execute();
}

function sync_session_cart_to_db(mysqli $db, int $userId): void
{
    if (!isset($_SESSION['cart']) || $_SESSION['cart'] === []) {
        return;
    }

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        add_to_db_cart($db, $userId, $productId, $quantity);
    }

    $_SESSION['cart'] = [];
}

function money(float $amount): string
{
    return 'PHP ' . number_format($amount, 2);
}

function get_admin_inventory_products(mysqli $db): array
{
    $result = $db->query("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON c.slug = p.category_slug
        ORDER BY p.name ASC
    ");

    if (!$result) {
        return [];
    }

    return $result->fetch_all(MYSQLI_ASSOC);
}

function search_products(mysqli $db, string $query): array
{
    $searchTerm = '%' . $query . '%';
    $stmt = $db->prepare('SELECT id, name, price, image_path, category_slug, stock_quantity FROM products WHERE name LIKE ? OR category_slug LIKE ? ORDER BY name ASC');
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $uniqueProducts = [];
    $seenNames = [];
    foreach ($results as $row) {
        if (!isset($seenNames[$row['name']])) {
            $seenNames[$row['name']] = true;
            $uniqueProducts[] = $row;
        }
    }
    return $uniqueProducts;
}

function search_categories(mysqli $db, string $query): array
{
    $searchTerm = '%' . $query . '%';
    $stmt = $db->prepare('SELECT slug, name, icon_class FROM categories WHERE name LIKE ? ORDER BY name ASC');
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function add_product(mysqli $db, string $categorySlug, string $name, float $price, string $imagePath, int $stockQuantity): bool
{
    $stmt = $db->prepare('INSERT INTO products (category_slug, name, price, image_path, stock_quantity) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssdsi', $categorySlug, $name, $price, $imagePath, $stockQuantity);

    return $stmt->execute();
}

function get_user_addresses(int $userId): array
{
    global $db;
    $stmt = $db->prepare('SELECT id, label, address_line, is_default FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_payment_gateways(int $userId): array
{
    global $db;
    $stmt = $db->prepare('SELECT id, gateway_key FROM user_payment_gateways WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_orders(int $userId): array
{
    global $db;
    $stmt = $db->prepare('SELECT id, order_number, status, fulfillment_type, total_amount, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_order_items(mysqli $db, int $orderId): array
{
    $stmt = $db->prepare('SELECT id, product_id, product_name, quantity, unit_price FROM order_items WHERE order_id = ?');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ─── Payment Gateways (local PH only, no emojis) ────────────────────────────

function payment_gateways(): array
{
    return [
        'gcash'      => 'GCash',
        'maya'       => 'Maya',
        'bdo'        => 'BDO Online Banking',
        'bpi'        => 'BPI Online Banking',
        'metrobank'  => 'Metrobank Online',
        'unionbank'  => 'UnionBank Online',
        'seabank'    => 'SeaBank',
        'cimb'       => 'CIMB Bank',
        'instapay'   => 'InstaPay',
        'dragonpay'  => 'DragonPay',
    ];
}

function get_saved_payment_gateway(): string
{
    global $db;
    $user = current_user();
    if ($user) {
        $gateways = get_user_payment_gateways($user['id']);
        if (!empty($gateways)) {
            return $gateways[0]['gateway_key'];
        }
    }
    return $_SESSION['payment_gateway'] ?? 'gcash';
}

function get_saved_payment_method(): string
{
    return $_SESSION['payment_method'] ?? 'online';
}

function save_payment_preference(string $method, string $gateway): void
{
    $_SESSION['payment_method'] = $method;
    $_SESSION['payment_gateway'] = $gateway;
}

// ─── Orders ──────────────────────────────────────────────────────────────────

function get_orders(): array
{
    $user = current_user();
    if (!$user) {
        return [];
    }
    return get_user_orders($user['id']);
}

function find_order(string|int $orderId): ?array
{
    global $db;
    $user = current_user();
    if (!$user) {
        return null;
    }
    $id = (int) $orderId;
    $stmt = $db->prepare('SELECT id, user_id, order_number, status, fulfillment_type, delivery_type, subtotal, delivery_fee, discount_amount, total_amount, created_at FROM orders WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $user['id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) {
        return null;
    }
    $order['items'] = get_order_items($db, (int) $order['id']);
    $order['total'] = $order['total_amount'];
    return $order;
}

function count_orders_by_status(string $status): int
{
    global $db;
    $user = current_user();
    if (!$user) {
        return 0;
    }
    $stmt = $db->prepare('SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = ?');
    $stmt->bind_param('is', $user['id'], $status);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int) ($row['cnt'] ?? 0);
}

function update_order_status(string|int $orderId, string $status): bool
{
    global $db;
    $id = (int) $orderId;
    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

function reduce_order_stock(mysqli $db, int $orderId): bool
{
    $items = get_order_items($db, $orderId);
    $success = true;
    foreach ($items as $item) {
        $stmt = $db->prepare('UPDATE products p JOIN products p2 ON p.name = p2.name SET p.stock_quantity = GREATEST(0, p.stock_quantity - ?) WHERE p2.id = ?');
        $qty = (int)$item['quantity'];
        $pid = (int)$item['product_id'];
        $stmt->bind_param('ii', $qty, $pid);
        if (!$stmt->execute()) {
            $success = false;
        }
    }
    return $success;
}


// ─── Cart / Order Creation ────────────────────────────────────────────────────

function add_items_to_cart(array $items): void
{
    global $db;
    $user = current_user();
    foreach ($items as $item) {
        $productId = (int) ($item['product_id'] ?? 0);
        $qty       = (int) ($item['quantity'] ?? 1);
        if ($productId <= 0) {
            continue;
        }
        if ($user) {
            add_to_db_cart($db, $user['id'], $productId, $qty);
        } else {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
        }
    }
}

function build_cart_order_items(mysqli $db, array $cart): array
{
    if (empty($cart)) {
        return [];
    }
    $products = get_products_by_ids($db, array_keys($cart));
    $items    = [];
    foreach ($cart as $pid => $qty) {
        if (!isset($products[$pid])) {
            continue;
        }
        $p       = $products[$pid];
        $items[] = [
            'product_id'     => (int) $pid,
            'name'           => $p['name'],
            'price'          => (float) $p['price'],
            'quantity'       => (int) $qty,
            'stock_quantity' => (int) $p['stock_quantity'],
            'image_path'     => $p['image_path'],
        ];
    }
    return $items;
}

function create_order_from_cart(mysqli $db, array $options = []): ?array
{
    $user = current_user();
    if (!$user) {
        return null;
    }

    $cart = get_cart_items($db);
    if (empty($cart)) {
        return null;
    }

    $items = build_cart_order_items($db, $cart);
    if (empty($items)) {
        return null;
    }

    $subtotal       = 0.0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $deliveryFee    = (float) ($options['delivery_fee'] ?? 0.0);
    $discountAmount = (float) ($options['discount_amount'] ?? 0.0);
    $total          = $subtotal + $deliveryFee - $discountAmount;

    $orderNumber    = 'GG-' . strtoupper(uniqid());
    $paymentMethod  = $options['payment_method'] ?? 'online';
    $gateway        = $options['gateway'] ?? '';
    $fulfillmentType = $options['fulfillment_type'] ?? 'delivery';
    $deliveryType   = $options['delivery_type'] ?? 'regular';
    $deliveryAddrId = isset($options['delivery_address_id']) ? (int) $options['delivery_address_id'] : null;
    $status         = 'To Pay';

    $stmt = $db->prepare('INSERT INTO orders (user_id, order_number, status, fulfillment_type, delivery_type, delivery_address_id, subtotal, delivery_fee, discount_amount, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param(
        'issssiiddd',
        $user['id'],
        $orderNumber,
        $status,
        $fulfillmentType,
        $deliveryType,
        $deliveryAddrId,
        $subtotal,
        $deliveryFee,
        $discountAmount,
        $total
    );
    if (!$stmt->execute()) {
        error_log('Order insert error: ' . $stmt->error);
        return null;
    }
    $orderId = $db->insert_id;

    foreach ($items as $item) {
        $pstmt = $db->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price) VALUES (?, ?, ?, ?, ?)');
        $pid   = (int) $item['product_id'];
        $qty   = (int) $item['quantity'];
        $price = (float) $item['price'];
        $name  = $item['name'];
        $pstmt->bind_param('iisid', $orderId, $pid, $name, $qty, $price);
        $pstmt->execute();
    }

    // Clear cart
    if ($user) {
        $cstmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $cstmt->bind_param('i', $user['id']);
        $cstmt->execute();

        if (!empty($options['voucher_code'])) {
            mark_voucher_used($db, $user['id'], $options['voucher_code']);
        }

        return find_order($orderId);
    } else {
        $_SESSION['cart'] = [];
    }

    return [
        'id'           => $orderId,
        'order_number' => $orderNumber,
        'gateway'      => $gateway,
        'total'        => $total,
    ];
}

// ─── Voucher Functions ────────────────────────────────────────────────────────

function has_used_voucher(mysqli $db, int $userId, string $voucherCode): bool
{
    $db->query("CREATE TABLE IF NOT EXISTS used_vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        voucher_code VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY(user_id, voucher_code)
    )");
    
    $stmt = $db->prepare('SELECT id FROM used_vouchers WHERE user_id = ? AND voucher_code = ?');
    $stmt->bind_param('is', $userId, $voucherCode);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function mark_voucher_used(mysqli $db, int $userId, string $voucherCode): void
{
    $db->query("CREATE TABLE IF NOT EXISTS used_vouchers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        voucher_code VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY(user_id, voucher_code)
    )");
    
    $stmt = $db->prepare('INSERT IGNORE INTO used_vouchers (user_id, voucher_code) VALUES (?, ?)');
    $stmt->bind_param('is', $userId, $voucherCode);
    $stmt->execute();
}

// ─── Admin Analytics (real DB) ────────────────────────────────────────────────

function get_all_orders_admin(mysqli $db, ?string $status = null, ?string $search = null): array
{
    $where = [];
    $params = [];
    $types  = '';

    if ($status && $status !== 'all') {
        $where[]  = 'o.status = ?';
        $params[] = $status;
        $types   .= 's';
    }
    if ($search) {
        $like     = '%' . $search . '%';
        $where[]  = '(u.full_name LIKE ? OR u.username LIKE ? OR o.order_number LIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types   .= 'sss';
    }

    $sql = "SELECT o.id, o.order_number, o.status, o.fulfillment_type,
                   o.total_amount, o.created_at,
                   u.full_name, u.username,
                   COUNT(oi.id) AS item_count
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            LEFT JOIN order_items oi ON oi.order_id = o.id
            " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT 100";

    $stmt = $db->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_all_users_admin(mysqli $db): array
{
    $result = $db->query("
        SELECT u.id, u.full_name, u.username, u.email, u.created_at,
               COUNT(o.id)         AS order_count,
               COALESCE(SUM(o.total_amount), 0) AS total_spent
        FROM users u
        LEFT JOIN orders o ON o.user_id = u.id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function get_user_order_history_admin(mysqli $db, int $userId): array
{
    $stmt = $db->prepare("
        SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at,
               COUNT(oi.id) AS item_count
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_weekly_sales_admin(mysqli $db): array
{
    $result = $db->query("
        SELECT DAYOFWEEK(created_at) AS dow,
               DAYNAME(created_at)   AS day_name,
               COALESCE(SUM(total_amount), 0) AS total
        FROM orders
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          AND status NOT IN ('Cancelled')
        GROUP BY DAYOFWEEK(created_at), DAYNAME(created_at)
        ORDER BY DAYOFWEEK(created_at) ASC
    ");
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $map  = [];
    foreach ($rows as $r) {
        $map[(int)$r['dow']] = (float)$r['total'];
    }
    $out = [];
    for ($i = 1; $i <= 7; $i++) {
        $out[] = ['day' => $days[$i - 1], 'val' => $map[$i] ?? 0];
    }
    return $out;
}

function get_monthly_sales_admin(mysqli $db): array
{
    $year   = date('Y');
    $result = $db->query("
        SELECT MONTH(created_at) AS month_num,
               COALESCE(SUM(total_amount), 0) AS total
        FROM orders
        WHERE YEAR(created_at) = {$year}
          AND status NOT IN ('Cancelled')
        GROUP BY MONTH(created_at)
        ORDER BY month_num ASC
    ");
    $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $map    = [];
    foreach ($rows as $r) {
        $map[(int)$r['month_num']] = (float)$r['total'];
    }
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $out    = [];
    for ($m = 1; $m <= 12; $m++) {
        $out[] = ['m' => $months[$m - 1], 'v' => $map[$m] ?? 0];
    }
    return $out;
}

function get_top_products_admin(mysqli $db, int $limit = 5): array
{
    $stmt = $db->prepare("
        SELECT p.name,
               c.name AS category_name,
               SUM(oi.quantity) AS units_sold,
               SUM(oi.quantity * oi.unit_price) AS revenue
        FROM order_items oi
        LEFT JOIN products p ON p.id = oi.product_id
        LEFT JOIN categories c ON c.slug = p.category_slug
        WHERE oi.product_id IS NOT NULL
        GROUP BY oi.product_id
        ORDER BY revenue DESC
        LIMIT ?
    ");
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_admin_revenue_summary(mysqli $db): array
{
    $row = $db->query("
        SELECT
            COALESCE(SUM(CASE WHEN status NOT IN ('Cancelled') THEN total_amount END), 0) AS total_sales,
            COALESCE(SUM(CASE WHEN status NOT IN ('Cancelled') AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN total_amount END), 0) AS this_month,
            COALESCE(SUM(CASE WHEN status NOT IN ('Cancelled') AND YEARWEEK(created_at, 1) = YEARWEEK(NOW(), 1) THEN total_amount END), 0) AS this_week,
            COALESCE(AVG(CASE WHEN status NOT IN ('Cancelled') THEN total_amount END), 0) AS avg_order,
            COUNT(*) AS total_orders
        FROM orders
    ")->fetch_assoc();
    return $row ?: [];
}
