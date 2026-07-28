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
    
    $payment_amount = (float)$_POST['payment_amount'];
    $grand_total = (float)$_POST['grand_total'];
    $senior_id = trim($_POST['senior_id'] ?? '');
    
    // ============================================
    // VALIDATE SENIOR ID
    // ============================================
    if (isset($_SESSION['is_senior']) && $_SESSION['is_senior'] == 1) {
        if (empty($senior_id)) {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Senior ID Required!',
                'text' => 'Please enter your Senior Citizen ID number.'
            ];
            redirect('../pages/cart.php');
            exit();
        }
        
        if (!preg_match('/^[0-9]{4}-[0-9]{6}$/', $senior_id)) {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Invalid Senior ID Format!',
                'text' => 'Please use format: YYYY-XXXXXX (e.g., 2026-123456)'
            ];
            redirect('../pages/cart.php');
            exit();
        }
        
        $_SESSION['senior_id'] = $senior_id;
    }
    
    if ($payment_amount < $grand_total) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Insufficient Payment!',
            'text' => 'Please enter the correct amount. Minimum: ' . formatCurrency($grand_total)
        ];
        redirect('../pages/cart.php');
        exit();
    }
    
    // ============================================
    // FINAL STOCK CHECK
    // ============================================
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Error!',
                'text' => 'Product not found: ' . $item['name']
            ];
            redirect('../pages/cart.php');
            exit();
        }
        
        if ($item['quantity'] > $product['stock']) {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Stock Changed!',
                'text' => 'Sorry, "' . $item['name'] . '" only has ' . $product['stock'] . ' unit(s) available.'
            ];
            redirect('../pages/cart.php');
            exit();
        }
    }
    
    // ============================================
    // COMPUTE TOTALS
    // ============================================
    $subtotal_original = 0;
    $vatable_total = 0;
    $vat_exempt_total = 0;
    $vat_zero_total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare("SELECT vat_type FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();
        $vat_type = $product['vat_type'] ?? 'V';
        
        $item_original = $item['original_price'] * $item['quantity'];
        $subtotal_original += $item_original;
        
        if ($vat_type == 'V') {
            $vatable_total += $item_original;
        } elseif ($vat_type == 'E') {
            $vat_exempt_total += $item_original;
        } elseif ($vat_type == 'Z') {
            $vat_zero_total += $item_original;
        }
    }
    
    $vat = $vatable_total * 0.12;
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
        
        $stmt = $pdo->prepare("INSERT INTO sales (
            invoice_number, user_id, 
            subtotal_original, vat_amount, 
            vatable_total, vat_exempt_total, vat_zero_total,
            discount_amount, discount_type, senior_id,
            grand_total, payment_amount, change_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $invoice, $_SESSION['user_id'],
            $subtotal_original, $vat,
            $vatable_total, $vat_exempt_total, $vat_zero_total,
            $discount, $discount_type, $senior_id,
            $grand_total, $payment_amount, $change
        ]);
        $sale_id = $pdo->lastInsertId();
        
        foreach ($_SESSION['cart'] as $item) {
            $subtotal_orig = $item['original_price'] * $item['quantity'];
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, original_price, selling_price, subtotal_original) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['original_price'], $item['selling_price'], $subtotal_orig]);
            
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['id']]);
        }
        
        $pdo->commit();
        
        $_SESSION['cart'] = [];
        $_SESSION['is_senior'] = 0;
        $_SESSION['senior_id'] = '';
        
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