<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

$role = $_SESSION['role'] ?? 'staff';
if (!isAdmin() && $role != 'hr') {
    redirect('../../pages/dashboard.php');
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    $_SESSION['error'] = 'Employee not found.';
    redirect('employees.php');
    exit();
}

require_once '../../includes/header.php';
?>

<!-- HR SIDEBAR -->
<nav class="col-md-2 d-md-block sidebar" style="background: #2c3e50; min-height: calc(100vh - 70px); padding: 20px;">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white" href="index.php">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white active" href="employees.php">
                <i class="bi bi-person"></i> Employees
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="attendance.php">
                <i class="bi bi-clock"></i> Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="leave-requests.php">
                <i class="bi bi-calendar-check"></i> Leave Requests
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-white" href="../../pages/dashboard.php">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </li>
    </ul>
    <hr class="text-white">
    <div class="text-white text-center small">
        <i class="bi bi-person-circle"></i><br>
        <?php echo $_SESSION['full_name'] ?? 'User'; ?>
        <br><span class="badge bg-success">HR</span>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Employee Profile</h1>
        <a href="employees.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Personal Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Employee ID</th>
                            <td><?php echo htmlspecialchars($employee['employee_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Middle Name</th>
                            <td><?php echo htmlspecialchars($employee['middle_name'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?php echo htmlspecialchars($employee['phone'] ?? 'N/A'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Work Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Position</th>
                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                        </tr>
                        <tr>
                            <th>Department</th>
                            <td><?php echo htmlspecialchars($employee['department']); ?></td>
                        </tr>
                        <tr>
                            <th>Date Hired</th>
                            <td><?php echo date('M d, Y', strtotime($employee['date_hired'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $employee['status'] == 'active' ? 'success' : 
                                        ($employee['status'] == 'inactive' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($employee['status']); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>