<?php
session_start();

require_once '../includes/functions.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$key = $_GET['key'] ?? null;

if ($key !== null && isset($_SESSION['cart'][$key])) {
    $item_name = $_SESSION['cart'][$key]['name'];
    
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    
    $_SESSION['swal'] = [
        'type' => 'success',
        'title' => 'Item Removed!',
        'text' => '"' . $item_name . '" has been removed from your cart.'
    ];
} else {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Item not found in cart.'
    ];
}

// Check where to redirect (sales or cart)
$redirect_to = $_GET['redirect'] ?? 'cart.php';
redirect('../pages/' . $redirect_to);
?>