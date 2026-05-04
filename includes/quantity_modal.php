<!-- Quantity Selection Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quantityModalLabel">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_to_cart.php" method="POST">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="modalProductImage" src="" alt="" style="max-height: 150px; object-fit: contain;">
                        <h4 id="modalProductName" class="mt-2"></h4>
                        <p id="modalProductPrice" class="text-muted"></p>
                    </div>
                    <div class="mb-3">
                        <label for="quantityInput" class="form-label">Quantity</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="form-control text-center" id="quantityInput" name="quantity" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                        </div>
                        <p id="modalStockQty" class="text-muted small mt-2 mb-0"></p>
                    </div>
                    <input type="hidden" name="product_id" id="modalProductId">
                    <input type="hidden" name="redirect" id="modalRedirect">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background-color: #007a5e; border-color: #007a5e;">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginRequiredModalLabel">Login Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-person-lock" style="font-size: 3rem; color: #007a5e;"></i>
                <p class="mt-3">You need to be logged in to add items to your cart and save them to your account.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Later</button>
                <a href="login.php" class="btn btn-primary" style="background-color: #007a5e; border-color: #007a5e;">Login Now</a>
            </div>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-box-arrow-right" style="font-size: 3rem; color: #dc3545;"></i>
                <p class="mt-3">Are you sure you want to log out of your account?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="logout.php" id="logoutConfirmBtn" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
const IS_LOGGED_IN = <?= is_logged_in() ? 'true' : 'false' ?>;

function openQuantityModal(productId, productName, productPrice, productImage, redirect, stockQty) {
    if (!IS_LOGGED_IN) {
        var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        loginModal.show();
        return;
    }

    const qtyInput = document.getElementById('quantityInput');
    const addToCartBtn = document.querySelector('#quantityModal button[type="submit"]');
    const stockDisplay = document.getElementById('modalStockQty');
    const parsedStock = parseInt(stockQty) || 0;

    document.getElementById('modalProductId').value = productId;
    document.getElementById('modalProductName').innerText = productName;
    document.getElementById('modalProductPrice').innerText = productPrice;
    document.getElementById('modalProductImage').src = productImage;
    document.getElementById('modalRedirect').value = redirect;
    
    qtyInput.value = parsedStock > 0 ? 1 : 0;
    qtyInput.max = parsedStock;
    
    if (stockDisplay) {
        stockDisplay.innerText = 'Stock: ' + parsedStock;
        stockDisplay.classList.toggle('text-danger', parsedStock <= 5);
        stockDisplay.classList.toggle('fw-bold', parsedStock <= 5);
    }

    if (addToCartBtn) {
        if (parsedStock <= 0) {
            addToCartBtn.disabled = true;
            addToCartBtn.innerText = 'Out of Stock';
            qtyInput.disabled = true;
        } else {
            addToCartBtn.disabled = false;
            addToCartBtn.innerText = 'Add to Cart';
            qtyInput.disabled = false;
        }
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('quantityModal'));
    myModal.show();
}

function changeQuantity(delta) {
    const input = document.getElementById('quantityInput');
    if (input.disabled) return;
    let value = parseInt(input.value) || 0;
    const max = parseInt(input.max) || 0;
    
    value += delta;
    if (value < 1 && max > 0) value = 1;
    if (value > max) value = max;
    if (max <= 0) value = 0;
    
    input.value = value;
}

function confirmLogout(is_admin = false) {
    if (is_admin) {
        document.getElementById('logoutConfirmBtn').href = 'admin_logout.php';
    } else {
        document.getElementById('logoutConfirmBtn').href = 'logout.php';
    }
    var logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
    logoutModal.show();
}
</script>

