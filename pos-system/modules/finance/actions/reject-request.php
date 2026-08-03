<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE finance_requests SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Finance request rejected';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../requests.php');
?>