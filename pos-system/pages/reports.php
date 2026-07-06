<?php
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth.php';

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

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
                        <a class="nav-link" href="receipts.php">
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
                        <a class="nav-link active" href="reports.php">
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
                <h1>Sales Reports</h1>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total_transactions,
                COALESCE(SUM(subtotal_original), 0) as total_original,
                COALESCE(SUM(vat_amount), 0) as total_vat,
                COALESCE(SUM(discount_amount), 0) as total_discount,
                COALESCE(SUM(grand_total), 0) as total_revenue,
                COALESCE(AVG(grand_total), 0) as avg_sale
                FROM sales 
                WHERE DATE(sale_date) BETWEEN ? AND ?");
            $stmt->execute([$start_date, $end_date]);
            $summary = $stmt->fetch();
            ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Transactions</h6>
                            <h3><?php echo $summary['total_transactions']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Original Sales</h6>
                            <h3><?php echo formatCurrency($summary['total_original']); ?></h3>
                            <small>Before VAT</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total VAT Collected</h6>
                            <h3><?php echo formatCurrency($summary['total_vat']); ?></h3>
                            <small>12% of original</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stats">
                        <div class="card-body">
                            <h6>Total Revenue</h6>
                            <h3><?php echo formatCurrency($summary['total_revenue']); ?></h3>
                            <small>With VAT</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Sales Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Staff</th>
                                    <th>Subtotal (Orig)</th>
                                    <th>VAT</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT s.*, u.full_name as staff_name 
                                    FROM sales s 
                                    LEFT JOIN users u ON s.user_id = u.id 
                                    WHERE DATE(s.sale_date) BETWEEN ? AND ? 
                                    ORDER BY s.sale_date DESC
                                ");
                                $stmt->execute([$start_date, $end_date]);
                                if($stmt->rowCount() > 0):
                                while($sale = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td><strong><?php echo $sale['invoice_number']; ?></strong></td>
                                    <td><?php echo $sale['staff_name']; ?></td>
                                    <td><?php echo formatCurrency($sale['subtotal_original']); ?></td>
                                    <td><?php echo formatCurrency($sale['vat_amount']); ?></td>
                                    <td><?php echo $sale['discount_amount'] > 0 ? formatCurrency($sale['discount_amount']) : '-'; ?></td>
                                    <td><strong><?php echo formatCurrency($sale['grand_total']); ?></strong></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                    <td>
                                        <a href="view-receipt.php?id=<?php echo $sale['id']; ?>" 
                                           target="_blank" 
                                           class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="view-receipt.php?id=<?php echo $sale['id']; ?>&print=1" 
                                           target="_blank" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">No sales found</td>
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