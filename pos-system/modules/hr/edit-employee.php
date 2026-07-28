<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/rbac/roles.php';

if (!userCan('manage_employees')) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Access Denied!',
        'text' => 'You do not have permission to edit employees.'
    ];
    redirect('../employees.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id <= 0) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Invalid employee ID.'
    ];
    redirect('../employees.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    $_SESSION['swal'] = [
        'type' => 'error',
        'title' => 'Error!',
        'text' => 'Employee not found.'
    ];
    redirect('../employees.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Edit Employee</h1>
        <a href="../employees.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
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
        <div class="card-body">
            <form action="actions/edit-employee-action.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" class="form-control" value="<?php echo $employee['employee_id']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?php echo $employee['first_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?php echo $employee['last_name']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="<?php echo $employee['middle_name']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $employee['email']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $employee['phone']; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control" value="<?php echo $employee['position']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control" value="<?php echo $employee['department']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Date Hired</label>
                        <input type="date" name="date_hired" class="form-control" value="<?php echo $employee['date_hired']; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="terminated" <?php echo $employee['status'] == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>