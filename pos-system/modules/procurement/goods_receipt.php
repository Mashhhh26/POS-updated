<?php
session_start();
require_once '../../config/database.php';
require_permission('receive_goods');
verify_csrf();
$db = getDB();
$error_message = $success_message = null;
$selectedPoId = (int)($_GET['po_id'] ?? $_POST['po_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receive_goods'])) {
    try {
        $po = (int)($_POST['po_id'] ?? 0);
        if ($po <= 0) throw new Exception('Invalid purchase order.');

        $db->beginTransaction();
        $q = $db->prepare('SELECT * FROM purchase_orders WHERE id=? FOR UPDATE');
        $q->execute([$po]);
        $poData = $q->fetch();
        if (!$poData || !in_array($poData['status'], ['approved','partially_received'], true)) {
            throw new Exception('PO is not available for receiving.');
        }

        $grn = 'GRN-' . date('Ymd') . '-' . random_int(1000, 9999);
        $db->prepare("INSERT INTO goods_receipts(grn_number,po_id,received_by,delivery_note,notes,status) VALUES(?,?,?,?,?,'completed')")
            ->execute([$grn, $po, $_SESSION['user_id'], sanitize($_POST['delivery_note'] ?? ''), sanitize($_POST['notes'] ?? '')]);
        $grId = $db->lastInsertId();

        $q = $db->prepare('SELECT * FROM po_items WHERE po_id=? FOR UPDATE');
        $q->execute([$po]);
        $any = false;

        foreach ($q->fetchAll() as $item) {
            $x = $_POST['items'][$item['id']] ?? [];
            $received = (int)($x['received'] ?? 0);
            $accepted = (int)($x['accepted'] ?? 0);
            $rejected = (int)($x['rejected'] ?? 0);
            $remaining = max(0, (int)$item['quantity'] - (int)$item['received_quantity']);

            if (!valid_qty($received, true) || !valid_qty($accepted, true) || !valid_qty($rejected, true) || $accepted + $rejected !== $received || $received > $remaining) {
                throw new Exception('Invalid receiving quantity for ' . $item['item_description'] . '; remaining ' . $remaining . '.');
            }
            if ($received === 0) continue;
            $any = true;

            $db->prepare('INSERT INTO gr_items(goods_receipt_id,po_item_id,received_quantity,accepted_quantity,rejected_quantity,remarks) VALUES(?,?,?,?,?,?)')
                ->execute([$grId, $item['id'], $received, $accepted, $rejected, sanitize($x['remarks'] ?? '')]);
            $db->prepare('UPDATE po_items SET received_quantity=received_quantity+? WHERE id=?')->execute([$received, $item['id']]);

            if ($accepted > 0) {
                $pid = (int)($item['product_id'] ?? 0);

                // If procurement has not linked this PO item yet, create a catalog product.
                if ($pid <= 0) {
                    $name = trim($item['item_description']);
                    $existing = $db->prepare('SELECT id FROM products WHERE LOWER(product_name)=LOWER(?) LIMIT 1');
                    $existing->execute([$name]);
                    $pid = (int)($existing->fetchColumn() ?: 0);

                    if ($pid <= 0) {
                        $code = 'PO-' . $po . '-' . $item['id'];
                        $db->prepare("INSERT INTO products(product_code,product_name,description,price,cost,stock_quantity,category,low_stock_threshold) VALUES(?,?,?,?,?,0,'Computer Parts',5)")
                            ->execute([$code, $name, 'Catalog item created from Purchase Order ' . $poData['po_number'], (float)$item['unit_price'], (float)$item['unit_price']]);
                        $pid = (int)$db->lastInsertId();
                    }
                    $db->prepare('UPDATE po_items SET product_id=? WHERE id=?')->execute([$pid, $item['id']]);
                }

                $p = $db->prepare('SELECT id FROM products WHERE id=? FOR UPDATE');
                $p->execute([$pid]);
                if (!$p->fetch()) throw new Exception('Linked product not found.');

                $db->prepare('UPDATE products SET stock_quantity=stock_quantity+?,cost=? WHERE id=?')
                    ->execute([$accepted, (float)$item['unit_price'], $pid]);
                $db->prepare("INSERT INTO stock_movements(product_id,user_id,type,quantity,reference,notes) VALUES(?,?,?,?,?,?)")
                    ->execute([$pid, $_SESSION['user_id'], 'in', $accepted, $grn, 'Accepted goods from PO ' . $poData['po_number']]);
            }
        }

        if (!$any) throw new Exception('Receive at least one item.');

        $c = $db->prepare('SELECT COUNT(*) FROM po_items WHERE po_id=? AND received_quantity<quantity');
        $c->execute([$po]);
        $fullyReceived = (int)$c->fetchColumn() === 0;
        $status = $fullyReceived ? 'closed' : 'partially_received';
        $db->prepare('UPDATE purchase_orders SET status=? WHERE id=?')->execute([$status, $po]);

        // Create a payable AP invoice record once the first goods receipt is posted.
        // Finance can later replace the generated number with the supplier's actual invoice number.
        $exists = $db->prepare('SELECT id FROM invoices WHERE po_id=? AND status NOT IN ("rejected") LIMIT 1');
        $exists->execute([$po]);
        if ($fullyReceived && !$exists->fetch()) {
            $apNo = 'AP-' . $poData['po_number'];
            $db->prepare("INSERT INTO invoices(invoice_number,po_id,supplier_id,invoice_date,total_amount,tax_amount,net_amount,status,match_status,notes) VALUES(?,?,?,?,?,?,?,'pending_payment','pending',?)")
                ->execute([$apNo,$po,$poData['supplier_id'],date('Y-m-d'),(float)$poData['total_amount'],(float)$poData['tax'],(float)$poData['grand_total'],'Auto-created from GRN '.$grn.'. Replace with the supplier invoice number once received.']);
        }
        $db->commit();

        logActivity("Received goods for PO #{$po} - {$grn}" . ($fullyReceived ? ' and closed PO' : ''));
        header('Location: ' . BASE_PATH . 'modules/procurement/goods_receipt.php?success=' . urlencode("Goods received: {$grn}" . ($fullyReceived ? ' · PO closed automatically.' : '')));
        exit();
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $error_message = $e->getMessage();
    }
}

