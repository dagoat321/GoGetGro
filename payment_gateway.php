<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user();

$orderId   = trim($_GET['orderId'] ?? $_POST['order_id'] ?? '');
$gateway   = trim($_GET['gateway'] ?? $_POST['gateway'] ?? get_saved_payment_gateway());
$allGw     = payment_gateways();
$gwName    = $allGw[$gateway] ?? ucfirst($gateway);

$order = ($orderId !== '') ? find_order($orderId) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $orderId !== '' && isset($_POST['pay_with_paymongo'])) {
    // Load secret key from external config
    $secrets = file_exists(__DIR__ . '/config/secrets.php') ? require __DIR__ . '/config/secrets.php' : [];
    $apiKey = $secrets['PAYMONGO_SECRET_KEY'] ?? '';

    
    $amountInCents = intval(round((float)$order['total_amount'] * 100));
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    // Assuming root path or detect current folder
    $baseUri = $protocol . $host . rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    
    $payload = [
        'data' => [
            'attributes' => [
                'billing' => [
                    'name' => $user['full_name'] ?? 'Customer',
                    'email' => $user['email'] ?? 'customer@gogetgro.com'
                ],
                'send_email_receipt' => true,
                'show_description' => true,
                'show_line_items' => true,
                'description' => 'GoGetGro Order #' . $order['order_number'],
                'line_items' => [
                    [
                        'currency' => 'PHP',
                        'amount' => $amountInCents,
                        'name' => 'GoGetGro Order #' . $order['order_number'],
                        'quantity' => 1
                    ]
                ],
                'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay', 'dob'],
                'success_url' => $baseUri . '/payment_success.php?order_id=' . $order['id'],
                'cancel_url' => $baseUri . '/payment_gateway.php?orderId=' . $order['id'] . '&gateway=' . urlencode($gateway)
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($apiKey . ':')
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Payment Gateway Error: ' . $err];
    } else {
        $res = json_decode($response, true);
        if (isset($res['data']['attributes']['checkout_url'])) {
            header('Location: ' . $res['data']['attributes']['checkout_url']);
            exit;
        } else {
            $errorMsg = $res['errors'][0]['detail'] ?? 'Unable to generate checkout session.';
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'PayMongo Error: ' . $errorMsg];
        }
    }
}

$isWallet = in_array($gateway, ['gcash', 'maya'], true);

$pageTitle = 'Payment Gateway';
$extraCss  = ['stylesheet1.css'];
require_once __DIR__ . '/includes/header.php';
?>

<main class="content-wrapper">
    <div style="max-width:560px;margin:48px auto;padding:0 16px;">
        <div style="background:#fff;border:1px solid #e1ebe5;border-radius:22px;box-shadow:0 14px 34px rgba(0,0,0,0.08);overflow:hidden;">
            
            <!-- Gateway Header -->
            <div style="background:#f8faf9;padding:32px;border-bottom:1px solid #eee;text-align:center;">
                <div style="width:70px;height:70px;background:#fff;border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 4px 12px rgba(0,0,0,0.05);font-size:1.8rem;color:#007a5e;">
                    <i class="bi <?= $isWallet ? 'bi-wallet2' : 'bi-bank' ?>"></i>
                </div>
                <h4 style="margin:0;font-weight:800;color:#1a1a1a;"><?= htmlspecialchars($gwName) ?></h4>
                <p style="margin:4px 0 0;color:#666;font-size:0.9rem;">Secure Checkout</p>
            </div>

            <div style="padding:32px;">
                <?php if (!$order): ?>
                    <div class="alert alert-warning rounded-4 border-0">
                        Order not found or already processed.
                    </div>
                    <a href="orderhistory.php" class="btn btn-light w-100 rounded-pill py-3 fw-bold border mt-2">Back to Orders</a>
                <?php else: ?>
                    <div style="background:#f1f7f4;border-radius:16px;padding:24px;margin-bottom:28px;">
                        <div style="display:flex;justify-content:between;margin-bottom:8px;">
                            <span style="color:#666;font-size:0.85rem;">Pay to GoGetGro</span>
                            <span style="margin-left:auto;color:#1a1a1a;font-weight:700;font-size:0.85rem;">#<?= htmlspecialchars($order['order_number']) ?></span>
                        </div>
                        <div style="font-size:2rem;font-weight:900;color:#007a5e;"><?= money((float)$order['total_amount']) ?></div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderId) ?>">
                        <input type="hidden" name="gateway" value="<?= htmlspecialchars($gateway) ?>">
                        <input type="hidden" name="pay_with_paymongo" value="1">
                        
                        <?php if (isset($_SESSION['flash'])): ?>
                            <div class="alert alert-<?= $_SESSION['flash']['type'] ?> rounded-4 border-0">
                                <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
                            </div>
                            <?php unset($_SESSION['flash']); ?>
                        <?php endif; ?>

                        <div style="display:flex;align-items:center;gap:12px;background:#f8faf9;padding:16px;border-radius:12px;margin-bottom:28px;border:1px solid #e1ebe5;">
                            <i class="bi bi-shield-check" style="font-size:1.5rem;color:#007a5e;"></i>
                            <span style="font-size:0.9rem;color:#444;line-height:1.4;">
                                You will be securely redirected to PayMongo to complete your payment. PayMongo supports GCash, Maya, GrabPay, Credit/Debit Cards, and Online Banking.
                            </span>
                        </div>

                        <button type="submit" style="width:100%;background:#007a5e;color:#fff;border:none;border-radius:50px;padding:16px;font-weight:800;font-size:1rem;box-shadow:0 8px 20px rgba(0,122,94,0.25);transition:all 0.2s;">
                            PROCEED TO PAYMONGO
                        </button>
                        
                        <a href="orderhistory.php" style="display:block;text-align:center;margin-top:20px;color:#666;text-decoration:none;font-size:0.9rem;font-weight:600;">
                            Cancel and go back
                        </a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="text-align:center;margin-top:32px;color:#999;font-size:0.8rem;">
            <i class="bi bi-lock-fill me-1"></i> Secured by GoGetGro Payment Systems
        </div>
    </div>
</main>

<style>
    button:hover { background: #00664f !important; transform: translateY(-1px); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

