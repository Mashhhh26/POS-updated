<?php
session_start();

require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_SESSION['cart'])) {
        $_SESSION['swal'] = [
            'type' => 'warning',
            'title' => 'Cart Empty!',
            'text' => 'Please add items to cart first.'
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // REMOVED: $customer_name = trim($_POST['customer_name'] ?? '');
    $payment_amount = (float)$_POST['payment_amount'];
    $grand_total = (float)$_POST['grand_total'];
    
    if ($payment_amount < $grand_total) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Insufficient Payment!',
            'text' => 'Please enter the correct amount. Minimum: ' . formatCurrency($grand_total)
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // Calculate totals from original prices
    $subtotal_original = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal_original += $item['original_price'] * $item['quantity'];
    }
    
    $vat = $subtotal_original * 0.12;
    $subtotal_selling = $subtotal_original + $vat;
    $discount = 0;
    $discount_type = 'none';
    
    if (isset($_SESSION['is_senior']) && $_SESSION['is_senior'] == 1) {
        $discount = $subtotal_selling * 0.20;
        $discount_type = 'senior_citizen';
    }
    
    $grand_total = $subtotal_selling - $discount;
    $change = $payment_amount - $grand_total;
    $invoice = generateInvoiceNumber();
    
    try {
        $pdo->beginTransaction();
        
        // Insert sale - REMOVED customer_name
        $stmt = $pdo->prepare("INSERT INTO sales (invoice_number, user_id, customer_name, subtotal_original, vat_amount, discount_amount, discount_type, grand_total, payment_amount, change_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$invoice, $_SESSION['user_id'], 'Walk-in', $subtotal_original, $vat, $discount, $discount_type, $grand_total, $payment_amount, $change]);
        $sale_id = $pdo->lastInsertId();
        
        // Insert sale items and update stock
        foreach ($_SESSION['cart'] as $item) {
            $subtotal_orig = $item['original_price'] * $item['quantity'];
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, original_price, selling_price, subtotal_original) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['original_price'], $item['selling_price'], $subtotal_orig]);
            
            // Update stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        $pdo->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        $_SESSION['is_senior'] = 0;
        
        // Save for receipt display
        $_SESSION['last_invoice'] = $invoice;
        $_SESSION['last_sale_id'] = $sale_id;
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Sale Completed!',
            'text' => 'Invoice: ' . $invoice . ' | Change: ' . formatCurrency($change)
        ];
        redirect('../pages/sales.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Transaction Failed!',
            'text' => 'Error: ' . $e->getMessage()
        ];
        redirect('../pages/cart.php');
    }
} else {
    redirect('../pages/sales.php');
}
?>