<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE stock > 0 ORDER BY name");

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Point of Sale</h1>
        <div>
            <a href="receipts.php" class="btn btn-info me-2">
                <i class="bi bi-receipt"></i> Receipts
            </a>
            <button onclick="window.location.reload()" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i> New Sale
            </button>
        </div>
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
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Product Selection -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5>Products</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while($p = $products->fetch()): 
                            $is_low_stock = ($p['stock'] <= 5);
                        ?>
                        <div class="col-md-4 mb-2">
                            <div class="card <?php echo $is_low_stock ? 'border-warning' : ''; ?>">
                                <div class="card-body p-2">
                                    <div class="text-center">
                                        <div><strong><?php echo $p['name']; ?></strong></div>
                                        <div class="text-muted small"><?php echo formatCurrency($p['price']); ?></div>
                                        <div class="text-muted small">Stock: <?php echo $p['stock']; ?></div>
                                        <?php if($is_low_stock): ?>
                                            <span class="badge bg-warning text-dark mt-1">Low Stock!</span>
                                            <br>
                                            <button class="btn btn-sm btn-danger mt-1" 
                                                    onclick="requestRestock(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>', <?php echo $p['price']; ?>, <?php echo $p['stock']; ?>)">
                                                <i class="bi bi-box-seam"></i> Request Restock
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-primary mt-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#addToCartModal"
                                                    data-id="<?php echo $p['id']; ?>"
                                                    data-name="<?php echo addslashes($p['name']); ?>"
                                                    data-price="<?php echo $p['price']; ?>"
                                                    data-stock="<?php echo $p['stock']; ?>">
                                                <i class="bi bi-cart-plus"></i> Add to Cart
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Cart -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Cart <span class="badge bg-primary"><?php echo count($_SESSION['cart']); ?></span></h5>
                </div>
                <div class="card-body">
                    <?php if(empty($_SESSION['cart'])): ?>
                        <p class="text-muted text-center">Cart is empty</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $total = 0; foreach($_SESSION['cart'] as $key => $item): 
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total += $subtotal;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td>
                                            <form action="actions/update-cart.php" method="POST" class="d-flex gap-1">
                                                <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" class="form-control form-control-sm" style="width: 60px;">
                                                <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                            </form>
                                        </td>
                                        <td><?php echo formatCurrency($item['price']); ?></td>
                                        <td><?php echo formatCurrency($subtotal); ?></td>
                                        <td>
                                            <a href="actions/remove-from-cart.php?key=<?php echo $key; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('Remove item?')">
                                                <i class="bi bi-x"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">Total:</td>
                                        <td><?php echo formatCurrency($total); ?></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="actions/clear-cart.php" class="btn btn-danger btn-sm">Clear Cart</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Checkout -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5>Checkout</h5></div>
                <div class="card-body">
                    <?php if(!empty($_SESSION['cart'])): 
                        $total = 0;
                        foreach($_SESSION['cart'] as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                    ?>
                    <form action="actions/checkout.php" method="POST" id="checkoutForm">
                        <div class="mb-3">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" value="<?php echo formatCurrency($total); ?>" readonly>
                            <input type="hidden" name="total" value="<?php echo $total; ?>">
                        </div>
                        <div class="mb-3">
                            <label>Payment</label>
                            <input type="number" name="payment" id="payment" class="form-control" 
                                   step="0.01" min="<?php echo $total; ?>" max="1000000" required>
                            <small class="text-muted">Min: <?php echo formatCurrency($total); ?> | Max: 1,000,000.00</small>
                        </div>
                        <div class="mb-3" id="changeDisplay" style="display: none;">
                            <label>Change</label>
                            <input type="text" id="change" class="form-control" readonly style="font-weight: bold; color: green;">
                        </div>
                        <button type="button" onclick="confirmCheckout()" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Complete Sale
                        </button>
                    </form>
                    <?php else: ?>
                    <p class="text-muted text-center">Add items to cart</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ========================================== -->
<!-- ADD TO CART MODAL                           -->
<!-- ========================================== -->
<div class="modal fade" id="addToCartModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add to Cart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-to-cart.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    <div class="mb-3">
                        <label>Product</label>
                        <input type="text" id="modal_product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="text" id="modal_product_price" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Available Stock</label>
                        <input type="text" id="modal_product_stock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Quantity</label>
                        <input type="number" name="quantity" id="modal_quantity" class="form-control" 
                               min="1" value="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- REQUEST RESTOCK MODAL                       -->
<!-- ========================================== -->
<div class="modal fade" id="restockModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Restock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/request-restock.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="restock_product_id">
                    <div class="mb-3">
                        <label>Product</label>
                        <input type="text" id="restock_product_name" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Current Stock</label>
                        <input type="text" id="restock_current_stock" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Unit Price</label>
                        <input type="text" id="restock_unit_price" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Quantity to Request</label>
                        <input type="number" name="quantity" id="restock_quantity" class="form-control" 
                               min="1" value="10" required>
                        <small class="text-muted">Recommended: 10, 20, 50, 100</small>
                    </div>
                    <div class="mb-3">
                        <label>Total Amount</label>
                        <input type="text" id="restock_total" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control" value="Store" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================
// REQUEST RESTOCK
// ============================================
function requestRestock(id, name, price, stock) {
    document.getElementById('restock_product_id').value = id;
    document.getElementById('restock_product_name').value = name;
    document.getElementById('restock_current_stock').value = stock;
    document.getElementById('restock_unit_price').value = 'Php ' + parseFloat(price).toFixed(2);
    document.getElementById('restock_quantity').value = 10;
    updateRestockTotal();
    
    var modal = new bootstrap.Modal(document.getElementById('restockModal'));
    modal.show();
}

function updateRestockTotal() {
    let qty = document.getElementById('restock_quantity').value || 0;
    let priceText = document.getElementById('restock_unit_price').value || 'Php 0';
    let unitPrice = parseFloat(priceText.replace('Php ', '')) || 0;
    let total = qty * unitPrice;
    document.getElementById('restock_total').value = 'Php ' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('restock_quantity');
    if (qtyInput) {
        qtyInput.addEventListener('input', updateRestockTotal);
    }
});

