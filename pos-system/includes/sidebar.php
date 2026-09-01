<?php
if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__) . '/config/database.php';
}

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar bg-white shadow-sm p-3" style="min-height: calc(100vh - 56px); width: 250px; flex-shrink: 0; overflow-y: auto;">
    <ul class="nav flex-column">
        
        <!-- DASHBOARD -->
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'index.php' || $current_page == 'dashboard.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>index.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        
        <!-- SALES -->
        <?php if (hasPermission('view_products') || hasPermission('process_sales')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Sales</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'sales.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/sales.php">
                <i class="fas fa-cash-register me-2"></i> Point of Sale
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'products.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/products.php">
                <i class="fas fa-boxes me-2"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'customers.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/customers.php">
                <i class="fas fa-users me-2"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'returns.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/returns.php">
                <i class="fas fa-undo-alt me-2"></i> Returns
            </a>
        </li>
        <?php endif; ?>
        
        <!-- INVENTORY -->
        <?php if (hasPermission('manage_products')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Inventory</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'stock_movement.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/inventory/stock_movement.php">
                <i class="fas fa-exchange-alt me-2"></i> Stock Movement
            </a>
        </li>
        <?php endif; ?>
        
        <!-- HRM -->
        <?php if (hasPermission('view_attendance') || hasRole('HRM') || hasRole('Admin')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Human Resources</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'attendance.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/attendance.php">
                <i class="fas fa-clipboard-check me-2"></i> Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'employees.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/hrm/employees.php">
                <i class="fas fa-user-tie me-2"></i> Employees
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'leaves.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/hrm/leaves.php">
                <i class="fas fa-calendar-alt me-2"></i> Leave Management
            </a>
        </li>
        <?php endif; ?>
        
        <!-- PROCUREMENT -->
        <?php if (hasPermission('view_requisitions')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Procurement</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_dir == 'procurement' && $current_page == 'dashboard.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/procurement/dashboard.php">
                <i class="fas fa-chart-pie me-2"></i> Procurement Dashboard
            </a>
        </li>
        <ul class="nav flex-column ms-3">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'requisitions.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/requisitions.php">
                    <i class="fas fa-file-invoice me-2"></i> Requisitions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'rfqs.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/rfqs.php">
                    <i class="fas fa-file-signature me-2"></i> RFQ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'suppliers.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/suppliers.php">
                    <i class="fas fa-truck me-2"></i> Suppliers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'purchase_orders.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/purchase_orders.php">
                    <i class="fas fa-file-pdf me-2"></i> Purchase Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'goods_receipt.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/goods_receipt.php">
                    <i class="fas fa-warehouse me-2"></i> Goods Receipt
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'invoices.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/invoices.php">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'payments.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/payments.php">
                    <i class="fas fa-money-bill-wave me-2"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'performance.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
                   href="<?php echo BASE_PATH; ?>modules/procurement/performance.php">
                    <i class="fas fa-star me-2"></i> Performance
                </a>
            </li>
        </ul>
        <?php endif; ?>
        
        <!-- FINANCE -->
        <?php if (hasPermission('view_reports') || hasPermission('view_expenses') || hasPermission('view_budget')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Finance</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/reports.php">
                <i class="fas fa-chart-bar me-2"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'expenses.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/expenses.php">
                <i class="fas fa-money-bill me-2"></i> Expenses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'budget.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/budget.php">
                <i class="fas fa-coins me-2"></i> Budget
            </a>
        </li>
        <?php endif; ?>
        
        <!-- ADMIN -->
        <?php if (hasPermission('view_users') || hasRole('Admin')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">Administration</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'users.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/users.php">
                <i class="fas fa-user-cog me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'roles.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/roles.php">
                <i class="fas fa-user-tag me-2"></i> Roles & Permissions
            </a>
        </li>
        <?php endif; ?>
        
        <!-- SYSTEM (Admin Only) -->
        <?php if (hasRole('Admin')): ?>
        <li class="nav-item mt-3">
            <span class="text-uppercase small text-muted fw-bold px-2">System</span>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'settings.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/settings.php">
                <i class="fas fa-cog me-2"></i> System Settings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'backup.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/backup.php">
                <i class="fas fa-database me-2"></i> Backup & Restore
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'activity_logs.php' ? 'active bg-primary text-white' : 'text-dark'; ?>" 
               href="<?php echo BASE_PATH; ?>modules/activity_logs.php">
                <i class="fas fa-list me-2"></i> Activity Logs
            </a>
        </li>
        <?php endif; ?>
        
        <hr class="my-3">
        
        <!-- LOGOUT -->
        <li class="nav-item">
            <a class="nav-link text-danger fw-bold" href="<?php echo BASE_PATH; ?>logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
        
    </ul>
</div>