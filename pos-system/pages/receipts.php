<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';
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
                        <a class="nav-link active" href="receipts.php">
                            <i class="bi bi-receipt"></i> Receipts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="bi bi-box"></i> Products
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
                <h1>Receipt History</h1>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <?php if(isset($_SESSION['swal'])): ?>
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

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label>Search Invoice</label>
                            <input type="text" name="search" class="form-control" placeholder="Enter invoice number..." value="<?php echo $_GET['search'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control" value="<?php echo $_GET['from_date'] ?? ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control" value="<?php echo $_GET['to_date'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Receipts Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>All Receipts</h5>
                    <span class="badge bg-primary">Total: <?php 
                        $count_query = "SELECT COUNT(*) FROM sales";
                        if (!empty($_GET['search'])) {
                            $count_query .= " WHERE invoice_number LIKE '%" . $_GET['search'] . "%'";
                        }
                        $count = $pdo->query($count_query)->fetchColumn();
                        echo $count;
                    ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice Number</th>
                                    <th>Customer</th>
                                    <th>Staff</th>
                                    <th>Total Amount</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Build query with filters
                                $sql = "SELECT s.*, u.full_name as staff_name 
                                        FROM sales s 
                                        LEFT JOIN users u ON s.user_id = u.id 
                                        WHERE 1=1";
                                
                                $params = [];
                                
                                if (!empty($_GET['search'])) {
                                    $sql .= " AND s.invoice_number LIKE ?";
                                    $params[] = '%' . $_GET['search'] . '%';
                                }
                                
                                if (!empty($_GET['from_date'])) {
                                    $sql .= " AND DATE(s.sale_date) >= ?";
                                    $params[] = $_GET['from_date'];
                                }
                                
                                if (!empty($_GET['to_date'])) {
                                    $sql .= " AND DATE(s.sale_date) <= ?";
                                    $params[] = $_GET['to_date'];
                                }
                                
                                $sql .= " ORDER BY s.sale_date DESC LIMIT 100";
                                
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute($params);
                                
                                if($stmt->rowCount() > 0):
                                $counter = 1;
                                while($receipt = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><strong><?php echo $receipt['invoice_number']; ?></strong></td>
                                    <td><?php echo $receipt['customer_name'] ?: 'Walk-in'; ?></td>
                                    <td><?php echo $receipt['staff_name']; ?></td>
                                    <td><strong><?php echo formatCurrency($receipt['grand_total']); ?></strong></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($receipt['sale_date'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view-receipt.php?id=<?php echo $receipt['id']; ?>" 
                                               target="_blank" 
                                               class="btn btn-info"
                                               title="View Receipt">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="view-receipt.php?id=<?php echo $receipt['id']; ?>&print=1" 
                                               target="_blank" 
                                               class="btn btn-success"
                                               title="Print Receipt">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button onclick="deleteReceipt(<?php echo $receipt['id']; ?>)" 
                                                    class="btn btn-danger"
                                                    title="Delete Receipt"
                                                    <?php echo isAdmin() ? '' : 'disabled'; ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-receipt" style="font-size: 40px; color: #ccc;"></i>
                                        <p class="mt-2 text-muted">No receipts found</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Receipts</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Revenue</h6>
                            <h3><?php echo formatCurrency($pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales")->fetchColumn()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Today's Receipts</h6>
                            <h3><?php echo $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn(); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Today's Sales</h6>
                            <h3><?php echo formatCurrency($pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchColumn()); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function deleteReceipt(id) {
    if (!confirm('Are you sure you want to delete this receipt? This action cannot be undone!')) {
        return;
    }
    
    Swal.fire({
        title: 'Delete Receipt?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../actions/delete-receipt.php?id=' + id;
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