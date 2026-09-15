<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . 'login.php');
    exit();
}

if (!hasPermission('process_sales')) {
    logActivity("Access denied: sales.php - User: " . $_SESSION['username']);
    header('Location: ' . BASE_PATH . 'index.php?error=access_denied');
    exit();
}

$db = getDB();

$customers = $db->query("SELECT id, customer_code, first_name, last_name, company FROM customers ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_sale'])) {
    $items=json_decode($_POST['items']??'[]',true);$discount=(float)($_POST['discount']??0);$tax=(float)($_POST['tax']??0);
    if(!is_array($items)||count($items)<1||count($items)>100){echo json_encode(['success'=>false,'message'=>'Sale must contain 1–100 items.']);exit();}
    if(!valid_money($discount)||!valid_money($tax)||!valid_percent($tax)){echo json_encode(['success'=>false,'message'=>'Invalid discount or tax.']);exit();}
    try{$db->beginTransaction();$subtotal=0;$valid=[];foreach($items as $it){$pid=(int)($it['product_id']??0);$qty=(int)($it['quantity']??0);if($pid<=0||!valid_qty($qty))throw new Exception('Invalid product or quantity.');$q=$db->prepare('SELECT id,product_name,price,stock_quantity FROM products WHERE id=? FOR UPDATE');$q->execute([$pid]);$pr=$q->fetch();if(!$pr)throw new Exception('Product not found.');if($qty>(int)$pr['stock_quantity'])throw new Exception('Insufficient stock for '.$pr['product_name'].'. Available: '.$pr['stock_quantity']);$unit=(float)$pr['price'];if(!valid_money($unit,false))throw new Exception('Invalid product price.');$line=round($unit*$qty,2);$subtotal+=$line;$valid[]=[$pid,$qty,$unit,$line];}if($discount>$subtotal)throw new Exception('Discount cannot exceed subtotal.');$grand=round($subtotal-$discount+$tax,2);if(!valid_money($grand))throw new Exception('Calculated total is invalid.');$inv='INV-'.date('Ymd').'-'.random_int(1000,9999);$stmt=$db->prepare("INSERT INTO sales(invoice_number,user_id,customer_id,customer_name,total_amount,discount,tax,payment_method,status) VALUES(?,?,?,?,?,?,?,?,'completed')");$stmt->execute([$inv,$_SESSION['user_id'],!empty($_POST['customer_id'])?(int)$_POST['customer_id']:null,sanitize($_POST['customer_name']??'Walk-in Customer'),$subtotal,$discount,$tax,sanitize($_POST['payment_method']??'cash')]);$sid=$db->lastInsertId();foreach($valid as [$pid,$qty,$unit,$line]){$db->prepare('INSERT INTO sales_items(sale_id,product_id,quantity,unit_price,subtotal) VALUES(?,?,?,?,?)')->execute([$sid,$pid,$qty,$unit,$line]);$db->prepare('UPDATE products SET stock_quantity=stock_quantity-? WHERE id=? AND stock_quantity>=?')->execute([$qty,$pid,$qty]);if($db->query('SELECT ROW_COUNT()')->fetchColumn()!=1)throw new Exception('Stock changed during checkout. Please retry.');$db->prepare("INSERT INTO stock_movements(product_id,user_id,type,quantity,reference,notes) VALUES(?,?,?,?,?,?)")->execute([$pid,$_SESSION['user_id'],'out',$qty,$inv,'POS sale']);}$db->commit();logActivity("Processed sale #{$inv} - ₱".number_format($grand,2));echo json_encode(['success'=>true,'message'=>"Sale #{$inv} completed!",'invoice'=>$inv,'sale_id'=>$sid,'grand_total'=>$grand]);exit();}catch(Throwable $e){if($db->inTransaction())$db->rollBack();echo json_encode(['success'=>false,'message'=>$e->getMessage()]);exit();}
}

