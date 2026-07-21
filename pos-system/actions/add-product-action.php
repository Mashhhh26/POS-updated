<?php
require_once '../includes/functions.php';
require_once '../config/database.php';

if (!isAdminOrCoAdmin()) {
    redirect('../pages/dashboard.php');
}

// ============================================
// HELPER: GENERATE PRODUCT CODE
// ============================================
function generateProductCode($pdo) {
    $stmt = $pdo->query("SELECT product_code FROM products ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if ($last) {
        $last_num = (int)substr($last['product_code'], 1);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    
    return 'P' . str_pad($next_num, 3, '0', STR_PAD_LEFT);
}

// ============================================
// HELPER: CHECK IF CODE EXISTS
// ============================================
function codeExists($pdo, $code) {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE product_code = ?");
    $stmt->execute([$code]);
    return $stmt->rowCount() > 0;
}

// ============================================
// PROCESS FORM
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_code = $_POST['product_code'];
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';
    $original_price = $_POST['original_price'];
    $selling_price = $_POST['selling_price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'] ?? '';
    $vat_type = $_POST['vat_type'] ?? 'V';
    
    // ============================================
    // VALIDATION: STOCK LIMIT (MAX 10,000)
    // ============================================
    if ($stock > 10000) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Stock Limit Exceeded!',
            'text' => 'Stock cannot exceed 10,000 units. Current: ' . $stock
        ];
        redirect('../pages/add-product.php');
        exit();
    }
    
    if ($stock < 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Stock!',
            'text' => 'Stock cannot be negative.'
        ];
        redirect('../pages/add-product.php');
        exit();
    }
    
    // ============================================
    // VALIDATION: PRICE LIMIT (MAX 1,000,000)
    // ============================================
    if ($original_price > 1000000) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Price Limit Exceeded!',
            'text' => 'Price cannot exceed ₱1,000,000.00. Current: ₱' . number_format($original_price, 2)
        ];
        redirect('../pages/add-product.php');
        exit();
    }
    
    if ($original_price <= 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Price!',
            'text' => 'Price must be greater than 0.'
        ];
        redirect('../pages/add-product.php');
        exit();
    }
    
    // ============================================
    // CHECK DUPLICATE PRODUCT CODE
    // ============================================
    if (codeExists($pdo, $product_code)) {
        $new_code = generateProductCode($pdo);
        
        while (codeExists($pdo, $new_code)) {
            $last_num = (int)substr($new_code, 1);
            $new_code = 'P' . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
        }
        
        $_SESSION['swal'] = [
            'type' => 'warning',
            'title' => 'Duplicate Code Detected!',
            'text' => 'Product code "' . $product_code . '" already exists. Using "' . $new_code . '" instead.'
        ];
        
        $product_code = $new_code;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (product_code, name, description, original_price, selling_price, stock, category, vat_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $name, $description, $original_price, $selling_price, $stock, $category, $vat_type]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Product Added!',
            'text' => 'Product "' . $name . '" added successfully. Code: ' . $product_code
        ];
        redirect('../pages/products.php');
        
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $new_code = generateProductCode($pdo);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO products (product_code, name, description, original_price, selling_price, stock, category, vat_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$new_code, $name, $description, $original_price, $selling_price, $stock, $category, $vat_type]);
                
                $_SESSION['swal'] = [
                    'type' => 'success',
                    'title' => 'Product Added!',
                    'text' => 'Product "' . $name . '" added successfully. Code: ' . $new_code
                ];
                redirect('../pages/products.php');
                
            } catch(PDOException $e2) {
                $_SESSION['swal'] = [
                    'type' => 'error',
                    'title' => 'Error!',
                    'text' => 'Failed to add product: ' . $e2->getMessage()
                ];
                redirect('../pages/add-product.php');
            }
        } else {
            $_SESSION['swal'] = [
                'type' => 'error',
                'title' => 'Error!',
                'text' => 'Error: ' . $e->getMessage()
            ];
            redirect('../pages/add-product.php');
        }
    }
} else {
    redirect('../pages/add-product.php');
}
?>