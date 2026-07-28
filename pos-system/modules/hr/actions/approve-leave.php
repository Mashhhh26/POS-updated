<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('approve_leave_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to approve leave requests.'
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
    $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'approved', approved_by = ?, date_processed = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Leave Approved!',
        'text' => 'Leave request has been approved.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to approve leave: ' . $e->getMessage()
    ];
}

redirect('../leave-requests.php');
?>