<?php
// pages/admin/users.php
$page_title = 'Users';
require_once 'admin_guard.php';

$msg = '';

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    // Don't allow deleting self
    if ($del === (int)$_SESSION['user_id']) {
        $msg = '<div class="alert alert-danger">You cannot delete your own account.</div>';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role!='admin'");
        $stmt->bind_param('i', $del);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $msg = '<div class="alert alert-success alert-dismissible fade show">User deleted.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } else {
            $msg = '<div class="alert alert-danger">Cannot delete this user.</div>';
        }
        $stmt->close();
    }
}

// Search & Pagination
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1,(int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page-1)*$perPage;

$where  = '';
$params = [];
$types  = '';
if ($search !== '') {
    $where  = "WHERE full_name LIKE ? OR email LIKE ? OR username LIKE ?";
    $like   = "%$search%";
    $params = [$like, $like, $like];
    $types  = 'sss';
}

$cStmt = $conn->prepare("SELECT COUNT(*) FROM users $where");
if ($types) $cStmt->bind_param($types,...$params);
$cStmt->execute();
$totalRows = $cStmt->get_result()->fetch_row()[0];
$cStmt->close();
$totalPages = ceil($totalRows/$perPage);

$fp = array_merge($params,[$perPage,$offset]);
$ft = $types.'ii';
$stmt = $conn->prepare("SELECT id,full_name,username,email,phone,role,created_at FROM users $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
if ($ft) $stmt->bind_param($ft,...$fp);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'admin_sidebar.php';
?>

<?= $msg ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" style="width:240px;"
               placeholder="Search name / email / username…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-sm btn-gold">Search</button>
        <?php if ($search): ?><a href="users.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <span class="text-muted small"><?= $totalRows ?> user<?= $totalRows!==1?'s':'' ?></span>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">No users found.</td></tr>
                <?php else: foreach ($users as $u): ?>
                <tr>
                    <td class="px-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:36px;height:36px;background:var(--gold);font-size:.85rem;flex-shrink:0;">
                                <?= strtoupper(substr($u['full_name'],0,1)) ?>
                            </div>
                            <span class="fw-semibold small"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="small"><?= htmlspecialchars($u['username']) ?></td>
                    <td class="small"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="small"><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $u['role']==='admin'?'bg-danger':'bg-info text-dark' ?>">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td class="small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-center">
                        <?php if ($u['role'] !== 'admin' && (int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                        <a href="users.php?delete=<?= $u['id'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete user &quot;<?= htmlspecialchars(addslashes($u['full_name'])) ?>&quot;? This cannot be undone.');">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= $page<=1?'disabled':'' ?>">
            <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">‹</a>
        </li>
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
            <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">›</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'admin_footer.php'; ?>