$pending = $db->query("SELECT po.*,s.company_name FROM purchase_orders po JOIN suppliers s ON s.id=po.supplier_id WHERE po.status IN('approved','partially_received') ORDER BY po.created_at DESC")->fetchAll();
$selected = null; $selectedItems = [];
if ($selectedPoId > 0) {
    $q = $db->prepare("SELECT po.*,s.company_name FROM purchase_orders po JOIN suppliers s ON s.id=po.supplier_id WHERE po.id=?");
    $q->execute([$selectedPoId]); $selected = $q->fetch();
    if ($selected) { $q = $db->prepare('SELECT * FROM po_items WHERE po_id=? ORDER BY id'); $q->execute([$selectedPoId]); $selectedItems = $q->fetchAll(); }
}
$success_message = $_GET['success'] ?? $success_message;
?>
<!doctype html><html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Goods Receipt</title>
<link rel="stylesheet" href="<?php echo BASE_PATH;?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913"><link rel="stylesheet" href="<?php echo BASE_PATH;?>assets/vendor/fontawesome/all.min.css?v=20260913"><link rel="stylesheet" href="<?php echo BASE_PATH;?>assets/css/custom.css?v=20260913"></head><body>
<?php include BASE_PATH.'includes/header.php';?><div class="d-flex"><?php include BASE_PATH.'includes/sidebar.php';?><main class="main-content flex-grow-1 p-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><div class="text-primary small fw-bold text-uppercase" style="letter-spacing:.08em">Procurement</div><h4 class="mb-1 fw-bold"><i class="fas fa-warehouse me-2 text-primary"></i>Goods Receipt</h4><div class="text-muted">Receive accepted goods into Inventory. Fully received POs close automatically.</div></div></div>
<?php if($success_message):?><div id="flash-message" data-type="success" data-message="<?php echo h($success_message);?>"></div><?php endif;?>
<?php if($error_message):?><div id="flash-message" data-type="error" data-message="<?php echo h($error_message);?>"></div><?php endif;?>
<?php if($selected): ?>
<div class="card border-0 mb-4"><div class="card-header bg-transparent p-3 d-flex justify-content-between"><div><strong><?php echo h($selected['po_number']);?></strong><div class="small text-muted"><?php echo h($selected['company_name']);?></div></div><span class="badge bg-primary align-self-start"><?php echo h(str_replace('_',' ',$selected['status']));?></span></div>
<form method="POST"><div class="card-body"><input type="hidden" name="csrf_token" value="<?php echo h(csrf_token());?>"><input type="hidden" name="po_id" value="<?php echo (int)$selected['id'];?>">
<div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Delivery Note</label><input name="delivery_note" class="form-control" maxlength="100"></div><div class="col-md-6"><label class="form-label">Notes</label><input name="notes" class="form-control" maxlength="500"></div></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Item</th><th class="text-center">Ordered</th><th class="text-center">Already Received</th><th class="text-center">Remaining</th><th style="min-width:110px">Receive</th><th style="min-width:110px">Accepted</th><th style="min-width:110px">Rejected</th><th>Remarks</th></tr></thead><tbody>
<?php foreach($selectedItems as $item):$remaining=max(0,(int)$item['quantity']-(int)$item['received_quantity']);?><tr><td><strong><?php echo h($item['item_description']);?></strong><div class="small text-muted">₱<?php echo number_format($item['unit_price'],2);?> each</div></td><td class="text-center"><?php echo (int)$item['quantity'];?></td><td class="text-center"><?php echo (int)$item['received_quantity'];?></td><td class="text-center"><span class="badge bg-<?php echo $remaining?'warning':'success';?>"><?php echo $remaining;?></span></td><td><input type="number" min="0" max="<?php echo $remaining;?>" name="items[<?php echo $item['id'];?>][received]" class="form-control receive-input" value="0" data-max="<?php echo $remaining;?>"></td><td><input type="number" min="0" max="<?php echo $remaining;?>" name="items[<?php echo $item['id'];?>][accepted]" class="form-control accepted-input" value="0"></td><td><input type="number" min="0" max="<?php echo $remaining;?>" name="items[<?php echo $item['id'];?>][rejected]" class="form-control rejected-input" value="0"></td><td><input name="items[<?php echo $item['id'];?>][remarks]" class="form-control"></td></tr><?php endforeach;?></tbody></table></div></div>
<div class="card-footer bg-transparent d-flex justify-content-between"><a href="goods_receipt.php" class="btn btn-outline-secondary">Back</a><button class="btn btn-primary" name="receive_goods" type="submit"><i class="fas fa-box-open me-1"></i> Post Goods Receipt</button></div></form></div>
<?php elseif($selectedPoId>0):?><div class="alert alert-warning">Purchase order not found or is no longer available for receiving.</div><?php endif;?>
<div class="card border-0"><div class="card-header bg-transparent p-3"><strong><i class="fas fa-truck-loading me-2 text-primary"></i>Approved POs awaiting receipt</strong></div><div class="card-body">
<?php if(empty($pending)):?><div class="text-center text-muted py-4">No approved purchase orders are waiting for receipt.</div><?php else: foreach($pending as $po):?><div class="d-flex flex-wrap justify-content-between align-items-center border rounded-3 p-3 mb-2"><div><strong><?php echo h($po['po_number']);?></strong><div class="small text-muted"><?php echo h($po['company_name']);?> · ₱<?php echo number_format($po['grand_total'],2);?></div></div><a class="btn btn-sm btn-primary" href="?po_id=<?php echo (int)$po['id'];?>"><i class="fas fa-box-open me-1"></i> Receive</a></div><?php endforeach;endif;?></div></div>
</main></div><script src="<?php echo BASE_PATH;?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script><script src="<?php echo BASE_PATH;?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script><script src="<?php echo BASE_PATH;?>assets/js/script.js?v=20260913"></script>
<script>document.querySelectorAll('.receive-input').forEach(input=>input.addEventListener('input',()=>{const row=input.closest('tr');const max=parseInt(input.dataset.max||'0',10);let v=Math.max(0,Math.min(max,parseInt(input.value||'0',10)));input.value=v;row.querySelector('.accepted-input').value=v;row.querySelector('.rejected-input').value=0;}));document.querySelectorAll('.accepted-input,.rejected-input').forEach(input=>input.addEventListener('input',()=>{const row=input.closest('tr'),rec=parseInt(row.querySelector('.receive-input').value||'0',10);let a=Math.max(0,parseInt(row.querySelector('.accepted-input').value||'0',10)),r=Math.max(0,parseInt(row.querySelector('.rejected-input').value||'0',10));if(a+r>rec){if(input.classList.contains('accepted-input'))a=Math.max(0,rec-r);else r=Math.max(0,rec-a);}row.querySelector('.accepted-input').value=a;row.querySelector('.rejected-input').value=r;}));</script>
</body></html>
