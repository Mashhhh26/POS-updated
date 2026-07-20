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
                        <a class="nav-link" href="sales.php">
                            <i class="bi bi-cart"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="summary.php">
                            <i class="bi bi-graph-up"></i> Summary
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>                 
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
                <h1>Edit Product</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
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
                            <div class="col-md-6 mb-3">
                                <label>Stock</label>
                                <input type="number" name="stock" class="form-control" value="<?php echo $product['stock']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>