<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Product Deleted!',
        'text' => 'Product has been deleted successfully.'
    ];
} catch(PDOException $e) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete product.'
    ];
}

redirect('../pages/products.php');
?>