<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    die('Invalid receipt ID');
}

$stmt = $pdo->prepare("SELECT s.*, u.full_name as staff_name FROM sales s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    die('Sale not found');
}

$items = $pdo->prepare("SELECT si.*, p.name FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$items->execute([$id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $sale['invoice_number']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f0f2f5; font-family: 'Courier New', monospace; }
        .receipt-container { max-width: 370px; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .receipt-header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
        .receipt-header .store-name { font-size: 18px; font-weight: bold; }
        .receipt-header .receipt-title { font-size: 14px; font-weight: bold; margin-top: 5px; }
        .receipt-header .invoice-info { font-size: 12px; display: flex; justify-content: space-between; padding: 2px 0; border-top: 1px dashed #ccc; padding-top: 5px; margin-top: 5px; }
        .receipt-item { display: flex; justify-content: space-between; padding: 2px 0; font-size: 13px; }
        .receipt-totals { border-top: 1px solid #000; padding-top: 8px; margin-top: 5px; }
        .receipt-totals .total-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 13px; }
        .receipt-totals .total-row.grand-total { font-weight: bold; font-size: 16px; }
        .vat-breakdown { border-top: 1px solid #000; padding-top: 8px; margin-top: 8px; }
        .vat-breakdown .vat-row { display: flex; justify-content: space-between; padding: 2px 0; font-size: 12px; }
        .vat-breakdown .vat-row.vat-total { font-weight: bold; border-top: 1px dashed #999; padding-top: 5px; margin-top: 3px; }
        .receipt-footer { border-top: 1px solid #000; padding-top: 10px; margin-top: 10px; text-align: center; font-size: 11px; color: #666; }
        .btn-container { max-width: 370px; margin: 10px auto; display: flex; gap: 10px; justify-content: center; }
        .btn-container .btn { flex: 1; }
        @media print { body { background: white; } .receipt-container { box-shadow: none; border-radius: 0; } .btn-container { display: none !important; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="btn-container no-print">
            <button onclick="window.print()" class="btn btn-success"><i class="bi bi-printer"></i> Print</button>
            <button onclick="window.close()" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Close</button>
            <button onclick="window.location.href='dashboard.php'" class="btn btn-primary"><i class="bi bi-house"></i> Home</button>
        </div>
        
        <div class="receipt-container" id="receipt">
            <div class="receipt-header">
                <div class="store-name">POINT OF SALE</div>
                <div class="receipt-title">SALES INVOICE</div>
                <div class="invoice-info">
                    <span><?php echo date('m/d/Y h:i A', strtotime($sale['sale_date'])); ?></span>
                    <span>#<?php echo str_pad($sale['id'], 4, '0', STR_PAD_LEFT); ?></span>
                    <span><?php echo $sale['invoice_number']; ?></span>
                </div>
            </div>
            
            <div class="receipt-items">
                <?php while($item = $items->fetch()): 
                    $vat_type = $item['vat_type'] ?? 'V';
                    $vat_label = $vat_type == 'V' ? 'V' : ($vat_type == 'E' ? 'E' : 'Z');
                ?>
                <div class="receipt-item">
                    <span><?php echo $item['quantity']; ?> <?php echo $item['name']; ?> <small><?php echo $vat_label; ?></small></span>
                    <span><?php echo number_format($item['selling_price'] * $item['quantity'], 2); ?></span>
                </div>
                <?php endwhile; ?>
            </div>
            
            <div class="receipt-totals">
                <div class="total-row"><span><?php echo $items->rowCount(); ?> Item(s)</span><span><?php echo number_format($sale['subtotal_original'] + $sale['vat_amount'], 2); ?></span></div>
                <div class="total-row grand-total"><span>TOTAL DUE</span><span><?php echo number_format($sale['grand_total'], 2); ?></span></div>
                <div class="total-row"><span>CASH</span><span><?php echo number_format($sale['payment_amount'], 2); ?></span></div>
                <div class="total-row"><span>CHANGE</span><span><?php echo number_format($sale['change_amount'], 2); ?></span></div>
            </div>
            
            <div class="vat-breakdown">
                <div class="vat-row"><span>VATable (V)</span><span><?php echo number_format($sale['vatable_total'] ?? 0, 2); ?></span></div>
                <div class="vat-row"><span>VAT-Exempt (E)</span><span><?php echo number_format($sale['vat_exempt_total'] ?? 0, 2); ?></span></div>
                <div class="vat-row"><span>VAT Zero-Rated (Z)</span><span><?php echo number_format($sale['vat_zero_total'] ?? 0, 2); ?></span></div>
                <div class="vat-row vat-total"><span>VAT</span><span><?php echo number_format($sale['vat_amount'], 2); ?></span></div>
            </div>
            
            <div class="receipt-footer">
                <div class="trans-info">Trans# <?php echo str_pad($sale['id'], 8, '0', STR_PAD_LEFT); ?> | SI# <?php echo str_pad($sale['id'], 8, '0', STR_PAD_LEFT); ?></div>
                <div style="margin-top:5px;">Thank you for your purchase!</div>
            </div>
        </div>
    </div>
    
    <?php if(isset($_GET['print']) && $_GET['print'] == '1'): ?>
    <script>window.onload = function() { window.print(); }</script>
    <?php endif; ?>
</body>
</html>