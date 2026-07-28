<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('manage_supply_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to delete supply requests.'
    ];
    redirect('../requests.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid request ID.'
    ];
    redirect('../requests.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM supply_requests WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Request Deleted!',
        'text' => 'Supply request has been deleted successfully.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete request: ' . $e->getMessage()
    ];
}

redirect('../requests.php');
?>