<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Ensure rating and email flags columns exist
try {
    $db->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS rating TINYINT(1) DEFAULT NULL");
    $db->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_email_sent TINYINT(1) DEFAULT 0");
    $db->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS rating_email_sent TINYINT(1) DEFAULT 0");
} catch (Exception $e) {
    error_log("Failed to alter orders table: " . $e->getMessage());
}

$user = current_user();
$allGw = payment_gateways();

$statusTabs = [
    'all'        => null,
    'to-pay'     => 'To Pay',
    'to-ship'    => 'To Ship',
    'to-rate'    => 'To Rate',
    'completed'  => 'Completed',
    'cancelled'  => 'Cancelled',
];
$activeTab = $_GET['tab'] ?? 'all';
if (!array_key_exists($activeTab, $statusTabs)) {
    $activeTab = 'all';
}

// Handle flash
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $orderId = trim($_POST['order_id'] ?? '');

    if ($action === 'reorder' && $orderId !== '') {
        $order = find_order($orderId);
        if ($order) {
            $outOfStock = [];
            $lowStock = [];
            $toAdd = [];
            
            foreach (($order['items'] ?? []) as $item) {
                $pid = (int)$item['product_id'];
                $requestedQty = (int)$item['quantity'];
                
                $pStmt = $db->prepare("SELECT name, stock_quantity, image_path FROM products WHERE id = ?");
                $pStmt->bind_param('i', $pid);
                $pStmt->execute();
                $pRes = $pStmt->get_result()->fetch_assoc();
                
                if ($pRes) {
                    $stock = (int)$pRes['stock_quantity'];
                    $name  = $pRes['name'];
                    $img   = $pRes['image_path'];
                    
                    if ($stock <= 0) {
                        $outOfStock[] = ['name' => $name, 'image' => $img];
                    } elseif ($stock < $requestedQty) {
                        $lowStock[] = ['name' => $name, 'image' => $img, 'requested' => $requestedQty, 'available' => $stock];
                        $toAdd[] = ['product_id' => $pid, 'quantity' => $stock];
                    } else {
                        $toAdd[] = ['product_id' => $pid, 'quantity' => $requestedQty];
                    }
                }
            }
            
            // Clear current cart so order-again replaces it exactly
            $cStmt = $db->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $cStmt->bind_param('i', $user['id']);
            $cStmt->execute();
            $_SESSION['cart'] = [];
            
            if (!empty($toAdd)) {
                add_items_to_cart($toAdd);
            }
            
            if (!empty($outOfStock) || !empty($lowStock)) {
                $_SESSION['oa_stock_warning'] = json_encode([
                    'out_of_stock' => $outOfStock,
                    'low_stock'    => $lowStock,
                ]);
            }
        }
        header('Location: order-again.php?order=' . urlencode($order['order_number']));
        exit;
    }
    if ($action === 'pay' && $orderId !== '') {
        $gw = $_POST['gateway'] ?? get_saved_payment_gateway();
        save_payment_preference('online', $gw);
        header('Location: payment_gateway.php?gateway=' . urlencode($gw) . '&orderId=' . urlencode($orderId));
        exit;
    }
    if ($action === 'cancel' && $orderId !== '') {
        $order = find_order($orderId);
        if ($order && $order['status'] === 'To Pay') {
            update_order_status($order['id'], 'Cancelled');
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order #' . $order['order_number'] . ' cancelled successfully.'];
        }
        header('Location: orderhistory.php?tab=to-pay');
        exit;
    }
    if ($action === 'receive' && $orderId !== '') {
        try {
            update_order_status($orderId, 'To Rate');
            
            $order = find_order($orderId);
            if ($order && !empty($user['email']) && empty($order['delivery_email_sent'])) {
                require_once __DIR__ . '/includes/email_helper.php';
                $items = get_order_items($db, $orderId);
                $itemsHtml = '';
                foreach($items as $item) {
                    $itemsHtml .= "<tr>
                        <td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item['product_name']) . "</td>
                        <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>" . $item['quantity'] . "</td>
                        <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>&#8369;" . number_format($item['unit_price'] * $item['quantity'], 2) . "</td>
                    </tr>";
                }
                $emailSubject = "Your order #" . $order['order_number'] . " has been delivered";
                $emailMessage = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
                        <h2 style='margin: 0;'>GoGetGro</h2>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Dear " . htmlspecialchars($user['first_name'] ?? 'Customer') . ",</p>
                        <p>Your order <strong style='color: #007a5e;'>#" . $order['order_number'] . "</strong> has been delivered on " . date('d/m/Y') . ".</p>
                        <p>Please log in to the GoGetGro App and rate your experience. Your feedback helps us improve.</p>
                        
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='http://localhost/g3/orderhistory.php?tab=to-rate' style='background-color: #007a5e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;'>Rate Order</a>
                        </div>
                        
                        <h4 style='border-bottom: 1px solid #ddd; padding-bottom: 10px; color: #555;'>ORDER DETAILS</h4>
                        <p><strong>Order ID:</strong> #" . $order['order_number'] . "<br>
                        <strong>Order Date:</strong> " . date('d/m/Y H:i:s', strtotime($order['created_at'])) . "</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                            <thead>
                                <tr>
                                    <th style='padding: 8px; border-bottom: 1px solid #ddd; text-align: left;'>Item</th>
                                    <th style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>Qty</th>
                                    <th style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                " . $itemsHtml . "
                            </tbody>
                        </table>
                        
                        <p style='text-align: right; font-size: 16px;'><strong>Total Payment: &#8369;" . number_format($order['total_amount'], 2) . "</strong></p>
                        
                        <br>
                        <p>Best Regards,<br>GoGetGro Support Team</p>
                    </div>
                    <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                        &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
                    </div>
                </div>
                ";
                
                if (send_gogetgro_email($user['email'], $emailSubject, $emailMessage)) {
                    $uStmt = $db->prepare('UPDATE orders SET delivery_email_sent = 1 WHERE id = ?');
                    $uStmt->bind_param('i', $orderId);
                    $uStmt->execute();
                }
            }
        } catch (Exception $e) {
            error_log("Error processing order receive: " . $e->getMessage());
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Order marked as received. Please rate your experience!'];
        header('Location: orderhistory.php?tab=to-rate');
        exit;
    }
    if ($action === 'rate' && $orderId !== '') {
        try {
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
            if ($rating !== null && $rating >= 1 && $rating <= 5) {
                $rStmt = $db->prepare('UPDATE orders SET rating = ? WHERE id = ?');
                $rStmt->bind_param('ii', $rating, $orderId);
                $rStmt->execute();
            }
            update_order_status($orderId, 'Completed');
            
            $order = find_order($orderId);
            if ($order && !empty($user['email']) && empty($order['rating_email_sent'])) {
                require_once __DIR__ . '/includes/email_helper.php';
                $emailSubject = "Thank you for rating order #" . $order['order_number'];
                $emailMessage = "
                <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #007a5e; padding: 20px; text-align: center; color: white;'>
                        <h2 style='margin: 0;'>GoGetGro</h2>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Hi " . htmlspecialchars($user['first_name'] ?? 'Customer') . ",</p>
                        <p>Thank you for rating your order <strong style='color: #007a5e;'>#" . $order['order_number'] . "</strong>.</p>
                        <p>You gave it a rating of <strong>" . $rating . " out of 5 stars</strong>.</p>
                        <p>Your feedback is very important to us and helps us improve our service. We hope to see you again soon.</p>
                        <br>
                        <p>Best Regards,<br>GoGetGro Support Team</p>
                    </div>
                    <div style='background-color: #f8f9fa; padding: 10px; text-align: center; font-size: 12px; color: #777;'>
                        &copy; " . date('Y') . " G3 Quad, Inc. All rights reserved.
                    </div>
                </div>
                ";
                
                if (send_gogetgro_email($user['email'], $emailSubject, $emailMessage)) {
                    $uStmt = $db->prepare('UPDATE orders SET rating_email_sent = 1 WHERE id = ?');
                    $uStmt->bind_param('i', $orderId);
                    $uStmt->execute();
                }
            }
        } catch (Exception $e) {
            error_log("Error processing order rating: " . $e->getMessage());
        }

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Thank you for your rating!'];
        header('Location: orderhistory.php?tab=completed');
        exit;
    }
}

