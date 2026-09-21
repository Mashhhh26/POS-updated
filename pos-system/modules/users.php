<?php
session_start();
require_once '../config/database.php';
require_permission('view_user_accounts');
verify_csrf();
$db = getDB();
$db->exec("CREATE TABLE IF NOT EXISTS user_action_requests(
  id INT NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, action ENUM('deactivate') NOT NULL, reason VARCHAR(500) NOT NULL,
  requested_by INT NOT NULL, status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending', reviewed_by INT NULL, reviewed_at DATETIME NULL,
  reviewer_note VARCHAR(500) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY user_id(user_id), KEY requested_by(requested_by), KEY status(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$ok = $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($id <= 0 && !in_array($action, ['approve_deactivation','reject_deactivation','create'], true)) throw new Exception('Invalid user account.');
        if (in_array($action, ['deactivate', 'archive'], true) && $reason === '') {
            throw new Exception(ucfirst($action) . ' reason is required.');
        }

        if ($action === 'deactivate') {
            require_permission('manage_user_accounts');
            if ($id === (int)$_SESSION['user_id'] || $id === 1) throw new Exception('This protected account cannot be deactivated.');
            $dup = $db->prepare("SELECT id FROM user_action_requests WHERE user_id=? AND action='deactivate' AND status='pending' LIMIT 1");
            $dup->execute([$id]);
            if ($dup->fetch()) throw new Exception('A deactivation request is already pending for this account.');
            $db->prepare("INSERT INTO user_action_requests(user_id,action,reason,requested_by) VALUES(?,?,?,?)")
                ->execute([$id,'deactivate',$reason,$_SESSION['user_id']]);
            logActivity("Requested deactivation for user ID: {$id}");
            $ok = 'Deactivation request submitted for approval. The account remains active until approved.';
        } elseif ($action === 'approve_deactivation') {
            if (!hasRole('Admin') && !hasRole('HRM')) throw new Exception('Only HRM/Admin approvers can approve account deactivation.');
            $requestId=(int)($_POST['request_id']??0);
            $rq=$db->prepare("SELECT * FROM user_action_requests WHERE id=? AND action='deactivate' AND status='pending'");
            $rq->execute([$requestId]); $request=$rq->fetch();
            if(!$request) throw new Exception('Pending deactivation request not found.');
            $db->beginTransaction();
            $db->prepare("UPDATE users SET is_active=0, account_status='deactivated', deactivation_reason=?, deactivated_at=NOW(), deactivated_by=? WHERE id=?")
                ->execute([$request['reason'],$_SESSION['user_id'],$request['user_id']]);
            $db->prepare("INSERT INTO user_status_history(user_id,action,reason,performed_by) VALUES(?, 'deactivated',?,?)")
                ->execute([$request['user_id'],$request['reason'],$_SESSION['user_id']]);
            $db->prepare("UPDATE user_action_requests SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?")->execute([$_SESSION['user_id'],$requestId]);
            $db->commit();
            $ok='Deactivation approved. The account is now inactive.';
        } elseif ($action === 'reject_deactivation') {
            if (!hasRole('Admin') && !hasRole('HRM')) throw new Exception('Only HRM/Admin approvers can reject account deactivation.');
            $requestId=(int)($_POST['request_id']??0);
            $db->prepare("UPDATE user_action_requests SET status='rejected', reviewed_by=?, reviewed_at=NOW(), reviewer_note=? WHERE id=? AND status='pending'")
                ->execute([$_SESSION['user_id'], $reason ?: 'Rejected by approver.',$requestId]);
            $ok='Deactivation request rejected. The account remains active.';
        } elseif ($action === 'create') {
            require_permission('manage_user_accounts');
            $username=trim($_POST['username']??''); $password=$_POST['password']??''; $email=trim($_POST['email']??''); $fullName=trim($_POST['full_name']??''); $roleId=(int)($_POST['role_id']??0);
            if($username===''||strlen($username)<3) throw new Exception('Username must be at least 3 characters.');
            if(strlen($password)<8) throw new Exception('Password must be at least 8 characters.');
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Enter a valid email address.');
            if($fullName==='') throw new Exception('Full name is required.');
            $dup=$db->prepare('SELECT id FROM users WHERE username=? OR email=? LIMIT 1'); $dup->execute([$username,$email]);
            if($dup->fetch()) throw new Exception('Username or email is already registered.');
            $stmt=$db->prepare("INSERT INTO users(username,password,email,full_name,role_id,is_active,account_status) VALUES(?,?,?,?,?,1,'active')");
            $stmt->execute([$username,password_hash($password,PASSWORD_DEFAULT),$email,$fullName,$roleId?:null]);
            $ok='User account created successfully.'; logActivity("Created user account: {$username}");
        } elseif ($action === 'edit') {
            require_permission('manage_user_accounts');
            if($id<=0) throw new Exception('Invalid user.');
            $email=trim($_POST['email']??''); $fullName=trim($_POST['full_name']??''); $roleId=(int)($_POST['role_id']??0);
            if($fullName===''||!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Valid name and email are required.');
            $roleCheck=$db->prepare('SELECT id FROM roles WHERE id=?');$roleCheck->execute([$roleId]);if(!$roleCheck->fetch())throw new Exception('Select a valid role.');
            $dup=$db->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');$dup->execute([$email,$id]);if($dup->fetch())throw new Exception('Email is already used by another account.');
            $db->prepare('UPDATE users SET full_name=?,email=?,role_id=? WHERE id=?')->execute([$fullName,$email,$roleId,$id]);
            logActivity("Updated account #{$id} and assigned role #{$roleId}");
            $ok='Account details and role updated successfully.';
        } elseif ($action === 'activate') {
            require_permission('manage_user_accounts');
            if ($id===1) throw new Exception('Protected account.');
            $db->prepare("UPDATE users SET is_active=1, account_status='active', deactivation_reason=NULL, deactivated_at=NULL, deactivated_by=NULL WHERE id=?")->execute([$id]);
            $db->prepare("INSERT INTO user_status_history(user_id,action,performed_by) VALUES(?, 'reactivated',?)")->execute([$id,$_SESSION['user_id']]);
            $ok='Account activated successfully.';
        } elseif ($action === 'archive') {
            require_permission('archive_users');
            if ($id === (int)$_SESSION['user_id'] || $id === 1) throw new Exception('This protected account cannot be archived.');
            $check=$db->prepare("SELECT account_status FROM users WHERE id=?");$check->execute([$id]);$account=$check->fetchColumn();
            if($account!=='deactivated') throw new Exception('Account must be deactivated before it can be archived.');
            $db->beginTransaction();
            $db->prepare("UPDATE users SET is_active=0, account_status='archived', archived_at=NOW(), archived_by=? WHERE id=?")
                ->execute([$_SESSION['user_id'], $id]);
            $db->prepare("INSERT INTO user_status_history(user_id,action,reason,performed_by) VALUES(?, 'archived',?,?)")
                ->execute([$id, $reason, $_SESSION['user_id']]);
            $db->commit();
            $ok = 'Account archived. Historical business transactions remain preserved.';
        } elseif ($action === 'restore') {
            require_permission('archive_users');
            $db->prepare("UPDATE users SET is_active=1, account_status='active', deactivation_reason=NULL, deactivated_at=NULL, deactivated_by=NULL, archived_at=NULL, archived_by=NULL WHERE id=?")
                ->execute([$id]);
            $db->prepare("INSERT INTO user_status_history(user_id,action,performed_by) VALUES(?, 'restored',?)")
                ->execute([$id, $_SESSION['user_id']]);
            $ok = 'Account restored successfully.';
        } elseif ($action === 'purge') {
            require_permission('permanently_delete_users');
            if ($id === 1 || $id === (int)$_SESSION['user_id']) throw new Exception('Protected account.');
            $q = $db->prepare("SELECT username FROM users WHERE id=? AND account_status='archived'");
            $q->execute([$id]);
            if (!$q->fetch()) throw new Exception('Only archived accounts can be permanently deleted.');

            $db->beginTransaction();
            foreach ([
                'sales' => 'user_id', 'attendance' => 'user_id', 'stock_movements' => 'user_id',
                'backup_logs' => 'created_by', 'requisitions' => 'requester_id',
                'rfqs' => 'procurement_officer_id', 'purchase_orders' => 'procurement_officer_id',
                'goods_receipts' => 'received_by', 'payments' => 'processed_by',
                'supplier_performance' => 'evaluated_by', 'returns' => 'created_by', 'expenses' => 'created_by'
            ] as $table => $column) {
                $db->prepare("UPDATE {$table} SET {$column}=NULL WHERE {$column}=?")->execute([$id]);
            }
            $db->prepare('UPDATE requisitions SET budget_owner_id=NULL WHERE budget_owner_id=?')->execute([$id]);
            $db->prepare('UPDATE purchase_orders SET finance_approver_id=NULL WHERE finance_approver_id=?')->execute([$id]);
            $db->prepare('DELETE FROM employees WHERE user_id=?')->execute([$id]);
            $db->prepare('DELETE FROM user_status_history WHERE user_id=?')->execute([$id]);
            $db->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
            $db->commit();
            $ok = 'Archived account permanently deleted; business history was preserved.';
        }
    } catch (Throwable $e) {
        if ($db->inTransaction()) $db->rollBack();
        $err = $e->getMessage();
    }
}

$roles = $db->query("SELECT id,role_name FROM roles ORDER BY role_name")->fetchAll();
$pendingRequests = $db->query("SELECT ar.*, u.full_name, u.username, req.full_name AS requester_name FROM user_action_requests ar JOIN users u ON u.id=ar.user_id JOIN users req ON req.id=ar.requested_by WHERE ar.status='pending' ORDER BY ar.created_at DESC")->fetchAll();
$users = $db->query("SELECT u.*, r.role_name, e.employee_id, e.position, e.department
    FROM users u
    LEFT JOIN roles r ON r.id=u.role_id
    LEFT JOIN employees e ON e.user_id=u.id
    ORDER BY FIELD(COALESCE(u.account_status,'active'),'active','deactivated','archived'), u.full_name")->fetchAll();
?>
<!doctype html>
<html lang="en" data-bs-theme="light" data-pos-theme="light" data-pos-palette="indigo">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>HRM User Accounts</title>
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.min.css?v=20260913">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/vendor/fontawesome/all.min.css?v=20260913">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/custom.css?v=20260913">
</head>
<body>
<?php include BASE_PATH.'includes/header.php'; ?>
<div class="d-flex">
<?php include BASE_PATH.'includes/sidebar.php'; ?>
<main class="main-content flex-grow-1 p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="text-primary small fw-bold text-uppercase" style="letter-spacing:.08em">Human Resources</div><h4 class="mb-1 fw-bold"><i class="fas fa-user-shield me-2 text-primary"></i>User Accounts</h4><div class="text-muted">Manage account status without destroying business history.</div></div>
        <div class="d-flex align-items-center gap-2"><span class="badge rounded-pill bg-primary px-3 py-2"><i class="fas fa-users me-1"></i><?php echo count($users); ?> accounts</span><?php if(hasPermission('manage_user_accounts')): ?><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal"><i class="fas fa-user-plus me-1"></i>Create Account</button><?php endif; ?></div>
    </div>

    <?php if ($ok): ?><div id="flash-message" data-type="success" data-message="<?php echo h($ok); ?>"></div><?php endif; ?>
    <?php if ($err): ?><div id="flash-message" data-type="error" data-message="<?php echo h($err); ?>"></div><?php endif; ?>

    <div class="card border-0 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-8"><div class="input-group"><span class="input-group-text"><i class="fas fa-search"></i></span><input id="userSearch" class="form-control" placeholder="Search name, username, role, department..."></div></div>
                <div class="col-md-4"><select id="statusFilter" class="form-select"><option value="all">All statuses</option><option value="active">Active</option><option value="deactivated">Deactivated</option><option value="archived">Archived</option></select></div>
            </div>
        </div>
    </div>

    <?php if((hasPermission('approve_user_account_actions') || hasRole('Admin') || hasRole('HRM')) && $pendingRequests): ?>
    <div class="card border-0 mb-4"><div class="card-header bg-transparent d-flex justify-content-between align-items-center"><strong><i class="fas fa-user-clock me-2 text-warning"></i>Pending Account Deactivation Requests</strong><span class="badge bg-warning text-dark"><?php echo count($pendingRequests); ?></span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Account</th><th>Requested By</th><th>Reason</th><th>Date</th><th class="text-end">Approval</th></tr></thead><tbody>
    <?php foreach($pendingRequests as $rq): ?><tr><td><strong><?php echo h($rq['full_name']); ?></strong><div class="small text-muted">@<?php echo h($rq['username']); ?></div></td><td><?php echo h($rq['requester_name']); ?></td><td><?php echo h($rq['reason']); ?></td><td><?php echo date('M d, Y h:i A',strtotime($rq['created_at'])); ?></td><td class="text-end"><form method="post" class="d-inline"><?php echo csrf_field(); ?><input type="hidden" name="action" value="approve_deactivation"><input type="hidden" name="request_id" value="<?php echo $rq['id']; ?>"><button class="btn btn-sm btn-danger"><i class="fas fa-user-slash me-1"></i>Approve</button></form><form method="post" class="d-inline ms-1"><?php echo csrf_field(); ?><input type="hidden" name="action" value="reject_deactivation"><input type="hidden" name="request_id" value="<?php echo $rq['id']; ?>"><button class="btn btn-sm btn-outline-secondary">Reject</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <div class="card border-0">
        <div class="card-header bg-transparent p-3 d-flex justify-content-between align-items-center"><strong><i class="fas fa-list me-2 text-primary"></i>Account Directory</strong><span class="small text-muted">Archive first · permanent deletion is separate</span></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="usersTable">
                <thead><tr><th class="ps-3">Employee</th><th>Account</th><th>Role</th><th>Status</th><th>Reason</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u):
                    $status = $u['account_status'] ?: 'active';
                    $badge = $status === 'active' ? 'success' : ($status === 'archived' ? 'secondary' : 'warning');
                    $search = strtolower(($u['full_name']??'').' '.($u['username']??'').' '.($u['role_name']??'').' '.($u['department']??''));
                ?>
                <tr data-status="<?php echo h($status); ?>" data-search="<?php echo h($search); ?>">
                    <td class="ps-3"><strong><?php echo h($u['full_name']); ?></strong><div class="small text-muted"><?php echo h($u['employee_id'] ?: 'No employee ID'); ?><?php if($u['position']): ?> · <?php echo h($u['position']); ?><?php endif; ?></div></td>
                    <td><span class="fw-semibold">@<?php echo h($u['username']); ?></span><div class="small text-muted"><?php echo h($u['email']); ?></div></td>
                    <td><span class="badge rounded-pill bg-primary-subtle text-primary"><?php echo h($u['role_name'] ?: 'Unassigned'); ?></span></td>
                    <td><span class="badge rounded-pill bg-<?php echo $badge; ?>"><?php echo h(ucfirst($status)); ?></span></td>
                    <td class="text-muted small"><?php echo h($u['deactivation_reason'] ?: '—'); ?></td>
                    <td class="text-end pe-3">
                        <button type="button" class="btn btn-sm btn-primary view-user-actions" data-bs-toggle="modal" data-bs-target="#userActionsModal" data-id="<?php echo (int)$u['id']; ?>" data-name="<?php echo h($u['full_name']); ?>" data-email="<?php echo h($u['email']); ?>" data-role="<?php echo (int)$u['role_id']; ?>" data-status="<?php echo h($status); ?>" data-can-edit="<?php echo hasPermission('manage_user_accounts') && $status!=='archived' ? '1':'0'; ?>" data-can-deactivate="<?php echo ((int)$u['id'] !== 1 && (int)$u['id'] !== (int)$_SESSION['user_id'] && $status === 'active' && hasPermission('manage_user_accounts')) ? '1':'0'; ?>" data-can-activate="<?php echo ($status === 'deactivated' && hasPermission('manage_user_accounts')) ? '1':'0'; ?>" data-can-archive="<?php echo ((int)$u['id'] !== 1 && $status === 'deactivated' && hasPermission('archive_users')) ? '1':'0'; ?>" data-can-restore="<?php echo ($status === 'archived' && hasPermission('archive_users')) ? '1':'0'; ?>" data-can-purge="<?php echo ($status === 'archived' && hasPermission('permanently_delete_users')) ? '1':'0'; ?>"><i class="fas fa-eye me-1"></i> View Actions</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</div>

<div class="modal fade" id="userActionsModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
  <div class="modal-header"><div><h5 class="modal-title mb-0" id="uaName">User Actions</h5><div class="small text-muted" id="uaMeta"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><div class="d-grid gap-2">
   <button type="button" class="btn btn-outline-primary ua-action" id="uaEdit"><i class="fas fa-user-pen me-2"></i>Edit / Promote / Assign Role</button>
   <button type="button" class="btn btn-outline-danger ua-action" id="uaDeactivate"><i class="fas fa-user-slash me-2"></i>Deactivate</button>
   <button type="button" class="btn btn-outline-success ua-action" id="uaActivate"><i class="fas fa-user-check me-2"></i>Activate</button>
   <button type="button" class="btn btn-outline-warning ua-action" id="uaArchive"><i class="fas fa-box-archive me-2"></i>Archive</button>
   <button type="button" class="btn btn-outline-secondary ua-action" id="uaRestore"><i class="fas fa-rotate-left me-2"></i>Restore</button>
   <button type="button" class="btn btn-outline-danger ua-action" id="uaPurge"><i class="fas fa-trash me-2"></i>Delete Permanently</button>
  </div></div>
 </div></div>
</div>

<div class="modal fade" id="createAccountModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content border-0"><div class="modal-header"><h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Create User Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="post"><div class="modal-body"><?php echo csrf_field(); ?><input type="hidden" name="action" value="create"><div class="row g-3"><div class="col-md-6"><label class="form-label">Full Name</label><input name="full_name" class="form-control" required></div><div class="col-md-6"><label class="form-label">Username</label><input name="username" class="form-control" minlength="3" required></div><div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div><div class="col-md-6"><label class="form-label">Temporary Password</label><input type="password" name="password" class="form-control" minlength="8" required></div><div class="col-12"><label class="form-label">Role</label><select name="role_id" class="form-select"><option value="">Select role</option><?php foreach($roles as $role): ?><option value="<?php echo $role['id']; ?>"><?php echo h($role['role_name']); ?></option><?php endforeach; ?></select></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary"><i class="fas fa-user-plus me-1"></i>Create Account</button></div></form></div></div></div>


<div class="modal fade" id="editUserModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0"><form method="post"><div class="modal-header"><h5 class="modal-title">Edit / Promote Account</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><?php echo csrf_field(); ?><input type="hidden" name="action" value="edit"><input type="hidden" name="user_id" id="edit_user_id"><label class="form-label">Full Name</label><input name="full_name" id="edit_user_name" class="form-control mb-3" required><label class="form-label">Email</label><input type="email" name="email" id="edit_user_email" class="form-control mb-3" required><label class="form-label">Role / Promotion</label><select name="role_id" id="edit_user_role" class="form-select"><?php foreach($roles as $role): ?><option value="<?php echo (int)$role['id']; ?>"><?php echo h($role['role_name']); ?></option><?php endforeach; ?></select></div><div class="modal-footer"><button class="btn btn-primary">Save Changes</button></div></form></div></div></div>

<div class="modal fade" id="reasonModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0">
  <div class="modal-header"><h5 class="modal-title" id="reasonTitle">Account action</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><p class="text-muted small" id="reasonHint">Please provide a reason.</p><textarea id="reasonInput" class="form-control" rows="4" maxlength="500" placeholder="Enter the reason..."></textarea></div>
  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary" id="reasonContinue">Continue</button></div>
 </div></div>
</div>

<script src="<?php echo BASE_PATH; ?>assets/vendor/sweetalert2/sweetalert2.all.min.js?v=20260913"></script>
<script src="<?php echo BASE_PATH; ?>assets/vendor/bootstrap/bootstrap.bundle.min.js?v=20260913"></script>
<script src="<?php echo BASE_PATH; ?>assets/js/script.js?v=20260913"></script>
<script>
const csrf = <?php echo json_encode(csrf_token()); ?>;
const modal = new bootstrap.Modal(document.getElementById('reasonModal'));
let pendingAction = null;
function submitAction(action,id,reason){const f=document.createElement('form');f.method='post';f.innerHTML='<input type="hidden" name="csrf_token" value="'+csrf+'"><input type="hidden" name="action" value="'+action+'"><input type="hidden" name="user_id" value="'+id+'"><input type="hidden" name="reason" value="'+String(reason||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;')+'">';document.body.appendChild(f);f.submit();}
document.querySelectorAll('.act').forEach(btn=>btn.addEventListener('click',()=>{
 const action=btn.dataset.a,id=btn.dataset.id;
 if(action==='deactivate'||action==='archive'){
   pendingAction={action,id};
   document.getElementById('reasonTitle').textContent=action==='deactivate'?'Deactivate account':'Archive account';
   document.getElementById('reasonHint').textContent=action==='deactivate'?'A reason is required and will be recorded in the account history.':'A reason is required for the audit trail.';
   document.getElementById('reasonInput').value=''; modal.show(); return;
 }
 const title=action==='purge'?'Permanently delete archived account?':(action==='activate'?'Activate this account?':'Restore this account?');
 const text=action==='purge'?'This cannot be undone. Business transaction history will remain preserved.':'The account will become active again.';
 Swal.fire({icon:action==='purge'?'error':'warning',title,text,showCancelButton:true,confirmButtonText:action==='purge'?'Delete permanently':'Restore',reverseButtons:true}).then(r=>{if(r.isConfirmed)submitAction(action,id,'');});
}));
document.getElementById('uaEdit').addEventListener('click',()=>{
 document.getElementById('edit_user_id').value=currentUserAction.id;document.getElementById('edit_user_name').value=currentUserAction.name;document.getElementById('edit_user_email').value=currentUserAction.email;document.getElementById('edit_user_role').value=currentUserAction.role;actionsModal.hide();editModal.show();
});
document.getElementById('uaDeactivate').addEventListener('click',()=>{actionsModal.hide();pendingAction={action:'deactivate',id:currentUserAction.id};document.getElementById('reasonTitle').textContent='Deactivate account';document.getElementById('reasonHint').textContent='A reason is required and will be recorded in the account history.';document.getElementById('reasonInput').value='';modal.show();});
document.getElementById('uaArchive').addEventListener('click',()=>{actionsModal.hide();pendingAction={action:'archive',id:currentUserAction.id};document.getElementById('reasonTitle').textContent='Archive account';document.getElementById('reasonHint').textContent='A reason is required for the audit trail.';document.getElementById('reasonInput').value='';modal.show();});
document.getElementById('uaActivate').addEventListener('click',()=>{actionsModal.hide();postUserAction('activate','');});
document.getElementById('uaRestore').addEventListener('click',()=>{actionsModal.hide();postUserAction('restore','');});
document.getElementById('uaPurge').addEventListener('click',()=>{actionsModal.hide();Swal.fire({icon:'warning',title:'Permanently delete archived account?',text:'This cannot be undone.',showCancelButton:true,confirmButtonText:'Delete permanently',reverseButtons:true}).then(r=>{if(r.isConfirmed)postUserAction('purge','');});});
document.getElementById('reasonContinue').addEventListener('click',()=>{const reason=document.getElementById('reasonInput').value.trim();if(!reason){showError('Please enter a reason.');return;}modal.hide();submitAction(pendingAction.action,pendingAction.id,reason);});
const actionsModalEl=document.getElementById('userActionsModal');
const actionsModal=new bootstrap.Modal(actionsModalEl);
let currentUserAction={};
function setActionVisible(id,show){document.getElementById(id).classList.toggle('d-none',!show);}
document.querySelectorAll('.view-user-actions').forEach(b=>b.addEventListener('click',()=>{
 currentUserAction={id:b.dataset.id,name:b.dataset.name,email:b.dataset.email,role:b.dataset.role,status:b.dataset.status};
 document.getElementById('uaName').textContent=currentUserAction.name;
 document.getElementById('uaMeta').textContent='@'+(b.closest('tr')?.querySelector('.fw-semibold')?.textContent.replace('@','')||'')+' · '+(b.dataset.status||'active');
 setActionVisible('uaEdit',b.dataset.canEdit==='1'); setActionVisible('uaDeactivate',b.dataset.canDeactivate==='1'); setActionVisible('uaActivate',b.dataset.canActivate==='1'); setActionVisible('uaArchive',b.dataset.canArchive==='1'); setActionVisible('uaRestore',b.dataset.canRestore==='1'); setActionVisible('uaPurge',b.dataset.canPurge==='1');
}));
function postUserAction(action,reason){const f=document.createElement('form');f.method='post';f.innerHTML='<input type="hidden" name="csrf_token" value="'+<?php echo json_encode(csrf_token()); ?>+'"><input type="hidden" name="action" value="'+action+'"><input type="hidden" name="user_id" value="'+currentUserAction.id+'"><input type="hidden" name="reason" value="'+String(reason||'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))+'">';document.body.appendChild(f);f.submit();}
const editModal=new bootstrap.Modal(document.getElementById('editUserModal'));document.querySelectorAll('.edit-user').forEach(b=>b.addEventListener('click',()=>{document.getElementById('edit_user_id').value=b.dataset.id;document.getElementById('edit_user_name').value=b.dataset.name;document.getElementById('edit_user_email').value=b.dataset.email;document.getElementById('edit_user_role').value=b.dataset.role;editModal.show();}));
const search=document.getElementById('userSearch'),filter=document.getElementById('statusFilter');
function applyFilters(){const q=search.value.toLowerCase().trim(),s=filter.value;document.querySelectorAll('#usersTable tbody tr').forEach(r=>{r.style.display=(s==='all'||r.dataset.status===s)&&(!q||r.dataset.search.includes(q))?'':'none';});}
search.addEventListener('input',applyFilters);filter.addEventListener('change',applyFilters);
</script>
</body></html>
