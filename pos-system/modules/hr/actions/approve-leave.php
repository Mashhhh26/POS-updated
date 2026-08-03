<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE leave_requests SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Leave request approved';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../leave-requests.php');
?>