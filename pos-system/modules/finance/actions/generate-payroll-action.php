<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/rbac/roles.php';

if (!userCan('manage_payroll')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to generate payroll.'
    ];
    redirect('../payroll.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pay_period_start = $_POST['pay_period_start'];
    $pay_period_end = $_POST['pay_period_end'];
    $employee_id = $_POST['employee_id'] ?? '';
    
    $sql = "SELECT id FROM employees WHERE status = 'active'";
    if (!empty($employee_id)) {
        $sql .= " AND id = $employee_id";
    }
    $employees = $pdo->query($sql);
    
    $count = 0;
    
    try {
        $pdo->beginTransaction();
        
        foreach ($employees as $emp) {
            $check = $pdo->prepare("SELECT id FROM payroll WHERE employee_id = ? AND pay_period_start = ? AND pay_period_end = ?");
            $check->execute([$emp['id'], $pay_period_start, $pay_period_end]);
            if ($check->rowCount() > 0) {
                continue;
            }
            
            // Sample payroll calculation
            $gross_pay = rand(15000, 50000);
            $deductions = $gross_pay * 0.12;
            $net_pay = $gross_pay - $deductions;
            
            $stmt = $pdo->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, gross_pay, deductions, net_pay) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$emp['id'], $pay_period_start, $pay_period_end, $gross_pay, $deductions, $net_pay]);
            $count++;
        }
        
        $pdo->commit();
        
        $_SESSION['swal'] = [
            'type' => 'success',
            'title' => 'Payroll Generated!',
            'text' => $count . ' payroll record(s) generated successfully.'
        ];
        redirect('../payroll.php');
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['swal'] = [
            'type' => 'error',
            'title' => 'Error!',
            'text' => 'Failed to generate payroll: ' . $e->getMessage()
        ];
        redirect('../payroll.php');
    }
} else {
    redirect('../payroll.php');
}
?>