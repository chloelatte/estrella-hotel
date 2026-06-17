<?php
// pages/booking_addons.php – Step 3: Add-ons & Special Services
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Add-ons & Services';
$base = '../';

// Guard: must have booking session
if (!isset($_SESSION['booking']['room_id'])) {
    header('Location: booking.php');
    exit;
}

$booking = $_SESSION['booking'];

// Get room info
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param('i', $booking['room_id']);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get addons from DB — fallback hardcoded if table empty
$addons_result = $conn->query("SELECT * FROM addons WHERE is_active = 1 ORDER BY id ASC");
$addons = [];
if ($addons_result) while ($a = $addons_result->fetch_assoc()) $addons[] = $a;

// Fallback addons if DB empty
if (empty($addons)) {
    $addons = [
        ['id'=>1,'name'=>'Breakfast','description'=>'Daily continental breakfast for two — served in-room or at the restaurant.','price'=>150000,'unit'=>'person/night','is_active'=>1],
        ['id'=>2,'name'=>'Airport Transfer','description'=>'Private chauffeur service from the nearest airport to the hotel.','price'=>350000,'unit'=>'trip','is_active'=>1],
        ['id'=>3,'name'=>'Spa & Wellness Package','description'=>'60-minute couples massage and access to the hydrotherapy pool.','price'=>750000,'unit'=>'session','is_active'=>1],
        ['id'=>4,'name'=>'Romantic Setup','description'=>'Rose petals, candles, champagne, and a personalised welcome card in your room.','price'=>500000,'unit'=>'room','is_active'=>1],
        ['id'=>5,'name'=>'Late Check-Out','description'=>'Extend your check-out until 4 PM to enjoy every last moment.','price'=>200000,'unit'=>'booking','is_active'=>1],
    ];
}

// Handle POST – save addons, go to payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_addons = [];
    $addon_total = 0;
    foreach ($addons as $addon) {
        $qty = (int)($_POST['addon_qty_' . $addon['id']] ?? 0);
        if ($qty > 0) {
            $mult = (strtolower($addon['name']) === 'breakfast') ? (int)($booking['num_nights'] ?? 1) : 1;
            $subtotal = $qty * $addon['price'] * $mult;
            $selected_addons[] = [
                'id' => $addon['id'], 'name' => $addon['name'],
                'qty' => $qty, 'price' => $addon['price'],
                'mult' => $mult, 'subtotal' => $subtotal,
            ];
            $addon_total += $subtotal;
        }
    }
    $_SESSION['booking']['addons']      = $selected_addons;
    $_SESSION['booking']['addon_total'] = $addon_total;
    header('Location: booking_payment.php');
    exit;
}

// Price breakdown
$room_price = $room['price_per_night'] ?? 0;
$nights     = (int)($booking['num_nights'] ?? 1);
$num_rooms  = (int)($booking['num_rooms'] ?? 1);
$room_total = $room_price * $nights * $num_rooms;
$service    = $room_total * 0.10;
$tax        = $room_total * 0.10;
$grand      = $room_total + $service + $tax;

$room_images = [
    'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=200&q=80',
    'deluxe'       => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=200&q=80',
    'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=200&q=80',
    'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=200&q=80',
];
$addon_images = [
    'Breakfast'              => 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=300&q=80',
    'Airport Transfer'       => 'https://images.unsplash.com/photo-1464219789935-c2d9d9aba644?w=300&q=80',
    'Spa & Wellness Package' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=300&q=80',
    'Romantic Setup'         => 'https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=300&q=80',
    'Late Check-Out'         => 'https://images.unsplash.com/photo-1495435798646-a289417cdb80?w=300&q=80',
];

include '../includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="min-height:200px;background-image:url('https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">Add-ons & Services</h1>
        <p class="page-hero__sub">Enhance your stay with curated experiences.</p>
    </div>
</section>

