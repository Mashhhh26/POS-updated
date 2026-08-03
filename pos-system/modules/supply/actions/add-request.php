<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = $_POST['department'];
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
        $_SESSION['error'] = 'Please add at least one item.';
        redirect('../requests.php');
        exit();
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO supply_requests (request_number, requested_by, department, total_amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$request_number, $_SESSION['user_id'], $department, $total_amount]);
        $request_id = $pdo->lastInsertId();
        
        foreach ($items_data as $item) {
            $stmt = $pdo->prepare("INSERT INTO supply_items (request_id, item_name, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$request_id, $item['name'], $item['qty'], $item['price'], $item['total']]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = 'Supply request #' . $request_number . ' submitted successfully';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../requests.php');
}
?>