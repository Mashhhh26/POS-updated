<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $key = $_POST['key'] ?? null;
    $quantity = (int)$_POST['quantity'] ?? 1;
    
    if ($key === null || !isset($_SESSION['cart'][$key])) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Item not found in cart.'
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // Get product details to check stock
    $product_id = $_SESSION['cart'][$key]['id'];
    $item_name = $_SESSION['cart'][$key]['name'];
    
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Product not found in database.'
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // ============================================
    // CHECK STOCK AVAILABILITY
    // ============================================
    
    // If quantity is 0 or negative, remove from cart
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$key]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Item Removed!',
            'text' => '"' . $item_name . '" has been removed from your cart.'
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // Check if requested quantity exceeds available stock
    if ($quantity > $product['stock']) {
        // Set to max available stock
        $max_available = $product['stock'];
        
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Insufficient Stock!',
            'text' => 'Only ' . $max_available . ' unit(s) available for "' . $item_name . '". Setting to maximum available.'
        ];
        
        // Update to maximum available stock
        $_SESSION['cart'][$key]['quantity'] = $max_available;
        redirect('../pages/cart.php');
        exit();
    }
    
    // Update quantity
    $_SESSION['cart'][$key]['quantity'] = $quantity;
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Quantity Updated!',
        'text' => '"' . $item_name . '" quantity updated to ' . $quantity . '.'
    ];
    redirect('../pages/cart.php');
}
?>