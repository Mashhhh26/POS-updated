<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
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
                        <a class="nav-link active" href="sales.php">
                            <i class="bi bi-cart"></i> Point of Sale
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
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
                    <li class="nav-item">
                        <a class="nav-link" href="summary.php">
                            <i class="bi bi-graph-up"></i> Summary
                        </a>
                    </li>
                    <?php endif; ?> 
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

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Add Items to Cart</h5>
                        </div>
                        <div class="card-body">
                            <form action="../actions/add-to-cart.php" method="POST" class="row g-3">
                                <div class="col-md-5">
                                    <label>Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php while($p = $products->fetch()): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo $p['name']; ?> - ₱<?php echo number_format($p['selling_price'], 2); ?> (Stock: <?php echo $p['stock']; ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Quantity</label>
                                    <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus"></i> Add to Cart
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <a href="cart.php" class="btn btn-success w-100">
                                        <i class="bi bi-cart-check"></i> View Cart
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Current Cart Summary -->
                    <div class="card mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Current Cart</h5>
                            <span class="badge bg-primary"><?php echo count($_SESSION['cart']); ?> item(s)</span>
                        </div>
                        <div class="card-body">
                            <?php if(empty($_SESSION['cart'])): ?>
                                <p class="text-muted text-center py-3">
                                    <i class="bi bi-cart" style="font-size: 40px; display: block; color: #ccc;"></i>
                                    Cart is empty. Add some products above.
                                </p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Orig Price</th>
                                                <th>Selling Price</th>
                                                <th>Subtotal</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $counter = 1;
                                            $subtotal_selling = 0;
                                            foreach($_SESSION['cart'] as $key => $item):
                                                $subtotal = $item['selling_price'] * $item['quantity'];
                                                $subtotal_selling += $subtotal;
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><strong><?php echo $item['name']; ?></strong></td>
                                                <td><?php echo $item['quantity']; ?></td>
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
                                                <th><strong><?php echo formatCurrency($subtotal_selling); ?></strong></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <button onclick="clearCart()" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Clear Cart
                                        </button>
                                    </div>
                                    <div>
                                        <a href="cart.php" class="btn btn-primary">
                                            <i class="bi bi-cart-check"></i> Proceed to Checkout
                                        </a>
                                    </div>
                                </div>
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