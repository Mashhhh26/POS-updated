<?php
require_once '../config/database.php';  // Ito na nag-start ng session
require_once '../includes/functions.php';

// No need to call session_start() again because database.php already started it

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    die('Invalid receipt ID');
}

// Get sale details
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name as staff_name 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    die('Sale not found');
}

// Get sale items
$items = $pdo->prepare("
    SELECT si.*, p.name 
    FROM sale_items si 
    JOIN products p ON si.product_id = p.id 
    WHERE si.sale_id = ?
");
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
        /* Receipt Styles */
        body {
            background: #f0f2f5;
            font-family: 'Courier New', monospace;
        }
        
        .receipt-container {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #ddd;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .receipt-header h3 {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .receipt-header .store-info {
            font-size: 12px;
            color: #666;
            margin: 0;
        }
        
        .receipt-header .invoice-number {
            font-size: 14px;
            font-weight: bold;
            color: #3498db;
            margin: 5px 0;
        }
        
        .receipt-header .date-time {
            font-size: 12px;
            color: #888;
        }
        
        .receipt-body {
            margin-bottom: 15px;
        }
        
        .receipt-info {
            font-size: 13px;
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .receipt-info .row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        
        .receipt-items {
            margin: 10px 0;
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13px;
            border-bottom: 1px dotted #eee;
        }
        
        .receipt-item .item-name {
            flex: 1;
        }
        
        .receipt-item .item-qty {
            margin: 0 10px;
            color: #666;
        }
        
        .receipt-item .item-price {
            font-weight: bold;
        }
        
        .receipt-totals {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .receipt-totals .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        
        .receipt-totals .total-row.vat {
            color: #666;
            font-size: 12px;
        }
        
        .receipt-totals .total-row.discount {
            color: #e74c3c;
        }
        
        .receipt-totals .total-row.grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            border-top: 2px solid #27ae60;
            padding-top: 8px;
            margin-top: 5px;
        }
        
        .receipt-totals .total-row.payment {
            color: #2980b9;
        }
        
        .receipt-totals .total-row.change {
            color: #27ae60;
            font-weight: bold;
        }
        
        .receipt-footer {
            text-align: center;
            border-top: 2px dashed #ddd;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 12px;
            color: #888;
        }
        
        .receipt-footer .thank-you {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .btn-container {
            max-width: 400px;
            margin: 10px auto;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .btn-container .btn {
            flex: 1;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                border-radius: 0;
                margin: 0 auto;
                padding: 15px;
                max-width: 100%;
            }
            
            .btn-container {
                display: none !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .receipt-container {
                border: none !important;
            }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .receipt-container {
                margin: 10px;
                padding: 15px;
            }
            
            .receipt-item {
                font-size: 12px;
            }
            
            .receipt-totals .total-row.grand-total {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Button -->
        <div class="btn-container no-print">
            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer"></i> Print Receipt
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Close
            </button>
            <button onclick="window.location.href='dashboard.php'" class="btn btn-primary">
                <i class="bi bi-house"></i> Dashboard
            </button>
        </div>
        
        <!-- Receipt -->
        <div class="receipt-container" id="receipt">
            <!-- Header -->
            <div class="receipt-header">
                <h3>POS System</h3>
                <p class="store-info">Point of Sale System</p>
                <p class="store-info">Tel: (02) 123-4567</p>
                <p class="invoice-number"><?php echo $sale['invoice_number']; ?></p>
                <p class="date-time"><?php echo date('F d, Y h:i A', strtotime($sale['sale_date'])); ?></p>
            </div>
            
            <!-- Info -->
            <div class="receipt-info">
                <div class="row">
                    <span>Staff:</span>
                    <span><?php echo $sale['staff_name']; ?></span>
                </div>
                <?php if($sale['discount_type'] == 'senior_citizen'): ?>
                <div class="row" style="color: #e74c3c;">
                    <span>Discount Type:</span>
                    <span>Senior Citizen (20%)</span>
                </div>
                <?php endif; ?>
            </div>
                        
            <!-- Items -->
            <div class="receipt-items">
                <div style="display:flex; justify-content:space-between; font-weight:bold; border-bottom:2px solid #333; padding-bottom:5px; margin-bottom:5px; font-size:13px;">
                    <span>Item</span>
                    <span>Qty</span>
                    <span>Amount</span>
                </div>
                <?php 
                $subtotal_orig = 0;
                while($item = $items->fetch()): 
                    $item_subtotal = $item['selling_price'] * $item['quantity'];
                    $subtotal_orig += $item['subtotal_original'];
                ?>
                <div class="receipt-item">
                    <span class="item-name"><?php echo $item['name']; ?></span>
                    <span class="item-qty">x<?php echo $item['quantity']; ?></span>
                    <span class="item-price"><?php echo number_format($item_subtotal, 2); ?></span>
                </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Totals -->
            <div class="receipt-totals">
                <div class="total-row">
                    <span>Subtotal (Original)</span>
                    <span><?php echo number_format($sale['subtotal_original'], 2); ?></span>
                </div>
                <div class="total-row vat">
                    <span>VAT (12%)</span>
                    <span><?php echo number_format($sale['vat_amount'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Subtotal (with VAT)</span>
                    <span><?php echo number_format($sale['subtotal_original'] + $sale['vat_amount'], 2); ?></span>
                </div>
                <?php if($sale['discount_amount'] > 0): ?>
                <div class="total-row discount">
                    <span>Senior Discount (20%)</span>
                    <span>-<?php echo number_format($sale['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="total-row grand-total">
                    <span>TOTAL</span>
                    <span><?php echo number_format($sale['grand_total'], 2); ?></span>
                </div>
                <div class="total-row payment">
                    <span>Payment</span>
                    <span><?php echo number_format($sale['payment_amount'], 2); ?></span>
                </div>
                <div class="total-row change">
                    <span>Change</span>
                    <span><?php echo number_format($sale['change_amount'], 2); ?></span>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="receipt-footer">
                <p class="thank-you">Thank you for your purchase!</p>
                <p>Please come again!</p>
                <p style="font-size:10px; margin-top:10px;">
                    This serves as your official receipt<br>
                    <?php echo date('Y-m-d H:i:s', strtotime($sale['sale_date'])); ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Print auto if requested -->
    <?php if(isset($_GET['print']) && $_GET['print'] == '1'): ?>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
    <?php endif; ?>
</body>
</html>