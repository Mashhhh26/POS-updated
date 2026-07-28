<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('approve_finance_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to approve finance requests.'
    ];
    redirect('../finance-requests.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid request ID.'
    ];
    redirect('../finance-requests.php');
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE finance_requests SET status = 'approved', approved_by = ?, date_processed = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Request Approved!',
        'text' => 'Finance request has been approved.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to approve request: ' . $e->getMessage()
    ];
}

redirect('../finance-requests.php');
?>