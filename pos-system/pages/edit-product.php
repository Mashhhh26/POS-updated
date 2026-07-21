<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdminOrCoAdmin()) {
    redirect('dashboard.php');
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid product ID.'
    ];
    redirect('products.php');
    exit();
}

// Get product data
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
    exit();
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
                        <a class="nav-link" href="cart.php">
                            <i class="bi bi-cart-check"></i> Cart
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart'] ?? []); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    <?php if(isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i> Users
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout-action.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                <div class="mt-4 p-3 text-white" style="border-top: 1px solid #34495e;">
                    <small>
                        <i class="bi bi-person"></i> <?php echo $_SESSION['full_name']; ?>
                        <br>
                        <span class="badge bg-<?php 
                            echo $_SESSION['role'] == 'admin' ? 'danger' : 
                                ($_SESSION['role'] == 'co-admin' ? 'warning' : 'info'); 
                        ?>">
                            <?php echo ucfirst($_SESSION['role']); ?>
                        </span>
                    </small>
                </div>
            </div>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Edit Product</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Products
                </a>
            </div>

            <?php if(isset($_SESSION['swal']) && !empty($_SESSION['swal'])): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: '<?php echo $_SESSION['swal']['type']; ?>',
                            title: '<?php echo $_SESSION['swal']['title']; ?>',
                            text: '<?php echo addslashes($_SESSION['swal']['text']); ?>',
                            timer: 4000,
                            showConfirmButton: true,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Got it!',
                            timerProgressBar: true
                        });
                    });
                </script>
                <?php unset($_SESSION['swal']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5>Editing: <?php echo $product['name']; ?></h5>
                </div>
                <div class="card-body">
                    <form action="../actions/edit-product-action.php" method="POST" id="editProductForm" onsubmit="return validateEditForm()">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Product Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="product_code" class="form-control" 
                                           value="<?php echo $product['product_code']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit_product_name" class="form-control" 
                                       value="<?php echo $product['name']; ?>" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"><?php echo $product['description']; ?></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Original Price (before VAT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="original_price" id="edit_original_price" step="0.01" 
                                           class="form-control" value="<?php echo $product['original_price']; ?>" 
                                           required min="0.01" max="1000000" oninput="updateEditTotals()">
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Max: ₱1,000,000.00
                                </small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>VAT (12%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="edit_vat_display" class="form-control" 
                                           value="<?php echo number_format($product['original_price'] * 0.12, 2); ?>" 
                                           readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Selling Price (with VAT)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="edit_selling_display" class="form-control" 
                                           value="<?php echo number_format($product['selling_price'], 2); ?>" 
                                           readonly style="background: #f8f9fa; font-weight: bold; color: #28a745;">
                                    <input type="hidden" name="selling_price" id="edit_selling_price" 
                                           value="<?php echo $product['selling_price']; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Stock <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box"></i></span>
                                    <input type="number" name="stock" id="edit_stock" class="form-control" 
                                           value="<?php echo $product['stock']; ?>" 
                                           required min="0" max="10000">
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Max: 10,000 units
                                </small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control" 
                                       value="<?php echo $product['category']; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>VAT Type <span class="text-danger">*</span></label>
                                <select name="vat_type" class="form-select" required>
                                    <option value="V" <?php echo ($product['vat_type'] ?? 'V') == 'V' ? 'selected' : ''; ?>>VATable (V) - with 12% VAT</option>
                                    <option value="E" <?php echo ($product['vat_type'] ?? 'V') == 'E' ? 'selected' : ''; ?>>VAT-Exempt (E) - no VAT</option>
                                    <option value="Z" <?php echo ($product['vat_type'] ?? 'V') == 'Z' ? 'selected' : ''; ?>>Zero-Rated (Z) - 0% VAT</option>
                                </select>
                                <small class="text-muted">
                                    V = may VAT | E = walang VAT | Z = 0% VAT
                                </small>
                            </div>
                            
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Update Product
                                </button>
                                <a href="products.php" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// ============================================
// UPDATE EDIT TOTALS
// ============================================
function updateEditTotals() {
    let original = document.getElementById('edit_original_price').value;
    if (original > 0) {
        let vat = original * 0.12;
        let selling = parseFloat(original) + parseFloat(vat);
        
        document.getElementById('edit_vat_display').value = vat.toFixed(2);
        document.getElementById('edit_selling_display').value = selling.toFixed(2);
        document.getElementById('edit_selling_price').value = selling.toFixed(2);
    } else {
        document.getElementById('edit_vat_display').value = '';
        document.getElementById('edit_selling_display').value = '';
        document.getElementById('edit_selling_price').value = '';
    }
}

// ============================================
// VALIDATE EDIT FORM
// ============================================
function validateEditForm() {
    let price = document.getElementById('edit_original_price').value;
    let stock = document.getElementById('edit_stock').value;
    let name = document.getElementById('edit_product_name').value;
    
    // Check product name
    if (!name || name.trim() === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Product Name Required!',
            text: 'Please enter the product name.',
            timer: 3000,
            showConfirmButton: false
        });
        return false;
    }
    
    // Check price limit (max 1,000,000)
    if (parseFloat(price) > 1000000) {
        Swal.fire({
            icon: 'error',
            title: 'Price Limit Exceeded!',
            text: 'Original price cannot exceed ₱1,000,000.00',
            timer: 4000,
            showConfirmButton: true,
            confirmButtonColor: '#d33'
        });
        document.getElementById('edit_original_price').focus();
        return false;
    }
    
    if (parseFloat(price) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Price!',
            text: 'Price must be greater than 0.',
            timer: 3000,
            showConfirmButton: false
        });
        document.getElementById('edit_original_price').focus();
        return false;
    }
    
    // Check stock limit (max 10,000)
    if (parseInt(stock) > 10000) {
        Swal.fire({
            icon: 'error',
            title: 'Stock Limit Exceeded!',
            text: 'Stock cannot exceed 10,000 units.',
            timer: 4000,
            showConfirmButton: true,
            confirmButtonColor: '#d33'
        });
        document.getElementById('edit_stock').focus();
        return false;
    }
    
    if (parseInt(stock) < 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Stock!',
            text: 'Stock cannot be negative.',
            timer: 3000,
            showConfirmButton: false
        });
        document.getElementById('edit_stock').focus();
        return false;
    }
    
    return true;
}

// ============================================
// REAL-TIME VALIDATION
// ============================================

// Stock validation
document.getElementById('edit_stock').addEventListener('input', function() {
    let val = parseInt(this.value);
    if (val > 10000) {
        this.value = 10000;
        Swal.fire({
            icon: 'warning',
            title: 'Stock Limit!',
            text: 'Maximum stock is 10,000 units.',
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    }
});

// Price validation
document.getElementById('edit_original_price').addEventListener('input', function() {
    let val = parseFloat(this.value);
    if (val > 1000000) {
        this.value = 1000000;
        Swal.fire({
            icon: 'warning',
            title: 'Price Limit!',
            text: 'Maximum price is ₱1,000,000.00',
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
        });
    }
});

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>