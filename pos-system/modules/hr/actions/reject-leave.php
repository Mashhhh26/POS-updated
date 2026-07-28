<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('approve_leave_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to reject leave requests.'
    ];
    redirect('../leave-requests.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid leave request ID.'
    ];
    redirect('../leave-requests.php');
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'rejected', approved_by = ?, date_processed = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Leave Rejected!',
        'text' => 'Leave request has been rejected.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to reject leave: ' . $e->getMessage()
    ];
}

redirect('../leave-requests.php');
?>