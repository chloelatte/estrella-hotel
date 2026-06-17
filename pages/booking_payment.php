<?php
// pages/booking_payment.php – Step 4: Payment
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Payment';
$base = '../';

if (!isset($_SESSION['booking']['room_id'])) {
    header('Location: booking.php');
    exit;
}

$booking = $_SESSION['booking'];

// Get room
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param('i', $booking['room_id']);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Price calculations
$room_price  = $room['price_per_night'];
$nights      = $booking['num_nights'];
$num_rooms   = $booking['num_rooms'];
$room_total  = $room_price * $nights * $num_rooms;
$addon_total = $booking['addon_total'] ?? 0;
$service     = $room_total * 0.10;
$tax         = $room_total * 0.10;
$grand       = $room_total + $service + $tax + $addon_total;
$addons      = $booking['addons'] ?? [];

$error = '';

// Handle POST – process payment, create reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8');

    if (!$payment_method) {
        $error = 'Pilih metode pembayaran.';
    } else {
        // Generate booking code
        $booking_code = generate_booking_code();

        // Ensure uniqueness
        do {
            $chk = $conn->prepare("SELECT id FROM reservations WHERE booking_code = ?");
            $chk->bind_param('s', $booking_code);
            $chk->execute();
            $chk->store_result();
            $exists = $chk->num_rows > 0;
            $chk->close();
            if ($exists) $booking_code = generate_booking_code();
        } while ($exists);

        $user_id     = $_SESSION['user_id'] ?? null;
        $addons_json = json_encode($addons);

        $stmt = $conn->prepare("INSERT INTO reservations
            (booking_code, user_id, room_id, guest_name, guest_email, guest_phone,
             check_in, check_out, guests, rooms_count,
             room_price, addon_total, service_charge, tax, total_price,
             special_request, payment_method, payment_status, status, addons_json)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'paid','confirmed',?)");

        $guests_val = (int)($booking['num_guests'] ?? 2);
        $stmt->bind_param(
            'siissssssiidddddss',
            $booking_code,
            $user_id,
            $booking['room_id'],
            $booking['guest_name'],
            $booking['guest_email'],
            $booking['guest_phone'],
            $booking['checkin_date'],
            $booking['checkout_date'],
            $guests_val,
            $num_rooms,
            $room_price,
            $addon_total,
            $service,
            $tax,
            $grand,
            $booking['special_request'],
            $payment_method,
            $addons_json
        );

        if ($stmt->execute()) {
            $reservation_id = $conn->insert_id;
            $_SESSION['last_booking_code'] = $booking_code;
            $_SESSION['last_reservation_id'] = $reservation_id;
            unset($_SESSION['booking']);
            $stmt->close();
            header('Location: booking_confirm.php?code=' . urlencode($booking_code));
            exit;
        } else {
            $error = 'Terjadi kesalahan saat menyimpan reservasi: ' . htmlspecialchars($conn->error);
        }
        $stmt->close();
    }
}

$room_images = [
    'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&q=80',
    'deluxe'       => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?w=200&q=80',
    'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=200&q=80',
    'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=200&q=80',
];

include '../includes/header.php';
?>

<section class="page-hero" style="min-height:220px;background-image:url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">Secure Payment</h1>
        <p class="page-hero__sub">Complete your reservation — safe, fast, and secure.</p>
    </div>
</section>

