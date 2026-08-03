<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid request ID.';
    redirect('../approvals.php');
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE supply_requests 
        SET finance_approved = 1, 
            status = 'finance_approved' 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Request approved by Finance. Awaiting Supply approval.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../approvals.php');
?>