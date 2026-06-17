<?php
// pages/booking_confirm.php – Step 5: Confirmation
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Booking Confirmed';
$base = '../';

$code = htmlspecialchars(trim($_GET['code'] ?? ''), ENT_QUOTES, 'UTF-8');
if (!$code) { header('Location: ../index.php'); exit; }

// Get reservation (JOIN for full details)
$stmt = $conn->prepare("
    SELECT r.*, rm.name AS room_name, rm.type AS room_type, rm.size_sqm, rm.capacity,
           rm.view_type, rm.bed_type, rm.price_per_night
    FROM reservations r
    JOIN rooms rm ON r.room_id = rm.id
    WHERE r.booking_code = ?
    LIMIT 1
");
$stmt->bind_param('s', $code);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) { header('Location: ../index.php'); exit; }

$nights_val = max(1, (int)((strtotime($res['check_out']) - strtotime($res['check_in'])) / 86400));
$addons = json_decode($res['addons_json'] ?? '[]', true) ?: [];

$room_images = [
    'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&q=80',
    'deluxe'       => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=200&q=80',
    'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=200&q=80',
    'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=200&q=80',
];

include '../includes/header.php';
?>

<!-- Confirmation hero banner -->
<section class="page-hero" style="min-height:200px;background-image:url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">Booking Confirmed</h1>
        <p class="page-hero__sub">Your reservation is confirmed — we look forward to welcoming you.</p>
    </div>
</section>

