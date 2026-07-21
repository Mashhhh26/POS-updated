<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Quantity!',
            'text' => 'Quantity must be at least 1.'
        ];
        redirect('../pages/sales.php');
        exit();
    }
    
    // Get product details
    $stmt = $pdo->prepare("SELECT id, name, original_price, selling_price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Product Not Found!',
            'text' => 'The selected product does not exist.'
        ];
        redirect('../pages/sales.php');
        exit();
    }
    
    // ============================================
    // CHECK STOCK AVAILABILITY
    // ============================================
    
    // Check if requested quantity exceeds stock
    if ($quantity > $product['stock']) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Insufficient Stock!',
            'text' => 'Only ' . $product['stock'] . ' unit(s) available for "' . $product['name'] . '".'
        ];
        redirect('../pages/sales.php');
        exit();
    }
    
    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $new_qty = $item['quantity'] + $quantity;
            
            // Check if total quantity exceeds stock
            if ($new_qty > $product['stock']) {
                $_SESSION['swal'] = [
                    'type' => 'error',
                    'title' => 'Insufficient Stock!',
                    'text' => 'You already have ' . $item['quantity'] . ' in cart. Only ' . $product['stock'] . ' units available. Max additional: ' . ($product['stock'] - $item['quantity'])
                ];
                redirect('../pages/sales.php');
                exit();
            }
            
            $item['quantity'] = $new_qty;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'original_price' => $product['original_price'],
            'selling_price' => $product['selling_price'],
            'quantity' => $quantity
        ];
    }
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Added to Cart!',
        'text' => $product['name'] . ' x' . $quantity . ' added successfully.'
    ];
    redirect('../pages/sales.php');
}
?>