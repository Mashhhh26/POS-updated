<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

// Check if admin
if (!isAdmin()) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'Only administrators can delete receipts.'
    ];
    redirect('../pages/receipts.php');
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid receipt ID.'
    ];
    redirect('../pages/receipts.php');
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Get sale items to restore stock
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    // Restore stock for each item
    foreach ($items as $item) {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Delete sale items
    $stmt = $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?");
    $stmt->execute([$id]);
    
    // Delete sale
    $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->execute([$id]);
    
    $pdo->commit();
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Receipt Deleted!',
        'text' => 'Receipt and related items have been deleted successfully.'
    ];
    
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Failed to delete receipt: ' . $e->getMessage()
    ];
}

redirect('../pages/receipts.php');
?>