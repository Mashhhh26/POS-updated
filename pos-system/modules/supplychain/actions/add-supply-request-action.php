<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('create_supply_requests')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to create supply requests.'
    ];
    redirect('../requests.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['department'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $items = $_POST['items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $prices = $_POST['prices'] ?? [];
    
    $request_number = 'SR-' . date('Ymd') . '-' . rand(1000, 9999);
    $total_amount = 0;
    $items_data = [];
    
    for ($i = 0; $i < count($items); $i++) {
        if (!empty($items[$i])) {
            $qty = $quantities[$i] ?? 1;
            $price = $prices[$i] ?? 0;
            $total = $qty * $price;
            $total_amount += $total;
            $items_data[] = [
                'name' => $items[$i],
                'qty' => $qty,
                'price' => $price,
                'total' => $total
            ];
        }
    }
    
    if (empty($items_data)) {
        $_SESSION['swal'] = [
            'type' => 'warning',
            'title' => 'No Items!',
            'text' => 'Please add at least one item to the request.'
        ];
        redirect('../requests.php');
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO supply_requests (request_number, requested_by, department, priority, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$request_number, $_SESSION['user_id'], $department, $priority, $total_amount]);
        $request_id = $pdo->lastInsertId();
        
        foreach ($items_data as $item) {
            $stmt = $pdo->prepare("INSERT INTO supply_items (request_id, item_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$request_id, $item['name'], $item['qty'], $item['price'], $item['total']]);
        }
        
        $pdo->commit();
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Request Submitted!',
            'text' => 'Supply request #' . $request_number . ' has been submitted for approval.'
        ];
        redirect('../requests.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to submit request: ' . $e->getMessage()
        ];
        redirect('../requests.php');
    }
} else {
    redirect('../requests.php');
}
?>