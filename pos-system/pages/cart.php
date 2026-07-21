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

if (!isset($_SESSION['senior_id'])) {
    $_SESSION['senior_id'] = '';
}

// Handle senior discount toggle
if (isset($_POST['toggle_senior'])) {
    if (isset($_POST['is_senior']) && $_POST['is_senior'] == 1) {
        $_SESSION['is_senior'] = 1;
        $_SESSION['senior_id'] = $_POST['senior_id'] ?? '';
    } else {
        $_SESSION['is_senior'] = 0;
        $_SESSION['senior_id'] = '';
    }
    header("Location: cart.php");
    exit();
}

// ============================================
// COMPUTE TOTALS WITH SENIOR DISCOUNT
// ============================================
$subtotal_original = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal_original += $item['original_price'] * $item['quantity'];
}

$vat = $subtotal_original * 0.12;
$subtotal_selling = $subtotal_original + $vat;

// Check if senior is active
$is_senior = isset($_SESSION['is_senior']) && $_SESSION['is_senior'] == 1;

// Compute discount (20% of subtotal with VAT)
if ($is_senior) {
    $discount = $subtotal_selling * 0.20;
    $grand_total = $subtotal_selling - $discount;
    $discount_display = '- ' . formatCurrency($discount);
    $discount_class = 'text-danger';
} else {
    $discount = 0;
    $grand_total = $subtotal_selling;
    $discount_display = '₱0.00';
    $discount_class = 'text-muted';
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sales.php">
                            <i class="bi bi-cart"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">
                            <i class="bi bi-cart-check"></i> Cart
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                        </a>
                    </li>
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
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
                        <a class="nav-link" href="../actions/logout-action.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                <div class="mt-4 p-3 text-white" style="border-top: 1px solid #34495e;">
                    <small>
                        <i class="bi bi-person"></i> <?php echo $_SESSION['full_name']; ?>
                        <br>
                        <span class="badge bg-<?php 
                            echo $_SESSION['role'] == 'admin' ? 'danger' : 
                                ($_SESSION['role'] == 'co-admin' ? 'warning' : 'info'); 
                        ?>">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                    </small>
                </div>
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
                                                <th>Available Stock</th>
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
                                                
                                                $stock_stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                                                $stock_stmt->execute([$item['id']]);
                                                $stock_data = $stock_stmt->fetch();
                                                $current_stock = $stock_data['stock'] ?? 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                                                <td><?php echo formatCurrency($item['original_price']); ?></td>
                                                <td><?php echo formatCurrency($item['selling_price']); ?></td>
                                                <td>
                                                    <?php if($current_stock <= 0): ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php elseif($current_stock <= 5): ?>
                                                        <span class="badge bg-warning"><?php echo $current_stock; ?> left</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?php echo $current_stock; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form action="../actions/update-cart.php" method="POST" class="d-flex gap-2">
                                                        <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                               min="1" max="<?php echo $current_stock; ?>" 
                                                               class="form-control form-control-sm" style="width: 70px;" required>
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                    <small class="text-muted">Max: <?php echo $current_stock; ?></small>
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
                                <!-- TOTALS DISPLAY -->
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
                                    
                                    <!-- SENIOR DISCOUNT DISPLAY -->
                                    <div class="d-flex justify-content-between <?php echo $discount_class; ?>" style="border-top: 1px dashed #ddd; padding-top: 5px; margin-top: 5px;">
                                        <span>Senior Discount (20%)</span>
                                        <span><?php echo $discount_display; ?></span>
                                    </div>
                                    
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong style="font-size: 16px;">Grand Total</strong>
                                        <strong class="text-success" style="font-size: 20px;"><?php echo formatCurrency($grand_total); ?></strong>
                                    </div>
                                </div>

                                <!-- SENIOR CITIZEN SECTION -->
                                <div class="mb-3">
                                    <div class="card" style="background: <?php echo $is_senior ? '#d4edda' : '#f8f9fa'; ?>; border: 2px solid <?php echo $is_senior ? '#28a745' : '#dee2e6'; ?>; border-radius: 8px;">
                                        <div class="card-body">
                                            <div class="form-check">
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
                                            </div>
                                            
                                            <!-- SENIOR ID FIELD -->
                                            <div id="seniorIdFieldCart" style="<?php echo $is_senior ? 'display: block;' : 'display: none;'; ?> margin-top: 10px;">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-card-id"></i></span>
                                                    <input type="text" name="senior_id" id="senior_id_cart" class="form-control" 
                                                           placeholder="Enter Senior Citizen ID (e.g., 2026-123456)" 
                                                           value="<?php echo $_SESSION['senior_id'] ?? ''; ?>"
                                                           <?php echo $is_senior ? 'required' : ''; ?>
                                                           maxlength="11">
                                                </div>
                                                <small class="text-muted">
                                                    <i class="bi bi-info-circle"></i> Format: YYYY-XXXXXX (e.g., 2026-123456)
                                                </small>
                                            </div>
                                            
                                            <?php if($is_senior): ?>
                                            <div class="mt-2">
                                                <small class="text-success">
                                                    <i class="bi bi-check-circle"></i> You saved <?php echo formatCurrency($discount); ?>!
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hidden form for senior toggle -->
                                <form id="seniorToggleForm" method="POST" style="display:none;">
                                    <input type="hidden" name="toggle_senior" value="1">
                                    <input type="hidden" name="is_senior" id="senior_hidden" value="<?php echo $is_senior ? '1' : '0'; ?>">
                                    <input type="hidden" name="senior_id" id="senior_id_hidden" value="<?php echo $_SESSION['senior_id'] ?? ''; ?>">
                                </form>

                                <!-- Checkout Form -->
                                <form action="../actions/checkout.php" method="POST" id="checkoutForm">
                                    <div class="mb-3">
                                        <label>Payment Amount</label>
                                        <input type="number" name="payment_amount" id="paymentAmount" class="form-control" 
                                               step="0.01" min="<?php echo $grand_total; ?>" required>
                                        <small class="text-muted">Minimum: <?php echo formatCurrency($grand_total); ?></small>
                                    </div>
                                    <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
                                    <input type="hidden" name="senior_id" id="checkout_senior_id" value="<?php echo $_SESSION['senior_id'] ?? ''; ?>">
                                    
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
    let idField = document.getElementById('seniorIdFieldCart');
    let idInput = document.getElementById('senior_id_cart');
    let idHidden = document.getElementById('senior_id_hidden');
    
    hiddenInput.value = checkbox.checked ? '1' : '0';
    
    if (checkbox.checked) {
        idField.style.display = 'block';
        idInput.required = true;
        idInput.focus();
    } else {
        idField.style.display = 'none';
        idInput.required = false;
        idInput.value = '';
        idHidden.value = '';
    }
    
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
    let checkbox = document.getElementById('is_senior_cart');
    let idInput = document.getElementById('senior_id_cart');
    
    // Validate senior ID if checked
    if (checkbox.checked) {
        let seniorId = idInput.value.trim();
        
        if (seniorId === '') {
            Swal.fire({
                icon: 'error',
                title: 'Senior ID Required!',
                text: 'Please enter your Senior Citizen ID number.',
                timer: 3000,
                showConfirmButton: false
            });
            idInput.focus();
            return;
        }
        
        let pattern = /^[0-9]{4}-[0-9]{6}$/;
        if (!pattern.test(seniorId)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Senior ID Format!',
                text: 'Please use format: YYYY-XXXXXX (e.g., 2026-123456)',
                timer: 3000,
                showConfirmButton: false
            });
            idInput.focus();
            return;
        }
        
        document.getElementById('checkout_senior_id').value = seniorId;
    }
    
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
</script>

<?php require_once '../includes/footer.php'; ?>