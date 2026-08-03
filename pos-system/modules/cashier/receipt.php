<?php
require_once '../../includes/functions.php';
require_once '../../config/database.php';
require_once '../../includes/auth.php';

if (!isLoggedIn()) {
    redirect('../../pages/login.php');
    exit();
}

require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';
?>

<main class="col-md-10 ms-sm-auto px-md-4 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1>Receipts</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to POS
        </a>
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
        <div class="card-header">
            <h5>All Receipts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $receipts = $pdo->query("SELECT * FROM sales ORDER BY sale_date DESC");
                            if($receipts->rowCount() > 0):
                            while($row = $receipts->fetch()):
                        ?>
                        <tr>
                            <td><strong><?php echo $row['invoice_number']; ?></strong></td>
                            <td><?php echo formatCurrency($row['grand_total']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($row['sale_date'])); ?></td>
                            <td>
                                <a href="../../pages/view-receipt.php?id=<?php echo $row['id']; ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="../../pages/view-receipt.php?id=<?php echo $row['id']; ?>&print=1" 
                                   target="_blank" 
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-printer"></i> Print
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                            else:
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">No receipts found</td>
                        </tr>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="4" class="text-center text-danger">Error: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once '../../includes/footer.php'; ?>