<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$pageTitle  = 'My Account';
$extraCss   = ['stylesheet1.css', 'stylesheet5.css'];
require_once __DIR__ . '/includes/header.php';

$addresses  = get_user_addresses($user['id']);
$gateways   = get_user_payment_gateways($user['id']);
$orders     = get_user_orders($user['id']);

$toPay      = array_filter($orders, fn($o) => $o['status'] === 'To Pay');
$toShip     = array_filter($orders, fn($o) => $o['status'] === 'To Ship');
$toRate     = array_filter($orders, fn($o) => $o['status'] === 'To Rate');
$history    = array_filter($orders, fn($o) => in_array($o['status'], ['Delivered', 'Cancelled', 'Completed']));

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$allGateways = payment_gateways();
$savedGwKeys = array_column($gateways, 'gateway_key');

// Refresh user data from DB to ensure fields like created_at are present and data is up to date
$userStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->bind_param('i', $user['id']);
$userStmt->execute();
$dbUser = $userStmt->get_result()->fetch_assoc();
if ($dbUser) {
    $user = array_merge($user, $dbUser);
    $_SESSION['user'] = $user;
}

// Get newsletter subscription status from DB
$db->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS newsletter_subscribed TINYINT(1) NOT NULL DEFAULT 1");
$isSubscribed = (bool)($user['newsletter_subscribed'] ?? 1);
?>
    <main class="content-wrapper">

        <?php if ($flash): ?>
            <div id="profileFlash" class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mt-3 rounded-3" role="alert">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <script>setTimeout(()=>{ const a=document.getElementById('profileFlash'); if(a){ const b=bootstrap.Alert.getOrCreateInstance(a); b.close(); }}, 5000);</script>
        <?php endif; ?>

        <nav aria-label="breadcrumb" class="my-3">
            <ol class="breadcrumb m-0" style="font-size: 0.9rem;">
                <li class="breadcrumb-item"><a href="index.php" style="color: var(--primary-green); text-decoration: none;">Home</a></li>
                <li class="px-2" style="color: var(--primary-green);"><i class="bi bi-chevron-right" style="font-size: 0.7rem;"></i></li>
                <li class="breadcrumb-item active" aria-current="page">Account</li>
            </ol>
        </nav>

        <section class="profile-header p-4 mb-0 d-flex align-items-center" style="min-height:160px;">
            <div>
                <p class="mb-1 text-white opacity-75" style="font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;">My Account</p>
                <h2 class="fw-bold m-0" style="font-size:2rem;">Welcome Back, <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>!</h2>
                <p class="mb-0 mt-1 text-white opacity-75" style="font-size:0.9rem;">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
            </div>
        </section>

        <div class="row g-4 overlap-row">
            <!-- LEFT: Account Info + Newsletter -->
            <div class="col-lg-6 d-flex flex-column">
                <div class="profile-card p-4 mb-4">
                    <h5 class="section-title mb-4">ACCOUNT INFORMATION</h5>
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">Contact Information</h6>
                        <p class="mb-1 small">NAME: <strong><?= htmlspecialchars($user['full_name'] ?? '—') ?></strong></p>
                        <p class="mb-1 small">E-MAIL: <strong><?= htmlspecialchars($user['email']) ?></strong></p>
                        <p class="mb-1 small">USERNAME: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
                        <p class="mb-1 small">PASSWORD: ••••••••</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-auto pt-3 border-top">
                        <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editInfoModal">
                            <i class="bi bi-pencil-square me-1"></i> Edit Info
                        </button>
                        <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#passwordModal">
                            <i class="bi bi-shield-lock me-1"></i> Change Password
                        </button>
                    </div>
                </div>

                <div class="profile-card p-4 flex-grow-1">
                    <h5 class="section-title mb-3">NEWSLETTERS</h5>
                    <?php if ($isSubscribed): ?>
                        <div style="background:#f0faf6;border-radius:10px;padding:14px 16px;margin-bottom:14px;border:1px solid #c8e8de;">
                            <div style="font-size:.78rem;color:#007a5e;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;"><i class="bi bi-newspaper me-1"></i>GoGetGro Weekly Deals</div>
                            <div style="font-size:.88rem;font-weight:600;color:#1a1a1a;margin-bottom:4px;">Up to 30% off Fresh Vegetables this week!</div>
                            <div style="font-size:.82rem;color:#555;">Exclusive for subscribers &mdash; sent to <strong><?= htmlspecialchars($user['email']) ?></strong></div>
                        </div>
                        <p class="small mb-0 text-muted">Subscribed — receiving exclusive deals and updates.</p>
                    <?php else: ?>
                        <p class="small mb-0 text-muted">You are not subscribed to our newsletter. Subscribe to receive exclusive deals!</p>
                    <?php endif; ?>
                    <div class="mt-auto pt-3 border-top text-end">
                        <form method="POST" action="toggle-newsletter.php" style="display:inline;">
                            <?php if ($isSubscribed): ?>
                                <button type="submit" class="btn btn-light btn-sm border text-danger rounded-pill px-3">
                                    <i class="bi bi-bell-slash me-1"></i> Unsubscribe
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-light btn-sm border text-success rounded-pill px-3">
                                    <i class="bi bi-bell me-1"></i> Subscribe
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Address Book + Payment Gateways -->
            <div class="col-lg-6 d-flex flex-column">
                <div class="profile-card p-4 mb-4">
                    <h5 class="section-title mb-4">ADDRESS BOOK</h5>
                    <?php if (empty($addresses)): ?>
                        <p class="text-muted small">No addresses saved yet.</p>
                    <?php else: ?>
                        <?php foreach ($addresses as $i => $addr): ?>
                            <?php if ($i > 0) echo '<hr>'; ?>
                            <p class="mb-0 text-muted" style="font-size:0.7rem;">* <?= htmlspecialchars($addr['label']) ?></p>
                            <p class="small mb-3"><?= htmlspecialchars($addr['address_line']) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-2 mt-auto pt-3 border-top">
                        <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                            <i class="bi bi-plus-circle me-1"></i> Add
                        </button>
                        <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editAddressModal" <?= empty($addresses) ? 'disabled' : '' ?>>
                            <i class="bi bi-pencil me-1"></i> Edit
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#deleteAddressModal" <?= empty($addresses) ? 'disabled' : '' ?>>
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>

                <div class="profile-card p-4 flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="section-title m-0">PAYMENT GATEWAYS</h5>
                        <div class="d-flex gap-2">
                            <i class="bi bi-credit-card fs-5 text-muted"></i>
                            <i class="bi bi-wallet2 fs-5 text-muted"></i>
                            <i class="bi bi-phone fs-5 text-muted"></i>
                        </div>
                    </div>
                    
                    <div class="p-3 mb-4 rounded-3" style="background: linear-gradient(135deg, #f8fbf9 0%, #eef6f3 100%); border: 1px solid #d0e8de;">
                        <div class="d-flex align-items-start">
                            <div class="bg-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm me-3" style="width: 36px; height: 36px; flex-shrink: 0;">
                                <i class="bi bi-shield-check text-success fs-6"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: #007a5e; font-size: 0.9rem;">Secure Checkout</h6>
                                <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">Your preferred gateway will be used for faster and secure 1-click checkout on your future orders.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 mb-4">
                        <p class="small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px; color: #555; font-size: 0.75rem;">Supported Methods</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-wallet2" style="color: #007bff;"></i> GCash</span>
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-wallet2" style="color: #28a745;"></i> Maya</span>
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-bank" style="color: #17a2b8;"></i> Online Banking</span>
                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-credit-card" style="color: #6610f2;"></i> Credit/Debit</span>
                        </div>
                    </div>

                    <form method="POST" action="save-gateway.php" class="mt-auto">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase" style="letter-spacing: 0.5px; color: #555;">Primary Gateway</label>
                            <select name="gateways[]" class="form-select rounded-pill" id="profileGatewaySelect" style="padding-top: 10px; padding-bottom: 10px;">
                                <?php foreach ($allGateways as $key => $name): ?>
                                    <option value="<?= $key ?>" <?= in_array($key, $savedGwKeys) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 rounded-pill py-2 fw-bold" style="letter-spacing: 0.5px;">
                            <i class="bi bi-check2-circle me-1"></i> Save Payment Method
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Orders Bar -->
        <div class="overlap-row mt-4 mb-5">
            <div class="profile-card p-4">
                <h5 class="section-title mb-4">RECENT ORDERS</h5>
                <div class="row text-center order-status-bar g-0 py-2">
                    <a class="col text-decoration-none text-dark" href="orderhistory.php?tab=to-pay" style="cursor:pointer;">
                        <div class="order-tab-icon">
                            <i class="bi bi-wallet2 fs-2 d-block mb-1"></i>
                            <?php if (count($toPay)): ?><span class="badge-count"><?= count($toPay) ?></span><?php endif; ?>
                        </div>
                        <small class="d-block">To Pay</small>
                    </a>
                    <a class="col border-start text-decoration-none text-dark" href="orderhistory.php?tab=to-ship" style="cursor:pointer;">
                        <div class="order-tab-icon">
                            <i class="bi bi-box-seam fs-2 d-block mb-1"></i>
                            <?php if (count($toShip)): ?><span class="badge-count"><?= count($toShip) ?></span><?php endif; ?>
                        </div>
                        <small class="d-block">To Ship</small>
                    </a>

                    <a class="col border-start text-decoration-none text-dark" href="orderhistory.php?tab=to-rate" style="cursor:pointer;">
                        <div class="order-tab-icon">
                            <i class="bi bi-star fs-2 d-block mb-1"></i>
                            <?php if (count($toRate)): ?><span class="badge-count"><?= count($toRate) ?></span><?php endif; ?>
                        </div>
                        <small class="d-block">To Rate</small>
                    </a>
                    <div class="col border-start" style="cursor:pointer;">
                        <a href="orderhistory.php" style="text-decoration:none;color:inherit;">
                            <i class="bi bi-journal-text fs-2 d-block mb-1"></i>
                            <small>Order History</small>
                        </a>
                    </div>
                    <div class="col border-start" style="cursor:pointer;">
                        <a href="order-again.php" style="text-decoration:none;color:inherit;">
                            <i class="bi bi-arrow-repeat fs-2 d-block mb-1"></i>
                            <small>Order Again</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- ─────────────── MODALS ─────────────── -->


    <!-- (Modals removed and linked to orderhistory.php instead) -->

    <!-- Edit Info Modal -->
    <div class="modal fade" id="editInfoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--primary-green);">
                    <h5 class="modal-title text-white fw-bold">Edit Profile Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="update-profile.php">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control rounded-pill" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 py-2 rounded-pill">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--primary-green);">
                    <h5 class="modal-title text-white fw-bold">Change Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="change-password.php">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control rounded-pill" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 py-2 rounded-pill">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Address Modal -->
    <div class="modal fade" id="addAddressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--primary-green);">
                    <h5 class="modal-title text-white fw-bold">Add New Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="save-address.php">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Label (e.g. Home, Office)</label>
                            <input type="text" name="label" class="form-control rounded-pill" placeholder="Home" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Address</label>
                            <textarea name="address_line" class="form-control" rows="3" placeholder="Street, Barangay, City, Province, Zip..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 py-2 rounded-pill">Add Address</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Address Modal -->
    <div class="modal fade" id="editAddressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--primary-green);">
                    <h5 class="modal-title text-white fw-bold">Edit Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="save-address.php">
                        <input type="hidden" name="action" value="edit">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Address</label>
                            <select name="address_id" class="form-select rounded-pill" id="editAddrSelect" onchange="loadEditAddress()">
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?= (int)$addr['id'] ?>"
                                        data-label="<?= htmlspecialchars($addr['label']) ?>"
                                        data-line="<?= htmlspecialchars($addr['address_line']) ?>">
                                        <?= htmlspecialchars($addr['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Label</label>
                            <input type="text" name="label" id="editAddrLabel" class="form-control rounded-pill" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Address</label>
                            <textarea name="address_line" id="editAddrLine" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-gogetgro w-100 py-2 rounded-pill">Update Address</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Address Modal -->
    <div class="modal fade" id="deleteAddressModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--primary-green);">
                    <h5 class="modal-title text-white fw-bold">Delete Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="save-address.php">
                        <input type="hidden" name="action" value="delete">
                        <p class="small mb-3">Choose the address to remove:</p>
                        <select name="address_id" class="form-select rounded-pill mb-3">
                            <?php foreach ($addresses as $addr): ?>
                                <option value="<?= (int)$addr['id'] ?>"><?= htmlspecialchars($addr['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary w-100 py-2 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger w-100 py-2 rounded-pill">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
    .gw-select-wrapper { position: relative; }
    .gw-select {
        appearance: none;
        background-color: #f8fbf9;
        border: 2px solid #d0e8de;
        color: #007a5e;
        font-weight: 600;
        padding: 10px 40px 10px 16px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .gw-select:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(0,122,94,0.12); outline: none; }
    .gw-select-wrapper::after {
        content: '\276F';
        font-size: 0.7rem; color: var(--primary-green);
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%) rotate(90deg);
        pointer-events: none;
    }
    .order-tab-icon { position: relative; display: inline-block; }
    .badge-count {
        position: absolute; top: -4px; right: -10px;
        background: #dc3545; color: #fff; font-size: 0.65rem;
        min-width: 18px; height: 18px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .modal-order-card { border: 1px solid #e5ebe7; border-radius: 14px; padding: 16px; margin-bottom: 14px; }
    .order-id-sm { font-weight: 700; color: var(--primary-green); margin-bottom: 4px; }
    .order-meta { font-size: 0.8rem; color: #60756a; margin-bottom: 10px; }
    .order-item-sm { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 3px 0; }
    .order-total-sm { display: flex; justify-content: space-between; font-weight: 700; border-top: 1px solid #eee; padding-top: 8px; margin-top: 8px; }
    /* Fix profile header padding for overlap */
    .profile-header { padding: 32px 40px 120px 40px !important; }
    @media (max-width: 768px) { .profile-header { padding: 20px 16px 100px 16px !important; } }
    </style>

    <script>
        function loadEditAddress() {
            const sel = document.getElementById('editAddrSelect');
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            document.getElementById('editAddrLabel').value = opt.dataset.label || '';
            document.getElementById('editAddrLine').value = opt.dataset.line || '';
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadEditAddress();
        });
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
