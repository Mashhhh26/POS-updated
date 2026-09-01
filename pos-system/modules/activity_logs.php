<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasRole('Admin')) {
    logActivity("Access denied: activity_logs.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$where = [];
$params = [];

if (isset($_GET['user_id']) && $_GET['user_id']) {
    $where[] = "user_id = ?";
    $params[] = (int)$_GET['user_id'];
}

if (isset($_GET['action']) && $_GET['action']) {
    $where[] = "action LIKE ?";
    $params[] = '%' . $_GET['action'] . '%';
}

if (isset($_GET['date_from']) && $_GET['date_from']) {
    $where[] = "DATE(created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && $_GET['date_to']) {
    $where[] = "DATE(created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

$logs = $db->prepare("
    SELECT al.*, u.full_name as user, u.username 
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    {$whereClause}
    ORDER BY al.created_at DESC
    LIMIT 100
");
$logs->execute($params);
$logsData = $logs->fetchAll();

$users = $db->query("SELECT id, full_name FROM users")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn(),
    'today' => $db->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'login' => $db->query("SELECT COUNT(*) FROM activity_logs WHERE action LIKE '%login%'")->fetchColumn(),
    'error' => $db->query("SELECT COUNT(*) FROM activity_logs WHERE action LIKE '%error%'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs</title>
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
                <h4 class="mb-0"><i class="fas fa-list me-2 text-primary"></i> Activity Logs</h4>
                <button class="btn btn-danger" onclick="clearLogs()">
                    <i class="fas fa-trash me-1"></i> Clear Logs
                </button>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Logs</h6>
                            <h3 class="fw-bold text-primary"><?php echo number_format($stats['total']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Today</h6>
                            <h3 class="fw-bold text-success"><?php echo number_format($stats['today']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Login Events</h6>
                            <h3 class="fw-bold text-info"><?php echo number_format($stats['login']); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Errors</h6>
                            <h3 class="fw-bold text-danger"><?php echo number_format($stats['error']); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach($users as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo isset($_GET['user_id']) && $_GET['user_id'] == $u['id'] ? 'selected' : ''; ?>>
                                    <?php echo $u['full_name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Action</label>
                            <input type="text" name="action" class="form-control" placeholder="Search action..." value="<?php echo $_GET['action'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logsData as $log): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $log['user']; ?></strong>
                                    <br><small class="text-muted"><?php echo $log['username']; ?></small>
                                </td>
                                <td><?php echo $log['action']; ?></td>
                                <td><?php echo $log['ip_address']; ?></td>
                                <td><?php echo date('M d, Y h:i:s A', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($logsData)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No logs found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js"></script>
    
    <script>
        function clearLogs() {
            confirmDelete('This will permanently delete all activity logs. Continue?', function() {
                fetch('?clear=1')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess('Logs cleared!');
                            location.reload();
                        } else {
                            showError(data.message);
                        }
                    });
            });
        }
    </script>
</body>
</html>