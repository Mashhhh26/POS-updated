<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_performance')) {
    logActivity("Access denied: performance.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rate_supplier'])) {
    $po_id = (int)$_POST['po_id'];
    $supplier_id = (int)$_POST['supplier_id'];
    $otif_score = (float)$_POST['otif_score'];
    $quality_score = (float)$_POST['quality_score'];
    $responsiveness_score = (float)$_POST['responsiveness_score'];
    $overall_rating = ($otif_score + $quality_score + $responsiveness_score) / 3;
    $comments = sanitize($_POST['comments']);
    $evaluated_by = $_SESSION['user_id'];
    
    try {
        $stmt = $db->prepare("INSERT INTO supplier_performance (po_id, supplier_id, otif_score, quality_score, responsiveness_score, overall_rating, comments, evaluated_by) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$po_id, $supplier_id, $otif_score, $quality_score, $responsiveness_score, $overall_rating, $comments, $evaluated_by]);
        
        $avg = $db->prepare("SELECT AVG(overall_rating) FROM supplier_performance WHERE supplier_id = ?");
        $avg->execute([$supplier_id]);
        $newRating = $avg->fetchColumn() ?? 0;
        
        $db->prepare("UPDATE suppliers SET rating = ? WHERE id = ?")->execute([$newRating, $supplier_id]);
        
        $success_message = "Supplier performance rated successfully!";
        logActivity("Rated supplier #{$supplier_id} - Overall: {$overall_rating}");
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

$closedPos = $db->query("
    SELECT po.*, s.company_name, s.id as supplier_id 
    FROM purchase_orders po
    JOIN suppliers s ON po.supplier_id = s.id
    WHERE po.status = 'fulfilled'
    AND po.id NOT IN (SELECT po_id FROM supplier_performance)
    ORDER BY po.created_at DESC
")->fetchAll();

$ratings = $db->query("
    SELECT sp.*, s.company_name, po.po_number, u.full_name as evaluator
    FROM supplier_performance sp
    JOIN suppliers s ON sp.supplier_id = s.id
    JOIN purchase_orders po ON sp.po_id = po.id
    JOIN users u ON sp.evaluated_by = u.id
    ORDER BY sp.evaluation_date DESC
    LIMIT 50
")->fetchAll();

$supplierRatings = $db->query("
    SELECT s.id, s.company_name, s.rating as current_rating,
           COUNT(sp.id) as total_evaluations,
           AVG(sp.otif_score) as avg_otif,
           AVG(sp.quality_score) as avg_quality,
           AVG(sp.responsiveness_score) as avg_responsiveness
    FROM suppliers s
    LEFT JOIN supplier_performance sp ON s.id = sp.supplier_id
    GROUP BY s.id
    ORDER BY current_rating DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Performance</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-star me-2 text-warning"></i> Supplier Performance</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <?php if (!empty($closedPos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i> Rate Supplier Performance</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select PO</label>
                                <select name="po_id" class="form-select" required>
                                    <option value="">Select PO to rate</option>
                                    <?php foreach($closedPos as $po): ?>
                                    <option value="<?php echo $po['id']; ?>" data-supplier="<?php echo $po['supplier_id']; ?>">
                                        <?php echo $po['po_number']; ?> - <?php echo $po['company_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="supplier_id" id="supplier_id">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Comments</label>
                                <input type="text" name="comments" class="form-control" placeholder="Feedback for supplier">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">OTIF Score (1-5)</label>
                                <input type="number" name="otif_score" class="form-control" step="0.5" min="1" max="5" required>
                                <small class="text-muted">On-Time In-Full</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quality Score (1-5)</label>
                                <input type="number" name="quality_score" class="form-control" step="0.5" min="1" max="5" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Responsiveness (1-5)</label>
                                <input type="number" name="responsiveness_score" class="form-control" step="0.5" min="1" max="5" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="rate_supplier" class="btn btn-primary">
                                    <i class="fas fa-star me-1"></i> Submit Rating
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-chart-simple me-2 text-primary"></i> Supplier Ratings Summary</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Rating</th>
                                <th>Evaluations</th>
                                <th>OTIF</th>
                                <th>Quality</th>
                                <th>Responsiveness</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($supplierRatings as $sup): ?>
                            <tr>
                                <td><strong><?php echo $sup['company_name']; ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?php echo $sup['current_rating'] >= 4 ? 'success' : ($sup['current_rating'] >= 3 ? 'warning' : 'danger'); ?> fs-6"><?php echo number_format($sup['current_rating'] ?? 0, 1); ?></span>
                                        <div class="ms-2"><?php $stars = round($sup['current_rating'] ?? 0); for($i = 1; $i <= 5; $i++) { echo $i <= $stars ? '⭐' : '☆'; } ?></div>
                                    </div>
                                </td>
                                <td><?php echo $sup['total_evaluations']; ?></td>
                                <td><?php echo number_format($sup['avg_otif'] ?? 0, 1); ?></td>
                                <td><?php echo number_format($sup['avg_quality'] ?? 0, 1); ?></td>
                                <td><?php echo number_format($sup['avg_responsiveness'] ?? 0, 1); ?></td>
                                <td><span class="badge bg-<?php echo ($sup['current_rating'] ?? 0) >= 4 ? 'success' : 'warning'; ?>"><?php echo ($sup['current_rating'] ?? 0) >= 4 ? '⭐ Preferred' : 'Standard'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Recent Evaluations</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>PO #</th>
                                <th>Supplier</th>
                                <th>OTIF</th>
                                <th>Quality</th>
                                <th>Responsiveness</th>
                                <th>Overall</th>
                                <th>Evaluator</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ratings as $rating): ?>
                            <tr>
                                <td><?php echo $rating['po_number']; ?></td>
                                <td><?php echo $rating['company_name']; ?></td>
                                <td><span class="badge bg-<?php echo $rating['otif_score'] >= 4 ? 'success' : 'warning'; ?>"><?php echo $rating['otif_score']; ?></span></td>
                                <td><span class="badge bg-<?php echo $rating['quality_score'] >= 4 ? 'success' : 'warning'; ?>"><?php echo $rating['quality_score']; ?></span></td>
                                <td><span class="badge bg-<?php echo $rating['responsiveness_score'] >= 4 ? 'success' : 'warning'; ?>"><?php echo $rating['responsiveness_score']; ?></span></td>
                                <td><strong><?php echo number_format($rating['overall_rating'], 1); ?></strong></td>
                                <td><?php echo $rating['evaluator']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($rating['evaluation_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    
    <script>
        document.querySelector('select[name="po_id"]').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            document.getElementById('supplier_id').value = option.dataset.supplier || '';
        });
    </script>
</body>
</html>