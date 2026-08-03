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
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Shopping Cart</h1>
        <div>
            <a href="sales.php" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Continue Shopping
            </a>
            <a href="receipts.php" class="btn btn-info">
                <i class="bi bi-receipt"></i> Receipts
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

    <?php if(empty($_SESSION['cart'])): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-cart" style="font-size: 80px; color: #ccc;"></i>
                <h4>Your cart is empty</h4>
                <a href="sales.php" class="btn btn-primary">Browse Products</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h5>Cart Items</h5></div>
                    <div class="card-body">
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
                                        <td>
                                            <form action="../actions/update-cart.php" method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="key" value="<?php echo $key; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" class="form-control form-control-sm" style="width: 70px;">
                                                <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                            </form>
                                        </td>
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
                        <a href="../actions/clear-cart.php" class="btn btn-danger">Clear Cart</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h5>Summary</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Total:</span>
                            <strong><?php echo formatCurrency($total); ?></strong>
                        </div>
                        <a href="sales.php" class="btn btn-primary w-100 mt-3">Add More Items</a>
                        <form action="../actions/checkout.php" method="POST" class="mt-3">
                            <input type="hidden" name="total" value="<?php echo $total; ?>">
                            <div class="mb-2">
                                <label>Payment</label>
                                <input type="number" name="payment" class="form-control" step="0.01" min="<?php echo $total; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Checkout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../../includes/footer.php'; ?>