$searchQuery = trim($_GET['search'] ?? '');
$dateFilter  = trim($_GET['date'] ?? '');

$orders = get_orders();
if ($statusTabs[$activeTab] !== null) {
    $orders = array_values(array_filter($orders, fn($o) => ($o['status'] ?? '') === $statusTabs[$activeTab]));
}

if ($searchQuery !== '') {
    $sq = strtolower($searchQuery);
    $orders = array_values(array_filter($orders, function($o) use ($sq, $db) {
        if (str_contains(strtolower($o['order_number']), $sq)) return true;
        // Search items too
        $items = get_order_items($db, $o['id']);
        foreach($items as $i) {
            if (str_contains(strtolower($i['product_name']), $sq)) return true;
        }
        return false;
    }));
}

if ($dateFilter !== '') {
    $orders = array_values(array_filter($orders, function($o) use ($dateFilter) {
        return date('Y-m-d', strtotime($o['created_at'])) === $dateFilter;
    }));
}

function oh_status_class(string $s): string {
    return match($s) {
        'To Pay'      => 'bg-warning text-dark',
        'To Ship'     => 'bg-info text-dark',
        'To Rate'     => 'bg-secondary',
        'Delivered'   => 'bg-success',
        'Completed'   => 'bg-success',
        'Cancelled'   => 'bg-danger',
        default       => 'bg-secondary'
    };
}

