<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('manage_employees')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to add employees.'
    ];
    redirect('../employees.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $position = $_POST['position'];
    $department = $_POST['department'];
    $date_hired = $_POST['date_hired'];
    $status = $_POST['status'] ?? 'active';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO employees (employee_id, first_name, last_name, middle_name, email, phone, position, department, date_hired, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $first_name, $last_name, $middle_name, $email, $phone, $position, $department, $date_hired, $status]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Employee Added!',
            'text' => 'Employee "' . $first_name . ' ' . $last_name . '" added successfully.'
        ];
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to add employee: ' . $e->getMessage()
        ];
    }
    
    redirect('../employees.php');
} else {
    redirect('../employees.php');
}
?>