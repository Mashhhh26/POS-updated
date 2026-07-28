<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Load RBAC if exists
$rbac_file = __DIR__ . '/../../includes/rbac/roles.php';
if (file_exists($rbac_file)) {
    require_once $rbac_file;
}

// Check if user has access (HR or Admin)
$can_access = false;
if (function_exists('userCan')) {
    $can_access = userCan('view_employees');
}
if (!$can_access && isset($_SESSION['role'])) {
    $can_access = ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'co-admin' || $_SESSION['role'] == 'hr');
}

if (!$can_access) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to view employees.'
    ];
    redirect('../../pages/dashboard.php');
    exit();
}

require_once '../../includes/header.php';

// Load HR Sidebar
require_once '../../includes/sidebar/hr-sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Employee Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="bi bi-plus"></i> Add Employee
        </button>
    </div>

    <?php if(isset($_SESSION['swal']) && !empty($_SESSION['swal'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $_SESSION['swal']['type']; ?>',
                    title: '<?php echo $_SESSION['swal']['title']; ?>',
                    text: '<?php echo addslashes($_SESSION['swal']['text']); ?>',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            });
        </script>
        <?php unset($_SESSION['swal']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>All Employees</h5>
            <span class="badge bg-primary"><?php 
                try {
                    echo $pdo->query("SELECT COUNT(*) FROM employees")->fetchColumn(); 
                } catch (PDOException $e) {
                    echo '0';
                }
            ?> total</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $employees = $pdo->query("SELECT * FROM employees ORDER BY last_name");
                            if($employees->rowCount() > 0):
                            while($emp = $employees->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['employee_id']); ?></td>
                            <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td><?php echo htmlspecialchars($emp['department']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $emp['status'] == 'active' ? 'success' : 
                                        ($emp['status'] == 'inactive' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($emp['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewEmployee(<?php echo $emp['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="editEmployee(<?php echo $emp['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteEmployee(<?php echo $emp['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="6" class="text-center py-3">No employees found</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="6" class="text-center text-danger">Error loading employees: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="actions/add-employee.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Position</label>
                            <input type="text" name="position" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Department</label>
                            <input type="text" name="department" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date Hired</label>
                            <input type="date" name="date_hired" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewEmployee(id) {
    window.location.href = 'view-employee.php?id=' + id;
}
function editEmployee(id) {
    window.location.href = 'edit-employee.php?id=' + id;
}
function deleteEmployee(id) {
    Swal.fire({
        title: 'Delete Employee?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'actions/delete-employee-action.php?id=' + id;
        }
    });
}
</script>

<?php require_once '../../includes/footer.php'; ?>