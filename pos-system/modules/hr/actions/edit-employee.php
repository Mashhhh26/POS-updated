<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/database.php';

if (!userCan('manage_employees')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to edit employees.'
    ];
    redirect('../employees.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
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
    
    // Check if employee ID already exists (excluding current)
    $check = $pdo->prepare("SELECT id FROM employees WHERE employee_id = ? AND id != ?");
    $check->execute([$employee_id, $id]);
    if ($check->rowCount() > 0) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Duplicate Employee ID!',
            'text' => 'Employee ID "' . $employee_id . '" already exists.'
        ];
        redirect('../edit-employee.php?id=' . $id);
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE employees SET employee_id=?, first_name=?, last_name=?, middle_name=?, email=?, phone=?, position=?, department=?, date_hired=?, status=? WHERE id=?");
        $stmt->execute([$employee_id, $first_name, $last_name, $middle_name, $email, $phone, $position, $department, $date_hired, $status, $id]);
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Employee Updated!',
            'text' => 'Employee updated successfully.'
        ];
        redirect('../employees.php');
        
    } catch (PDOException $e) {
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to update employee: ' . $e->getMessage()
        ];
        redirect('../edit-employee.php?id=' . $id);
    }
} else {
    redirect('../employees.php');
}
?>