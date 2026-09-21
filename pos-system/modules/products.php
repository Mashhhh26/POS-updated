<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('view_products')) {
    logActivity("Access denied: products.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (isset($_POST['edit_product'])) {
            require_permission('manage_products');
            $id=(int)($_POST['product_id']??0);
            $code=trim($_POST['product_code']??''); $name=trim($_POST['product_name']??'');
            $price=$_POST['price']??''; $cost=$_POST['cost']??''; $category=trim($_POST['category']??'');
            $threshold=$_POST['low_stock_threshold']??5;
            if($id<=0||$code===''||$name==='') throw new Exception('Product code and name are required.');
            if(!valid_money($price,false)||!valid_money($cost,true)||!valid_qty($threshold,true)) throw new Exception('Invalid product values.');
            $dup=$db->prepare('SELECT id FROM products WHERE product_code=? AND id<>? LIMIT 1'); $dup->execute([$code,$id]);
            if($dup->fetch()) throw new Exception('Product code already exists.');
            $stmt=$db->prepare('UPDATE products SET product_code=?,product_name=?,description=?,price=?,cost=?,category=?,low_stock_threshold=? WHERE id=?');
            $stmt->execute([$code,$name,sanitize($_POST['description']??''),(float)$price,(float)$cost,$category,(int)$threshold,$id]);
            logActivity("Updated product #{$id}: {$name}"); $success_message='Product updated successfully.';
        } elseif(isset($_POST['delete_product'])) {
            require_permission('manage_products');
            $id=(int)($_POST['product_id']??0);
            if($id<=0) throw new Exception('Invalid product.');
            $refs=$db->prepare('SELECT (SELECT COUNT(*) FROM sales_items WHERE product_id=?) + (SELECT COUNT(*) FROM stock_movements WHERE product_id=?) AS c');
            $refs->execute([$id,$id]);
            if((int)$refs->fetchColumn()>0) throw new Exception('This product has transaction history and cannot be deleted. Keep it for audit/history.');
            $db->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
            logActivity("Deleted product #{$id}"); $success_message='Product deleted successfully.';
        }
    } catch(Throwable $e) { $error_message=$e->getMessage(); }
}

if($_SERVER['REQUEST_METHOD']==='POST' && $isAjax){header('Content-Type: application/json');echo json_encode(['success'=>$error_message===null,'message'=>$error_message??$success_message]);exit();}

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'low_stock' => $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold")->fetchColumn(),
    'out_of_stock' => $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity = 0")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i> Products</h4>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div id="flash-message" data-type="success" data-message="<?php echo htmlspecialchars($success_message); ?>"></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div id="flash-message" data-type="error" data-message="<?php echo htmlspecialchars($error_message); ?>"></div>
            <?php endif; ?>
            
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Products</h6>
                            <h3 class="fw-bold text-primary"><?php echo $stats['total']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Low Stock</h6>
                            <h3 class="fw-bold text-warning"><?php echo $stats['low_stock']; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Out of Stock</h6>
                            <h3 class="fw-bold text-danger"><?php echo $stats['out_of_stock']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Cost</th>
                                <th>Stock</th>
                                <th>Threshold</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $product): ?>
                            <tr id="product-row-<?php echo $product['id']; ?>">
                                <td><span class="badge bg-secondary"><?php echo $product['product_code']; ?></span></td>
                                <td><strong><?php echo $product['product_name']; ?></strong></td>
                                <td><?php echo $product['category'] ?: '-'; ?></td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>₱<?php echo number_format($product['cost'], 2); ?></td>
                                <td>
                                    <?php
                                    $stockClass = 'success';
                                    if ($product['stock_quantity'] <= 0) {
                                        $stockClass = 'danger';
                                    } elseif ($product['stock_quantity'] <= $product['low_stock_threshold']) {
                                        $stockClass = 'warning';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $stockClass; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td><?php echo $product['low_stock_threshold']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-product" data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $product['id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    
    <script>
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('saveProductBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            btn.disabled = true;
        });
        
        
    </script>

<script>
const productData = <?php echo json_encode($products, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
const csrfToken = <?php echo json_encode(csrf_token()); ?>;
document.querySelectorAll('.edit-product').forEach(btn=>btn.addEventListener('click',()=>{
 const p=productData.find(x=>Number(x.id)===Number(btn.dataset.id)); if(!p)return;
 Swal.fire({title:'Edit Product',html:`
 <input id="sw-code" class="swal2-input" placeholder="Product Code" value="${escapeHtml(p.product_code)}">
 <input id="sw-name" class="swal2-input" placeholder="Product Name" value="${escapeHtml(p.product_name)}">
 <input id="sw-category" class="swal2-input" placeholder="Category" value="${escapeHtml(p.category||'')}">
 <input id="sw-price" type="number" min="0.01" step="0.01" class="swal2-input" placeholder="Selling Price" value="${p.price}">
 <input id="sw-cost" type="number" min="0" step="0.01" class="swal2-input" placeholder="Cost" value="${p.cost||0}">
 <input id="sw-threshold" type="number" min="0" step="1" class="swal2-input" placeholder="Low Stock Threshold" value="${p.low_stock_threshold}">
 <textarea id="sw-desc" class="swal2-textarea" placeholder="Description">${escapeHtml(p.description||'')}</textarea>`,
 showCancelButton:true,confirmButtonText:'Save Changes',focusConfirm:false,
 preConfirm:()=>{const f=new FormData();f.append('csrf_token',csrfToken);f.append('edit_product','1');f.append('product_id',p.id);
 ['code','name','category','price','cost','threshold','desc'].forEach(k=>f.append(''+({code:'product_code',name:'product_name',category:'category',price:'price',cost:'cost',threshold:'low_stock_threshold',desc:'description'}[k]),document.getElementById('sw-'+k).value)); return fetch('',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:f}).then(r=>r.json()).then(d=>{if(!d.success){Swal.showValidationMessage(d.message||'Unable to save changes.');return false;}return true;}).catch(()=>{Swal.showValidationMessage('Request failed');});}
 }).then(r=>{if(r.isConfirmed) location.reload();});
}));
function escapeHtml(v){return String(v).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
document.querySelectorAll('.delete-product').forEach(btn=>btn.addEventListener('click',()=>{
 confirmAction(`Delete <strong>${escapeHtml(btn.dataset.name)}</strong>? Products with transaction history cannot be deleted.`,'Delete',()=>{
  const f=new FormData();f.append('csrf_token',csrfToken);f.append('delete_product','1');f.append('product_id',btn.dataset.id);
  fetch('',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'},body:f}).then(r=>r.json()).then(d=>{if(d.success)location.reload();else showError(d.message||'Delete failed.');}).catch(()=>showError('Delete failed.'));
 });
}));
</script>

</body>
</html>