<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!isset($_SESSION['is_senior'])) {
    $_SESSION['is_senior'] = 0;
}

if (!isset($_SESSION['senior_id'])) {
    $_SESSION['senior_id'] = '';
}

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
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Shopping Cart</h1>
        <a href="sales.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
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
                    <div class="card-header"><h5>Cart Items (<?php echo count($_SESSION['cart']); ?>)</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
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
                                    <?php $counter = 1; foreach($_SESSION['cart'] as $key => $item): 
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
                                                <button type="submit" class="btn btn-sm btn-warning"><i class="bi bi-arrow-repeat"></i></button>
                                            </form>
                                        </td>
                                        <td><?php echo formatCurrency($subtotal); ?></td>
                                        <td>
                                            <a href="../actions/remove-from-cart.php?key=<?php echo $key; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="../actions/clear-cart.php" class="btn btn-danger" onclick="return confirm('Clear all items?')">
                            <i class="bi bi-trash3"></i> Clear Cart
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h5>Order Summary</h5></div>
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

                        <form method="POST" action="" class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_senior" id="is_senior_cart" class="form-check-input" 
                                       value="1" <?php echo $is_senior ? 'checked' : ''; ?>
                                       onchange="this.form.submit()">
                                <input type="hidden" name="toggle_senior" value="1">
                                <label class="form-check-label" for="is_senior_cart">Senior Citizen (20% discount)</label>
                            </div>
                        </form>

                        <form action="../actions/checkout.php" method="POST">
                            <div class="mb-3">
                                <label>Payment Amount</label>
                                <input type="number" name="payment_amount" class="form-control" 
                                       step="0.01" min="<?php echo $grand_total; ?>" required>
                            </div>
                            <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
                            <input type="hidden" name="senior_id" value="<?php echo $_SESSION['senior_id'] ?? ''; ?>">
                            <button type="submit" class="btn btn-success w-100">Complete Sale</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>