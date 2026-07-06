<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_code = $_POST['product_code'];
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';
    $original_price = $_POST['original_price'];
    $selling_price = $_POST['selling_price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_code, name, description, original_price, selling_price, stock, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $name, $description, $original_price, $selling_price, $stock, $category]);
        
        // Set SweetAlert success
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Product Added!',
            'text' => 'Product "' . $name . '" has been added successfully.'
        ];
    } catch(PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to add product: ' . $e->getMessage()
        ];
    }
    
    redirect('../pages/products.php');
}
?>