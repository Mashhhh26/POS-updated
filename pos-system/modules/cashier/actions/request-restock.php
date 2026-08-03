<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $department = $_POST['department'] ?? 'Store';
    
    // Get product details
    $stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found.';
        redirect('../index.php');
        exit();
    }
    
    // Generate request number
    $request_number = 'SR-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Calculate total amount
    $unit_price = $product['price'];
    $total_amount = $quantity * $unit_price;
    
    try {
        $pdo->beginTransaction();
        
        // Insert supply request
        $stmt = $pdo->prepare("
            INSERT INTO supply_requests (request_number, requested_by, department, total_amount) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$request_number, $_SESSION['user_id'], $department, $total_amount]);
        $request_id = $pdo->lastInsertId();
        
        // Insert supply item
        $stmt = $pdo->prepare("
            INSERT INTO supply_items (request_id, item_name, quantity, unit_price, total_price) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $request_id,
            $product['name'],
            $quantity,
            $unit_price,
            $total_amount
        ]);
        
        $pdo->commit();
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Restock Request Submitted!',
            'text' => 'Request #' . $request_number . ' has been sent for approval.'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../index.php');
}
?>