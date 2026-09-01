<?php
session_start();
require_once 'config/database.php';

if (!isLoggedIn()) { 
    header('Location: ' . BASE_PATH . 'login.php'); 
    exit(); 
}

$db = getDB();

// Check attendance for HRM
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$_SESSION['user_id'], $today]);
$hasAttendance = $stmt->fetch();

// ============================================
// DASHBOARD STATISTICS
// ============================================

// Sales Stats
$stats['sales_today'] = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount - discount + tax), 0) as total 
    FROM sales 
    WHERE DATE(created_at) = CURDATE()
")->fetch();

$stats['sales_month'] = $db->query("
    SELECT COUNT(*) as count, COALESCE(SUM(total_amount - discount + tax), 0) as total 
    FROM sales 
    WHERE MONTH(created_at) = MONTH(CURDATE()) 
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetch();

// User Stats
$stats['total_users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['total_products'] = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stats['total_customers'] = $db->query("SELECT COUNT(*) FROM customers")->fetchColumn();

// Attendance Today
$stats['attendance_today'] = $db->query("SELECT COUNT(*) FROM attendance WHERE date = CURDATE()")->fetchColumn();

// Procurement Stats
$stats['pending_reqs'] = $db->query("SELECT COUNT(*) FROM requisitions WHERE status = 'pending_budget'")->fetchColumn();
$stats['pending_pos'] = $db->query("SELECT COUNT(*) FROM purchase_orders WHERE status = 'pending_approval'")->fetchColumn();
$stats['total_suppliers'] = $db->query("SELECT COUNT(*) FROM suppliers WHERE is_active = 1")->fetchColumn();

// ============================================
// LOW STOCK ALERT
// ============================================
$lowStockProducts = $db->query("
    SELECT id, product_name, product_code, stock_quantity, low_stock_threshold 
    FROM products 
    WHERE stock_quantity <= low_stock_threshold
    ORDER BY stock_quantity ASC
    LIMIT 10
")->fetchAll();

$lowStockCount = count($lowStockProducts);

// ============================================
// RECENT ACTIVITIES
// ============================================
$recentActivities = $db->query("
    SELECT al.*, u.full_name as user 
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
")->fetchAll();

// ============================================
// MONTHLY SALES CHART
// ============================================
$monthlySales = $db->query("
    SELECT 
        DATE_FORMAT(created_at, '%b') as month,
        COUNT(*) as count,
        COALESCE(SUM(total_amount - discount + tax), 0) as total
    FROM sales 
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
    ORDER BY MONTH(created_at)
")->fetchAll();

$chartLabels = array_column($monthlySales, 'month');
$chartData = array_column($monthlySales, 'total');

// ============================================
// TOP PRODUCTS
// ============================================
$topProducts = $db->query("
    SELECT 
        p.product_name,
        SUM(si.quantity) as total_sold,
        SUM(si.subtotal) as total_revenue
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    JOIN sales s ON si.sale_id = s.id
    WHERE DATE(s.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// ============================================
// PENDING REQUISITIONS
// ============================================
$pendingReqs = $db->query("
    SELECT req_number, department, estimated_value, created_at 
    FROM requisitions 
    WHERE status = 'pending_budget'
    ORDER BY created_at ASC
    LIMIT 5
")->fetchAll();

// ============================================
// EXPENSES THIS MONTH
// ============================================
$expensesThisMonth = $db->query("
    SELECT COALESCE(SUM(amount), 0) as total 
    FROM expenses 
    WHERE MONTH(expense_date) = MONTH(CURDATE()) 
    AND YEAR(expense_date) = YEAR(CURDATE())
")->fetchColumn();

// ============================================
// BUDGET SUMMARY
// ============================================
$budgetStats = $db->query("
    SELECT 
        COALESCE(SUM(allocated_amount), 0) as total,
        COALESCE(SUM(used_amount), 0) as used
    FROM budgets 
    WHERE status = 'active'
")->fetch();

$budgetRemaining = $budgetStats['total'] - $budgetStats['used'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <!-- Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2 text-primary"></i> Dashboard</h4>
                    <small class="text-muted">Welcome back, <?php echo $_SESSION['full_name']; ?>!</small>
                </div>
                <div>
                    <span class="badge bg-primary p-2">
                        <i class="far fa-calendar-alt me-1"></i> <?php echo date('F d, Y'); ?>
                    </span>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Today's Sales</h6>
                                    <h3 class="fw-bold text-primary mb-0">₱<?php echo number_format($stats['sales_today']['total'], 2); ?></h3>
                                    <small><?php echo $stats['sales_today']['count']; ?> transactions</small>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-cash-register text-primary fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Products</h6>
                                    <h3 class="fw-bold text-success mb-0"><?php echo $stats['total_products']; ?></h3>
                                    <small><?php echo $stats['total_customers']; ?> customers</small>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-boxes text-success fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Attendance Today</h6>
                                    <h3 class="fw-bold text-warning mb-0"><?php echo $stats['attendance_today']; ?></h3>
                                    <small><?php echo $stats['total_users']; ?> total users</small>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-clipboard-check text-warning fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Budget Remaining</h6>
                                    <h3 class="fw-bold text-info mb-0">₱<?php echo number_format($budgetRemaining, 2); ?></h3>
                                    <small>₱<?php echo number_format($budgetStats['total'], 2); ?> allocated</small>
                                </div>
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                    <i class="fas fa-coins text-info fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Second Row Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Expenses (This Month)</h6>
                            <h4 class="fw-bold text-danger">₱<?php echo number_format($expensesThisMonth, 2); ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pending Requisitions</h6>
                            <h4 class="fw-bold text-warning"><?php echo $stats['pending_reqs']; ?></h4>
                            <small><?php echo $stats['pending_pos']; ?> pending POs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Active Suppliers</h6>
                            <h4 class="fw-bold text-primary"><?php echo $stats['total_suppliers']; ?></h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Low Stock Items</h6>
                            <h4 class="fw-bold text-danger"><?php echo $lowStockCount; ?></h4>
                            <small>Products below threshold</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts & Tables -->
            <div class="row g-4">
                <!-- Sales Chart -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i> Monthly Sales</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Products -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-crown me-2 text-warning"></i> Top Products</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($topProducts)): ?>
                                <p class="text-muted text-center">No sales data yet</p>
                            <?php else: ?>
                                <?php foreach($topProducts as $product): ?>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span><?php echo $product['product_name']; ?></span>
                                    <div>
                                        <span class="badge bg-secondary me-1"><?php echo $product['total_sold']; ?> sold</span>
                                        <span class="text-success">₱<?php echo number_format($product['total_revenue'], 2); ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2 text-warning"></i> Low Stock Alert
                                <?php if ($lowStockCount > 0): ?>
                                <span class="badge bg-danger ms-2"><?php echo $lowStockCount; ?> items</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            <?php if (empty($lowStockProducts)): ?>
                                <div class="text-center text-success py-3">
                                    <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                    <p>All products have sufficient stock!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach($lowStockProducts as $p): ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong><?php echo $p['product_name']; ?></strong>
                                        <br><small class="text-muted"><?php echo $p['product_code']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-danger"><?php echo $p['stock_quantity']; ?> left</span>
                                        <br><small class="text-muted">Threshold: <?php echo $p['low_stock_threshold']; ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-clock me-2 text-primary"></i> Recent Activities</h5>
                        </div>
                        <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                            <?php if (empty($recentActivities)): ?>
                                <p class="text-muted text-center">No recent activities</p>
                            <?php else: ?>
                                <?php foreach($recentActivities as $activity): ?>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <div>
                                        <strong><?php echo $activity['user']; ?></strong>
                                        <br><small class="text-muted"><?php echo $activity['action']; ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($activity['created_at'])); ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Requisitions -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-clock me-2 text-warning"></i> Pending Requisitions</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <?php if (empty($pendingReqs)): ?>
                                <p class="text-muted text-center">No pending requisitions</p>
                            <?php else: ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Requisition #</th>
                                            <th>Department</th>
                                            <th>Estimated Value</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($pendingReqs as $req): ?>
                                        <tr>
                                            <td><strong><?php echo $req['req_number']; ?></strong></td>
                                            <td><?php echo $req['department']; ?></td>
                                            <td>₱<?php echo number_format($req['estimated_value'], 2); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_PATH; ?>modules/procurement/requisitions.php" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Popup Modal for HRM -->
    <?php if (!$hasAttendance && hasRole('HRM')): ?>
    <div class="modal fade" id="attendanceModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-clipboard-check me-2 text-primary"></i> Record Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo BASE_PATH; ?>modules/attendance.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="from_popup" value="true">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="present">✅ Present</option>
                                <option value="late">⏰ Late</option>
                                <option value="half-day">🌓 Half Day</option>
                                <option value="absent">❌ Absent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_attendance" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const labels = <?php echo json_encode($chartLabels); ?>;
        const data = <?php echo json_encode($chartData); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales Amount (₱)',
                    data: data,
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        <?php if (!$hasAttendance && hasRole('HRM')): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('attendanceModal'));
            modal.show();
        });
        <?php endif; ?>
    </script>
</body>
</html>