<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $key = $_POST['key'] ?? null;
    $quantity = (int)$_POST['quantity'] ?? 1;
    
    if ($key !== null && isset($_SESSION['cart'][$key])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }
}

redirect('../pages/cart.php');
?>