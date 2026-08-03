<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE payroll SET status = 'paid' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Payroll processed successfully';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../payroll.php');
?>