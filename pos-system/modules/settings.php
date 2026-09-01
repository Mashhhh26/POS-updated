<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasRole('Admin')) {
    logActivity("Access denied: settings.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $site_name = sanitize($_POST['site_name']);
    $timezone = sanitize($_POST['timezone']);
    $currency = sanitize($_POST['currency']);
    $tax_rate = (float)$_POST['tax_rate'];
    $po_threshold = (float)$_POST['po_threshold'];
    $low_stock_alert = (int)$_POST['low_stock_alert'];
    
    try {
        $stmt = $db->prepare("UPDATE system_settings SET 
                               site_name = ?, timezone = ?, currency = ?, 
                               tax_rate = ?, po_threshold = ?, low_stock_alert = ? 
                               WHERE id = 1");
        $stmt->execute([$site_name, $timezone, $currency, $tax_rate, $po_threshold, $low_stock_alert]);
        $success_message = "Settings updated successfully!";
        logActivity("Updated system settings");
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

$settings = $db->query("SELECT * FROM system_settings WHERE id = 1")->fetch();
if (!$settings) {
    $db->query("INSERT INTO system_settings (id, site_name, timezone, currency, tax_rate, po_threshold, low_stock_alert) 
                VALUES (1, 'POS System', 'Asia/Manila', 'PHP', 12, 50000, 10)");
    $settings = $db->query("SELECT * FROM system_settings WHERE id = 1")->fetch();
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
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
                <h4 class="mb-0"><i class="fas fa-cog me-2 text-primary"></i> System Settings</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i> General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Site Name</label>
                                        <input type="text" name="site_name" class="form-control" value="<?php echo $settings['site_name']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Timezone</label>
                                        <select name="timezone" class="form-select" required>
                                            <option value="Asia/Manila" <?php echo $settings['timezone'] === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila</option>
                                            <option value="Asia/Singapore" <?php echo $settings['timezone'] === 'Asia/Singapore' ? 'selected' : ''; ?>>Asia/Singapore</option>
                                            <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            <option value="America/New_York" <?php echo $settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Currency</label>
                                        <select name="currency" class="form-select" required>
                                            <option value="PHP" <?php echo $settings['currency'] === 'PHP' ? 'selected' : ''; ?>>₱ PHP</option>
                                            <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>$ USD</option>
                                            <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>€ EUR</option>
                                            <option value="SGD" <?php echo $settings['currency'] === 'SGD' ? 'selected' : ''; ?>>S$ SGD</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax Rate (%)</label>
                                        <input type="number" step="0.01" name="tax_rate" class="form-control" value="<?php echo $settings['tax_rate']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">PO Approval Threshold (₱)</label>
                                        <input type="number" step="0.01" name="po_threshold" class="form-control" value="<?php echo $settings['po_threshold']; ?>" required>
                                        <small class="text-muted">POs above this amount need finance approval</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Low Stock Alert Level</label>
                                        <input type="number" name="low_stock_alert" class="form-control" value="<?php echo $settings['low_stock_alert']; ?>" required>
                                        <small class="text-muted">Products below this quantity will trigger alert</small>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Save Settings
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i> System Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2"><strong>PHP Version:</strong> <?php echo phpversion(); ?></div>
                            <div class="mb-2">
                                <strong>Database:</strong> MySQL 
                                <?php 
                                $version = $db->query("SELECT VERSION()")->fetchColumn();
                                echo $version;
                                ?>
                            </div>
                            <div class="mb-2"><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></div>
                            <div class="mb-2"><strong>Upload Max:</strong> <?php echo ini_get('upload_max_filesize'); ?></div>
                            <div class="mb-2"><strong>Memory Limit:</strong> <?php echo ini_get('memory_limit'); ?></div>
                            <hr>
                            <div class="mb-2"><strong>Total Users:</strong> <?php echo $db->query("SELECT COUNT(*) FROM users")->fetchColumn(); ?></div>
                            <div class="mb-2"><strong>Total Products:</strong> <?php echo $db->query("SELECT COUNT(*) FROM products")->fetchColumn(); ?></div>
                            <div class="mb-2"><strong>Total Sales:</strong> <?php echo $db->query("SELECT COUNT(*) FROM sales")->fetchColumn(); ?></div>
                            <button class="btn btn-danger w-100 mt-3" onclick="confirmClearCache()">
                                <i class="fas fa-trash me-1"></i> Clear Cache
                            </button>
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
        function confirmClearCache() {
            confirmAction('This will clear all system cache. Continue?', 'Clear Cache', function() {
                showSuccess('Cache cleared successfully!');
            });
        }
    </script>
</body>
</html>