<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('manage_attendance')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to delete attendance records.'
    ];
    redirect('../attendance.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid attendance ID.'
    ];
    redirect('../attendance.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Attendance Deleted!',
        'text' => 'Attendance record deleted successfully.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete attendance: ' . $e->getMessage()
    ];
}

redirect('../attendance.php');
?>