<div class="container py-5">
    <!-- Steps – all completed -->
    <div class="booking-steps">
        <div class="d-flex justify-content-between">
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">1. SELECT ROOM</span></div>
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">2. BOOKING DETAILS</span></div>
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">3. ADD-ONS</span></div>
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">4. PAYMENT</span></div>
            <div class="step-item"><div class="step-icon active"><i class="bi bi-check2-circle"></i></div><span class="step-label active">5. CONFIRMATION</span></div>
        </div>
    </div>

    <div class="row g-5">
        <!-- LEFT: Confirmation card -->
        <div class="col-lg-7">
            <div class="estrella-card text-center py-5">
                <div class="confirm-check">
                    <i class="bi bi-check2"></i>
                </div>

                <h2 style="font-family:'Cormorant Garamond',serif;font-size:2.2rem;color:var(--dark);">
                    Booking Confirmed!
                </h2>
                <p class="text-muted">
                    Thank you for choosing Estrella Hotel &amp; Resort.<br>
                    Your booking has been confirmed.
                </p>

                <!-- Booking reference -->
                <div class="booking-reference">
                    <p class="text-muted mb-1" style="font-size:.8rem;font-weight:500;letter-spacing:.12em;text-transform:uppercase;">YOUR BOOKING REFERENCE</p>
                    <div class="booking-code"><?= htmlspecialchars($res['booking_code']) ?></div>
                </div>

                <p class="text-muted" style="font-size:.85rem;">
                    A confirmation email has been sent to<br>
                    <strong style="color:var(--dark);"><?= htmlspecialchars($res['guest_email']) ?></strong>
                </p>

                <!-- Action buttons -->
                <div class="row g-3 justify-content-center mt-2">
                    <div class="col-auto">
                        <button onclick="window.print()" class="btn btn-ghost-gold">
                            <i class="bi bi-printer me-2"></i>PRINT CONFIRMATION
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="mailto:<?= htmlspecialchars($res['guest_email']) ?>?subject=Booking+Confirmation+<?= urlencode($res['booking_code']) ?>"
                            class="btn btn-ghost-gold">
                            <i class="bi bi-envelope me-2"></i>EMAIL CONFIRMATION
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="https://calendar.google.com/calendar/r/eventedit?text=Estrella+Hotel+Stay&dates=<?= str_replace('-','',$res['check_in']) ?>/<?= str_replace('-','',$res['check_out']) ?>&details=Booking+<?= urlencode($res['booking_code']) ?>"
                            target="_blank" class="btn btn-gold">
                            <i class="bi bi-calendar-plus me-2"></i>ADD TO CALENDAR
                        </a>
                    </div>
                </div>

                <a href="../index.php" class="btn btn-gold w-100 mt-4 py-2">BACK TO HOME</a>
            </div>

            <!-- Bottom info bars -->
            <div class="row g-3 mt-3 text-center">
                <div class="col-12"><div class="estrella-card p-3">
                    <h6 class="section-eyebrow mb-2">NEED HELP?</h6>
                    <p class="mb-1 text-muted" style="font-size:.83rem;">Our reservation team is ready to assist you.</p>
                    <div class="d-flex justify-content-center gap-4 flex-wrap">
                        <span style="font-size:.83rem;"><i class="bi bi-telephone text-gold me-1"></i>+62 812 3456 7890</span>
                        <span style="font-size:.83rem;"><i class="bi bi-envelope text-gold me-1"></i>reservation@estrella.com</span>
                    </div>
                </div></div>
            </div>

            <!-- Guarantees -->
            <div class="row g-3 mt-1 text-center">
                <?php
                $gs = [
                    ['bi-award','BEST RATE GUARANTEE','Best price when you book directly.'],
                    ['bi-heart','EXCLUSIVE BENEFITS','Special privileges and seasonal offers.'],
                    ['bi-arrow-repeat','FLEXIBLE CANCELLATION','Free cancellation up to 48 hours.'],
                    ['bi-headset','24/7 CUSTOMER SERVICE',"We're here to assist, anytime."],
                ];
                foreach ($gs as $g): ?>
                <div class="col-6 col-md-3">
                    <i class="bi <?= $g[0] ?> text-gold d-block mb-1" style="font-size:1.2rem;"></i>
                    <p class="mb-0" style="font-size:.62rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;"><?= $g[1] ?></p>
                    <p class="mb-0 text-muted" style="font-size:.7rem;"><?= $g[2] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- RIGHT: Full booking summary -->
        <div class="col-lg-5">
            <div class="booking-summary">
                <h6 class="section-eyebrow mb-3">YOUR BOOKING SUMMARY</h6>

                <div class="d-flex gap-3 mb-3 align-items-center">
                    <img src="<?= $room_images[$res['room_type']] ?? $room_images['standard'] ?>" alt="Room" class="summary-room-img">
                    <div>
                        <p class="mb-0" style="font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:600;">
                            <?= htmlspecialchars(strtoupper($res['room_name'])) ?>
                        </p>
                        <p class="text-muted mb-0" style="font-size:.73rem;">
                            <?= $res['size_sqm'] ?>m² · <?= $res['capacity'] ?> Guests · <?= htmlspecialchars($res['view_type']) ?>
                        </p>
                        <p class="text-muted mb-0" style="font-size:.73rem;"><?= htmlspecialchars($res['bed_type']) ?></p>
                        <p class="text-gold mb-0" style="font-size:.85rem;font-weight:600;">IDR <?= number_format($res['price_per_night'],0,',','.') ?>/night</p>
                    </div>
                </div>

                <div class="summary-row"><span>Check-in</span><span><?= htmlspecialchars($res['check_in']) ?></span></div>
                <div class="summary-row"><span>Check-out</span><span><?= htmlspecialchars($res['check_out']) ?></span></div>
                <div class="summary-row"><span>Nights</span><span><?= $nights_val ?></span></div>
                <div class="summary-row"><span>Rooms</span><span><?= $res['rooms_count'] ?> Room<?= $res['rooms_count']>1?'s':'' ?></span></div>
                <div class="summary-row"><span>Guests</span><span><?= $res['guests'] ?> Guests</span></div>

                <?php if (!empty($addons)): ?>
                <hr style="border-color:var(--border);">
                <h6 class="section-eyebrow mb-2" style="font-size:.65rem;">ADD-ONS SUMMARY</h6>
                <?php foreach ($addons as $a): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($a['name']) ?><?= $a['mult']>1 ? ' (×'.$a['mult'].' nights)' : '' ?></span>
                    <span>IDR <?= number_format($a['subtotal'],0,',','.') ?></span>
                </div>
                <?php endforeach; ?>
                <div class="summary-row"><span>Subtotal Add-ons</span><span><?= format_rupiah($res['addon_total']) ?></span></div>
                <?php endif; ?>

                <hr style="border-color:var(--border);">
                <div class="summary-row"><span>Room Price (<?= $nights_val ?> Nights)</span><span><?= format_rupiah($res['room_price'] * $nights_val * $res['rooms_count']) ?></span></div>
                <div class="summary-row"><span>Service Charge (10%)</span><span><?= format_rupiah($res['service_charge']) ?></span></div>
                <div class="summary-row"><span>Tax (10%)</span><span><?= format_rupiah($res['tax']) ?></span></div>

                <div class="summary-total">
                    <span class="summary-total-label">TOTAL <small style="font-size:.62rem;font-weight:400;">(Including Add-ons)</small></span>
                    <span class="summary-total-amount"><?= format_rupiah($res['total_price']) ?></span>
                </div>

                <div class="mt-3 pt-2 border-top" style="border-color:var(--border) !important;">
                    <div class="d-flex justify-content-between" style="font-size:.8rem;">
                        <span class="text-muted">Payment method</span>
                        <span class="fw-500 text-capitalize"><?= htmlspecialchars(str_replace('_',' ',$res['payment_method'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mt-1" style="font-size:.8rem;">
                        <span class="text-muted">Status</span>
                        <span class="badge-status badge-confirmed"><?= ucfirst($res['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
