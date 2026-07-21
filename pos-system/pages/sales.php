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
    header("Location: sales.php");
    exit();
}

$products = $pdo->query("SELECT id, name, original_price, selling_price, stock FROM products WHERE stock > 0 ORDER BY name");

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
                        <a class="nav-link active" href="sales.php">
                            <i class="bi bi-cart"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
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
                <h1>Point of Sale</h1>
                <a href="cart.php" class="btn btn-primary">
                    <i class="bi bi-cart"></i> View Cart 
                    <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
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

            <?php if(isset($_SESSION['last_invoice']) && isset($_SESSION['last_sale_id'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><i class="bi bi-check-circle"></i> Sale Completed!</strong>
                            <br>
                            Invoice: <strong><?php echo $_SESSION['last_invoice']; ?></strong>
                        </div>
                        <div>
                            <a href="view-receipt.php?id=<?php echo $_SESSION['last_sale_id']; ?>" 
                               target="_blank" 
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> View Receipt
                            </a>
                            <a href="view-receipt.php?id=<?php echo $_SESSION['last_sale_id']; ?>&print=1" 
                               target="_blank" 
                               class="btn btn-sm btn-success">
                                <i class="bi bi-printer"></i> Print Receipt
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                </div>
                <?php unset($_SESSION['last_invoice']); unset($_SESSION['last_sale_id']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h5>Add Items to Cart</h5>
                        </div>
                        <div class="card-body">
                            <form action="../actions/add-to-cart.php" method="POST" class="row g-3" id="addToCartForm">
                                <div class="col-md-6">
                                    <label>Product</label>
                                    <select name="product_id" id="productSelect" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php while($p = $products->fetch()): ?>
                                        <option value="<?php echo $p['id']; ?>" data-stock="<?php echo $p['stock']; ?>">
                                            <?php echo $p['name']; ?> - ₱<?php echo number_format($p['selling_price'], 2); ?> (Stock: <?php echo $p['stock']; ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" id="quantityInput" class="form-control" value="1" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5>Current Cart</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($_SESSION['cart'])): ?>
                                <p class="text-muted text-center">Cart is empty</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Stock</th>
                                                <th>Orig Price</th>
                                                <th>Selling Price</th>
                                                <th>Subtotal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $subtotal_selling_cart = 0;
                                            foreach($_SESSION['cart'] as $key => $item):
                                                $subtotal = $item['selling_price'] * $item['quantity'];
                                                $subtotal_selling_cart += $subtotal;
                                                
                                                $stock_stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                                                $stock_stmt->execute([$item['id']]);
                                                $stock_data = $stock_stmt->fetch();
                                                $current_stock = $stock_data['stock'] ?? 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $item['name']; ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>
                                                    <?php if($current_stock <= 0): ?>
                                                        <span class="badge bg-danger">0</span>
                                                    <?php elseif($current_stock <= 5): ?>
                                                        <span class="badge bg-warning"><?php echo $current_stock; ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?php echo $current_stock; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatCurrency($item['original_price']); ?></td>
                                                <td><?php echo formatCurrency($item['selling_price']); ?></td>
                                                <td><?php echo formatCurrency($subtotal); ?></td>
                                                <td>
                                                    <button onclick="removeItem(<?php echo $key; ?>, '<?php echo addslashes($item['name']); ?>')" 
                                                            class="btn btn-sm btn-danger">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-end">Total:</th>
                                                <th><?php echo formatCurrency($subtotal_selling_cart); ?></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <button onclick="clearCart()" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Clear Cart
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5>Checkout</h5>
                        </div>
                        <div class="card-body">
                            <?php if(!empty($_SESSION['cart'])): ?>
                                <?php
                                // ============================================
                                // COMPUTE TOTALS WITH SENIOR DISCOUNT
                                // ============================================
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
                                    $discount_display = '- ' . formatCurrency($discount);
                                    $discount_class = 'text-danger';
                                } else {
                                    $discount = 0;
                                    $grand_total = $subtotal_selling;
                                    $discount_display = '₱0.00';
                                    $discount_class = 'text-muted';
                                }
                                ?>
                                
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
                                    <div class="p-3" style="background: <?php echo $is_senior ? '#d4edda' : '#f8f9fa'; ?>; border-radius: 8px; border: 2px solid <?php echo $is_senior ? '#28a745' : '#dee2e6'; ?>;">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_senior_sales" 
                                                   name="is_senior_checkbox" value="1" <?php echo $is_senior ? 'checked' : ''; ?>
                                                   onchange="toggleSeniorSales()" style="transform: scale(1.2); margin-right: 10px;">
                                            <label class="form-check-label fw-bold" for="is_senior_sales">
                                                <i class="bi bi-person"></i> Senior Citizen
                                                <span class="badge bg-info">20% discount</span>
                                                <?php if($is_senior): ?>
                                                    <span class="badge bg-success">ACTIVE</span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                        
                                        <div id="seniorIdFieldSales" style="<?php echo $is_senior ? 'display: block;' : 'display: none;'; ?> margin-top: 10px;">
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-card-id"></i></span>
                                                <input type="text" name="senior_id" id="senior_id_sales" class="form-control" 
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

                                <form id="seniorToggleFormSales" method="POST" style="display:none;">
                                    <input type="hidden" name="toggle_senior" value="1">
                                    <input type="hidden" name="is_senior" id="senior_hidden_sales" value="<?php echo $is_senior ? '1' : '0'; ?>">
                                    <input type="hidden" name="senior_id" id="senior_id_hidden_sales" value="<?php echo $_SESSION['senior_id'] ?? ''; ?>">
                                </form>

                                <form action="../actions/checkout.php" method="POST" id="checkoutFormSales">
                                    <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
                                    <input type="hidden" name="senior_id" id="checkout_senior_id_sales" value="<?php echo $_SESSION['senior_id'] ?? ''; ?>">

                                    <div class="mb-3">
                                        <label>Payment Amount</label>
                                        <input type="number" name="payment_amount" id="paymentAmountSales" class="form-control" step="0.01" min="<?php echo $grand_total; ?>" required>
                                        <small class="text-muted">Minimum: <?php echo formatCurrency($grand_total); ?></small>
                                    </div>

                                    <button type="button" onclick="confirmCheckoutSales()" class="btn btn-success w-100" style="padding: 12px; font-size: 16px;">
                                        <i class="bi bi-check-circle"></i> Complete Sale
                                    </button>
                                </form>
                            <?php else: ?>
                                <p class="text-muted text-center">Add items to start checkout</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// ============================================
// SENIOR CITIZEN TOGGLE - SALES PAGE
// ============================================
function toggleSeniorSales() {
    let checkbox = document.getElementById('is_senior_sales');
    let hiddenInput = document.getElementById('senior_hidden_sales');
    let idField = document.getElementById('seniorIdFieldSales');
    let idInput = document.getElementById('senior_id_sales');
    let idHidden = document.getElementById('senior_id_hidden_sales');
    
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
    
    document.getElementById('seniorToggleFormSales').submit();
}

// ============================================
// REMOVE ITEM WITH SWEETALERT
// ============================================
function removeItem(key, name) {
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
function clearCart() {
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
// CHECKOUT CONFIRMATION - SALES PAGE
// ============================================
function confirmCheckoutSales() {
    let payment = document.getElementById('paymentAmountSales');
    let grandTotal = <?php echo isset($grand_total) ? $grand_total : 0; ?>;
    let checkbox = document.getElementById('is_senior_sales');
    let idInput = document.getElementById('senior_id_sales');
    
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
        
        document.getElementById('checkout_senior_id_sales').value = seniorId;
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
            document.getElementById('checkoutFormSales').submit();
        }
    });
}

// ============================================
// QUANTITY VALIDATION
// ============================================
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    let select = document.getElementById('productSelect');
    let quantity = document.getElementById('quantityInput');
    let stock = select.options[select.selectedIndex].getAttribute('data-stock');
    
    if (stock) {
        if (parseInt(quantity.value) > parseInt(stock)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock!',
                text: 'Only ' + stock + ' unit(s) available.',
                timer: 3000,
                showConfirmButton: false
            });
            return false;
        }
    }
});

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