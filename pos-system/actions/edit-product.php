<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $product_code = $_POST['product_code'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET product_code=?, name=?, price=?, stock=?, category=? WHERE id=?");
        $stmt->execute([$product_code, $name, $price, $stock, $category, $id]);
        $_SESSION['success'] = 'Product updated successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/products.php');
}
?>