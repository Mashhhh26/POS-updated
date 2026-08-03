<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

// Get products
$products = $pdo->query("SELECT id, name, price, stock FROM products WHERE stock > 0 ORDER BY name");
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Point of Sale</h1>
        <div>
            <a href="cart.php" class="btn btn-primary me-2">
                <i class="bi bi-cart"></i> View Cart
                <span class="badge bg-danger"><?php echo count($_SESSION['cart'] ?? []); ?></span>
            </a>
            <a href="receipts.php" class="btn btn-info me-2">
                <i class="bi bi-receipt"></i> Receipts
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
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
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5>Add Items</h5></div>
                <div class="card-body">
                    <form action="../actions/add-to-cart.php" method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label>Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                <?php while($p = $products->fetch()): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo $p['name']; ?> - <?php echo formatCurrency($p['price']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><h5>Current Cart</h5></div>
                <div class="card-body">
                    <?php if(empty($_SESSION['cart'])): ?>
                        <p class="text-muted text-center">Cart is empty</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php $total = 0; foreach($_SESSION['cart'] as $key => $item): 
                                        $subtotal = $item['price'] * $item['quantity'];
                                        $total += $subtotal;
                                    ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['price']); ?></td>
                                        <td><?php echo formatCurrency($subtotal); ?></td>
                                        <td>
                                            <a href="../actions/remove-from-cart.php?key=<?php echo $key; ?>" class="btn btn-sm btn-danger">Remove</a>
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
                        <a href="../actions/clear-cart.php" class="btn btn-danger btn-sm">Clear Cart</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

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
                    <form action="../actions/checkout.php" method="POST">
                        <div class="mb-3">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" value="<?php echo formatCurrency($total); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Payment</label>
                            <input type="number" name="payment" class="form-control" step="0.01" min="<?php echo $total; ?>" required>
                        </div>
                        <input type="hidden" name="total" value="<?php echo $total; ?>">
                        <button type="submit" class="btn btn-success w-100">Complete Sale</button>
                    </form>
                    <?php else: ?>
                    <p class="text-muted text-center">Add items to cart</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>