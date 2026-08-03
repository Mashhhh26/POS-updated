<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found';
        redirect('../pages/sales.php');
    }
    
    if ($product['stock'] < $quantity) {
        $_SESSION['error'] = 'Insufficient stock';
        redirect('../pages/sales.php');
    }
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $new_qty = $item['quantity'] + $quantity;
            if ($product['stock'] < $new_qty) {
                $_SESSION['error'] = 'Insufficient stock';
                redirect('../pages/sales.php');
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
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    $_SESSION['success'] = 'Added to cart';
    redirect('../pages/sales.php');
}
?>