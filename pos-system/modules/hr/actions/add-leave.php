<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'] ?? '';
    
    // Validate dates
    if ($start_date > $end_date) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Invalid Date Range!',
            'text' => 'Start date must be before end date.'
        ];
        redirect('../leave-requests.php');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, reason) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $leave_type, $start_date, $end_date, $reason]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Leave Request Filed!',
            'text' => 'Leave request submitted successfully.'
        ];
        redirect('../leave-requests.php');
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to submit leave request: ' . $e->getMessage()
        ];
        redirect('../leave-requests.php');
    }
} else {
    redirect('../leave-requests.php');
}
?>