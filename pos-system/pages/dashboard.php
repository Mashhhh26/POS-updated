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
                        <a class="nav-link active" href="dashboard.php">
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
                    
                    <!-- Products - Admin and Co-Admin only -->
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Receipts - All users -->
                    <li class="nav-item">
                        <a class="nav-link" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    
                    <!-- Reports - Admin and Co-Admin only -->
                    <?php if(isAdminOrCoAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="bi bi-file-text"></i> Reports
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Users - Admin only -->
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
                <h1>Dashboard</h1>
                <span>
                    Welcome, <?php echo $_SESSION['full_name']; ?>
                    <span class="badge bg-<?php 
                        echo $_SESSION['role'] == 'admin' ? 'danger' : 
                            ($_SESSION['role'] == 'co-admin' ? 'warning' : 'info'); 
                    ?>">
                        <?php echo ucfirst($_SESSION['role']); ?>
                    </span>
                </span>
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

            <?php
            // Statistics
            $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $total_sales = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
            $today_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn();
            $total_revenue = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales")->fetchColumn();
            ?>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <i class="bi bi-box stat-icon"></i>
                            <h6>Total Products</h6>
                            <h3><?php echo $total_products; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <i class="bi bi-cart-check stat-icon"></i>
                            <h6>Total Sales</h6>
                            <h3><?php echo $total_sales; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <i class="bi bi-cash-stack stat-icon"></i>
                            <h6>Today's Sales</h6>
                            <h3><?php echo formatCurrency($today_sales); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <i class="bi bi-graph-up-arrow stat-icon"></i>
                            <h6>Total Revenue</h6>
                            <h3><?php echo formatCurrency($total_revenue); ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Transactions</h5>
                    <a href="receipts.php" class="btn btn-sm btn-primary">View All Receipts</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recent = $pdo->query("SELECT * FROM sales ORDER BY sale_date DESC LIMIT 10");
                                if($recent->rowCount() > 0):
                                while($row = $recent->fetch()):
                                ?>
                                <tr>
                                    <td><strong><?php echo $row['invoice_number']; ?></strong></td>
                                    <td><?php echo formatCurrency($row['grand_total']); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($row['sale_date'])); ?></td>
                                    <td>
                                        <a href="view-receipt.php?id=<?php echo $row['id']; ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        <a href="view-receipt.php?id=<?php echo $row['id']; ?>&print=1" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-printer"></i> Print
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No transactions yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>