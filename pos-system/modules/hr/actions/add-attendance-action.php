<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('manage_attendance')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to add attendance records.'
    ];
    redirect('../attendance.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'] ?? null;
    $status = $_POST['status'] ?? 'present';
    
    // Check if attendance already exists
    $check = $pdo->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
    $check->execute([$employee_id, $date]);
    if ($check->rowCount() > 0) {
        $_SESSION['swal'] = [
            'type' => 'warning',
            'title' => 'Attendance Already Logged!',
            'text' => 'This employee already has attendance for this date.'
        ];
        redirect('../attendance.php');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time_in, $time_out, $status]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Attendance Logged!',
            'text' => 'Attendance record added successfully.'
        ];
        redirect('../attendance.php');
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to add attendance: ' . $e->getMessage()
        ];
        redirect('../attendance.php');
    }
} else {
    redirect('../attendance.php');
}
?>