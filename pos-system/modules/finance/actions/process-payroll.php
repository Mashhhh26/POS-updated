<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('manage_payroll')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to process payroll.'
    ];
    redirect('../payroll.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid payroll ID.'
    ];
    redirect('../payroll.php');
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE payroll SET status = 'paid', paid_date = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Payroll Processed!',
        'text' => 'Payroll has been marked as paid.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to process payroll: ' . $e->getMessage()
    ];
}

redirect('../payroll.php');
?>