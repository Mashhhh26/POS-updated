<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasRole('Admin')) {
    logActivity("Access denied: backup.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    $backup_dir = '../backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }
    
    $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $dbname = DB_NAME;
    
    $command = "mysqldump --host={$host} --user={$user} --password={$pass} {$dbname} > {$backup_file} 2>&1";
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($backup_file)) {
        try {
            $stmt = $db->prepare("INSERT INTO backup_logs (backup_file, backup_size, created_by) VALUES (?, ?, ?)");
            $stmt->execute([basename($backup_file), filesize($backup_file), $_SESSION['user_id']]);
        } catch(Exception $e) {}
        
        echo json_encode(['success' => true, 'file' => basename($backup_file), 'size' => filesize($backup_file)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Backup failed: ' . implode(' ', $output)]);
    }
    exit();
}

if (isset($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = '../backups/' . $filename;
    if (file_exists($filepath) && unlink($filepath)) {
        echo json_encode(['success' => true, 'message' => 'Backup deleted!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete!']);
    }
    exit();
}

$backup_dir = '../backups/';
$backups = [];
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => date('Y-m-d H:i:s', filemtime($backup_dir . $file))
            ];
        }
    }
    rsort($backups);
}

try {
    $logs = $db->query("
        SELECT bl.*, u.full_name as user 
        FROM backup_logs bl 
        JOIN users u ON bl.created_by = u.id 
        ORDER BY bl.created_at DESC 
        LIMIT 20
    ")->fetchAll();
} catch(Exception $e) {
    $logs = [];
}

$totalBackups = count($backups);
$totalSize = 0;
foreach ($backups as $b) {
    $totalSize += $b['size'];
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup</title>
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
                <h4 class="mb-0"><i class="fas fa-database me-2 text-primary"></i> Database Backup</h4>
                <button class="btn btn-success" onclick="createBackup()">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Create Backup
                </button>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Backups</h6>
                            <h3 class="fw-bold text-primary"><?php echo $totalBackups; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Size</h6>
                            <h3 class="fw-bold text-info"><?php echo number_format($totalSize / 1024 / 1024, 2); ?> MB</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Latest Backup</h6>
                            <h3 class="fw-bold text-success"><?php echo !empty($backups) ? date('M d, Y', strtotime($backups[0]['date'])) : 'None'; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-file-archive me-2 text-primary"></i> Backup Files</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($backups)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No backups found</td></tr>
                                    <?php else: ?>
                                    <?php foreach($backups as $backup): ?>
                                    <tr id="backup-row-<?php echo md5($backup['name']); ?>">
                                        <td><i class="fas fa-file me-2 text-primary"></i> <?php echo $backup['name']; ?></td>
                                        <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                        <td><?php echo $backup['date']; ?></td>
                                        <td>
                                            <a href="../backups/<?php echo $backup['name']; ?>" class="btn btn-sm btn-primary" download>
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-backup" data-file="<?php echo $backup['name']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-history me-2 text-primary"></i> Backup Logs</h5>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <?php if (empty($logs)): ?>
                            <p class="text-muted text-center">No backup logs</p>
                            <?php else: ?>
                            <?php foreach($logs as $log): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <div>
                                    <strong><?php echo $log['backup_file']; ?></strong>
                                    <br><small class="text-muted"><?php echo $log['user']; ?></small>
                                </div>
                                <div class="text-end">
                                    <small><?php echo number_format($log['backup_size'] / 1024, 2); ?> KB</small>
                                    <br><small class="text-muted"><?php echo date('M d, h:i A', strtotime($log['created_at'])); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i> Backup Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between"><span>Database:</span> <strong><?php echo DB_NAME; ?></strong></div>
                            <div class="d-flex justify-content-between"><span>Host:</span> <strong><?php echo DB_HOST; ?></strong></div>
                            <div class="d-flex justify-content-between"><span>Format:</span> <strong>SQL</strong></div>
                            <hr>
                            <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i> Backups stored in <strong>backups/</strong> folder.</div>
                            <button class="btn btn-outline-danger w-100" onclick="confirmDeleteAll()"><i class="fas fa-trash me-1"></i> Delete All</button>
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
        function createBackup() {
            Swal.fire({ title: 'Creating Backup...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
            
            fetch('?action=backup')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Backup Created!',
                            html: `<p><strong>File:</strong> ${data.file}</p><p><strong>Size:</strong> ${(data.size / 1024).toFixed(2)} KB</p>`,
                            confirmButtonColor: '#198754',
                            confirmButtonText: 'OK'
                        }).then(() => location.reload());
                    } else {
                        showError(data.message || 'Backup failed!');
                    }
                })
                .catch(() => showError('Error creating backup!'));
        }
        
        document.querySelectorAll('.delete-backup').forEach(btn => {
            btn.addEventListener('click', function() {
                const file = this.dataset.file;
                confirmDelete(`Delete backup file "${file}"?`, function() {
                    Swal.fire({ title: 'Deleting...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                    
                    fetch(`?delete=${encodeURIComponent(file)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                showSuccess('Backup deleted!');
                                location.reload();
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(() => showError('Error deleting backup!'));
                });
            });
        });
        
        function confirmDeleteAll() {
            confirmDelete('Delete ALL backup files?', function() {
                const backups = document.querySelectorAll('.delete-backup');
                if (backups.length === 0) {
                    showInfo('No backups to delete.');
                    return;
                }
                
                Swal.fire({ title: 'Deleting All...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                
                let deleted = 0;
                const total = backups.length;
                backups.forEach((btn, index) => {
                    setTimeout(() => {
                        const file = btn.dataset.file;
                        fetch(`?delete=${encodeURIComponent(file)}`)
                            .then(r => r.json())
                            .then(data => {
                                deleted++;
                                if (deleted === total) {
                                    showSuccess('All backups deleted!');
                                    location.reload();
                                }
                            });
                    }, index * 200);
                });
            });
        }
    </script>
</body>
</html>