<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['is_senior'])) {
    $_SESSION['is_senior'] = 0;
}

// Handle senior discount toggle
if (isset($_POST['toggle_senior'])) {
    if (isset($_POST['is_senior']) && $_POST['is_senior'] == 1) {
        $_SESSION['is_senior'] = 1;
    } else {
        $_SESSION['is_senior'] = 0;
    }
    header("Location: cart.php");
    exit();
}

// Compute totals
$subtotal_original = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal_original += $item['original_price'] * $item['quantity'];
}

$vat = $subtotal_original * 0.12;
$subtotal_selling = $subtotal_original + $vat;
$is_senior = isset($_SESSION['is_senior']) && $_SESSION['is_senior'] == 1;

if ($is_senior) {
    $discount = $subtotal_selling * 0.20;
    $grand_total = $subtotal_selling - $discount;
} else {
    $discount = 0;
    $grand_total = $subtotal_selling;
}

require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block sidebar">
            <div class="position-sticky">
                <h4 class="text-white text-center py-3">POS System</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php">
                            <i class="bi bi-cart"></i> Point of Sale
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">
                            <i class="bi bi-cart-check"></i> View Cart
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-text"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout-action.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Shopping Cart</h1>
                <a href="sales.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Continue Shopping
                </a>
            </div>

            <?php if(isset($_SESSION['swal']) && !empty($_SESSION['swal'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: '<?php echo $_SESSION['swal']['type']; ?>',
                            title: '<?php echo $_SESSION['swal']['title']; ?>',
                            text: '<?php echo addslashes($_SESSION['swal']['text']); ?>',
                            timer: 3000,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    });
                </script>
                <?php unset($_SESSION['swal']); ?>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if(empty($_SESSION['cart'])): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cart" style="font-size: 80px; color: #ccc;"></i>
                        <h4 class="mt-3">Your cart is empty</h4>
                        <p class="text-muted">Add some products to get started</p>
                        <a href="sales.php" class="btn btn-primary">Go to POS</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Cart Items (<?php echo count($_SESSION['cart']); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Orig Price</th>
                                                <th>Selling Price</th>
                                                <th>Quantity</th>
                                                <th>Subtotal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $counter = 1;
                                            foreach($_SESSION['cart'] as $key => $item):
                                                $subtotal = $item['selling_price'] * $item['quantity'];
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                                <td><?php echo formatCurrency($item['original_price']); ?></td>
                                                <td><?php echo formatCurrency($item['selling_price']); ?></td>
                                                <td>
                                                    <form action="../actions/update-cart.php" method="POST" class="d-flex gap-2">
                                                        <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                               min="1" class="form-control form-control-sm" style="width: 70px;" required>
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td><?php echo formatCurrency($subtotal); ?></td>
                                                <td>
                                                    <button onclick="removeItemCart(<?php echo $key; ?>, '<?php echo addslashes($item['name']); ?>')" 
                                                            class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button onclick="clearCartCart()" class="btn btn-danger">
                                    <i class="bi bi-trash3"></i> Clear Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal (Original)</span>
                                        <span><?php echo formatCurrency($subtotal_original); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted">
                                        <span>VAT (12%)</span>
                                        <span><?php echo formatCurrency($vat); ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal (with VAT)</span>
                                        <span><?php echo formatCurrency($subtotal_selling); ?></span>
                                    </div>
                                    <?php if($discount > 0): ?>
                                    <div class="d-flex justify-content-between text-danger">
                                        <span>Senior Discount (20%)</span>
                                        <span>-<?php echo formatCurrency($discount); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Grand Total</strong>
                                        <strong class="text-success"><?php echo formatCurrency($grand_total); ?></strong>
                                    </div>
                                </div>

                                <!-- SENIOR CITIZEN CHECKBOX -->
                                <div class="mb-3">
                                    <label class="fw-bold">Discount Options</label>
                                    <div class="form-check p-3" style="background: <?php echo $is_senior ? '#d4edda' : '#f8f9fa'; ?>; border-radius: 8px; border: 2px solid <?php echo $is_senior ? '#28a745' : '#dee2e6'; ?>;">
                                        <input type="checkbox" class="form-check-input" id="is_senior_cart" 
                                               name="is_senior_checkbox" value="1" <?php echo $is_senior ? 'checked' : ''; ?>
                                               onchange="toggleSeniorCart()" style="transform: scale(1.2); margin-right: 10px;">
                                        <label class="form-check-label fw-bold" for="is_senior_cart">
                                            <i class="bi bi-person"></i> Senior Citizen
                                            <span class="badge bg-info">20% discount</span>
                                            <?php if($is_senior): ?>
                                                <span class="badge bg-success">ACTIVE</span>
                                            <?php endif; ?>
                                        </label>
                                        <?php if($is_senior): ?>
                                        <br>
                                        <small class="text-success mt-1 d-block">
                                            <i class="bi bi-check-circle"></i> Discount applied! Save <?php echo formatCurrency($discount); ?>
                                        </small>
                                        <?php else: ?>
                                        <br>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="bi bi-info-circle"></i> Check this if customer is a senior citizen
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Hidden form for senior toggle -->
                                <form id="seniorToggleForm" method="POST" style="display:none;">
                                    <input type="hidden" name="toggle_senior" value="1">
                                    <input type="hidden" name="is_senior" id="senior_hidden" value="<?php echo $is_senior ? '1' : '0'; ?>">
                                </form>

                                <!-- Checkout Form - CUSTOMER NAME REMOVED -->
                                <form action="../actions/checkout.php" method="POST" id="checkoutForm">
                                    <div class="mb-3">
                                        <label>Payment Amount</label>
                                        <input type="number" name="payment_amount" id="paymentAmount" class="form-control" 
                                               step="0.01" min="<?php echo $grand_total; ?>" required>
                                        <small class="text-muted">Minimum: <?php echo formatCurrency($grand_total); ?></small>
                                    </div>
                                    <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
                                    
                                    <button type="button" onclick="confirmCheckout()" class="btn btn-success w-100" style="padding: 12px; font-size: 16px;">
                                        <i class="bi bi-check-circle"></i> Complete Sale
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// ============================================
// SENIOR CITIZEN TOGGLE
// ============================================
function toggleSeniorCart() {
    let checkbox = document.getElementById('is_senior_cart');
    let hiddenInput = document.getElementById('senior_hidden');
    hiddenInput.value = checkbox.checked ? '1' : '0';
    document.getElementById('seniorToggleForm').submit();
}

// ============================================
// REMOVE ITEM WITH SWEETALERT
// ============================================
function removeItemCart(key, name) {
    Swal.fire({
        title: 'Remove Item?',
        text: 'Are you sure you want to remove "' + name + '" from cart?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, remove!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/remove-from-cart.php?key=' + key;
        }
    });
}

// ============================================
// CLEAR CART WITH SWEETALERT
// ============================================
function clearCartCart() {
    Swal.fire({
        title: 'Clear Cart?',
        text: 'Are you sure you want to remove all items from cart?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, clear all!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/clear-cart.php';
        }
    });
}

// ============================================
// CHECKOUT CONFIRMATION
// ============================================
function confirmCheckout() {
    let payment = document.getElementById('paymentAmount');
    let grandTotal = <?php echo isset($grand_total) ? $grand_total : 0; ?>;
    
    if (!payment.value || payment.value <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Payment Required!',
            text: 'Please enter the payment amount.',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }
    
    if (parseFloat(payment.value) < grandTotal) {
        Swal.fire({
            icon: 'error',
            title: 'Insufficient Payment!',
            text: 'Payment must be at least ' + grandTotal.toFixed(2),
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }
    
    Swal.fire({
        title: 'Complete Sale?',
        text: 'Are you sure you want to complete this transaction?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, complete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('checkoutForm').submit();
        }
    });
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>