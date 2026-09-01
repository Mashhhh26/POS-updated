<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_reports')) {
    logActivity("Access denied: reports.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Sales Summary
$salesSummary = $db->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as total_sales, SUM(total_amount) as total_amount,
           SUM(discount) as total_discount, SUM(tax) as total_tax,
           SUM(total_amount - discount + tax) as net_total
    FROM sales WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY date DESC
");
$salesSummary->execute([$date_from, $date_to]);
$salesData = $salesSummary->fetchAll();

// Total Sales
$totalSales = $db->prepare("
    SELECT COUNT(*) as count, SUM(total_amount) as total, SUM(discount) as discount,
           SUM(tax) as tax, SUM(total_amount - discount + tax) as net
    FROM sales WHERE DATE(created_at) BETWEEN ? AND ?
");
$totalSales->execute([$date_from, $date_to]);
$totals = $totalSales->fetch();

// Top Products
$topProducts = $db->query("
    SELECT p.product_name, p.product_code, SUM(si.quantity) as total_sold, SUM(si.subtotal) as total_revenue
    FROM sales_items si JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.created_at) BETWEEN '$date_from' AND '$date_to'
    GROUP BY p.id ORDER BY total_sold DESC LIMIT 10
")->fetchAll();

// Procurement Summary
$procSummary = $db->prepare("
    SELECT COUNT(*) as total_pos, SUM(grand_total) as total_amount, COUNT(DISTINCT supplier_id) as suppliers
    FROM purchase_orders WHERE DATE(created_at) BETWEEN ? AND ? AND status IN ('fulfilled', 'closed')
");
$procSummary->execute([$date_from, $date_to]);
$procData = $procSummary->fetch();

// Top Suppliers
$topSuppliers = $db->prepare("
    SELECT s.company_name, COUNT(po.id) as po_count, SUM(po.grand_total) as total_amount
    FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.id
    WHERE DATE(po.created_at) BETWEEN ? AND ? AND po.status IN ('fulfilled', 'closed')
    GROUP BY po.supplier_id ORDER BY total_amount DESC LIMIT 10
");
$topSuppliers->execute([$date_from, $date_to]);
$supplierData = $topSuppliers->fetchAll();

// Expenses Summary
$expensesTotal = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE(expense_date) BETWEEN ? AND ?");
$expensesTotal->execute([$date_from, $date_to]);
$expensesData = $expensesTotal->fetch();

// Budget Summary
$budgetData = $db->query("SELECT COALESCE(SUM(allocated_amount), 0) as total, COALESCE(SUM(used_amount), 0) as used FROM budgets WHERE status = 'active'")->fetch();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <h4 class="mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i> Reports</h4>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4"><label class="form-label">Date From</label><input type="date" name="date_from" class="form-control" value="<?php echo $date_from; ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Date To</label><input type="date" name="date_to" class="form-control" value="<?php echo $date_to; ?>" required></div>
                        <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button></div>
                    </form>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Sales</h6>
                            <h3 class="fw-bold text-primary">₱<?php echo number_format($totals['net'] ?? 0, 2); ?></h3>
                            <small><?php echo $totals['count'] ?? 0; ?> transactions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Purchases</h6>
                            <h3 class="fw-bold text-danger">₱<?php echo number_format($procData['total_amount'] ?? 0, 2); ?></h3>
                            <small><?php echo $procData['total_pos'] ?? 0; ?> POs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Expenses</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($expensesData['total'] ?? 0, 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Budget Remaining</h6>
                            <h3 class="fw-bold text-success">₱<?php echo number_format(($budgetData['total'] ?? 0) - ($budgetData['used'] ?? 0), 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i> Daily Sales</h5></div>
                        <div class="card-body"><canvas id="salesChart" height="250"></canvas></div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i> Top Products</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead><tr><th>Product</th><th>Sold</th><th>Revenue</th></tr></thead>
                                    <tbody>
                                        <?php foreach($topProducts as $product): ?>
                                        <tr><td><small><?php echo $product['product_name']; ?></small></td><td><?php echo $product['total_sold']; ?></td><td>₱<?php echo number_format($product['total_revenue'], 2); ?></td></tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Sales Summary</h5></div>
                        <div class="card-body table-responsive" style="max-height:300px;">
                            <table class="table table-sm">
                                <thead><tr><th>Date</th><th>Sales</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <?php foreach($salesData as $sale): ?>
                                    <tr><td><?php echo date('M d', strtotime($sale['date'])); ?></td><td><?php echo $sale['total_sales']; ?></td><td>₱<?php echo number_format($sale['net_total'], 2); ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i> Top Suppliers</h5></div>
                        <div class="card-body table-responsive" style="max-height:300px;">
                            <table class="table table-sm">
                                <thead><tr><th>Supplier</th><th>POs</th><th>Amount</th></tr></thead>
                                <tbody>
                                    <?php foreach($supplierData as $supplier): ?>
                                    <tr><td><?php echo $supplier['company_name']; ?></td><td><?php echo $supplier['po_count']; ?></td><td>₱<?php echo number_format($supplier['total_amount'], 2); ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?php echo json_encode(array_reverse($salesData)); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: salesData.map(item => item.date),
                datasets: [{
                    label: 'Sales Amount',
                    data: salesData.map(item => item.net_total),
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                }, {
                    label: 'Transactions',
                    data: salesData.map(item => item.total_sales),
                    backgroundColor: 'rgba(25, 135, 84, 0.5)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: function(v) { return '₱' + v.toLocaleString(); } } },
                    y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
                }
            }
        });
    </script>
</body>
</html>