<div class="container py-5">

    <!-- Steps Indicator — step 1 & 2 completed, step 3 active -->
    <div class="booking-steps mb-4">
        <div class="d-flex justify-content-between">
            <div class="step-item">
                <div class="step-icon completed"><i class="bi bi-check2"></i></div>
                <span class="step-label completed">1. SELECT ROOM</span>
            </div>
            <div class="step-item">
                <div class="step-icon completed"><i class="bi bi-check2"></i></div>
                <span class="step-label completed">2. BOOKING DETAILS</span>
            </div>
            <div class="step-item">
                <div class="step-icon active"><i class="bi bi-gift"></i></div>
                <span class="step-label active">3. ADD-ONS</span>
            </div>
            <div class="step-item">
                <div class="step-icon"><i class="bi bi-credit-card"></i></div>
                <span class="step-label">4. PAYMENT</span>
            </div>
            <div class="step-item">
                <div class="step-icon"><i class="bi bi-check2-circle"></i></div>
                <span class="step-label">5. CONFIRMATION</span>
            </div>
        </div>
    </div>

    <form method="POST" action="booking_addons.php">
        <input type="hidden" id="room_price_val"   value="<?= $room_price ?>">
        <input type="hidden" id="num_nights_val"   value="<?= $nights ?>">
        <input type="hidden" id="num_rooms_val"    value="<?= $num_rooms ?>">
        <input type="hidden" id="addon_total_val"  value="0">

        <div class="row g-5">

            <!-- LEFT: Add-ons list -->
            <div class="col-lg-7">
                <div class="mb-4">
                    <span class="label-eyebrow">Step 3</span>
                    <h4 style="font-family:var(--display);font-size:1.5rem;font-weight:600;color:var(--harbor-dark);margin-bottom:.3rem;">
                        Add-ons &amp; Special Services
                    </h4>
                    <p class="body-sm mb-0">Select any extras to make your stay truly memorable. All are optional.</p>
                </div>

                <?php foreach ($addons as $addon):
                    $img  = $addon_images[$addon['name']] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300&q=80';
                    $mult = (strtolower($addon['name']) === 'breakfast') ? $nights : 1;
                    $priceLabel = 'IDR ' . number_format($addon['price'],0,',','.') . ' / ' . htmlspecialchars($addon['unit']);
                ?>
                <div class="addon-card mb-3" id="addonCard_<?= $addon['id'] ?>">
                    <!-- Image -->
                    <div class="addon-img-wrap">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($addon['name']) ?>" class="addon-img">
                    </div>
                    <!-- Info -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-1">
                            <strong class="addon-name"><?= htmlspecialchars($addon['name']) ?></strong>
                            <span class="addon-price"><?= $priceLabel ?></span>
                        </div>
                        <p class="addon-desc mb-0"><?= htmlspecialchars($addon['description']) ?></p>
                        <?php if ($mult > 1): ?>
                        <small class="text-harbor" style="font-size:.72rem;">× <?= $mult ?> nights multiplier</small>
                        <?php endif; ?>
                    </div>
                    <!-- Qty control -->
                    <div class="addon-qty flex-shrink-0">
                        <button type="button" class="qty-btn" onclick="changeQty(<?= $addon['id'] ?>, -1)">&#8722;</button>
                        <span class="qty-value" id="display_qty_<?= $addon['id'] ?>">0</span>
                        <button type="button" class="qty-btn" onclick="changeQty(<?= $addon['id'] ?>, 1)">&#43;</button>
                        <input type="hidden"
                            id="qty_<?= $addon['id'] ?>"
                            name="addon_qty_<?= $addon['id'] ?>"
                            value="0"
                            data-price="<?= $addon['price'] ?>"
                            data-mult="<?= $mult ?>">
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Skip option -->
                <div class="estrella-card d-flex align-items-center justify-content-between gap-3 mt-4"
                     style="background:var(--parchment);">
                    <div>
                        <p class="mb-0" style="font-size:.88rem;font-weight:500;color:var(--ink);">Prefer to skip add-ons?</p>
                        <p class="mb-0 body-sm">You can always add services after booking.</p>
                    </div>
                    <button type="submit" class="btn-outline-harbor" style="white-space:nowrap;border-radius:5px;font-size:.68rem;">
                        Skip &rarr;
                    </button>
                </div>
            </div>

            <!-- RIGHT: Booking Summary -->
            <div class="col-lg-5">
                <div class="booking-summary">
                    <h6 class="label-eyebrow mb-3">YOUR BOOKING SUMMARY</h6>

                    <!-- Room -->
                    <div class="d-flex gap-3 mb-3 align-items-center">
                        <img src="<?= $room_images[$room['type'] ?? 'standard'] ?? $room_images['standard'] ?>"
                             alt="Room" class="summary-room-img">
                        <div>
                            <p class="mb-0" style="font-family:var(--display);font-size:1.05rem;font-weight:600;color:var(--harbor-dark);">
                                <?= htmlspecialchars($room['name'] ?? '') ?>
                            </p>
                            <p class="mb-0" style="font-size:.73rem;color:var(--muted);">
                                <?= $room['size_sqm'] ?>m² · <?= $booking['num_guests'] ?> Guests
                            </p>
                            <p class="mb-0" style="font-size:.85rem;font-weight:600;color:var(--harbor);">
                                IDR <?= number_format($room['price_per_night'],0,',','.') ?>/night
                            </p>
                        </div>
                    </div>

                    <div class="summary-row"><span>Check-in</span><span><?= htmlspecialchars($booking['checkin_date']) ?></span></div>
                    <div class="summary-row"><span>Check-out</span><span><?= htmlspecialchars($booking['checkout_date']) ?></span></div>
                    <div class="summary-row"><span>Nights</span><span><?= $nights ?></span></div>
                    <div class="summary-row"><span>Rooms</span><span><?= $num_rooms ?></span></div>

                    <div style="margin:.75rem 0 .5rem;padding-top:.5rem;border-top:1px solid var(--border-light);">
                        <p class="label-eyebrow mb-2" style="font-size:.6rem;">Selected Add-ons</p>
                        <div id="addon_lines" style="font-size:.82rem;min-height:1.5rem;">
                            <span class="text-muted" style="font-size:.78rem;">None selected</span>
                        </div>
                    </div>

                    <div style="border-top:1px solid var(--border-light);margin-top:.5rem;padding-top:.5rem;">
                        <div class="summary-row"><span>Room Total</span><span><?= format_rupiah($room_total) ?></span></div>
                        <div class="summary-row"><span>Add-ons Total</span><span id="display_addon_total">IDR 0</span></div>
                        <div class="summary-row"><span>Service Charge (10%)</span><span id="display_service"><?= format_rupiah($service) ?></span></div>
                        <div class="summary-row"><span>Tax (10%)</span><span id="display_tax"><?= format_rupiah($tax) ?></span></div>
                    </div>

                    <div class="summary-total">
                        <span class="summary-total-label">TOTAL</span>
                        <span class="summary-total-amount" id="display_grand"><?= format_rupiah($grand) ?></span>
                    </div>

                    <button type="submit" class="btn-harbor w-100 mt-4" style="border-radius:5px;justify-content:center;font-size:.72rem;">
                        Continue to Payment &rarr;
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const ROOM_TOTAL = <?= $room_total ?>;
const SERVICE_RATE = 0.10;
const TAX_RATE    = 0.10;

