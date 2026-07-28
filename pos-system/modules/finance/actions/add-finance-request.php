<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_type = $_POST['request_type'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO finance_requests (request_type, amount, description, requested_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$request_type, $amount, $description, $_SESSION['user_id']]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Request Submitted!',
            'text' => 'Your finance request has been submitted.'
        ];
        redirect('../finance-requests.php');
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to submit request: ' . $e->getMessage()
        ];
        redirect('../finance-requests.php');
    }
} else {
    redirect('../finance-requests.php');
}
?>