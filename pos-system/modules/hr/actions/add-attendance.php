<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $date = $_POST['date'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'] ?? null;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance (employee_id, date, time_in, time_out) VALUES (?, ?, ?, ?)");
        $stmt->execute([$employee_id, $date, $time_in, $time_out]);
        $_SESSION['success'] = 'Attendance logged successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../attendance.php');
}
?>