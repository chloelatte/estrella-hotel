<?php
// pages/room_detail.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Room Detail';
$base = '../';

$room_id = (int)($_GET['id'] ?? 0);
if (!$room_id) { header('Location: rooms.php'); exit; }

// Get room + amenities (JOIN query)
$stmt = $conn->prepare("SELECT r.*, GROUP_CONCAT(a.name ORDER BY a.id SEPARATOR '||') AS amenity_names,
    GROUP_CONCAT(a.icon ORDER BY a.id SEPARATOR '||') AS amenity_icons
    FROM rooms r
    LEFT JOIN room_amenities ra ON r.id = ra.room_id
    LEFT JOIN amenities a ON ra.amenity_id = a.id
    WHERE r.id = ? GROUP BY r.id");
$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room   = $result->fetch_assoc();
$stmt->close();

if (!$room) { header('Location: rooms.php'); exit; }
$page_title = htmlspecialchars($room['name']);

$amenities = $room['amenity_names'] ? array_combine(
    explode('||', $room['amenity_names']),
    explode('||', $room['amenity_icons'])
) : [];

$room_images = [
    'standard'     => [
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&q=80',
        'https://images.unsplash.com/photo-1566195992011-5f6b21e539aa?w=500&q=80',
        'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=500&q=80',
        'https://images.unsplash.com/photo-1560184897-ae75f418493e?w=500&q=80',
    ],
    'deluxe'       => [
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=900&q=80',
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=500&q=80',
        'https://images.unsplash.com/photo-1620626011761-996317702782?w=500&q=80',
        'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=500&q=80',
    ],
    'executive'    => [
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=900&q=80',
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500&q=80',
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=500&q=80',
        'https://images.unsplash.com/photo-1620626011761-996317702782?w=500&q=80',
    ],
    'presidential' => [
        'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=900&q=80',
        'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=500&q=80',
        'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=500&q=80',
        'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500&q=80',
    ],
];
$imgs = $room_images[$room['type']] ?? $room_images['standard'];

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size:.8rem;">
            <li class="breadcrumb-item"><a href="../index.php"><i class="bi bi-house"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="rooms.php">Rooms</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($room['name']) ?></li>
        </ol>
    </nav>

    <!-- Image Gallery -->
    <div class="row g-2 mb-4">
        <div class="col-md-7">
            <img src="<?= $imgs[0] ?>" alt="<?= htmlspecialchars($room['name']) ?>"
                style="width:100%;height:380px;object-fit:cover;border-radius:8px;">
        </div>
        <div class="col-md-5">
            <div class="row g-2">
                <?php for($i=1;$i<4;$i++): if(!isset($imgs[$i])) break; ?>
                <div class="col-6">
                    <img src="<?= $imgs[$i] ?>" alt="Room view <?= $i ?>"
                        style="width:100%;height:<?= $i<=2?'180':'180' ?>px;object-fit:cover;border-radius:8px;">
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left: Details -->
        <div class="col-lg-8">
            <h1 style="font-family:'Cormorant Garamond',serif;font-size:2.2rem;font-weight:600;text-transform:uppercase;">
                <?= htmlspecialchars($room['name']) ?>
            </h1>
            <div class="room-meta mb-3">
                <span class="room-meta-item"><i class="bi bi-arrows-fullscreen"></i> <?= $room['size_sqm'] ?>m²</span>
                <span class="room-meta-item"><i class="bi bi-people"></i> <?= $room['capacity'] ?> Guests</span>
                <span class="room-meta-item"><i class="bi bi-eye"></i> <?= htmlspecialchars($room['view_type']) ?></span>
                <span class="room-meta-item"><i class="bi bi-door-open"></i> <?= htmlspecialchars($room['bed_type']) ?></span>
            </div>
            <p class="text-muted"><?= htmlspecialchars($room['description']) ?></p>

            <!-- Amenities -->
            <h5 class="mt-4 mb-3" style="font-family:'Jost',sans-serif;font-size:.85rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;">ROOM AMENITIES</h5>
            <div class="d-flex flex-wrap gap-1 mb-4">
                <?php foreach ($amenities as $name => $icon): ?>
                <span class="amenity-chip"><i class="bi <?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($name) ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs border-bottom" id="roomTab" role="tablist" style="border-color:var(--border);">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#details" style="font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;">Details</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#facilities" style="font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;">Room Facilities</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#policies" style="font-size:.8rem;letter-spacing:.1em;text-transform:uppercase;">Policies</button></li>
            </ul>
            <div class="tab-content pt-4" id="roomTabContent">
                <div class="tab-pane fade show active" id="details">
                    <p class="text-muted" style="font-size:.9rem;"><?= htmlspecialchars($room['description']) ?></p>
                    <ul class="text-muted" style="font-size:.88rem;line-height:2;">
                        <li>Spacious <?= $room['size_sqm'] ?>m² room with modern coastal design</li>
                        <li><?= htmlspecialchars($room['view_type']) ?> — breathtaking scenery</li>
                        <li><?= htmlspecialchars($room['bed_type']) ?></li>
                        <li>Complimentary minibar (replenished daily)</li>
                        <li>Luxurious bathroom with walk-in shower</li>
                        <li>High-speed Wi-Fi and Smart TV</li>
                        <li>24-hour room service</li>
                    </ul>
                </div>
                <div class="tab-pane fade" id="facilities">
                    <div class="row g-3">
                        <?php foreach ($amenities as $name => $icon): ?>
                        <div class="col-6 col-md-4 d-flex align-items-center gap-2 text-muted" style="font-size:.88rem;">
                            <i class="bi <?= htmlspecialchars($icon) ?> text-gold"></i>
                            <?= htmlspecialchars($name) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="policies">
                    <ul class="text-muted" style="font-size:.88rem;line-height:2.2;">
                        <li>Check-in: 14.00 | Check-out: 12.00</li>
                        <li>Free cancellation up to 48 hours before check-in</li>
                        <li>No smoking in rooms (outdoor area available)</li>
                        <li>Pets not allowed</li>
                        <li>Valid ID required at check-in</li>
                        <li>Children under 5 stay free</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right: Booking widget -->
        <div class="col-lg-4">
            <div class="booking-summary">
                <p class="section-eyebrow mb-1">BOOK YOUR STAY</p>
                <p class="text-muted mb-1" style="font-size:.8rem;">From</p>
                <div class="room-price mb-3" style="font-size:1.6rem;">
                    <?= format_rupiah($room['price_per_night']) ?> <span>/ night</span>
                </div>

                <form method="GET" action="booking.php">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Check-in</label>
                        <input type="date" name="checkin" class="form-control"
                            min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                            value="<?= htmlspecialchars($_GET['checkin'] ?? date('Y-m-d', strtotime('+1 day'))) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Check-out</label>
                        <input type="date" name="checkout" class="form-control"
                            min="<?= date('Y-m-d', strtotime('+2 days')) ?>"
                            value="<?= htmlspecialchars($_GET['checkout'] ?? date('Y-m-d', strtotime('+3 days'))) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Guests</label>
                        <select name="guests" class="form-select">
                            <?php for($g=1;$g<=$room['capacity'];$g++): ?>
                            <option value="<?= $g ?>" <?= ((int)($_GET['guests']??2))===$g?'selected':'' ?>>
                                <?= $g ?> Guest<?= $g>1?'s':'' ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">CHECK AVAILABILITY</button>
                </form>

                <hr style="border-color:var(--border);margin:1.25rem 0;">

                <div class="d-flex align-items-start gap-2 mb-2">
                    <i class="bi bi-shield-check text-gold"></i>
                    <div>
                        <strong style="font-size:.82rem;">Best rate guarantee</strong>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">Get the best price when you book directly with us.</p>
                    </div>
                </div>

                <h6 class="mt-3 mb-2" style="font-size:.75rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;">WHY BOOK DIRECTLY?</h6>
                <?php $perks = ['Best Rate Guarantee','Exclusive Member Benefits','Flexible Cancellation','Personalized Service']; ?>
                <div class="row g-1">
                    <?php foreach ($perks as $p): ?>
                    <div class="col-6 d-flex align-items-center gap-1" style="font-size:.75rem;color:var(--gray-muted);">
                        <i class="bi bi-check2 text-gold"></i> <?= $p ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
