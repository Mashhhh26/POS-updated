<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_products')) {
    logActivity("Access denied: products.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_code = sanitize($_POST['product_code']);
    $product_name = sanitize($_POST['product_name']);
    $description = sanitize($_POST['description']);
    $price = (float)$_POST['price'];
    $cost = (float)$_POST['cost'];
    $stock_quantity = (int)$_POST['stock_quantity'];
    $category = sanitize($_POST['category']);
    $low_stock_threshold = (int)$_POST['low_stock_threshold'] ?: 5;
    
    try {
        $stmt = $db->prepare("INSERT INTO products (product_code, product_name, description, price, cost, stock_quantity, category, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $product_name, $description, $price, $cost, $stock_quantity, $category, $low_stock_threshold]);
        $success_message = "Product <strong>{$product_name}</strong> added successfully!";
        logActivity("Added product: {$product_name}");
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'low_stock' => $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold")->fetchColumn(),
    'out_of_stock' => $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i> Products</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Product
                </button>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Products</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Low Stock</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['low_stock']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Out of Stock</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['out_of_stock']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Cost</th>
                                <th>Stock</th>
                                <th>Threshold</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $product): ?>
                            <tr id="product-row-<?php echo $product['id']; ?>">
                                <td><span class="badge bg-secondary"><?php echo $product['product_code']; ?></span></td>
                                <td><strong><?php echo $product['product_name']; ?></strong></td>
                                <td><?php echo $product['category'] ?: '-'; ?></td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>₱<?php echo number_format($product['cost'], 2); ?></td>
                                <td>
                                    <?php
                                    $stockClass = 'success';
                                    if ($product['stock_quantity'] <= 0) {
                                        $stockClass = 'danger';
                                    } elseif ($product['stock_quantity'] <= $product['low_stock_threshold']) {
                                        $stockClass = 'warning';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $stockClass; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td><?php echo $product['low_stock_threshold']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-product" data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $product['id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i> Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="productForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Product Code</label>
                                <input type="text" name="product_code" class="form-control" required placeholder="e.g. PRD-001">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Electronics">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" name="stock_quantity" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="product_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price (₱)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Cost (₱)</label>
                                <input type="number" step="0.01" name="cost" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" name="low_stock_threshold" class="form-control" value="5">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_product" class="btn btn-primary" id="saveProductBtn">
                            <i class="fas fa-save me-1"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('saveProductBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            btn.disabled = true;
        });
        
        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                confirmDelete(`Product "${name}" will be permanently deleted!`, function() {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    fetch(`?delete=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 2000,
                                    timerProgressBar: true,
                                    showConfirmButton: true,
                                    confirmButtonColor: '#198754',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    document.getElementById(`product-row-${id}`).remove();
                                });
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(() => showError('Error deleting product!'));
                });
            });
        });
    </script>
</body>
</html>