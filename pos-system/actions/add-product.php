<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_code = $_POST['product_code'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_code, name, price, stock, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $name, $price, $stock, $category]);
        $_SESSION['success'] = 'Product added successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../pages/products.php');
}
?>