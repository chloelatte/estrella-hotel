<?php
// pages/booking.php – Step 1: Select Room + Step 2: Booking Details
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Book Your Stay';
$base = '../';

// Get rooms from DB
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_available = 1 ORDER BY price_per_night ASC");
$rooms = [];
while ($r = $rooms_result->fetch_assoc()) $rooms[] = $r;

// Pre-selected values from GET params
$preselect_room = (int)($_GET['room_id']  ?? 0);
$pre_checkin    = htmlspecialchars($_GET['checkin']  ?? date('Y-m-d', strtotime('+1 day')), ENT_QUOTES, 'UTF-8');
$pre_checkout   = htmlspecialchars($_GET['checkout'] ?? date('Y-m-d', strtotime('+3 days')), ENT_QUOTES, 'UTF-8');
$pre_guests     = (int)($_GET['guests'] ?? 2);

// Handle Step 2 POST (go to add-ons)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'booking_details') {
    // Store in session
    $room_id   = (int)$_POST['room_id'];
    $guest_name  = htmlspecialchars(trim($_POST['guest_name']  ?? ''), ENT_QUOTES, 'UTF-8');
    $guest_email = htmlspecialchars(trim($_POST['guest_email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $guest_phone = htmlspecialchars(trim($_POST['guest_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $checkin     = htmlspecialchars(trim($_POST['checkin_date'] ?? ''), ENT_QUOTES, 'UTF-8');
    $checkout    = htmlspecialchars(trim($_POST['checkout_date'] ?? ''), ENT_QUOTES, 'UTF-8');
    $num_guests  = (int)($_POST['num_guests'] ?? 2);
    $num_rooms   = (int)($_POST['num_rooms']  ?? 1);
    $special_req = htmlspecialchars(trim($_POST['special_request'] ?? ''), ENT_QUOTES, 'UTF-8');

    $errors = [];
    if (!$room_id)                       $errors[] = 'Pilih kamar terlebih dahulu.';
    if (strlen($guest_name) < 3)         $errors[] = 'Nama tidak valid.';
    if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($guest_phone) < 8)        $errors[] = 'Nomor telepon tidak valid.';
    if (!$checkin || !$checkout)         $errors[] = 'Pilih tanggal check-in dan check-out.';
    if ($checkout <= $checkin)           $errors[] = 'Checkout harus setelah checkin.';

    if (empty($errors)) {
        // Get room price from DB (not from POST – prevent tampering)
        $stmt = $conn->prepare("SELECT price_per_night FROM rooms WHERE id = ?");
        $stmt->bind_param('i', $room_id);
        $stmt->execute();
        $rr = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $nights = max(1, (strtotime($checkout) - strtotime($checkin)) / 86400);

        $_SESSION['booking'] = [
            'room_id'         => $room_id,
            'guest_name'      => $guest_name,
            'guest_email'     => $guest_email,
            'guest_phone'     => $guest_phone,
            'checkin_date'    => $checkin,
            'checkout_date'   => $checkout,
            'num_nights'      => $nights,
            'num_rooms'       => $num_rooms,
            'num_guests'      => $num_guests,
            'special_request' => $special_req,
            'room_price'      => $rr['price_per_night'],
        ];
        header('Location: booking_addons.php');
        exit;
    }
}

// Defaults
$selected_room = null;
foreach ($rooms as $r) {
    if ($r['id'] === $preselect_room) { $selected_room = $r; break; }
}

$room_images = [
    'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=1200&q=90',
    'deluxe'       => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=1200&q=90',
    'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=1200&q=90',
    'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=1200&q=90',
];

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="min-height:280px;background-image:url('https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">Book Your Stay</h1>
        <p class="page-hero__sub">A few simple steps to your perfect getaway.</p>
    </div>
</section>

<div class="container py-5">
    <!-- Booking Steps indicator -->
    <div class="booking-steps">
        <div class="d-flex justify-content-between" id="stepsIndicator">
            <div class="step-item" id="si-1">
                <div class="step-icon active" id="si-icon-1"><i class="bi bi-door-open"></i></div>
                <span class="step-label active" id="si-lbl-1">1. SELECT ROOM</span>
            </div>
            <div class="step-item" id="si-2">
                <div class="step-icon" id="si-icon-2"><i class="bi bi-person"></i></div>
                <span class="step-label" id="si-lbl-2">2. BOOKING DETAILS</span>
            </div>
            <div class="step-item" id="si-3">
                <div class="step-icon" id="si-icon-3"><i class="bi bi-gift"></i></div>
                <span class="step-label" id="si-lbl-3">3. ADD-ONS</span>
            </div>
            <div class="step-item" id="si-4">
                <div class="step-icon" id="si-icon-4"><i class="bi bi-credit-card"></i></div>
                <span class="step-label" id="si-lbl-4">4. PAYMENT</span>
            </div>
            <div class="step-item" id="si-5">
                <div class="step-icon" id="si-icon-5"><i class="bi bi-check2-circle"></i></div>
                <span class="step-label" id="si-lbl-5">5. CONFIRMATION</span>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form id="bookingDetailsForm" method="POST" action="booking.php" novalidate>
        <input type="hidden" name="step" value="booking_details">
        <input type="hidden" name="room_id" id="selected_room_id" value="<?= $preselect_room ?>">
        <input type="hidden" id="room_price_val" value="<?= $selected_room['price_per_night'] ?? 0 ?>">
        <input type="hidden" id="num_nights_val" value="">
        <input type="hidden" id="num_rooms_val" value="1">

        <div class="row g-5">
            <!-- LEFT: Step 1 → select room, Step 2 → booking details -->
            <div class="col-lg-7">

                <!-- ─── Step 1: Select Room + Dates ─── -->
                <div class="estrella-card mb-4" id="step1Panel">
                    <h5 class="section-eyebrow mb-3">1. SELECT YOUR ROOM</h5>
                    <div class="row g-3 mb-4">
                        <?php foreach ($rooms as $room):
                            $img = $room_images[$room['type']] ?? $room_images['standard'];
                            $is_selected = ($preselect_room === (int)$room['id']);
                        ?>
                        <div class="col-6 col-md-3">
                            <div class="room-selector-card <?= $is_selected ? 'selected' : '' ?>"
                                data-room-id="<?= $room['id'] ?>"
                                data-room-price="<?= $room['price_per_night'] ?>"
                                data-room-name="<?= htmlspecialchars($room['name']) ?>"
                                onclick="selectRoom(this)">
                                <div class="check-badge"><i class="bi bi-check"></i></div>
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($room['name']) ?>"
                                    style="width:100%;height:90px;object-fit:cover;">
                                <div class="p-2">
                                    <p style="font-size:.7rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-bottom:2px;">
                                        <?= htmlspecialchars($room['name']) ?>
                                    </p>
                                    <p style="font-size:.68rem;color:var(--muted);margin-bottom:2px;">
                                        <i class="bi bi-arrows-fullscreen"></i> <?= $room['size_sqm'] ?>m²
                                        · <i class="bi bi-people"></i> <?= $room['capacity'] ?>
                                    </p>
                                    <p class="mb-0" style="font-size:.78rem;font-weight:600;color:var(--harbor);">
                                        IDR <?= number_format($room['price_per_night'],0,',','.') ?>/night
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dates row inside step 1 -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Check-in Date</label>
                            <input type="date" class="form-control" id="checkin_date" name="checkin_date"
                                value="<?= $pre_checkin ?>"
                                min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Check-out Date</label>
                            <input type="date" class="form-control" id="checkout_date" name="checkout_date"
                                value="<?= $pre_checkout ?>"
                                min="<?= date('Y-m-d', strtotime('+2 days')) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Guests</label>
                            <select class="form-select" name="num_guests" id="num_guests_input">
                                <?php for ($g = 1; $g <= 4; $g++): ?>
                                <option value="<?= $g ?>" <?= $pre_guests===$g?'selected':'' ?>><?= $g ?> Guest<?= $g>1?'s':'' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Continue to Step 2 -->
                    <div class="d-flex justify-content-end pt-1">
                        <button type="button" class="btn-harbor" id="continueToStep2Btn"
                            onclick="showStep2()" style="border-radius:5px;">
                            Continue to Booking Details &rarr;
                        </button>
                    </div>
                </div>

                <!-- ─── Step 2: Booking Details (shown after step 1) ─── -->
                <div class="estrella-card mb-4 position-relative" id="step2Panel"
                     style="<?= $preselect_room ? '' : 'opacity:.45;pointer-events:none;user-select:none;' ?>">
                    <!-- Lock icon shown when step 1 not done -->
                    <?php if (!$preselect_room): ?>
                    <div id="step2Lock" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;z-index:5;border-radius:var(--r-md);">
                        <div class="text-center">
                            <i class="bi bi-lock text-muted" style="font-size:1.75rem;"></i>
                            <p class="text-muted mt-2 mb-0" style="font-size:.82rem;">Complete Step 1 first</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <h5 class="section-eyebrow mb-1">2. BOOKING DETAILS</h5>
                    <p class="text-muted mb-3" style="font-size:.85rem;">Fill in your personal information</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="guest_name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="guest_name" name="guest_name" class="form-control"
                                placeholder="Enter your full name"
                                value="<?= htmlspecialchars($_POST['guest_name'] ?? ($_SESSION['full_name'] ?? '')) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="guest_email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="guest_email" name="guest_email" class="form-control"
                                placeholder="Enter your email"
                                value="<?= htmlspecialchars($_POST['guest_email'] ?? ($_SESSION['email'] ?? '')) ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="guest_phone">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" style="font-size:.85rem;">+62</span>
                                <input type="tel" id="guest_phone" name="guest_phone" class="form-control"
                                    placeholder="Enter phone number"
                                    value="<?= htmlspecialchars($_POST['guest_phone'] ?? '') ?>" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rooms</label>
                            <select class="form-select" name="num_rooms" id="num_rooms_select"
                                onchange="document.getElementById('num_rooms_val').value=this.value;updateGrandTotal();">
                                <option value="1">1 Room</option>
                                <option value="2">2 Rooms</option>
                                <option value="3">3 Rooms</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="special_request">Special Request <small class="text-muted">(Optional)</small></label>
                            <textarea id="special_request" name="special_request" class="form-control" rows="3"
                                placeholder="Write your special request here..."><?= htmlspecialchars($_POST['special_request'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <!-- Step 2 submit → go to add-ons -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn-harbor" style="border-radius:5px;">
                            Continue to Add-Ons &rarr;
                        </button>
                    </div>
                </div>

                <!-- Why book directly -->
                <div class="row g-3 mt-3 text-center">
                    <?php
                    $badges = [
                        ['bi-award','BEST RATE','book directly'],
                        ['bi-heart','EXCLUSIVE BENEFITS','member offers'],
                        ['bi-arrow-repeat','FLEXIBLE CANCELLATION','48 hours before'],
                        ['bi-headset','24/7 SERVICE','anytime, anywhere'],
                    ];
                    foreach ($badges as $b): ?>
                    <div class="col-6 col-md-3">
                        <i class="bi <?= $b[0] ?> d-block mb-1" style="font-size:1.3rem;color:var(--harbor);"></i>
                        <p class="mb-0" style="font-size:.65rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;"><?= $b[1] ?></p>
                        <p class="mb-0 text-muted" style="font-size:.72rem;"><?= $b[2] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- RIGHT: Booking Summary sidebar -->
            <div class="col-lg-5">
                <div class="booking-summary" id="bookingSummary">
                    <h6 class="section-eyebrow mb-3">YOUR BOOKING SUMMARY</h6>

                    <!-- Room preview -->
                    <div class="d-flex gap-3 mb-3 align-items-center">
                        <img src="<?= $selected_room ? ($room_images[$selected_room['type']] ?? $room_images['standard']) : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&q=80' ?>"
                            alt="Room" class="summary-room-img" id="summary_room_img">
                        <div>
                            <p class="mb-0" style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:600;" id="summary_room_name">
                                <?= htmlspecialchars($selected_room['name'] ?? 'Select a room') ?>
                            </p>
                            <p class="text-muted mb-0" style="font-size:.75rem;" id="summary_room_meta">
                                <?php if ($selected_room): ?>
                                    <?= $selected_room['size_sqm'] ?>m² · <?= $selected_room['capacity'] ?> Guests · <?= htmlspecialchars($selected_room['view_type']) ?>
                                <?php endif; ?>
                            </p>
                            <p class="text-gold mb-0" style="font-size:.88rem;font-weight:600;" id="summary_room_price">
                                <?= $selected_room ? format_rupiah($selected_room['price_per_night']) . '/night' : '—' ?>
                            </p>
                        </div>
                    </div>

                    <hr style="border-color:var(--border);">

                    <div class="summary-row"><span>Check-in</span> <span id="summary_checkin"><?= $pre_checkin ?></span></div>
                    <div class="summary-row"><span>Check-out</span> <span id="summary_checkout"><?= $pre_checkout ?></span></div>
                    <div class="summary-row"><span>Nights</span> <span id="nightCount">—</span></div>
                    <div class="summary-row"><span>Rooms</span> <span id="summary_rooms">1 Room</span></div>
                    <div class="summary-row"><span>Guests</span> <span id="summary_guests"><?= $pre_guests ?> Guests</span></div>

                    <hr style="border-color:var(--border);margin-top:.5rem;">

                    <div class="summary-row"><span>Room Price</span> <span id="display_room_total">—</span></div>
                    <div class="summary-row"><span>Service Charge (10%)</span> <span id="display_service">—</span></div>
                    <div class="summary-row"><span>Tax (10%)</span> <span id="display_tax">—</span></div>

                    <div class="summary-total">
                        <span class="summary-total-label">TOTAL</span>
                        <span class="summary-total-amount" id="display_grand">—</span>
                    </div>

                    <div class="alert-estrella rounded p-2 mt-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-gold"></i>
                        <div>
                            <strong style="font-size:.78rem;">Best Rate Guarantee</strong>
                            <p class="mb-0 text-muted" style="font-size:.72rem;">Get the best price when you book directly with us.</p>
                        </div>
                    </div>

                    <!-- Submit button is at bottom of Step 2 form, not here -->
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Activate a step in the timeline indicator
function activateStep(n) {
    for (let i = 1; i <= 5; i++) {
        const icon = document.getElementById('si-icon-' + i);
        const lbl  = document.getElementById('si-lbl-' + i);
        if (!icon || !lbl) continue;
        icon.classList.remove('active','completed');
        lbl.classList.remove('active','completed');
        if (i < n) {
            icon.classList.add('completed');
            lbl.classList.add('completed');
        } else if (i === n) {
            icon.classList.add('active');
            lbl.classList.add('active');
        }
    }
}

// showStep2: unlock step 2 panel and activate step 2 in timeline
function showStep2() {
    const roomId = document.getElementById('selected_room_id').value;
    if (!roomId || roomId === '0') {
        alert('Please select a room first.');
        return;
    }
    const panel = document.getElementById('step2Panel');
    const lock  = document.getElementById('step2Lock');
    if (panel) {
        panel.style.opacity = '1';
        panel.style.pointerEvents = 'auto';
        panel.style.userSelect = 'auto';
    }
    if (lock) lock.style.display = 'none';
    activateStep(2);
    // Scroll to step 2 panel
    setTimeout(() => panel?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
}

// Select room handler
function selectRoom(card) {
    document.querySelectorAll('.room-selector-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    document.getElementById('selected_room_id').value = card.dataset.roomId;
    document.getElementById('room_price_val').value   = card.dataset.roomPrice;
    document.getElementById('summary_room_name').textContent = card.dataset.roomName;
    document.getElementById('summary_room_price').textContent = 'IDR ' + Number(card.dataset.roomPrice).toLocaleString('id-ID') + '/night';
    updateGrandTotal();
}

// Keep summary dates in sync
document.getElementById('checkin_date')?.addEventListener('change', syncSummary);
document.getElementById('checkout_date')?.addEventListener('change', syncSummary);
document.getElementById('num_guests_input')?.addEventListener('change', () => {
    document.getElementById('summary_guests').textContent = document.getElementById('num_guests_input').value + ' Guests';
});
document.getElementById('num_rooms_select')?.addEventListener('change', () => {
    const n = document.getElementById('num_rooms_select').value;
    document.getElementById('summary_rooms').textContent = n + ' Room' + (n>1?'s':'');
    document.getElementById('num_rooms_val').value = n;
    updateGrandTotal();
});

function syncSummary() {
    const ci = document.getElementById('checkin_date').value;
    const co = document.getElementById('checkout_date').value;
    document.getElementById('summary_checkin').textContent  = ci;
    document.getElementById('summary_checkout').textContent = co;
    if (ci && co) {
        const nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
        document.getElementById('nightCount').textContent = nights > 0 ? nights : '—';
        document.getElementById('num_nights_val').value = nights > 0 ? nights : 1;
        updateGrandTotal();
    }
}
syncSummary();
</script>

<?php include '../includes/footer.php'; ?>
