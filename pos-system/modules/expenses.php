<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_expenses')) {
    logActivity("Access denied: expenses.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $expense_number = 'EXP-' . date('Ymd') . '-' . rand(1000, 9999);
    $category = sanitize($_POST['category']);
    $description = sanitize($_POST['description']);
    $amount = (float)$_POST['amount'];
    $expense_date = $_POST['expense_date'];
    $payment_method = sanitize($_POST['payment_method']);
    $created_by = $_SESSION['user_id'];
    
    try {
        $stmt = $db->prepare("INSERT INTO expenses (expense_number, category, description, amount, expense_date, payment_method, created_by, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->execute([$expense_number, $category, $description, $amount, $expense_date, $payment_method, $created_by]);
        $success_message = "Expense #{$expense_number} added!";
        logActivity("Added expense #{$expense_number} - ₱" . number_format($amount, 2));
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM expenses WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$expenses = $db->query("
    SELECT e.*, u.full_name as created_by_name 
    FROM expenses e
    JOIN users u ON e.created_by = u.id
    ORDER BY e.created_at DESC
")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses")->fetchColumn(),
    'count' => $db->query("SELECT COUNT(*) FROM expenses")->fetchColumn(),
    'this_month' => $db->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())")->fetchColumn(),
];

$categories = ['Office Supplies', 'Utilities', 'Rent', 'Salaries', 'Transportation', 'Marketing', 'Maintenance', 'Other'];
$paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'Cheque', 'GCash', 'PayMaya'];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-money-bill me-2 text-danger"></i> Expenses</h4>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Expense
                </button>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Expenses</h6>
                            <h3 class="fw-bold text-danger">₱<?php echo number_format($stats['total'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">This Month</h6>
                            <h3 class="fw-bold text-warning">₱<?php echo number_format($stats['this_month'], 2); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Transactions</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['count']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Date</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($expenses as $exp): ?>
                            <tr id="exp-row-<?php echo $exp['id']; ?>">
                                <td><strong><?php echo $exp['expense_number']; ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo $exp['category']; ?></span></td>
                                <td><?php echo $exp['description']; ?></td>
                                <td class="text-danger fw-bold">₱<?php echo number_format($exp['amount'], 2); ?></td>
                                <td><?php echo $exp['payment_method']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($exp['expense_date'])); ?></td>
                                <td><?php echo $exp['created_by_name']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-expense" data-id="<?php echo $exp['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addExpenseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-danger"></i> Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expense Date</label>
                                <input type="date" name="expense_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <?php foreach($paymentMethods as $method): ?>
                                    <option value="<?php echo $method; ?>"><?php echo $method; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_expense" class="btn btn-danger">
                            <i class="fas fa-save me-1"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        document.querySelectorAll('.delete-expense').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                confirmDelete('Delete this expense?', function() {
                    fetch(`?delete=${id}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess('Expense deleted!');
                                document.getElementById(`exp-row-${id}`).remove();
                            } else {
                                showError('Error deleting!');
                            }
                        });
                });
            });
        });
    </script>
</body>
</html>