function fmt(n) {
    return 'IDR ' + Math.round(n).toLocaleString('id-ID');
}

function changeQty(id, delta) {
    const input = document.getElementById('qty_' + id);
    const disp  = document.getElementById('display_qty_' + id);
    if (!input) return;
    let qty = parseInt(input.value || 0) + delta;
    qty = Math.max(0, Math.min(10, qty));
    input.value  = qty;
    disp.textContent = qty;
    recalc();
}

function recalc() {
    let addonTotal = 0;
    const lines = [];

    document.querySelectorAll('input[name^="addon_qty_"]').forEach(input => {
        const qty   = parseInt(input.value || 0);
        const price = parseFloat(input.dataset.price || 0);
        const mult  = parseFloat(input.dataset.mult || 1);
        const sub   = qty * price * mult;
        if (qty > 0) {
            const card  = input.closest('.addon-card');
            const label = card?.querySelector('.addon-name')?.textContent?.trim() || 'Add-on';
            lines.push(
                `<div class="summary-row"><span>${label} ×${qty}</span><span>${fmt(sub)}</span></div>`
            );
            addonTotal += sub;
        }
    });

    const linesEl = document.getElementById('addon_lines');
    if (linesEl) linesEl.innerHTML = lines.length
        ? lines.join('')
        : '<span class="text-muted" style="font-size:.78rem;">None selected</span>';

    const taxableBase  = ROOM_TOTAL + addonTotal;
    const service      = taxableBase * SERVICE_RATE;
    const tax          = taxableBase * TAX_RATE;
    const grand        = taxableBase + service + tax;

    document.getElementById('display_addon_total').textContent = fmt(addonTotal);
    document.getElementById('display_service').textContent     = fmt(service);
    document.getElementById('display_tax').textContent         = fmt(tax);
    document.getElementById('display_grand').textContent       = fmt(grand);
    document.getElementById('addon_total_val').value           = addonTotal;
}
</script>

<?php include '../includes/footer.php'; ?>
