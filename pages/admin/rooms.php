<?php
// pages/admin/rooms.php
$page_title = 'Manage Rooms';
require_once 'admin_guard.php';

$msg = '';

// ── DELETE ──────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
    $stmt->bind_param('i', $del);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success alert-dismissible fade show">Room deleted.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    } else {
        $msg = '<div class="alert alert-danger">Cannot delete — room may have reservations.</div>';
    }
    $stmt->close();
}

// ── CREATE / UPDATE ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = sanitize($_POST['name'] ?? '');
    $type        = sanitize($_POST['type'] ?? 'standard');
    $price       = (int)($_POST['price_per_night'] ?? 0);
    $capacity    = (int)($_POST['capacity'] ?? 2);
    $size_sqm    = (int)($_POST['size_sqm'] ?? 25);
    $view_type   = sanitize($_POST['view_type'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $image_url   = sanitize($_POST['image_url'] ?? '');
    $is_available= isset($_POST['is_available']) ? 1 : 0;

    // JS validation already ran; server-side check
    $errors = [];
    if ($name === '')  $errors[] = 'Room name is required.';
    if ($price <= 0)   $errors[] = 'Price must be greater than 0.';

    if (empty($errors)) {
        if (isset($_POST['room_id']) && is_numeric($_POST['room_id'])) {
            // UPDATE
            $id   = (int)$_POST['room_id'];
            $stmt = $conn->prepare("UPDATE rooms SET name=?,type=?,price_per_night=?,capacity=?,size_sqm=?,view_type=?,description=?,image_url=?,is_available=? WHERE id=?");
            $stmt->bind_param('ssiiisssi i', $name,$type,$price,$capacity,$size_sqm,$view_type,$description,$image_url,$is_available,$id);
            // fix bind (no space)
            $stmt->close();
            $stmt = $conn->prepare("UPDATE rooms SET name=?,type=?,price_per_night=?,capacity=?,size_sqm=?,view_type=?,description=?,image_url=?,is_available=? WHERE id=?");
            $stmt->bind_param('ssiiissiii', $name,$type,$price,$capacity,$size_sqm,$view_type,$description,$image_url,$is_available,$id);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success alert-dismissible fade show">Room updated successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            } else {
                $msg = '<div class="alert alert-danger">Update failed: ' . htmlspecialchars($conn->error) . '</div>';
            }
            $stmt->close();
        } else {
            // CREATE
            $stmt = $conn->prepare("INSERT INTO rooms (name,type,price_per_night,capacity,size_sqm,view_type,description,image_url,is_available) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssiiisssi', $name,$type,$price,$capacity,$size_sqm,$view_type,$description,$image_url,$is_available);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success alert-dismissible fade show">Room added successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            } else {
                $msg = '<div class="alert alert-danger">Insert failed: ' . htmlspecialchars($conn->error) . '</div>';
            }
            $stmt->close();
        }
    } else {
        $msg = '<div class="alert alert-danger">' . implode('<br>', $errors) . '</div>';
    }
}

// ── FETCH LIST ───────────────────────────────────────────────
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1,(int)($_GET['page'] ?? 1));
$perPage = 8;
$offset  = ($page-1)*$perPage;

$where  = '';
$params = [];
$types  = '';
if ($search !== '') {
    $where  = "WHERE name LIKE ? OR type LIKE ?";
    $like   = "%$search%";
    $params = [$like, $like];
    $types  = 'ss';
}

$cStmt = $conn->prepare("SELECT COUNT(*) FROM rooms $where");
if ($types) $cStmt->bind_param($types,...$params);
$cStmt->execute();
$totalRows = $cStmt->get_result()->fetch_row()[0];
$cStmt->close();
$totalPages = ceil($totalRows/$perPage);

$fp = array_merge($params,[$perPage,$offset]);
$ft = $types.'ii';
$stmt = $conn->prepare("SELECT * FROM rooms $where ORDER BY id DESC LIMIT ? OFFSET ?");
if ($ft) $stmt->bind_param($ft,...$fp);
$stmt->execute();
$rooms = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch single for edit
$editRoom = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE id=?");
    $stmt->bind_param('i',(int)$_GET['edit']);
    $stmt->execute();
    $editRoom = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

require_once 'admin_sidebar.php';
?>

<?= $msg ?>

