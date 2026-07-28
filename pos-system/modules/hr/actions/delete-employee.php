<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('manage_employees')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to delete employees.'
    ];
    redirect('../employees.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid employee ID.'
    ];
    redirect('../employees.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Employee Deleted!',
        'text' => 'Employee has been deleted successfully.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete employee: ' . $e->getMessage()
    ];
}

redirect('../employees.php');
?>