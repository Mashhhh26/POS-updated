<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$rbac_file = __DIR__ . '/../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Sales Summary</h1>
        <button onclick="window.location.reload()" class="btn btn-secondary">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
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
    // ============================================
    // GET SUMMARY DATA
    // ============================================
    
    // Today
    $today = date('Y-m-d');
    $today_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) = '$today'")->fetchColumn();
    $today_count = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) = '$today'")->fetchColumn();
    
    // This Week
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) >= '$week_start'")->fetchColumn();
    $week_count = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) >= '$week_start'")->fetchColumn();
    
    // This Month
    $month_start = date('Y-m-01');
    $month_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales WHERE DATE(sale_date) >= '$month_start'")->fetchColumn();
    $month_count = $pdo->query("SELECT COUNT(*) FROM sales WHERE DATE(sale_date) >= '$month_start'")->fetchColumn();
    
    // Total
    $total_sales = $pdo->query("SELECT COALESCE(SUM(grand_total), 0) FROM sales")->fetchColumn();
    $total_count = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
    
    // Top Products
    $top_products = $pdo->query("
        SELECT p.name, SUM(si.quantity) as total_qty, SUM(si.subtotal_original) as total_amount
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        GROUP BY si.product_id
        ORDER BY total_qty DESC
        LIMIT 5
    ");
    
    // Today's Transactions
    $today_transactions = $pdo->query("SELECT * FROM sales WHERE DATE(sale_date) = '$today' ORDER BY sale_date DESC LIMIT 10");
    ?>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #3498db;">
                <div class="card-body">
                    <h6>Today's Sales</h6>
                    <h3><?php echo formatCurrency($today_sales); ?></h3>
                    <small><?php echo $today_count; ?> transaction(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #2ecc71;">
                <div class="card-body">
                    <h6>This Week</h6>
                    <h3><?php echo formatCurrency($week_sales); ?></h3>
                    <small><?php echo $week_count; ?> transaction(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #f39c12;">
                <div class="card-body">
                    <h6>This Month</h6>
                    <h3><?php echo formatCurrency($month_sales); ?></h3>
                    <small><?php echo $month_count; ?> transaction(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card card-stats" style="border-left: 4px solid #e74c3c;">
                <div class="card-body">
                    <h6>Total Sales</h6>
                    <h3><?php echo formatCurrency($total_sales); ?></h3>
                    <small><?php echo $total_count; ?> transaction(s)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-trophy"></i> Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <?php if($top_products->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; while($p = $top_products->fetch()): ?>
                                <tr>
                                    <td>
                                        <?php if($rank == 1): ?>
                                            <span class="badge bg-warning">🥇</span>
                                        <?php elseif($rank == 2): ?>
                                            <span class="badge bg-secondary">🥈</span>
                                        <?php elseif($rank == 3): ?>
                                            <span class="badge bg-danger">🥉</span>
                                        <?php else: ?>
                                            <?php echo $rank; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $p['name']; ?></td>
                                    <td><?php echo $p['total_qty']; ?></td>
                                    <td><?php echo formatCurrency($p['total_amount']); ?></td>
                                </tr>
                                <?php $rank++; endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center">No products sold yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Today's Transactions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-clock-history"></i> Today's Transactions</h5>
                </div>
                <div class="card-body">
                    <?php if($today_transactions->rowCount() > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($t = $today_transactions->fetch()): ?>
                                <tr>
                                    <td><strong><?php echo $t['invoice_number']; ?></strong></td>
                                    <td><?php echo formatCurrency($t['grand_total']); ?></td>
                                    <td><?php echo date('h:i A', strtotime($t['sale_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted text-center">No transactions today</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>