<div class="container py-5">
    <!-- Steps -->
    <div class="booking-steps">
        <div class="d-flex justify-content-between">
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">1. SELECT ROOM</span></div>
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">2. BOOKING DETAILS</span></div>
            <div class="step-item"><div class="step-icon completed"><i class="bi bi-check2"></i></div><span class="step-label completed">3. ADD-ONS</span></div>
            <div class="step-item"><div class="step-icon active"><i class="bi bi-credit-card"></i></div><span class="step-label active">4. PAYMENT</span></div>
            <div class="step-item"><div class="step-icon"><i class="bi bi-check2-circle"></i></div><span class="step-label">5. CONFIRMATION</span></div>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Hidden pricing vals for JS -->
    <input type="hidden" id="room_price_val"    value="<?= $room_price ?>">
    <input type="hidden" id="num_nights_val"    value="<?= $nights ?>">
    <input type="hidden" id="num_rooms_val"     value="<?= $num_rooms ?>">
    <input type="hidden" id="addon_total_hidden" value="<?= $addon_total ?>">

    <form id="paymentForm" method="POST" action="booking_payment.php" novalidate>
        <div class="row g-5">
            <!-- LEFT: Payment methods -->
            <div class="col-lg-7">
                <h5 class="section-eyebrow mb-1">PAYMENT METHOD</h5>
                <p class="text-muted mb-4" style="font-size:.85rem;">Choose a payment method and complete your payment.</p>

                <!-- Credit / Debit Card -->
                <div class="payment-option selected" data-method="credit_card" onclick="selectPayment(this,'credit_card')">
                    <input type="radio" name="payment_method" value="credit_card" checked>
                    <i class="bi bi-credit-card text-gold" style="font-size:1.4rem;"></i>
                    <div class="flex-grow-1">
                        <strong style="font-size:.9rem;">Credit / Debit Card</strong>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <span class="badge" style="background:#1a1f71;color:#fff;font-size:.65rem;">VISA</span>
                        <span class="badge" style="background:#eb001b;color:#fff;font-size:.65rem;">MC</span>
                        <span class="badge" style="background:#003087;color:#fff;font-size:.65rem;">JCB</span>
                        <span class="badge" style="background:#006fcf;color:#fff;font-size:.65rem;">AMEX</span>
                    </div>
                </div>

                <!-- Credit card fields -->
                <div id="creditCardFields" class="estrella-card mb-3">
                    <div class="mb-3">
                        <label class="form-label" for="card_number">Card Number</label>
                        <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="card_name">Cardholder Name</label>
                        <input type="text" id="card_name" class="form-control" placeholder="Enter cardholder name">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="card_expiry">Expiry Date</label>
                            <input type="text" id="card_expiry" class="form-control" placeholder="MM / YY" maxlength="5">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" class="form-control" placeholder="123" maxlength="4">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer -->
                <div class="payment-option" data-method="bank_transfer" onclick="selectPayment(this,'bank_transfer')">
                    <input type="radio" name="payment_method" value="bank_transfer">
                    <i class="bi bi-bank text-gold" style="font-size:1.4rem;"></i>
                    <div class="flex-grow-1">
                        <strong style="font-size:.9rem;">Bank Transfer</strong>
                        <p class="mb-0 text-muted" style="font-size:.78rem;">Complete your payment via bank transfer.</p>
                    </div>
                </div>

                <!-- E-Wallet -->
                <div class="payment-option" data-method="e_wallet" onclick="selectPayment(this,'e_wallet')">
                    <input type="radio" name="payment_method" value="e_wallet">
                    <i class="bi bi-wallet2 text-gold" style="font-size:1.4rem;"></i>
                    <div class="flex-grow-1">
                        <strong style="font-size:.9rem;">E-Wallet</strong>
                        <p class="mb-0 text-muted" style="font-size:.78rem;">OVO · Dana · GoPay · ShopeePay</p>
                    </div>
                </div>

                <!-- Pay at Hotel -->
                <div class="payment-option" data-method="pay_at_hotel" onclick="selectPayment(this,'pay_at_hotel')">
                    <input type="radio" name="payment_method" value="pay_at_hotel">
                    <i class="bi bi-building text-gold" style="font-size:1.4rem;"></i>
                    <div class="flex-grow-1">
                        <strong style="font-size:.9rem;">Pay at Hotel</strong>
                        <p class="mb-0 text-muted" style="font-size:.78rem;">Pay when you arrive at the hotel.</p>
                    </div>
                </div>

                <p class="text-muted mt-3" style="font-size:.78rem;">
                    <i class="bi bi-lock me-1"></i> Your payment information is secure and encrypted.
                </p>

                <!-- Bottom badges -->
                <div class="row g-3 text-center mt-2">
                    <?php
                    $badges = [
                        ['bi-award','BEST RATE GUARANTEE','Best price, always.'],
                        ['bi-heart','EXCLUSIVE BENEFITS','Member privileges.'],
                        ['bi-arrow-repeat','FLEXIBLE CANCELLATION','48 hours before.'],
                        ['bi-headset','24/7 CUSTOMER SERVICE','Anytime, anywhere.'],
                    ];
                    foreach ($badges as $b): ?>
                    <div class="col-6 col-md-3">
                        <i class="bi <?= $b[0] ?> text-gold d-block mb-1"></i>
                        <p class="mb-0" style="font-size:.62rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;"><?= $b[1] ?></p>
                        <p class="mb-0 text-muted" style="font-size:.7rem;"><?= $b[2] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- RIGHT: Summary + Pay button -->
            <div class="col-lg-5">
                <div class="booking-summary">
                    <h6 class="section-eyebrow mb-3">YOUR BOOKING SUMMARY</h6>

                    <div class="d-flex gap-3 mb-3 align-items-center">
                        <img src="<?= $room_images[$room['type']] ?? $room_images['standard'] ?>" alt="Room" class="summary-room-img">
                        <div>
                            <p class="mb-0" style="font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:600;"><?= htmlspecialchars(strtoupper($room['name'])) ?></p>
                            <p class="text-muted mb-0" style="font-size:.73rem;"><?= $room['size_sqm'] ?>m² · <?= $booking['num_guests'] ?> Guests · <?= htmlspecialchars($room['view_type']) ?></p>
                            <?php if (!empty($room['bed_type'])): ?>
                            <p class="text-muted mb-0" style="font-size:.73rem;"><?= htmlspecialchars($room['bed_type']) ?></p>
                            <?php endif; ?>
                            <p class="text-gold mb-0" style="font-size:.85rem;font-weight:600;">IDR <?= number_format($room_price,0,',','.') ?>/night</p>
                        </div>
                    </div>

                    <div class="summary-row"><span>Check-in</span><span><?= htmlspecialchars($booking['checkin_date']) ?></span></div>
                    <div class="summary-row"><span>Check-out</span><span><?= htmlspecialchars($booking['checkout_date']) ?></span></div>
                    <div class="summary-row"><span>Nights</span><span><?= $nights ?></span></div>
                    <div class="summary-row"><span>Rooms</span><span><?= $num_rooms ?> Room<?= $num_rooms>1?'s':'' ?></span></div>
                    <div class="summary-row"><span>Guests</span><span><?= $booking['num_guests'] ?> Guests</span></div>

                    <?php if (!empty($addons)): ?>
                    <hr style="border-color:var(--border);">
                    <h6 class="section-eyebrow mb-2" style="font-size:.65rem;">ADD-ONS SUMMARY</h6>
                    <?php foreach ($addons as $a): ?>
                    <div class="summary-row">
                        <span><?= htmlspecialchars($a['name']) ?> (<?= $a['qty'] ?>x<?= $a['mult'] > 1 ? ' × '.$a['mult'].' nights' : '' ?>)</span>
                        <span>IDR <?= number_format($a['subtotal'],0,',','.') ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="summary-row"><span>Subtotal Add-ons</span><span><?= format_rupiah($addon_total) ?></span></div>
                    <?php endif; ?>

                    <hr style="border-color:var(--border);">
                    <div class="summary-row"><span>Room Price (<?= $nights ?> Nights)</span><span><?= format_rupiah($room_total) ?></span></div>
                    <div class="summary-row"><span>Service Charge (10%)</span><span><?= format_rupiah($service) ?></span></div>
                    <div class="summary-row"><span>Tax (10%)</span><span><?= format_rupiah($tax) ?></span></div>

                    <div class="summary-total">
                        <div>
                            <span class="summary-total-label d-block">TOTAL</span>
                            <span style="font-size:.65rem;color:var(--gray-muted);">(Including Add-ons)</span>
                        </div>
                        <span class="summary-total-amount" id="display_grand"><?= format_rupiah($grand) ?></span>
                    </div>
                </div>

                <!-- PAY NOW button (outside summary card, prominent) -->
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <p class="mb-0 text-muted" style="font-size:.75rem;">TOTAL PAYMENT</p>
                        <p class="mb-0 text-gold fw-bold" style="font-size:1.25rem;"><?= format_rupiah($grand) ?></p>
                    </div>
                    <button type="submit" class="btn btn-gold px-4 py-2">
                        <i class="bi bi-lock me-2"></i>PAY NOW
                    </button>
                </div>
                <p class="text-muted mt-2" style="font-size:.72rem;">You will be redirected to a secure payment gateway.</p>
            </div>
        </div>
    </form>
</div>

<script>
function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
    const ccFields = document.getElementById('creditCardFields');
    if (ccFields) ccFields.style.display = method === 'credit_card' ? 'block' : 'none';
}
</script>

<?php include '../includes/footer.php'; ?>
