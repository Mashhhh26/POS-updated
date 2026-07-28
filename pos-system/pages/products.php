<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Products</h1>
        <?php if(isAdminOrCoAdmin()): ?>
        <a href="add-product.php" class="btn btn-primary"><i class="bi bi-plus"></i> Add Product</a>
        <?php endif; ?>
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

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="searchProduct" class="form-control" placeholder="Search products...">
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="productTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Orig Price</th>
                            <th>VAT (12%)</th>
                            <th>Selling Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $products = $pdo->query("SELECT * FROM products ORDER BY name");
                        while($p = $products->fetch()):
                            $vat_amount = $p['original_price'] * 0.12;
                        ?>
                        <tr>
                            <td><?php echo $p['product_code']; ?></td>
                            <td><?php echo $p['name']; ?></td>
                            <td><?php echo $p['category']; ?></td>
                            <td><?php echo formatCurrency($p['original_price']); ?></td>
                            <td><?php echo formatCurrency($vat_amount); ?></td>
                            <td><strong><?php echo formatCurrency($p['selling_price']); ?></strong></td>
                            <td><?php echo $p['stock']; ?></td>
                            <td>
                                <?php if($p['stock'] > 10): ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php elseif($p['stock'] > 0): ?>
                                    <span class="badge bg-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(isAdminOrCoAdmin()): ?>
                                <a href="edit-product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="../actions/delete-product-action.php?id=<?php echo $p['id']; ?>" 
                                   class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('searchProduct').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#productTable tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>