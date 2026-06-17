<?php
// pages/my_bookings.php
$page_title = 'My Bookings';
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my_bookings');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$msg = '';

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel_id = (int)$_POST['cancel_id'];
    $stmt = $conn->prepare("UPDATE reservations SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'");
    $stmt->bind_param('ii', $cancel_id, $user_id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $msg = '<div class="alert alert-success">Reservation has been cancelled successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Could not cancel this reservation.</div>';
    }
    $stmt->close();
}

// Fetch user's reservations
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset  = ($page - 1) * $perPage;

$whereExtra = '';
$params     = [$user_id];
$types      = 'i';
if ($search !== '') {
    $whereExtra  = " AND (r.booking_code LIKE ? OR rm.name LIKE ?)";
    $like        = "%$search%";
    $params[]    = $like;
    $params[]    = $like;
    $types      .= 'ss';
}

// Total count
$countSql  = "SELECT COUNT(*) FROM reservations r JOIN rooms rm ON r.room_id=rm.id WHERE r.user_id=?$whereExtra";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_row()[0];
$countStmt->close();
$totalPages = ceil($totalRows / $perPage);

// Fetch page
$sql  = "SELECT r.*, rm.name AS room_name, rm.price_per_night, rm.image_url
         FROM reservations r
         JOIN rooms rm ON r.room_id = rm.id
         WHERE r.user_id = ?$whereExtra
         ORDER BY r.created_at DESC
         LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types   .= 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once '../includes/header.php';
?>

<!-- PAGE HERO (slim) -->
<div class="page-hero-slim" style="background:linear-gradient(135deg,var(--ocean) 0%,var(--ocean-dark,#1a3050) 100%);">
    <div class="container py-5 mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2" style="font-size:.8rem;">
                <li class="breadcrumb-item"><a href="../index.php" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item text-white active">My Bookings</li>
            </ol>
        </nav>
        <h1 class="text-white mb-1" style="font-family:var(--font-display);font-size:2rem;">My Bookings</h1>
        <p class="text-white-50 mb-0">Manage and track your reservations</p>
    </div>
</div>

<section class="py-5" style="background:var(--cream);">
    <div class="container">

        <?= $msg ?>

        <!-- Search & Summary -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by booking code or room..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn-harbor px-4">Search</button>
                    <?php if ($search): ?>
                        <a href="my_bookings.php" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted small">
                    Total: <strong><?= $totalRows ?></strong> reservation<?= $totalRows !== 1 ? 's' : '' ?>
                </span>
            </div>
        </div>

        <?php if (empty($reservations)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-harbor" style="font-size:4rem;"></i>
            <h4 class="mt-3 mb-2">No Reservations Found</h4>
            <p class="text-muted">You haven't made any bookings yet. Start planning your getaway!</p>
            <a href="booking.php" class="btn-harbor mt-2">Book Now</a>
        </div>

        <?php else: ?>

        <!-- Bookings List -->
        <div class="d-flex flex-column gap-4">
        <?php foreach ($reservations as $res):
            $nights   = (int)((strtotime($res['check_out']) - strtotime($res['check_in'])) / 86400);
            $nights   = max(1, $nights);
            $imgUrl   = !empty($res['image_url']) ? $res['image_url'] : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=300&q=80';
            $statusClass = match($res['status']) {
                'confirmed' => 'bg-success',
                'cancelled' => 'bg-danger',
                'completed' => 'bg-secondary',
                default      => 'bg-warning text-dark',
            };
        ?>
        <div class="card border-0 shadow-sm overflow-hidden booking-card">
            <div class="row g-0">
                <!-- Room image -->
                <div class="col-md-3" style="min-height:160px;">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Room" 
                         class="h-100 w-100" style="object-fit:cover;">
                </div>
                <!-- Details -->
                <div class="col-md-9">
                    <div class="card-body p-4">
                        <div class="row align-items-start">
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge <?= $statusClass ?> text-capitalize">
                                        <?= htmlspecialchars($res['status']) ?>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="bi bi-hash"></i> <?= htmlspecialchars($res['booking_code']) ?>
                                    </span>
                                </div>
                                <h5 class="fw-semibold mb-1"><?= htmlspecialchars($res['room_name']) ?></h5>
                                <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                                    <span><i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d M Y', strtotime($res['check_in'])) ?>
                                        &rarr; <?= date('d M Y', strtotime($res['check_out'])) ?>
                                    </span>
                                    <span><i class="bi bi-moon me-1"></i><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></span>
                                    <span><i class="bi bi-people me-1"></i><?= (int)$res['guests'] ?> guests</span>
                                    <?php if (!empty($res['rooms_count'])): ?>
                                    <span><i class="bi bi-door-open me-1"></i><?= (int)$res['rooms_count'] ?> room<?= $res['rooms_count'] > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($res['special_request'])): ?>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-chat-dots me-1"></i>
                                    <?= htmlspecialchars(substr($res['special_request'], 0, 100)) ?>
                                    <?= strlen($res['special_request']) > 100 ? '...' : '' ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-4 text-sm-end mt-3 mt-sm-0">
                                <p class="text-muted small mb-1">Total Payment</p>
                                <h5 class="text-harbor fw-semibold mb-3">
                                    <?= format_rupiah($res['total_price'] ?? 0) ?>
                                </h5>
                                <div class="d-flex flex-column gap-2 align-items-sm-end">
                                    <a href="booking_confirm.php?code=<?= urlencode($res['booking_code']) ?>" 
                                       class="btn btn-sm btn-outline-gold">
                                        <i class="bi bi-eye me-1"></i>View Detail
                                    </a>
                                    <?php if ($res['status'] === 'pending'): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                        <input type="hidden" name="cancel_id" value="<?= (int)$res['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-circle me-1"></i>Cancel
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