$products = $db->query("SELECT * FROM products WHERE stock_quantity > 0 ORDER BY product_name")->fetchAll();
$lowStockProducts = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")->fetchColumn();
$todaySales = $db->query("SELECT COUNT(*) FROM sales WHERE DATE(created_at) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<script>
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].indexOf(p)!==-1)document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point of Sale</title>
    <link href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
    <style>
        .product-card { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-radius: 10px; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .product-card .card-body { padding: 0.75rem; }
        .product-card .product-price { font-size: 1.1rem; font-weight: bold; color: #0d6efd; }
        .product-card .product-stock { font-size: 0.75rem; }
        .product-card .product-code { font-size: 0.65rem; background: #f8f9fa; padding: 2px 8px; border-radius: 10px; }
        .cursor-pointer { cursor: pointer; }
        .cart-item { padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
        .cart-item:last-child { border-bottom: none; }
        .qty-btn { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 14px; }
        .qty-input { width: 40px; text-align: center; border: none; background: transparent; font-weight: bold; }
        .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }
        .cart-total { font-size: 1.5rem; font-weight: bold; }
        .product-grid { max-height: 500px; overflow-y: auto; }
        .product-grid::-webkit-scrollbar { width: 5px; }
        .product-grid::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
        .low-stock-badge { position: absolute; top: 5px; right: 5px; font-size: 0.6rem; }
        .selected-customer { background: #e8f5e9; padding: 5px 10px; border-radius: 5px; font-size: 0.9rem; }
        @media (max-width: 768px) { .product-grid { max-height: 300px; } .cart-total { font-size: 1.2rem; } }
    </style>
</head>
<body>
    <?php include BASE_PATH . 'includes/header.php'; ?>
    
    <div class="d-flex">
        <?php include BASE_PATH . 'includes/sidebar.php'; ?>
        
        <div class="main-content flex-grow-1 p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0"><i class="fas fa-cash-register me-2 text-primary"></i> Point of Sale</h4>
                    <small class="text-muted"><?php echo date('F d, Y h:i A'); ?></small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($lowStockProducts > 0): ?>
                    <span class="badge bg-warning text-dark p-2"><i class="fas fa-exclamation-triangle me-1"></i> <?php echo $lowStockProducts; ?> low stock</span>
                    <?php endif; ?>
                    <span class="badge bg-info p-2"><i class="fas fa-receipt me-1"></i> Today: <?php echo $todaySales; ?></span>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" id="searchProduct" placeholder="Search products...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" id="categoryFilter">
                                        <option value="">All Categories</option>
                                        <?php
                                        $categories = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''")->fetchAll();
                                        foreach($categories as $cat):
                                        ?>
                                        <option value="<?php echo $cat['category']; ?>"><?php echo $cat['category']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="product-grid">
                                <div class="row g-2" id="productGrid">
                                    <?php foreach($products as $product): 
                                        $isLowStock = $product['stock_quantity'] <= 5;
                                    ?>
                                    <div class="col-6 col-md-4 col-lg-3 product-item" 
                                         data-id="<?php echo $product['id']; ?>" 
                                         data-name="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                         data-price="<?php echo $product['price']; ?>" 
                                         data-stock="<?php echo $product['stock_quantity']; ?>"
                                         data-code="<?php echo $product['product_code']; ?>"
                                         data-category="<?php echo $product['category']; ?>">
                                        <div class="card product-card h-100 position-relative" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['product_name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock_quantity']; ?>)">
                                            <?php if ($isLowStock): ?>
                                            <span class="badge bg-danger low-stock-badge">Low Stock</span>
                                            <?php endif; ?>
                                            <div class="card-body text-center">
                                                <div class="mb-2"><i class="fas fa-box text-primary" style="font-size: 2rem;"></i></div>
                                                <h6 class="mb-0 text-truncate" title="<?php echo htmlspecialchars($product['product_name']); ?>"><?php echo htmlspecialchars($product['product_name']); ?></h6>
                                                <small class="text-muted product-code"><?php echo $product['product_code']; ?></small>
                                                <div class="product-price mt-1">₱<?php echo number_format($product['price'], 2); ?></div>
                                                <div class="product-stock text-muted"><i class="fas fa-boxes me-1"></i> <?php echo $product['stock_quantity']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2 text-primary"></i> Cart</h5>
                            <span class="badge bg-secondary" id="cartCount">0 items</span>
                        </div>
                        <div class="card-body p-2">
                            <div class="mb-2 px-2">
                                <div class="row g-1">
                                    <div class="col-8">
                                        <select class="form-select form-select-sm" id="customerSelect" onchange="selectCustomer(this)">
                                            <option value="">Walk-in Customer</option>
                                            <?php foreach($customers as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo $c['first_name'] . ' ' . $c['last_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-sm btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div id="selectedCustomerDisplay" class="selected-customer mt-1" style="display:none;">
                                    <i class="fas fa-user-check text-success me-1"></i>
                                    <span id="customerNameDisplay">Walk-in Customer</span>
                                    <button class="btn btn-sm btn-link text-danger p-0 float-end" onclick="clearCustomer()"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            
                            <div class="cart-items" style="max-height: 280px; overflow-y: auto;" id="cartItems">
                                <div class="text-center text-muted py-4" id="emptyCart">
                                    <i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>
                                    No items in cart
                                </div>
                            </div>
                            
                            <div class="cart-summary px-2 mt-2">
                                <hr class="my-2">
                                <div class="d-flex justify-content-between small"><span>Subtotal:</span> <strong id="subtotalDisplay">₱0.00</strong></div>
                                <div class="d-flex justify-content-between small text-danger"><span>Discount:</span> <strong id="discountDisplay">-₱0.00</strong></div>
                                <div class="d-flex justify-content-between small text-warning"><span>Tax:</span> <strong id="taxDisplay">₱0.00</strong></div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between cart-total"><span>Total:</span> <strong id="totalDisplay" class="text-primary">₱0.00</strong></div>
                                
                                <div class="row g-1 mt-2">
                                    <div class="col-6"><input type="number" class="form-control form-control-sm" id="discount" placeholder="Discount ₱" value="0" onchange="updateTotal()"></div>
                                    <div class="col-6"><input type="number" class="form-control form-control-sm" id="tax" placeholder="Tax ₱" value="0" onchange="updateTotal()"></div>
                                </div>
                                <div class="mt-2">
                                    <select class="form-select form-select-sm" id="paymentMethod">
                                        <option value="cash">💵 Cash</option>
                                        <option value="card">💳 Card</option>
                                        <option value="gcash">📱 GCash</option>
                                        <option value="paymaya">📱 PayMaya</option>
                                    </select>
                                </div>
                                <button class="btn btn-success w-100 mt-2" id="checkoutBtn" onclick="processSale()"><i class="fas fa-check-circle me-1"></i> Checkout</button>
                                <button class="btn btn-outline-danger btn-sm w-100 mt-1" onclick="clearCart()"><i class="fas fa-trash me-1"></i> Clear Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i> Quick Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo BASE_PATH; ?>modules/customers.php">
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label small">Customer Code</label>
                            <input type="text" name="customer_code" class="form-control form-control-sm" value="CUST-<?php echo rand(1000, 9999); ?>" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label small">First Name</label><input type="text" name="first_name" class="form-control form-control-sm" required></div>
                            <div class="col-6"><label class="form-label small">Last Name</label><input type="text" name="last_name" class="form-control form-control-sm" required></div>
                        </div>
                        <div class="mt-2"><label class="form-label small">Phone</label><input type="text" name="phone" class="form-control form-control-sm"></div>
                        <input type="hidden" name="add_customer" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
    <script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
    
    <script>
        let cart = [];
        let subtotal = 0;
        let selectedCustomerId = null;
        let selectedCustomerName = 'Walk-in Customer';
        
        document.getElementById('searchProduct').addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(item => {
                const name = item.dataset.name.toLowerCase();
                const code = item.dataset.code.toLowerCase();
                item.style.display = (name.includes(search) || code.includes(search)) ? '' : 'none';
            });
        });
        
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const category = this.value.toLowerCase();
            document.querySelectorAll('.product-item').forEach(item => {
                const itemCategory = (item.dataset.category || '').toLowerCase();
                item.style.display = (!category || itemCategory === category) ? '' : 'none';
            });
        });
        
        function selectCustomer(select) {
            const option = select.options[select.selectedIndex];
            if (select.value) {
                selectedCustomerId = select.value;
                selectedCustomerName = option.text;
                document.getElementById('selectedCustomerDisplay').style.display = 'block';
                document.getElementById('customerNameDisplay').textContent = selectedCustomerName;
            } else {
                clearCustomer();
            }
        }
        
        function clearCustomer() {
            selectedCustomerId = null;
            selectedCustomerName = 'Walk-in Customer';
            document.getElementById('customerSelect').value = '';
            document.getElementById('selectedCustomerDisplay').style.display = 'none';
        }
        
        function addToCart(id, name, price, stock) {
            const existing = cart.find(item => item.id === id);
            if (existing) {
                if (existing.quantity < stock) {
                    existing.quantity++;
                    showToast(`${name} quantity updated!`, 'info', 'bottom-end');
                } else {
                    showToast('Not enough stock!', 'error', 'bottom-end');
                    return;
                }
            } else {
                cart.push({ id, name, price, quantity: 1 });
                showToast(`${name} added to cart!`, 'success', 'bottom-end');
            }
            updateCart();
        }
        
        function updateCart() {
            const container = document.getElementById('cartItems');
            const cartCount = document.getElementById('cartCount');
            
            if (cart.length === 0) {
                container.innerHTML = `<div class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-2x mb-2 d-block"></i>No items in cart</div>`;
                cartCount.textContent = '0 items';
                subtotal = 0;
                document.getElementById('subtotalDisplay').textContent = '₱0.00';
                updateTotal();
                return;
            }
            
            let html = '';
            subtotal = 0;
            cart.forEach((item, index) => {
                const total = item.price * item.quantity;
                subtotal += total;
                html += `
                    <div class="cart-item d-flex align-items-center gap-2 p-2">
                        <div class="flex-grow-1">
                            <div class="fw-bold small">${item.name}</div>
                            <div class="small text-muted">₱${item.price.toFixed(2)}</div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-outline-secondary btn-sm qty-btn" onclick="updateQuantity(${index}, -1)">−</button>
                            <input type="number" class="qty-input" value="${item.quantity}" min="1" onchange="setQuantity(${index}, this.value)">
                            <button class="btn btn-outline-secondary btn-sm qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                        <div class="fw-bold text-primary text-end" style="min-width: 70px;">₱${total.toFixed(2)}</div>
                        <button class="btn btn-sm btn-link text-danger p-0" onclick="removeItem(${index})"><i class="fas fa-times"></i></button>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            cartCount.textContent = cart.length + ' items';
            document.getElementById('subtotalDisplay').textContent = `₱${subtotal.toFixed(2)}`;
            updateTotal();
        }
        
        function updateQuantity(index, change) {
            const item = cart[index];
            if (item) {
                const newQty = item.quantity + change;
                if (newQty > 0) { item.quantity = newQty; updateCart(); }
            }
        }
        
        function setQuantity(index, value) {
            const qty = parseInt(value) || 1;
            if (qty > 0) { cart[index].quantity = qty; updateCart(); }
        }
        
        function removeItem(index) {
            cart.splice(index, 1);
            updateCart();
            showToast('Item removed', 'info', 'bottom-end');
        }
        
        function clearCart() {
            if (cart.length === 0) return;
            confirmAction('Clear all items?', 'Clear Cart', function() {
                cart = [];
                updateCart();
                showToast('Cart cleared!', 'info', 'bottom-end');
            });
        }
        
        function updateTotal() {
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const total = subtotal - discount + tax;
            document.getElementById('discountDisplay').textContent = `-₱${discount.toFixed(2)}`;
            document.getElementById('taxDisplay').textContent = `₱${tax.toFixed(2)}`;
            document.getElementById('totalDisplay').textContent = `₱${total.toFixed(2)}`;
        }
        
        function processSale() {
            if (cart.length === 0) {
                showError('Cart is empty!');
                return;
            }
            
            const total = subtotal - (parseFloat(document.getElementById('discount').value) || 0) + (parseFloat(document.getElementById('tax').value) || 0);
            
            Swal.fire({
                title: 'Confirm Sale',
                html: `
                    <div class="text-start">
                        <p><strong>Customer:</strong> ${selectedCustomerName}</p>
                        <p><strong>Items:</strong> ${cart.length}</p>
                        <p><strong>Total:</strong> ₱${total.toFixed(2)}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Confirm Sale'
            }).then(result => {
                if (result.isConfirmed) {
                    const data = {
                        customer_id: selectedCustomerId || '',
                        customer_name: selectedCustomerName,
                        payment_method: document.getElementById('paymentMethod').value,
                        total_amount: subtotal,
                        discount: parseFloat(document.getElementById('discount').value) || 0,
                        tax: parseFloat(document.getElementById('tax').value) || 0,
                        items: JSON.stringify(cart)
                    };
                    
                    Swal.fire({ title: 'Processing...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
                    
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ ...data, process_sale: true })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sale Complete!',
                                html: `<p>${result.message}</p><h3 class="text-success">₱${result.grand_total.toFixed(2)}</h3>
                                       <button class="btn btn-info mt-2" onclick="window.open('<?php echo BASE_PATH; ?>modules/print_receipt.php?id=${result.sale_id}','_blank')"><i class="fas fa-print me-1"></i> Print Receipt</button>`,
                                showConfirmButton: true,
                                confirmButtonColor: '#198754',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                cart = [];
                                updateCart();
                                document.getElementById('discount').value = 0;
                                document.getElementById('tax').value = 0;
                                clearCustomer();
                            });
                        } else {
                            showError(result.message);
                        }
                    })
                    .catch(() => showError('Error processing sale!'));
                }
            });
        }
    </script>
</body>
</html>