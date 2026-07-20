<?php
// Start session first!
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

// Make sure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
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
    }
    
    if ($product['stock'] < $quantity) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Insufficient Stock!',
            'text' => 'Only ' . $product['stock'] . ' units available.'
        ];
        redirect('../pages/sales.php');
    }
    
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $new_qty = $item['quantity'] + $quantity;
            if ($product['stock'] < $new_qty) {
                $_SESSION['swal'] = [
                    'type' => 'error',
                    'title' => 'Insufficient Stock!',
                    'text' => 'Only ' . $product['stock'] . ' units available.'
                ];
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