<?php
// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pos_system');

// ============================================
// BASE PATH CONFIGURATION - AUTO DETECT
// ============================================
$base_path = '';
$script_name = $_SERVER['SCRIPT_NAME'];

if (strpos($script_name, '/modules/procurement/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_name, '/modules/hrm/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_name, '/modules/inventory/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_name, '/modules/') !== false) {
    $base_path = '../';
} else {
    $base_path = '';
}

define('BASE_PATH', $base_path);
define('BASE_URL', '/pos-system/');

// ============================================
// DATABASE FUNCTIONS
// ============================================
function getDB() {
    try {
        $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasPermission($permission) {
    if (!isset($_SESSION['permissions'])) return false;
    return in_array($permission, $_SESSION['permissions']);
}

function hasRole($role_name) {
    if (!isset($_SESSION['role'])) return false;
    return $_SESSION['role'] === $role_name;
}

function logActivity($action) {
    if (!isset($_SESSION['user_id'])) return;
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    try {
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $action, $ip]);
    } catch(Exception $e) {
        // Silent fail for logs
    }
}

function redirect($path) {
    header('Location: ' . BASE_PATH . $path);
    exit();
}
?>