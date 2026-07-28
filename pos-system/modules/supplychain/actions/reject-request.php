<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('approve_supply_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to reject supply requests.'
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
    $stmt = $pdo->prepare("UPDATE supply_requests SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Request Rejected!',
        'text' => 'Supply request has been rejected.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to reject request: ' . $e->getMessage()
    ];
}

redirect('../requests.php');
?>