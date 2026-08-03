<?php
require_once '../../../includes/functions.php';
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pay_period_start = $_POST['pay_period_start'];
    $pay_period_end = $_POST['pay_period_end'];
    
    try {
        $employees = $pdo->query("SELECT id FROM employees WHERE status = 'active'");
        $count = 0;
        
        foreach ($employees as $emp) {
            $gross_pay = rand(15000, 50000);
            $deductions = $gross_pay * 0.12;
            $net_pay = $gross_pay - $deductions;
            
            $stmt = $pdo->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, gross_pay, deductions, net_pay) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$emp['id'], $pay_period_start, $pay_period_end, $gross_pay, $deductions, $net_pay]);
            $count++;
        }
        
        $_SESSION['success'] = $count . ' payroll record(s) generated successfully';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
    
    redirect('../payroll.php');
}
?>