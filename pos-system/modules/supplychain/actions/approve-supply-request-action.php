<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('approve_supply_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to approve supply requests.'
    ];
    redirect('../requests.php');
    exit();
}

$id = $_GET['id'] ?? 0;
$level = $_GET['level'] ?? 'finance';

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
    if ($level == 'finance') {
        $stmt = $pdo->prepare("UPDATE supply_requests SET status = 'finance_approved', finance_approved_by = ?, date_finance_approved = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $message = 'Request has been approved by Finance. Awaiting CEO approval.';
    } elseif ($level == 'ceo') {
        $stmt = $pdo->prepare("UPDATE supply_requests SET status = 'ceo_approved', ceo_approved_by = ?, date_ceo_approved = NOW() WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $message = 'Request has been approved by CEO. Ready for ordering.';
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Invalid approval level.'
        ];
        redirect('../requests.php');
        exit();
    }
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Request Approved!',
        'text' => $message
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to approve request: ' . $e->getMessage()
    ];
}

redirect('../requests.php');
?>