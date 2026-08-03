<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_SESSION['cart'])) {
        $_SESSION['error'] = 'Cart is empty.';
        redirect('../index.php');
        exit();
    }
    
    $payment = (float)$_POST['payment'];
    $total = (float)$_POST['total'];
    
    if ($payment < $total) {
        $_SESSION['error'] = 'Insufficient payment.';
        redirect('../index.php');
        exit();
    }
    
    if ($payment > 1000000) {
        $_SESSION['error'] = 'Payment exceeds maximum limit of 1,000,000.00';
        redirect('../index.php');
        exit();
    }
    
    $invoice = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
    $change = $payment - $total;
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO sales (invoice_number, user_id, grand_total, payment_amount, change_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$invoice, $_SESSION['user_id'], $total, $payment, $change]);
        $sale_id = $pdo->lastInsertId();
        
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price'], $subtotal]);
            
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        $pdo->commit();
        
        $_SESSION['cart'] = [];
        $_SESSION['last_invoice'] = $invoice;
        $_SESSION['last_sale_id'] = $sale_id;
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Sale Completed',
            'text' => 'Invoice: ' . $invoice . ' | Change: ' . formatCurrency($change)
        ];
        redirect('../index.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Transaction failed: ' . $e->getMessage();
        redirect('../index.php');
    }
}
?>