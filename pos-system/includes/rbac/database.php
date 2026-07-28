<?php
require_once __DIR__ . '/../../config/database.php';

// ============================================
// CHECK IF FUNCTIONS ALREADY EXIST
// ============================================

if (!function_exists('getAllRoles')) {

// ============================================
// ROLE FUNCTIONS
// ============================================

function getAllRoles() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY role_name");
    return $stmt->fetchAll();
}

function getRoleById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getRoleByName($name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE role_name = ?");
    $stmt->execute([$name]);
    return $stmt->fetch();
}

function createRole($role_name, $description) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
    return $stmt->execute([$role_name, $description]);
}

function updateRole($id, $role_name, $description) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, description = ? WHERE id = ?");
    return $stmt->execute([$role_name, $description, $id]);
}

function deleteRole($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================
// PERMISSION FUNCTIONS
// ============================================

function getAllPermissions() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM permissions ORDER BY module, permission_name");
    return $stmt->fetchAll();
}

function getPermissionsByModule($module) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE module = ? ORDER BY permission_name");
    $stmt->execute([$module]);
    return $stmt->fetchAll();
}

function getPermissionById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM permissions WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createPermission($permission_name, $module, $description) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, module, description) VALUES (?, ?, ?)");
    return $stmt->execute([$permission_name, $module, $description]);
}

function updatePermission($id, $permission_name, $module, $description) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE permissions SET permission_name = ?, module = ?, description = ? WHERE id = ?");
    return $stmt->execute([$permission_name, $module, $description, $id]);
}

function deletePermission($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM permissions WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================
// ROLE-PERMISSION FUNCTIONS
// ============================================

function getPermissionsByRole($role_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.* FROM permissions p
        JOIN role_permissions rp ON p.id = rp.permission_id
        WHERE rp.role_id = ?
        ORDER BY p.module, p.permission_name
    ");
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
}

function getRolePermissionsIds($role_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $stmt->execute([$role_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function assignPermissionToRole($role_id, $permission_id) {
    global $pdo;
    $check = $pdo->prepare("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?");
    $check->execute([$role_id, $permission_id]);
    if ($check->rowCount() > 0) {
        return true;
    }
    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    return $stmt->execute([$role_id, $permission_id]);
}

function removePermissionFromRole($role_id, $permission_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?");
    return $stmt->execute([$role_id, $permission_id]);
}

function syncRolePermissions($role_id, $permission_ids) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$role_id]);
        
        foreach ($permission_ids as $perm_id) {
            $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->execute([$role_id, $perm_id]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// ============================================
// USER ROLE FUNCTIONS
// ============================================

if (!function_exists('getUserRoles')) {
    function getUserRoles($user_id) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT r.* FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}

function getUserRolesIds($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function assignRoleToUser($user_id, $role_id) {
    global $pdo;
    $check = $pdo->prepare("SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?");
    $check->execute([$user_id, $role_id]);
    if ($check->rowCount() > 0) {
        return true;
    }
    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    return $stmt->execute([$user_id, $role_id]);
}

function removeRoleFromUser($user_id, $role_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?");
    return $stmt->execute([$user_id, $role_id]);
}

function syncUserRoles($user_id, $role_ids) {
    global $pdo;
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        foreach ($role_ids as $role_id) {
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $role_id]);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// ============================================
// PERMISSION CHECK FUNCTIONS
// ============================================

if (!function_exists('userHasPermission')) {
    function userHasPermission($user_id, $permission_name) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ? AND p.permission_name = ?
        ");
        $stmt->execute([$user_id, $permission_name]);
        return $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('userHasRole')) {
    function userHasRole($user_id, $role_name) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ? AND r.role_name = ?
        ");
        $stmt->execute([$user_id, $role_name]);
        return $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('userCan')) {
    function userCan($permission_name) {
        if (!isset($_SESSION['user_id'])) return false;
        return userHasPermission($_SESSION['user_id'], $permission_name);
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission($permission_name) {
        if (!userCan($permission_name)) {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Access Denied!',
                'text' => 'You do not have permission to access this page.'
            ];
            redirect('../pages/dashboard.php');
            exit();
        }
    }
}

} // END: if (!function_exists('getAllRoles'))
?>