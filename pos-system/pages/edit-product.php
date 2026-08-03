<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdmin()) {
    redirect('dashboard.php');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Product not found';
    redirect('products.php');
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Edit Product</h1>
        <a href="products.php" class="btn btn-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="../actions/edit-product.php" method="POST">
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
                    <div class="col-md-4 mb-3">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo $product['category']; ?>">
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