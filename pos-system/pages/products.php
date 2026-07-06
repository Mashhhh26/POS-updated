<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
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
                    
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-text"></i> Reports
                        </a>
                    </li>
                    <?php endif; ?>
                    
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
                
                <!-- User Info -->
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

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 main-content">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1>Products</h1>
                <?php if(isAdminOrCoAdmin()): ?>
                <a href="add-product.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Add Product
                </a>
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
                                            <span class="status-badge in-stock">In Stock</span>
                                        <?php elseif($p['stock'] > 0): ?>
                                            <span class="status-badge low-stock">Low Stock</span>
                                        <?php else: ?>
                                            <span class="status-badge out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(isAdminOrCoAdmin()): ?>
                                        <a href="edit-product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $p['id']; ?>, '<?php echo addslashes($p['name']); ?>')" 
                                                class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php else: ?>
                                        <span class="text-muted">View only</span>
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
    </div>
</div>

<script>
// ============================================
// SEARCH PRODUCTS
// ============================================
document.getElementById('searchProduct').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#productTable tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// ============================================
// DELETE CONFIRMATION WITH SWEETALERT
// ============================================
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete Product?',
        text: 'Are you sure you want to delete "' + name + '"?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/delete-product-action.php?id=' + id;
        }
    });
}

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