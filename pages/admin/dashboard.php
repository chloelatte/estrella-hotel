<?php
// pages/admin/dashboard.php
$page_title = 'Dashboard';
require_once 'admin_guard.php';

// Stats
$stats = [];

$r = $conn->query("SELECT COUNT(*) FROM reservations");
$stats['total_reservations'] = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM reservations WHERE status='pending'");
$stats['pending'] = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM reservations WHERE status='confirmed'");
$stats['confirmed'] = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE status!='cancelled'");
$stats['revenue'] = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM users WHERE role='guest'");
$stats['users'] = $r ? $r->fetch_row()[0] : 0;

$r = $conn->query("SELECT COUNT(*) FROM rooms");
$stats['rooms'] = $r ? $r->fetch_row()[0] : 0;

// Recent reservations
$recent = $conn->query("SELECT r.*, u.full_name, rm.name AS room_name 
    FROM reservations r 
    JOIN users u ON r.user_id=u.id 
    JOIN rooms rm ON r.room_id=rm.id 
    ORDER BY r.created_at DESC LIMIT 8");

require_once 'admin_sidebar.php';
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['icon'=>'bi-calendar-check','label'=>'Total Reservations','value'=>$stats['total_reservations'],'color'=>'var(--ocean)'],
        ['icon'=>'bi-clock-history', 'label'=>'Pending',            'value'=>$stats['pending'],           'color'=>'#d97706'],
        ['icon'=>'bi-check-circle',  'label'=>'Confirmed',          'value'=>$stats['confirmed'],         'color'=>'#16a34a'],
        ['icon'=>'bi-currency-dollar','label'=>'Revenue',           'value'=>format_rupiah($stats['revenue']),'color'=>'var(--gold)'],
        ['icon'=>'bi-people',        'label'=>'Registered Guests',  'value'=>$stats['users'],             'color'=>'#7c3aed'],
        ['icon'=>'bi-door-open',     'label'=>'Total Rooms',        'value'=>$stats['rooms'],             'color'=>'#0891b2'],
    ];
    foreach ($cards as $c):
    ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card border-0 shadow-sm h-100 p-3 text-center">
            <i class="bi <?= $c['icon'] ?> mb-2" style="font-size:1.8rem;color:<?= $c['color'] ?>;"></i>
            <div class="fw-bold fs-5" style="color:<?= $c['color'] ?>;"><?= $c['value'] ?></div>
            <div class="text-muted" style="font-size:.75rem;"><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Recent Reservations -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold">Recent Reservations</h6>
        <a href="reservations.php" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="dashRecentTable">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Booking Code</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recent && $recent->num_rows > 0):
                    while ($row = $recent->fetch_assoc()):
                    $statusClass = match($row['status']) {
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'secondary',
                        default     => 'warning',
                    };
                ?>
                <tr>
                    <td class="px-4"><code><?= htmlspecialchars($row['booking_code']) ?></code></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['room_name']) ?></td>
                    <td><?= htmlspecialchars($row['check_in']) ?></td>
                    <td><?= format_rupiah($row['total_price'] ?? 0) ?></td>
                    <td><span class="badge bg-<?= $statusClass ?> text-capitalize"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td>
                        <a href="reservations.php?edit=<?= (int)$row['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No reservations yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'admin_footer.php'; ?>
