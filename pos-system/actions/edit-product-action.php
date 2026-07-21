<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdminOrCoAdmin()) {
    redirect('../pages/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $product_code = $_POST['product_code'];
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';
    $original_price = $_POST['original_price'];
    $selling_price = $_POST['selling_price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';
    
    // ============================================
    // VALIDATION: STOCK LIMIT (MAX 10,000)
    // ============================================
    if ($stock > 10000) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Stock Limit Exceeded!',
            'text' => 'Stock cannot exceed 10,000 units. Current: ' . $stock
        ];
        redirect('../pages/edit-product.php?id=' . $id);
        exit();
    }
    
    if ($stock < 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Stock!',
            'text' => 'Stock cannot be negative.'
        ];
        redirect('../pages/edit-product.php?id=' . $id);
        exit();
    }
    
    // ============================================
    // VALIDATION: PRICE LIMIT (MAX 1,000,000)
    // ============================================
    if ($original_price > 1000000) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Price Limit Exceeded!',
            'text' => 'Price cannot exceed ₱1,000,000.00. Current: ₱' . number_format($original_price, 2)
        ];
        redirect('../pages/edit-product.php?id=' . $id);
        exit();
    }
    
    if ($original_price <= 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Price!',
            'text' => 'Price must be greater than 0.'
        ];
        redirect('../pages/edit-product.php?id=' . $id);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE products SET 
            product_code=?, 
            name=?, 
            description=?, 
            original_price=?, 
            selling_price=?, 
            stock=?, 
            category=? 
            WHERE id=?");
        $stmt->execute([$product_code, $name, $description, $original_price, $selling_price, $stock, $category, $id]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Product Updated!',
            'text' => 'Product "' . $name . '" updated successfully.'
        ];
        redirect('../pages/products.php');
        
    } catch(PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Error: ' . $e->getMessage()
        ];
        redirect('../pages/edit-product.php?id=' . $id);
    }
}
?>