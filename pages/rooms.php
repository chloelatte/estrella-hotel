<?php
// pages/rooms.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Our Rooms';
$base = '../';

// Filters dari GET
$type_filter    = htmlspecialchars($_GET['type']     ?? '', ENT_QUOTES, 'UTF-8');
$guests_filter  = (int)($_GET['guests']   ?? 0);
$budget_filter  = htmlspecialchars($_GET['budget']   ?? '', ENT_QUOTES, 'UTF-8');
$checkin_filter = htmlspecialchars($_GET['checkin']  ?? '', ENT_QUOTES, 'UTF-8');
$checkout_filter= htmlspecialchars($_GET['checkout'] ?? '', ENT_QUOTES, 'UTF-8');
$search         = htmlspecialchars($_GET['search']   ?? '', ENT_QUOTES, 'UTF-8');

// Build query
$where = ['r.is_available = 1'];
$params = [];
$types  = '';

if ($type_filter) {
    $where[] = 'r.type = ?';
    $params[] = $type_filter;
    $types .= 's';
}
if ($guests_filter > 0) {
    $where[] = 'r.capacity >= ?';
    $params[] = $guests_filter;
    $types .= 'i';
}
if ($budget_filter === 'budget') {
    $where[] = 'r.price_per_night <= 1500000';
} elseif ($budget_filter === 'mid') {
    $where[] = 'r.price_per_night BETWEEN 1500001 AND 3000000';
} elseif ($budget_filter === 'luxury') {
    $where[] = 'r.price_per_night > 3000000';
}
if ($search) {
    $like = '%' . $search . '%';
    $where[] = '(r.name LIKE ? OR r.description LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql = "SELECT r.* FROM rooms r WHERE " . implode(' AND ', $where) . " ORDER BY r.price_per_night ASC";
if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rooms_result = $stmt->get_result();
} else {
    $rooms_result = $conn->query($sql);
}
$rooms = [];
while ($r = $rooms_result->fetch_assoc()) $rooms[] = $r;

$room_images = [
    'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=80',
    'deluxe'       => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=700&q=80',
    'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700&q=80',
    'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=700&q=80',
];

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="min-height:240px;background-image:url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content">
        <span class="label-eyebrow" style="color:var(--sky-light);justify-content:center;display:flex;margin-bottom:.5rem;">Accommodations</span>
        <h1 class="page-hero__title">Find Your Perfect Room</h1>
        <p class="page-hero__sub">Every room a sanctuary — designed for comfort, crafted for memorable stays.</p>
    </div>
</section>

