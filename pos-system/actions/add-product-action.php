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
    // Get the highest product code number
    $stmt = $pdo->query("SELECT product_code FROM products ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetch();
    
    if ($last) {
        // Extract number from code (e.g., P001 -> 1, P010 -> 10)
        $last_num = (int)substr($last['product_code'], 1);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    
    // Format: P + 3 digits (e.g., P001, P002, P010, P100)
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
    // CHECK DUPLICATE PRODUCT CODE
    // ============================================
    $duplicate_found = false;
    $new_code = $product_code;
    
    // Check if code already exists
    if (codeExists($pdo, $product_code)) {
        $duplicate_found = true;
        
        // Generate new code
        $new_code = generateProductCode($pdo);
        
        // Make sure new code doesn't exist
        while (codeExists($pdo, $new_code)) {
            // If still exists, generate again
            $last_num = (int)substr($new_code, 1);
            $new_code = 'P' . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
        }
        
        // Set session message
        $_SESSION['swal'] = [
            'type' => 'warning',
            'title' => 'Duplicate Code Detected!',
            'text' => 'Product code "' . $product_code . '" already exists. Using "' . $new_code . '" instead.'
        ];
        
        // Use the new code
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
        // Check if error is duplicate entry
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            // Generate another new code
            $new_code = generateProductCode($pdo);
            
            // Retry with new code
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