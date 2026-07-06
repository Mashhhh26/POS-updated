<?php
// Start session first!
session_start();

require_once '../includes/functions.php';

// Make sure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $key = $_POST['key'] ?? null;
    $quantity = (int)$_POST['quantity'] ?? 1;
    
    if ($key !== null && isset($_SESSION['cart'][$key])) {
        $item_name = $_SESSION['cart'][$key]['name'];
        $old_qty = $_SESSION['cart'][$key]['quantity'];
        
        if ($quantity > 0) {
            $_SESSION['cart'][$key]['quantity'] = $quantity;
            
            $_SESSION['swal'] = [
                'type' => 'success',
                'title' => 'Quantity Updated!',
                'text' => '"' . $item_name . '" quantity changed from ' . $old_qty . ' to ' . $quantity . '.'
            ];
        } else {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            
            $_SESSION['swal'] = [
                'type' => 'success',
                'title' => 'Item Removed!',
                'text' => '"' . $item_name . '" has been removed from your cart.'
            ];
        }
    } else {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Item not found in cart.'
        ];
    }
}

redirect('../pages/cart.php');
?>