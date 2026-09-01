<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('manage_products')) {
    logActivity("Access denied: stock_movement.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

// Stock In
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stock_in'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $reference = sanitize($_POST['reference']);
    $notes = sanitize($_POST['notes']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
        
        $stmt = $db->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, reference, notes) 
                               VALUES (?, ?, 'in', ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $quantity, $reference, $notes]);
        
        $db->commit();
        $success_message = "Stock added successfully! +{$quantity} units";
        logActivity("Stock IN: +{$quantity} units for product ID: {$product_id}");
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

// Stock Out
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['stock_out'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $reference = sanitize($_POST['reference']);
    $notes = sanitize($_POST['notes']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $db->beginTransaction();
        
        $check = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
        $check->execute([$product_id]);
        $current = $check->fetchColumn();
        
        if ($current < $quantity) {
            throw new Exception("Insufficient stock! Available: {$current}");
        }
        
        $stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
        
        $stmt = $db->prepare("INSERT INTO stock_movements (product_id, user_id, type, quantity, reference, notes) 
                               VALUES (?, ?, 'out', ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $quantity, $reference, $notes]);
        
        $db->commit();
        $success_message = "Stock removed successfully! -{$quantity} units";
        logActivity("Stock OUT: -{$quantity} units for product ID: {$product_id}");
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Error: " . $e->getMessage();
    }
}

$products = $db->query("SELECT id, product_code, product_name, stock_quantity FROM products ORDER BY product_name")->fetchAll();
$movements = $db->query("
    SELECT sm.*, p.product_name, p.product_code, u.full_name as user 
    FROM stock_movements sm
    JOIN products p ON sm.product_id = p.id
    JOIN users u ON sm.user_id = u.id
    ORDER BY sm.created_at DESC
    LIMIT 100
")->fetchAll();

$stats = [
    'total_in' => $db->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'in'")->fetchColumn(),
    'total_out' => $db->query("SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE type = 'out'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Movement</title>
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
                <h4 class="mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i> Stock Movement</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock In</h6>
                            <h3 class="fw-bold text-success">+<?php echo number_format($stats['total_in']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Stock Out</h6>
                            <h3 class="fw-bold text-danger">-<?php echo number_format($stats['total_out']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0 text-success"><i class="fas fa-arrow-down me-2"></i> Stock In</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo $p['product_code']; ?> - <?php echo $p['product_name']; ?> (Stock: <?php echo $p['stock_quantity']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. PO-001, Return, etc.">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                </div>
                                <button type="submit" name="stock_in" class="btn btn-success w-100">
                                    <i class="fas fa-plus-circle me-1"></i> Add Stock
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0 text-danger"><i class="fas fa-arrow-up me-2"></i> Stock Out</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Product</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">Select Product</option>
                                        <?php foreach($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo $p['product_code']; ?> - <?php echo $p['product_name']; ?> (Stock: <?php echo $p['stock_quantity']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" required min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference</label>
                                    <input type="text" name="reference" class="form-control" placeholder="e.g. Sold, Return to supplier, etc.">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                                </div>
                                <button type="submit" name="stock_out" class="btn btn-danger w-100">
                                    <i class="fas fa-minus-circle me-1"></i> Remove Stock
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Stock Movement Log</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>Reference</th>
                                        <th>User</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($movements as $mov): ?>
                                    <tr>
                                        <td><?php echo $mov['product_name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $mov['type'] === 'in' ? 'success' : 'danger'; ?>">
                                                <?php echo strtoupper($mov['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold <?php echo $mov['type'] === 'in' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $mov['type'] === 'in' ? '+' : '-'; ?><?php echo $mov['quantity']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $mov['reference'] ?: '-'; ?></td>
                                        <td><?php echo $mov['user']; ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($mov['created_at'])); ?></td>
                                        <td><?php echo $mov['notes'] ?: '-'; ?></td>
                                    </tr>
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
</body>
</html>