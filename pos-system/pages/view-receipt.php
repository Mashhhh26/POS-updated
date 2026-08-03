<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT s.*, u.full_name as staff_name FROM sales s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    die('Receipt not found');
}

$items = $pdo->prepare("SELECT si.*, p.name FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$items->execute([$id]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body { font-family: monospace; padding: 20px; max-width: 400px; margin: auto; }
        .receipt { border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: white; }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 10px; }
        .item { display: flex; justify-content: space-between; padding: 5px 0; }
        .total { border-top: 2px solid #333; padding-top: 10px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h3>POS System</h3>
        <p><?php echo $sale['invoice_number']; ?></p>
        <small><?php echo date('M d, Y h:i A', strtotime($sale['sale_date'])); ?></small>
    </div>
    
    <?php while($item = $items->fetch()): ?>
    <div class="item">
        <span><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></span>
        <span><?php echo number_format($item['subtotal'], 2); ?></span>
    </div>
    <?php endwhile; ?>
    
    <div class="total">
        <div class="item"><span>Total</span><span><?php echo number_format($sale['grand_total'], 2); ?></span></div>
        <div class="item"><span>Payment</span><span><?php echo number_format($sale['payment_amount'], 2); ?></span></div>
        <div class="item"><span>Change</span><span><?php echo number_format($sale['change_amount'], 2); ?></span></div>
    </div>
    
    <div class="footer">Thank you for your purchase</div>
</div>
<button onclick="window.print()" class="btn btn-primary no-print mt-3">Print</button>
<button onclick="window.close()" class="btn btn-secondary no-print mt-3">Close</button>
</body>
</html>