$pageTitle = 'Order History';
$extraCss  = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="content-wrapper">
    <div class="container my-5">
        
        <?php if ($flash): ?>
            <script>
                Swal.fire({
                    icon: '<?= $flash['type'] === 'success' ? 'success' : 'info' ?>',
                    title: '<?= $flash['type'] === 'success' ? 'Success!' : 'Notice' ?>',
                    text: '<?= htmlspecialchars($flash['msg']) ?>',
                    confirmButtonColor: '#007a5e'
                });
            </script>
        <?php endif; ?>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 class="fw-bold mb-0" style="color: #007a5e;">Order History</h1>
        </div>
        
        <form method="GET" action="orderhistory.php" class="mb-4">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">
            <div class="input-group shadow-sm rounded-pill overflow-hidden" style="border: 1px solid #d8ede7; background: #fff; padding: 2px;">
                <span class="input-group-text bg-transparent border-0 pe-2 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 shadow-none bg-transparent" placeholder="Search order number or product name..." value="<?= htmlspecialchars($searchQuery) ?>" style="font-size:0.95rem;">
                
                <div class="dropdown">
                    <button class="btn btn-link text-muted text-decoration-none dropdown-toggle h-100 px-3 border-start" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem; color: #666 !important;">
                        <i class="bi bi-calendar3 me-1"></i> <?= $dateFilter ?: 'Date' ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow-lg border-0 rounded-4" style="min-width: 250px; margin-top: 10px;">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Filter by Date</label>
                            <input type="date" name="date" class="form-control rounded-3" value="<?= htmlspecialchars($dateFilter) ?>">
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 rounded-pill fw-bold btn-sm">Apply Date</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-gogetgro px-4 fw-bold rounded-pill ms-2">Search</button>
                
                <?php if ($searchQuery !== '' || $dateFilter !== ''): ?>
                    <a href="orderhistory.php?tab=<?= htmlspecialchars($activeTab) ?>" class="btn btn-link text-danger px-2 ms-1 d-flex align-items-center text-decoration-none" title="Clear Filters">
                        <i class="bi bi-x-circle-fill" style="font-size: 1.2rem;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Status Filter Tabs -->
        <ul class="nav nav-pills mb-4 overflow-auto flex-nowrap pb-2 border-bottom">
            <?php foreach ($statusTabs as $slug => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === $slug ? 'active' : '' ?> fw-semibold" 
                       href="orderhistory.php?tab=<?= $slug ?>"
                       style="<?= $activeTab === $slug ? 'background-color:#007a5e;' : 'color:#444;' ?>">
                        <?= $label ?? 'All Orders' ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-bag-x" style="font-size: 4rem; color: #dee2e6;"></i>
                <p class="mt-3 text-muted">No orders found in this category.</p>
                <a href="index.php" class="btn btn-primary mt-2" style="background:#007a5e; border:none; border-radius:50px; padding:10px 25px;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row g-4" id="orders-container">
                <?php foreach ($orders as $index => $order): ?>
                    <div class="col-12 order-card" style="<?= $index >= 5 ? 'display: none;' : '' ?>">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Order</span>
                                    <span class="fw-bold ms-1">#<?= htmlspecialchars($order['order_number']) ?></span>
                                    <span class="text-muted small ms-3"><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></span>
                                </div>
                                <span class="badge rounded-pill <?= oh_status_class($order['status']) ?> px-3 py-2">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light p-3 rounded-3 me-3">
                                                <i class="bi <?= $order['fulfillment_type'] === 'pickup' ? 'bi-shop' : 'bi-truck' ?> text-success" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 fw-bold"><?= $order['fulfillment_type'] === 'pickup' ? 'Store Pickup' : 'Home Delivery' ?></h6>
                                                <p class="text-muted small mb-0">Total Amount: <span class="fw-bold text-dark"><?= money((float)$order['total_amount']) ?></span></p>
                                            </div>
                                        </div>
                                        
                                        <!-- Items Summary -->
                                        <?php 
                                            $items = get_order_items($db, $order['id']);
                                            $itemNames = array_map(fn($i) => htmlspecialchars($i['product_name']) . ' (x' . $i['quantity'] . ')', $items);
                                        ?>
                                        <div class="text-muted small">
                                            <i class="bi bi-box-seam me-1"></i>
                                            <?= implode(', ', array_slice($itemNames, 0, 3)) ?><?= count($itemNames) > 3 ? '...' : '' ?>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-md-end mt-4 mt-md-0">
                                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                            
                                            <?php if ($order['status'] === 'To Pay'): ?>
                                                <form method="POST" class="d-inline-block">
                                                    <input type="hidden" name="action" value="pay">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold">Pay Now</button>
                                                </form>
                                                <form method="POST" class="d-inline-block" onsubmit="return confirmCancel(event)">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold">Cancel Order</button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($order['status'] === 'To Ship'): ?>
                                                <form method="POST" onsubmit="return confirmReceive(event)">
                                                    <input type="hidden" name="action" value="receive">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">Order Received</button>
                                                </form>
                                            <?php endif; ?>



                                                                                         <?php if ($order['status'] === 'To Rate'): ?>
                                                <form method="POST" onsubmit="return confirmRate(event)">
                                                    <input type="hidden" name="action" value="rate">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold text-dark">Rate & Review</button>
                                                </form>
                                             <?php endif; ?>

                                             <?php if (in_array($order['status'], ['Completed', 'Cancelled'])): ?>

                                                <form method="POST">
                                                    <input type="hidden" name="action" value="reorder">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-4 fw-bold">Buy Again</button>
                                                </form>
                                            <?php endif; ?>

                                            <button class="btn btn-light btn-sm rounded-pill px-4 border fw-semibold" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#details-<?= $order['id'] ?>">
                                                Details
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Collapsible Details -->
                                <div class="collapse mt-4" id="details-<?= $order['id'] ?>">
                                    <div class="border-top pt-3">
                                        <h6 class="fw-bold mb-3 small text-uppercase" style="letter-spacing: 1px; color: #888;">Order Items</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <tbody class="small">
                                                    <?php foreach ($items as $item): ?>
                                                        <tr>
                                                            <td style="width: 40px;"><div class="bg-light rounded p-1 text-center"><i class="bi bi-cart"></i></div></td>
                                                            <td class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></td>
                                                            <td class="text-center text-muted">x<?= $item['quantity'] ?></td>
                                                            <td class="text-end fw-bold"><?= money((float)$item['unit_price'] * $item['quantity']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($orders) > 5): ?>
                <div class="text-center mt-4">
                    <button id="show-more-orders-btn" class="btn btn-outline-success rounded-pill px-4 fw-bold">Show More Orders</button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</main>

<style>
.nav-pills .nav-link.active {
    background-color: #007a5e !important;
}
.nav-pills .nav-link:not(.active):hover {
    background-color: #f8f9fa;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.swal2-rating-container {
    display: flex;
    flex-direction: row-reverse;
    justify-content: center;
    font-size: 2.5rem;
    padding: 10px 0;
}
.swal2-rating-container input { display: none; }
.swal2-rating-container label {
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s;
    margin: 0 2px;
}
.swal2-rating-container label:hover,
.swal2-rating-container label:hover ~ label,
.swal2-rating-container input:checked ~ label {
    color: #ffc107;
}
</style>

<script>
function confirmReceive(event) {
    event.preventDefault();
    const form = event.target;
    Swal.fire({
        title: 'Order Received?',
        text: "Confirm that you have received your order.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#007a5e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, I received it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function confirmCancel(event) {
    event.preventDefault();
    const form = event.target;
    Swal.fire({
        title: 'Cancel Order?',
        text: "Are you sure you want to cancel this order? This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function confirmRate(e) {
    if(!e.target.querySelector('input[name="rating"]')) {
        e.preventDefault();
        Swal.fire({
            title: 'Rate your Order',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label text-muted small">Select Rating (1-5)</label>
                    <select id="swal-rating" class="form-select">
                        <option value="5">⭐⭐⭐⭐⭐ (5) Excellent</option>
                        <option value="4">⭐⭐⭐⭐ (4) Good</option>
                        <option value="3">⭐⭐⭐ (3) Average</option>
                        <option value="2">⭐⭐ (2) Poor</option>
                        <option value="1">⭐ (1) Terrible</option>
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Submit Rating',
            confirmButtonColor: '#007a5e',
            preConfirm: () => {
                return document.getElementById('swal-rating').value;
            }
        }).then((result) => {
            if(result.isConfirmed) {
                let ratingInput = document.createElement('input');
                ratingInput.type = 'hidden';
                ratingInput.name = 'rating';
                ratingInput.value = result.value;
                e.target.appendChild(ratingInput);
                e.target.submit();
            }
        });
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const showMoreBtn = document.getElementById('show-more-orders-btn');
    if (showMoreBtn) {
        let currentlyShowing = 5;
        const cards = document.querySelectorAll('.order-card');
        
        showMoreBtn.addEventListener('click', function() {
            let newlyShown = 0;
            for (let i = currentlyShowing; i < cards.length; i++) {
                cards[i].style.display = 'block';
                newlyShown++;
                if (newlyShown >= 5) break; // Show 5 more at a time
            }
            currentlyShowing += newlyShown;
            
            if (currentlyShowing >= cards.length) {
                showMoreBtn.style.display = 'none'; // Hide button if all shown
            } else {
                showMoreBtn.innerText = 'Show More Orders (' + (cards.length - currentlyShowing) + ' remaining)';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
