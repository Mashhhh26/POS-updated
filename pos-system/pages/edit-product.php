<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

if (!isAdminOrCoAdmin()) {
    redirect('dashboard.php');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Product not found.'
    ];
    redirect('products.php');
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Edit Product</h1>
        <a href="products.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="../actions/edit-product-action.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Product Code</label>
                        <input type="text" name="product_code" class="form-control" value="<?php echo $product['product_code']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Original Price</label>
                        <input type="number" name="original_price" step="0.01" class="form-control" value="<?php echo $product['original_price']; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>VAT (12%)</label>
                        <input type="text" class="form-control" value="<?php echo formatCurrency($product['original_price'] * 0.12); ?>" readonly>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Selling Price</label>
                        <input type="text" class="form-control" value="<?php echo formatCurrency($product['selling_price']); ?>" readonly>
                        <input type="hidden" name="selling_price" value="<?php echo $product['selling_price']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required min="0" max="10000">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo $product['category']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>VAT Type</label>
                        <select name="vat_type" class="form-select">
                            <option value="V" <?php echo ($product['vat_type'] ?? 'V') == 'V' ? 'selected' : ''; ?>>VATable (V)</option>
                            <option value="E" <?php echo ($product['vat_type'] ?? 'V') == 'E' ? 'selected' : ''; ?>>VAT-Exempt (E)</option>
                            <option value="Z" <?php echo ($product['vat_type'] ?? 'V') == 'Z' ? 'selected' : ''; ?>>Zero-Rated (Z)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>