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
    header("Location: sales.php");
    exit();
}

$products = $pdo->query("SELECT id, name, original_price, selling_price, stock FROM products WHERE stock > 0 ORDER BY name");

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

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

    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><h5>Add Items to Cart</h5></div>
                <div class="card-body">
                    <form action="../actions/add-to-cart.php" method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label>Product</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select Product</option>
                                <?php while($p = $products->fetch()): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo $p['name']; ?> - ₱<?php echo number_format($p['selling_price'], 2); ?>
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
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus"></i> Add</button>
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
                                    $subtotal_selling = 0;
                                    foreach($_SESSION['cart'] as $key => $item):
                                        $subtotal = $item['selling_price'] * $item['quantity'];
                                        $subtotal_selling += $subtotal;
                                    ?>
                                    <tr>
                                        <td><?php echo $item['name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo $item['stock'] ?? 0; ?></td>
                                        <td><?php echo formatCurrency($item['original_price']); ?></td>
                                        <td><?php echo formatCurrency($item['selling_price']); ?></td>
                                        <td><?php echo formatCurrency($subtotal); ?></td>
                                        <td>
                                            <a href="../actions/remove-from-cart.php?key=<?php echo $key; ?>" 
                                               class="btn btn-sm btn-danger" onclick="return confirm('Remove item?')">
                                                <i class="bi bi-x"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="../actions/clear-cart.php" class="btn btn-danger btn-sm" onclick="return confirm('Clear all items?')">
                            <i class="bi bi-trash"></i> Clear Cart
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5>Checkout</h5></div>
                <div class="card-body">
                    <?php if(!empty($_SESSION['cart'])): ?>
                        <?php
                        $subtotal_original = 0;
                        foreach($_SESSION['cart'] as $item) {
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
                        ?>
                        <form action="../actions/checkout.php" method="POST">
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
                                <?php if($is_senior): ?>
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

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_senior" id="is_senior" class="form-check-input" 
                                           value="1" <?php echo $is_senior ? 'checked' : ''; ?>
                                           onchange="this.form.submit()">
                                    <label class="form-check-label" for="is_senior">Senior Citizen (20% discount)</label>
                                </div>
                            </div>

                            <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
                            <div class="mb-3">
                                <label>Payment Amount</label>
                                <input type="number" name="payment_amount" class="form-control" step="0.01" min="<?php echo $grand_total; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Complete Sale</button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted text-center">Add items to start checkout</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>