// ============================================
// ADD TO CART MODAL
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#addToCartModal"]');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('modal_product_id').value = this.dataset.id;
            document.getElementById('modal_product_name').value = this.dataset.name;
            document.getElementById('modal_product_price').value = 'Php ' + parseFloat(this.dataset.price).toFixed(2);
            document.getElementById('modal_product_stock').value = this.dataset.stock;
            document.getElementById('modal_quantity').value = 1;
            document.getElementById('modal_quantity').max = this.dataset.stock;
            
            document.getElementById('modal_quantity').oninput = function() {
                const maxStock = parseInt(this.max) || 0;
                if (parseInt(this.value) > maxStock) {
                    this.value = maxStock;
                }
                if (parseInt(this.value) < 1) {
                    this.value = 1;
                }
            };
        });
    });
});

// ============================================
// CALCULATE CHANGE
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const paymentInput = document.getElementById('payment');
    if (paymentInput) {
        paymentInput.addEventListener('input', function() {
            let payment = parseFloat(this.value) || 0;
            let total = <?php echo isset($total) ? $total : 0; ?>;
            
            if (payment > 1000000) {
                this.value = 1000000;
                payment = 1000000;
                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Limit',
                    text: 'Maximum payment is 1,000,000.00',
                    timer: 2000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            }
            
            let change = payment - total;
            if (payment >= total) {
                document.getElementById('changeDisplay').style.display = 'block';
                document.getElementById('change').value = 'Php ' + change.toFixed(2);
            } else {
                document.getElementById('changeDisplay').style.display = 'none';
            }
        });
    }
});

// ============================================
// CHECKOUT CONFIRMATION
// ============================================
function confirmCheckout() {
    let payment = document.getElementById('payment');
    let total = <?php echo isset($total) ? $total : 0; ?>;
    
    if (!payment.value || parseFloat(payment.value) < total) {
        Swal.fire({
            icon: 'warning',
            title: 'Insufficient Payment',
            text: 'Please enter the correct amount.',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }
    
    if (parseFloat(payment.value) > 1000000) {
        Swal.fire({
            icon: 'warning',
            title: 'Payment Limit',
            text: 'Maximum payment is 1,000,000.00',
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
        confirmButtonText: 'Yes, complete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('checkoutForm').submit();
        }
    });
}

document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    let qty = document.getElementById('modal_quantity');
    let stock = document.getElementById('modal_product_stock');
    if (parseInt(qty.value) > parseInt(stock.value)) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Insufficient Stock',
            text: 'Only ' + stock.value + ' units available.',
            timer: 3000,
            showConfirmButton: false
        });
        return false;
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>