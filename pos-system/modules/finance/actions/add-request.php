<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_type = $_POST['request_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO finance_requests (request_type, amount, description, requested_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$request_type, $amount, $description, $_SESSION['user_id']]);
        $_SESSION['success'] = 'Finance request submitted successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../requests.php');
}
?>