<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

// Co-Admin can add products
if (!isAdminOrCoAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to add products.'
    ];
    redirect('dashboard.php');
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
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-text"></i> Reports
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
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="../actions/add-product-action.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Product Code</label>
                                <input type="text" name="product_code" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Original Price (before VAT)</label>
                                <input type="number" name="original_price" id="original_price" step="0.01" class="form-control" required oninput="computeSellingPrice()">
                                <small class="text-muted">Presyo bago idagdag ang 12% VAT</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>VAT (12%)</label>
                                <input type="text" id="vat_display" class="form-control" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Selling Price (with VAT)</label>
                                <input type="text" id="selling_price_display" class="form-control" readonly>
                                <input type="hidden" name="selling_price" id="selling_price">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Stock</label>
                                <input type="number" name="stock" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Save Product</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function computeSellingPrice() {
    let original = document.getElementById('original_price').value;
    if (original > 0) {
        let vat = original * 0.12;
        let selling = parseFloat(original) + parseFloat(vat);
        
        document.getElementById('vat_display').value = 'Php ' + vat.toFixed(2);
        document.getElementById('selling_price_display').value = 'Php ' + selling.toFixed(2);
        document.getElementById('selling_price').value = selling.toFixed(2);
    } else {
        document.getElementById('vat_display').value = '';
        document.getElementById('selling_price_display').value = '';
        document.getElementById('selling_price').value = '';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>