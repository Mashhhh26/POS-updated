<?php
require_once '../config/database.php';

$code = $_GET['code'] ?? '';

if (empty($code)) {
    echo json_encode(['exists' => false]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE product_code = ?");
$stmt->execute([$code]);
$exists = $stmt->rowCount() > 0;

echo json_encode(['exists' => $exists]);
?>