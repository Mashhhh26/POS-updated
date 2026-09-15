<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

$sale_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$sale_id) {
    die('Sale ID required');
}

$db = getDB();

$sale = $db->prepare("
    SELECT s.*, u.full_name as cashier 
    FROM sales s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$sale->execute([$sale_id]);
$saleData = $sale->fetch();

if (!$saleData) {
    die('Sale not found');
}

$items = $db->prepare("
    SELECT si.*, p.product_name 
    FROM sales_items si
    JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$items->execute([$sale_id]);
$saleItems = $items->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; background: white; }
        .receipt { text-align: center; }
        .receipt-header { border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .receipt-header h2 { margin: 0; font-size: 18px; }
        .receipt-header p { margin: 3px 0; }
        .receipt-body { text-align: left; }
        .receipt-body table { width: 100%; border-collapse: collapse; }
        .receipt-body th, .receipt-body td { padding: 4px 0; border-bottom: 1px dotted #ddd; }
        .receipt-body th { text-align: left; font-weight: bold; }
        .receipt-body td { text-align: left; }
        .receipt-body td:last-child { text-align: right; }
        .receipt-footer { border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; text-align: center; }
        .total-row { font-weight: bold; font-size: 14px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        @media print { body { margin: 0; padding: 5mm; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="receipt" id="receiptContent">
        <div class="receipt-header">
            <h2>POS SYSTEM</h2>
            <p><?php echo date('F d, Y'); ?></p>
            <p><?php echo date('h:i:s A'); ?></p>
            <hr>
            <p><strong>Receipt #:</strong> <?php echo $saleData['invoice_number']; ?></p>
            <p><strong>Cashier:</strong> <?php echo $saleData['cashier']; ?></p>
            <p><strong>Customer:</strong> <?php echo $saleData['customer_name'] ?: 'Walk-in Customer'; ?></p>
        </div>

        <div class="receipt-body">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($saleItems as $item): ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="receipt-footer">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">₱<?php echo number_format($saleData['total_amount'], 2); ?></td>
                </tr>
                <?php if ($saleData['discount'] > 0): ?>
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-₱<?php echo number_format($saleData['discount'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($saleData['tax'] > 0): ?>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">₱<?php echo number_format($saleData['tax'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="text-right">₱<?php echo number_format($saleData['total_amount'] - $saleData['discount'] + $saleData['tax'], 2); ?></td>
                </tr>
                <tr>
                    <td>Payment:</td>
                    <td class="text-right"><?php echo ucfirst($saleData['payment_method']); ?></td>
                </tr>
            </table>
            <hr>
            <p><strong>Thank you for your purchase!</strong></p>
            <p style="font-size: 10px; color: #999;">This is a computer-generated receipt.</p>
        </div>
    </div>

    <div class="no-print text-center" style="margin-top: 20px;">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i> Print</button>
        <button onclick="window.close()" class="btn btn-secondary"><i class="fas fa-times me-1"></i> Close</button>
    </div>

    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <script>
        window.onload = function() {
            setTimeout(() => window.print(), 500);
        };
    </script>
</body>
</html>