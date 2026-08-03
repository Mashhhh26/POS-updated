<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

$id = $_GET['id'] ?? 0;
$level = $_GET['level'] ?? 'finance';

if ($id <= 0) {
    $_SESSION['error'] = 'Invalid request ID.';
    redirect('../approvals.php');
    exit();
}

$check = $pdo->prepare("SELECT * FROM supply_requests WHERE id = ?");
$check->execute([$id]);
$request = $check->fetch();

if (!$request) {
    $_SESSION['error'] = 'Request not found.';
    redirect('../approvals.php');
    exit();
}

try {
    if ($level == 'finance') {
        // FINANCE APPROVAL
        $stmt = $pdo->prepare("
            UPDATE supply_requests 
            SET finance_approved = 1, 
                status = 'finance_approved' 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $_SESSION['success'] = 'Request approved by Finance. Awaiting Supply approval.';
    } else {
        // SUPPLY APPROVAL - Check if Finance approved first
        if ($request['finance_approved'] != 1) {
            $_SESSION['error'] = 'Cannot approve. Request must be approved by Finance first.';
            redirect('../approvals.php');
            exit();
        }
        
        if ($request['status'] == 'approved') {
            $_SESSION['error'] = 'Request already approved.';
            redirect('../approvals.php');
            exit();
        }
        
        $stmt = $pdo->prepare("
            UPDATE supply_requests 
            SET status = 'approved' 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        
        // AUTO-ADD TO PRODUCTS
        $items = $pdo->prepare("SELECT * FROM supply_items WHERE request_id = ?");
        $items->execute([$id]);
        
        $count_added = 0;
        while ($item = $items->fetch()) {
            $check_product = $pdo->prepare("SELECT id, stock FROM products WHERE name = ?");
            $check_product->execute([$item['item_name']]);
            
            if ($check_product->rowCount() > 0) {
                $product = $check_product->fetch();
                $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $product['id']]);
            } else {
                $product_code = 'P' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                $stmt = $pdo->prepare("
                    INSERT INTO products (product_code, name, price, stock, category) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $product_code,
                    $item['item_name'],
                    $item['unit_price'],
                    $item['quantity'],
                    $request['department'] ?? 'Office Supplies'
                ]);
            }
            $count_added++;
        }
        
        $_SESSION['success'] = 'Request fully approved. ' . $count_added . ' item(s) added to Products inventory.';
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

redirect('../approvals.php');
?>