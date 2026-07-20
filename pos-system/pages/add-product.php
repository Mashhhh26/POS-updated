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
                    <li class="nav-item">
                        <a class="nav-link" href="summary.php">
                            <i class="bi bi-graph-up"></i> Summary
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

            <!-- SweetAlert messages -->
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
                    <form action="../actions/add-product-action.php" method="POST" id="addProductForm">
                        <div class="row">
                            <!-- PRODUCT CODE - Auto-generated -->
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
                                           class="form-control" required oninput="computeSellingPrice()">
                                </div>
                                <small class="text-muted">Presyo bago idagdag ang 12% VAT</small>
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
                                <input type="number" name="stock" class="form-control" required min="0">
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
                                <button type="button" onclick="confirmAddProduct()" class="btn btn-primary btn-lg">
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
// CONFIRM ADD PRODUCT
// ============================================
function confirmAddProduct() {
    let name = document.getElementById('product_name').value;
    let price = document.getElementById('original_price').value;
    let stock = document.querySelector('input[name="stock"]').value;
    
    if (!name || !price || !stock) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form!',
            text: 'Please fill in all required fields.',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }
    
    if (parseFloat(price) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Price!',
            text: 'Price must be greater than 0.',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }
    
    Swal.fire({
        title: 'Add New Product?',
        text: 'Are you sure you want to add "' + name + '"?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, add!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('addProductForm').submit();
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>