<!-- Toolbar -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control form-control-sm" style="width:220px;"
               placeholder="Search room name / type…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-sm btn-gold">Search</button>
        <?php if ($search): ?><a href="rooms.php" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
    </form>
    <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#roomModal">
        <i class="bi bi-plus-lg me-1"></i>Add Room
    </button>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-3">Room</th>
                        <th>Type</th>
                        <th>Price/Night</th>
                        <th>Capacity</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rooms)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">No rooms found.</td></tr>
                <?php else: foreach ($rooms as $room): ?>
                <tr>
                    <td class="px-3">
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($room['image_url'])): ?>
                            <img src="<?= htmlspecialchars($room['image_url']) ?>" 
                                 class="rounded" style="width:50px;height:40px;object-fit:cover;" alt="">
                            <?php else: ?>
                            <div class="rounded bg-light d-flex align-items-center justify-content-center" 
                                 style="width:50px;height:40px;">
                                <i class="bi bi-image text-muted small"></i>
                            </div>
                            <?php endif; ?>
                            <span class="fw-semibold small"><?= htmlspecialchars($room['name']) ?></span>
                        </div>
                    </td>
                    <td class="small text-capitalize"><?= htmlspecialchars($room['type']) ?></td>
                    <td class="small"><?= format_rupiah($room['price_per_night']) ?></td>
                    <td class="small text-center"><?= (int)$room['capacity'] ?> guests</td>
                    <td class="small"><?= (int)$room['size_sqm'] ?> m²</td>
                    <td>
                        <span class="badge <?= $room['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $room['is_available'] ? 'Available' : 'Unavailable' ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="rooms.php?edit=<?= $room['id'] ?>" 
                           class="btn btn-sm btn-outline-secondary me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="rooms.php?delete=<?= $room['id'] ?>&search=<?= urlencode($search) ?>&page=<?= $page ?>"
                           class="btn btn-sm btn-outline-danger" title="Delete"
                           onclick="return confirm('Delete room &quot;<?= htmlspecialchars(addslashes($room['name'])) ?>&quot;? This cannot be undone.');">
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

<!-- Add / Edit Modal -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="roomForm" novalidate>
                <?php if ($editRoom): ?>
                <input type="hidden" name="room_id" value="<?= (int)$editRoom['id'] ?>">
                <?php endif; ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= $editRoom ? 'Edit Room' : 'Add New Room' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Room Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="inp_name" class="form-control"
                                   value="<?= htmlspecialchars($editRoom['name'] ?? '') ?>" required>
                            <div class="invalid-feedback" id="err_name"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="type" class="form-select">
                                <?php foreach (['standard','deluxe','executive','presidential'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($editRoom['type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price / Night (IDR) <span class="text-danger">*</span></label>
                            <input type="number" name="price_per_night" id="inp_price" class="form-control" min="0"
                                   value="<?= htmlspecialchars($editRoom['price_per_night'] ?? '') ?>" required>
                            <div class="invalid-feedback" id="err_price"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Capacity (guests)</label>
                            <input type="number" name="capacity" class="form-control" min="1" max="10"
                                   value="<?= htmlspecialchars($editRoom['capacity'] ?? 2) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Size (m²)</label>
                            <input type="number" name="size_sqm" class="form-control" min="1"
                                   value="<?= htmlspecialchars($editRoom['size_sqm'] ?? 25) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">View Type</label>
                            <input type="text" name="view_type" class="form-control"
                                   placeholder="Ocean View, Garden View…"
                                   value="<?= htmlspecialchars($editRoom['view_type'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Image URL</label>
                            <input type="url" name="image_url" class="form-control"
                                   placeholder="https://…"
                                   value="<?= htmlspecialchars($editRoom['image_url'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editRoom['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_available" 
                                       id="isAvailable" <?= ($editRoom['is_available'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isAvailable">Available for booking</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="rooms.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-gold">
                        <?= $editRoom ? 'Save Changes' : 'Add Room' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JS Validation for room form
document.getElementById('roomForm').addEventListener('submit', function(e) {
    let valid = true;
    const name  = document.getElementById('inp_name');
    const price = document.getElementById('inp_price');

    document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    name.classList.remove('is-invalid'); price.classList.remove('is-invalid');

    if (!name.value.trim()) {
        name.classList.add('is-invalid');
        document.getElementById('err_name').textContent = 'Room name is required.';
        valid = false;
    }
    if (!price.value || parseFloat(price.value) <= 0) {
        price.classList.add('is-invalid');
        document.getElementById('err_price').textContent = 'Price must be greater than 0.';
        valid = false;
    }
    if (!valid) e.preventDefault();
});

<?php if ($editRoom): ?>
// Auto-open modal when editing
document.addEventListener('DOMContentLoaded', function() {
    new bootstrap.Modal(document.getElementById('roomModal')).show();
});
<?php endif; ?>
</script>

<?php require_once 'admin_footer.php'; ?>
