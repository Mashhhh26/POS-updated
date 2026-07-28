<?php
session_start();

require_once '../includes/functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$item_count = count($_SESSION['cart']);

if ($item_count > 0) {
    $_SESSION['cart'] = [];
    $_SESSION['is_senior'] = 0;
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Cart Cleared!',
        'text' => 'All ' . $item_count . ' item(s) have been removed from your cart.'
    ];
} else {
    $_SESSION['swal'] = [
        'type' => 'warning',
        'title' => 'Cart Already Empty!',
        'text' => 'Your cart is already empty.'
    ];
}

// Check where to redirect (sales or cart)
$redirect_to = $_GET['redirect'] ?? 'cart.php';
redirect('../pages/' . $redirect_to);
?>