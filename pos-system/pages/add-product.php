<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isAdminOrCoAdmin()) {
    redirect('dashboard.php');
}

// ============================================
// AUTO-GENERATE PRODUCT CODE
// ============================================
function generateProductCode($pdo) {
    $stmt = $pdo->query("SELECT product_code FROM products ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if ($last) {
        $last_num = (int)substr($last['product_code'], 1);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    
    return 'P' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

$new_code = generateProductCode($pdo);

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
                <h1>Add New Product</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back
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
                <div class="card-body">
                    <form action="../actions/add-product-action.php" method="POST" id="addProductForm" onsubmit="return validateForm()">
                        <div class="row">
                            <!-- PRODUCT CODE -->
                            <div class="col-md-6 mb-3">
                                <label>Product Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="product_code" id="product_code" 
                                           class="form-control" value="<?php echo $new_code; ?>" 
                                           readonly style="background: #f8f9fa; font-weight: bold; color: #2c3e50;">
                                    <button type="button" class="btn btn-outline-secondary" onclick="refreshCode()" title="Generate new code">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Auto-generated. Click refresh to generate new code.
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label>Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="product_name" class="form-control" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Original Price (before VAT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" name="original_price" id="original_price" step="0.01" 
                                           class="form-control" required oninput="computeSellingPrice()"
                                           min="0.01" max="1000000">
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Max: ₱1,000,000.00
                                </small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>VAT (12%)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="vat_display" class="form-control" readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Selling Price (with VAT)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" id="selling_price_display" class="form-control" readonly style="background: #f8f9fa; font-weight: bold; color: #28a745;">
                                    <input type="hidden" name="selling_price" id="selling_price">
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Stock <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box"></i></span>
                                    <input type="number" name="stock" id="stock" class="form-control" 
                                           required min="0" max="10000" value="0">
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Max: 10,000 units
                                </small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g., Beverages, Groceries">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>VAT Type <span class="text-danger">*</span></label>
                                <select name="vat_type" class="form-select" required>
                                    <option value="V">VATable (V) - with 12% VAT</option>
                                    <option value="E">VAT-Exempt (E) - no VAT</option>
                                    <option value="Z">Zero-Rated (Z) - 0% VAT</option>
                                </select>
                                <small class="text-muted">
                                    V = may VAT | E = walang VAT | Z = 0% VAT
                                </small>
                            </div>
                            
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Save Product
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
// COMPUTE SELLING PRICE
// ============================================
function computeSellingPrice() {
    let original = document.getElementById('original_price').value;
    if (original > 0) {
        let vat = original * 0.12;
        let selling = parseFloat(original) + parseFloat(vat);
        
        document.getElementById('vat_display').value = vat.toFixed(2);
        document.getElementById('selling_price_display').value = selling.toFixed(2);
        document.getElementById('selling_price').value = selling.toFixed(2);
    } else {
        document.getElementById('vat_display').value = '';
        document.getElementById('selling_price_display').value = '';
        document.getElementById('selling_price').value = '';
    }
}

// ============================================
// REFRESH PRODUCT CODE
// ============================================
function refreshCode() {
    let currentCode = document.getElementById('product_code').value;
    let currentNum = parseInt(currentCode.substring(1));
    let newNum = currentNum + 1;
    let newCode = 'P' + String(newNum).padStart(3, '0');
    document.getElementById('product_code').value = newCode;
}

// ============================================
// VALIDATE FORM - STOCK & PRICE LIMITS
// ============================================
function validateForm() {
    let price = document.getElementById('original_price').value;
    let stock = document.getElementById('stock').value;
    let name = document.getElementById('product_name').value;
    
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
        document.getElementById('original_price').focus();
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
        document.getElementById('original_price').focus();
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
        document.getElementById('stock').focus();
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
        document.getElementById('stock').focus();
        return false;
    }
    
    return true;
}

// Real-time validation for stock
document.getElementById('stock').addEventListener('input', function() {
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

// Real-time validation for price
document.getElementById('original_price').addEventListener('input', function() {
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
</script>

<?php require_once '../includes/footer.php'; ?>