<div class="container py-5">
    <!-- Filter Bar -->
    <div class="filter-bar mb-4">
        <form method="GET" action="rooms.php">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label">Search</label>
                    <div class="search-box">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" class="form-control"
                            placeholder="Search rooms..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Room Type</label>
                    <div class="custom-select-wrap">
                        <select name="type" class="form-select custom-select-chevron">
                            <option value="">All Types</option>
                            <option value="standard"     <?= $type_filter==='standard'     ? 'selected':'' ?>>Standard</option>
                            <option value="deluxe"       <?= $type_filter==='deluxe'       ? 'selected':'' ?>>Deluxe</option>
                            <option value="executive"    <?= $type_filter==='executive'    ? 'selected':'' ?>>Executive</option>
                            <option value="presidential" <?= $type_filter==='presidential' ? 'selected':'' ?>>Presidential</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Guests</label>
                    <div class="custom-select-wrap">
                        <select name="guests" class="form-select custom-select-chevron">
                            <option value="0">Any</option>
                            <option value="1" <?= $guests_filter===1 ? 'selected':'' ?>>1 Guest</option>
                            <option value="2" <?= $guests_filter===2 ? 'selected':'' ?>>2 Guests</option>
                            <option value="3" <?= $guests_filter===3 ? 'selected':'' ?>>3 Guests</option>
                            <option value="4" <?= $guests_filter===4 ? 'selected':'' ?>>4 Guests</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label">Budget</label>
                    <div class="custom-select-wrap">
                        <select name="budget" class="form-select custom-select-chevron">
                            <option value="">Any Budget</option>
                            <option value="budget" <?= $budget_filter==='budget' ? 'selected':'' ?>>Up to Rp 1.5M</option>
                            <option value="mid"    <?= $budget_filter==='mid'    ? 'selected':'' ?>>Rp 1.5M – 3M</option>
                            <option value="luxury" <?= $budget_filter==='luxury' ? 'selected':'' ?>>Above Rp 3M</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn-harbor w-100" style="border-radius:5px;font-size:.68rem;letter-spacing:.12em;padding:.72rem 1rem;">
                        Find Rooms
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="d-flex align-items-center gap-2 mb-4">
        <h5 style="font-family:'Playfair Display',serif;font-weight:600;color:var(--harbor-dark);margin:0;">Our Room Collection</h5>
        <span class="badge ms-1" style="background:var(--linen);color:var(--harbor);font-size:.72rem;border:1px solid var(--border);font-family:'DM Sans',sans-serif;"><?= count($rooms) ?> rooms</span>
    </div>

    <!-- No results -->
    <div id="noSearchResult" style="display:none;" class="alert alert-estrella">
        <i class="bi bi-info-circle me-2"></i>Tidak ada kamar yang sesuai pencarian.
    </div>

    <?php if (empty($rooms)): ?>
    <div class="text-center py-5">
        <i class="bi bi-door-closed" style="font-size:3rem;color:var(--border);"></i>
        <p class="mt-3 text-muted">Tidak ada kamar yang tersedia dengan filter ini.</p>
        <a href="rooms.php" class="btn btn-ghost-gold">Lihat Semua Kamar</a>
    </div>
    <?php else: ?>

    <?php foreach ($rooms as $room):
        $img = $room_images[$room['type']] ?? $room_images['standard'];
    ?>
    <div class="hcard searchable-row mb-4">
        <div class="row g-0 align-items-stretch">
            <!-- Fixed-height image column -->
            <div class="col-md-4 col-lg-3">
                <div class="hcard__img-wrap" style="min-height:230px;">
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($room['name']) ?>" loading="lazy">
                    <span class="hcard__tag"><?= ucfirst($room['type']) ?></span>
                </div>
            </div>
            <!-- Content column -->
            <div class="col-md-8 col-lg-9">
                <div class="hcard__body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <h3 class="hcard__name"><?= htmlspecialchars($room['name']) ?></h3>
                        <div class="hcard__price-header text-end flex-shrink-0">
                            <?= format_rupiah($room['price_per_night']) ?>
                            <small>/ night</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="meta-pill"><i class="bi bi-arrows-fullscreen"></i><?= $room['size_sqm'] ?>m²</span>
                        <span class="meta-pill"><i class="bi bi-people"></i><?= $room['capacity'] ?> Guests</span>
                        <span class="meta-pill"><i class="bi bi-eye"></i><?= htmlspecialchars($room['view_type']) ?></span>
                        <?php if (!empty($room['bed_type'])): ?>
                        <span class="meta-pill"><i class="bi bi-moon"></i><?= htmlspecialchars($room['bed_type']) ?></span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size:.87rem;color:var(--muted);line-height:1.78;flex:1;margin-bottom:1.4rem;">
                        <?= htmlspecialchars($room['description']) ?>
                    </p>
                    <div class="d-flex gap-2 flex-wrap mt-auto">
                        <a href="room_detail.php?id=<?= $room['id'] ?>" class="btn-outline-harbor" style="font-size:.66rem;padding:.52rem 1.1rem;">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                        <a href="booking.php?room_id=<?= $room['id'] ?>" class="btn-harbor" style="font-size:.66rem;padding:.52rem 1.1rem;">
                            <i class="bi bi-calendar2-check me-1"></i>Book Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Guarantee badges -->
    <div class="row g-3 mt-3 text-center">
        <?php
        $badges = [
            ['bi-award', 'BEST RATE GUARANTEE', 'Get the best price when you book directly.'],
            ['bi-heart', 'FLEXIBLE BOOKING', 'Free cancellation up to 48 hours before arrival.'],
            ['bi-gift', 'EXCLUSIVE BENEFITS', 'Special privileges and seasonal offers.'],
            ['bi-headset', '24/7 CUSTOMER SERVICE', "We're here to assist you, anytime."],
        ];
        foreach ($badges as $b): ?>
        <div class="col-6 col-md-3">
            <i class="bi <?= $b[0] ?> text-gold d-block mb-1" style="font-size:1.4rem;"></i>
            <p class="mb-0" style="font-size:.68rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;"><?= $b[1] ?></p>
            <p class="mb-0 text-muted" style="font-size:.75rem;"><?= $b[2] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
