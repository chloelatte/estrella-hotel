<?php
// pages/admin/reservations.php
$page_title = 'Reservations';
require_once 'admin_guard.php';

$msg = '';

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt   = $conn->prepare("DELETE FROM reservations WHERE id=?");
    $stmt->bind_param('i', $del_id);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show">Reservation deleted successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $msg = '<div class="alert alert-danger">Failed to delete reservation.</div>';
    }
    $stmt->close();
}

// UPDATE STATUS (edit form POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $upd_id     = (int)$_POST['res_id'];
    $upd_status = in_array($_POST['status'], ['pending','confirmed','completed','cancelled'])
                  ? $_POST['status'] : 'pending';
    $stmt = $conn->prepare("UPDATE reservations SET status=? WHERE id=?");
    $stmt->bind_param('si', $upd_status, $upd_id);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show">Status updated successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    $stmt->close();
}

// Search & Pagination
$search  = sanitize($_GET['search'] ?? '');
$filter  = in_array($_GET['filter'] ?? '', ['pending','confirmed','completed','cancelled','']) ? ($_GET['filter'] ?? '') : '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where  = "WHERE 1=1";
$params = [];
$types  = '';
if ($search !== '') {
    $where  .= " AND (r.booking_code LIKE ? OR u.full_name LIKE ? OR rm.name LIKE ?)";
    $like    = "%$search%";
    $params  = array_merge($params, [$like, $like, $like]);
    $types  .= 'sss';
}
if ($filter !== '') {
    $where  .= " AND r.status=?";
    $params[] = $filter;
    $types   .= 's';
}

$baseSQL = "FROM reservations r JOIN users u ON r.user_id=u.id JOIN rooms rm ON r.room_id=rm.id $where";

$countStmt = $conn->prepare("SELECT COUNT(*) $baseSQL");
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_row()[0];
$countStmt->close();
$totalPages = ceil($totalRows / $perPage);

$fetchParams  = array_merge($params, [$perPage, $offset]);
$fetchTypes   = $types . 'ii';
$stmt = $conn->prepare("SELECT r.*, u.full_name, u.email, rm.name AS room_name $baseSQL ORDER BY r.created_at DESC LIMIT ? OFFSET ?");
if ($fetchTypes) $stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch single for edit modal
$editRow = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT r.*, u.full_name, rm.name AS room_name FROM reservations r JOIN users u ON r.user_id=u.id JOIN rooms rm ON r.room_id=rm.id WHERE r.id=?");
    $stmt->bind_param('i', (int)$_GET['edit']);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

require_once 'admin_sidebar.php';
?>

<?= $msg ?>

<!-- Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
               placeholder="Search code / guest / room…" value="<?= htmlspecialchars($search) ?>">
        <select name="filter" class="form-select form-select-sm" style="width:140px;" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm btn-gold">Search</button>
        <?php if ($search || $filter): ?><a href="reservations.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <span class="text-muted small"><?= $totalRows ?> reservation<?= $totalRows!==1?'s':'' ?></span>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Booking Code</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                <tr><td colspan="9" class="text-center text-muted py-5">No reservations found.</td></tr>
                <?php else: foreach ($rows as $row):
                    $nights = max(1,(int)((strtotime($row['check_out'])-strtotime($row['check_in']))/86400));
                    $statusClass = match($row['status']) {
                        'confirmed' => 'success', 'cancelled' => 'danger',
                        'completed' => 'secondary', default => 'warning'
                    };
                ?>
                <tr>
                    <td class="px-3"><code class="small"><?= htmlspecialchars($row['booking_code']) ?></code></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($row['full_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= htmlspecialchars($row['email']) ?></div>
                    </td>
                    <td class="small"><?= htmlspecialchars($row['room_name']) ?></td>
                    <td class="small"><?= htmlspecialchars($row['check_in']) ?></td>
                    <td class="small"><?= htmlspecialchars($row['check_out']) ?></td>
                    <td class="small text-center"><?= $nights ?></td>
                    <td class="small"><?= format_rupiah($row['total_price']??0) ?></td>
                    <td><span class="badge bg-<?= $statusClass ?> text-capitalize"><?= $row['status'] ?></span></td>
                    <td class="text-center">
                        <a href="reservations.php?edit=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-outline-secondary me-1" title="Edit Status">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="reservations.php?delete=<?= $row['id'] ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>&page=<?= $page ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Delete reservation <?= htmlspecialchars($row['booking_code']) ?>? This cannot be undone.');"
                           title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination pagination-sm justify-content-center">
        <li class="page-item <?= $page<=1?'disabled':'' ?>">
            <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">‹</a>
        </li>
        <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
            <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&filter=<?= urlencode($filter) ?>">›</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Edit Status Modal (auto-opens if ?edit= present) -->
<?php if ($editRow): ?>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="res_id" value="<?= (int)$editRow['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Reservation Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <strong><?= htmlspecialchars($editRow['booking_code']) ?></strong> —
                        <?= htmlspecialchars($editRow['full_name']) ?> ·
                        <?= htmlspecialchars($editRow['room_name']) ?>
                    </p>
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select" required>
                        <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $editRow['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <a href="reservations.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" name="update_status" class="btn btn-gold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
});
</script>
<?php endif; ?>

<?php require_once 'admin_footer.php'; ?>
