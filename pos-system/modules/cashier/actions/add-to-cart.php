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
    
    $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error'] = 'Product not found.';
        redirect('../index.php');
        exit();
    }
    
    if ($product['stock'] < $quantity) {
        $_SESSION['error'] = 'Not enough stock. Only ' . $product['stock'] . ' available.';
        redirect('../index.php');
        exit();
    }
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $new_qty = $item['quantity'] + $quantity;
            if ($product['stock'] < $new_qty) {
                $_SESSION['error'] = 'Not enough stock.';
                redirect('../index.php');
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
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }
    
    $_SESSION['success'] = $product['name'] . ' added to cart.';
    redirect('../index.php');
}
?>