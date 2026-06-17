<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/config.php';
$page_title = 'Home';
$base = '';

$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_available=1 ORDER BY price_per_night ASC");
$rooms = [];
while ($row = $rooms_result->fetch_assoc()) $rooms[] = $row;

include 'includes/header.php';
?>

<!-- ═══════ HERO ═══════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero__bg" style="background-image:url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1800&q=85');"></div>
    <div class="hero__overlay"></div>

    <div class="container hero__content">
        <div class="row">
            <div class="col-lg-6 col-xl-5">

                <div class="hero__ribbon fade-in-up">
                    <span class="hero__ribbon-dot"></span>
                    <span>Côte d'Azur · European Riviera</span>
                </div>

                <h1 class="hero__title fade-in-up fade-in-up-delay-1">
                    Where <em>Elegance</em><br>Meets the Sea
                </h1>

                <p class="hero__sub fade-in-up fade-in-up-delay-2">
                    A storied château nestled on the European coastline — where timeless architecture, cerulean waters, and impeccable service converge into one unforgettable retreat.
                </p>

                <div class="hero__cta fade-in-up fade-in-up-delay-3">
                    <a href="pages/booking.php" class="btn-harbor">
                        <i class="bi bi-calendar2-check"></i> Reserve a Room
                    </a>
                    <a href="pages/rooms.php" class="btn-ghost-light">
                        Explore Rooms
                    </a>
                </div>

                <!-- Stat row -->
                <div class="d-flex gap-4 mt-5 pt-2 fade-in-up fade-in-up-delay-4" style="border-top:1px solid rgba(255,255,255,.12);padding-top:1.4rem !important;">
                    <?php foreach ([['1905','Est.'],['150','Rooms'],['98%','Satisfaction']] as $s): ?>
                    <div>
                        <div style="font-family:'Playfair Display',serif;font-size:1.85rem;font-weight:600;color:#fff;line-height:1;"><?= $s[0] ?></div>
                        <div style="font-family:'DM Sans',sans-serif;font-size:.58rem;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-top:4px;"><?= $s[1] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>

    <div class="hero__scroll">
        <span>Scroll</span>
        <div class="hero__scroll-line"></div>
    </div>
</section>

<!-- ═══════ ROOMS INTRO ════════════════════════════════════════ -->
<section class="section section--linen">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Left: text -->
            <div class="col-lg-5 fade-in-up">
                <span class="label-eyebrow">Our Rooms</span>
                <h2 class="heading-lg" style="color:var(--harbor-dark);margin-bottom:.5rem;">
                    Crafted for<br>the Discerning Guest
                </h2>
                <div class="rule-harbor"></div>
                <p class="body-lead mb-4">
                    Each room at Estrella is a private sanctuary — furnished with curated antiques, handwoven linens, and floor-to-ceiling windows that frame the Mediterranean in all its glory.
                </p>
                <div class="d-flex gap-4 mb-4">
                    <?php foreach ([['bi-wifi','Wi-Fi'],['bi-water','Sea View'],['bi-cup-hot','24h Room Service']] as $f): ?>
                    <div class="text-center">
                        <i class="bi <?= $f[0] ?> d-block mb-1" style="font-size:1.2rem;color:var(--harbor);"></i>
                        <span style="font-family:'DM Sans',sans-serif;font-size:.62rem;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);"><?= $f[1] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="pages/rooms.php" class="btn-outline-harbor">View All Rooms</a>
            </div>

            <!-- Right: mosaic -->
            <div class="col-lg-7 fade-in-up fade-in-up-delay-1">
                <div class="mosaic">
                    <div class="mosaic__cell span2">
                        <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&q=82" alt="Standard Room" loading="lazy">
                    </div>
                    <div class="mosaic__cell">
                        <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?w=700&q=80" alt="Deluxe Room" loading="lazy">
                    </div>
                    <div class="mosaic__cell">
                        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700&q=80" alt="Executive Room" loading="lazy">
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════ ROOMS GRID — 4 symmetric cards ════════════════════ -->
<section class="section section--parchment">
    <div class="container">

        <div class="text-center mb-5 fade-in-up">
            <span class="label-eyebrow">Accommodations</span>
            <h2 class="heading-xl" style="color:var(--harbor-dark);">Discover Your Perfect Room</h2>
            <div class="rule-harbor center"></div>
            <p class="body-lead mx-auto" style="max-width:520px;">
                From intimate sea-view rooms to our storied Presidential Suite — every stay is shaped around you.
            </p>
        </div>

        <?php
        $room_images = [
            'standard'     => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=700&q=82',
            'deluxe'       => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=700&q=82',
            'executive'    => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=700&q=82',
            'presidential' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?w=700&q=82',
        ];
        $room_descs = [
            'standard'     => 'Thoughtfully appointed with coastal décor and all the essentials for a restful stay.',
            'deluxe'       => 'A private balcony, ocean panorama, and refined interiors for an elevated experience.',
            'executive'    => 'Expansive layout with a separate lounge and premium ocean-facing facilities.',
            'presidential' => 'Our grandest suite — exclusive butler service, panoramic terrace, and timeless luxury.',
        ];
        ?>

        <!-- Equal-height 4-col grid -->
        <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
            <?php foreach ($rooms as $i => $room):
                $img  = $room_images[$room['type']] ?? $room_images['standard'];
                $desc = $room_descs[$room['type']]  ?? '';
            ?>
            <div class="col fade-in-up fade-in-up-delay-<?= $i+1 ?>">
                <div class="room-card">

                    <!-- Aspect-ratio locked image -->
                    <div class="room-card__img">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($room['name']) ?>" loading="lazy">
                        <span class="room-card__tag"><?= ucfirst($room['type']) ?></span>
                    </div>

                    <!-- Body -->
                    <div class="room-card__body">
                        <div class="room-card__name"><?= htmlspecialchars($room['name']) ?></div>
                        <div class="room-card__desc"><?= $desc ?></div>

                        <div class="room-card__meta">
                            <span class="meta-pill"><i class="bi bi-people"></i><?= $room['capacity'] ?> guests</span>
                            <span class="meta-pill"><i class="bi bi-arrows-fullscreen"></i><?= $room['size_sqm'] ?>m²</span>
                            <span class="meta-pill"><i class="bi bi-eye"></i><?= htmlspecialchars($room['view_type'] ?? 'View') ?></span>
                        </div>

                        <div class="room-card__footer">
                            <div class="room-price">
                                <?= format_rupiah($room['price_per_night']) ?>
                                <small>per night</small>
                            </div>
                            <a href="pages/room_detail.php?id=<?= $room['id'] ?>"
                               class="btn-outline-harbor" style="font-size:.62rem;padding:.4rem .9rem;">
                               Details
                            </a>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="pages/rooms.php" class="btn-harbor">
                <i class="bi bi-grid me-1"></i> Browse All Rooms & Rates
            </a>
        </div>
    </div>
</section>

<!-- ═══════ OFFER BANNER ══════════════════════════════════════ -->
<section class="offer-banner py-0">
    <div class="offer-banner__bg"></div>
    <div class="container offer-banner__content">
        <div class="row align-items-center py-5 g-5">
            <div class="col-lg-6">
                <span class="offer-pill">Limited Offer · Save up to 30%</span>
                <h2 class="offer-title">
                    Riviera<br><em>Honeymoon</em><br>Escape
                </h2>
                <p class="offer-sub">A curated romantic getaway along the French Riviera — ocean-view suite, couples spa, and candlelight dining included.</p>
                <a href="pages/booking.php" class="btn-harbor me-2">Claim This Offer</a>
                <a href="pages/about.php" class="btn-ghost-light">Learn More</a>
            </div>
            <div class="col-lg-6">
                <ul class="offer-perks">
                    <?php foreach ([
                        ['bi-check2-circle', 'Complimentary couples spa & hydrotherapy'],
                        ['bi-check2-circle', 'Romantic candlelight dinner with ocean view'],
                        ['bi-check2-circle', 'Daily in-room breakfast with champagne'],
                        ['bi-check2-circle', 'Late check-out and personal concierge'],
                        ['bi-check2-circle', 'Complimentary room upgrade on arrival'],
                    ] as $p): ?>
                    <li>
                        <i class="bi <?= $p[0] ?>"></i>
                        <?= $p[1] ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ═══════ WHY ESTRELLA — 4 symmetric cards ═════════════════ -->
<section class="section section--linen">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="label-eyebrow">Why Choose Us</span>
            <h2 class="heading-xl" style="color:var(--harbor-dark);">The Estrella Difference</h2>
            <div class="rule-harbor center"></div>
        </div>

        <div class="row g-4 row-cols-1 row-cols-sm-2 row-cols-lg-4">
            <?php
            $features = [
                ['bi-shield-check', 'Best Rate Guarantee',  'Book directly and we guarantee the lowest available rate, every time.'],
                ['bi-arrow-repeat', 'Flexible Cancellation','Cancel free of charge up to 48 hours before your arrival date.'],
                ['bi-star',         'Exclusive Privileges', 'Seasonal offers, suite upgrades, and curated experiences for guests.'],
                ['bi-headset',      '24/7 Concierge',       'Our dedicated team anticipates every need, day and night.'],
            ];
            foreach ($features as $i => $f):
            ?>
            <div class="col fade-in-up fade-in-up-delay-<?= $i+1 ?>">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi <?= $f[0] ?>"></i></div>
                    <h6><?= $f[1] ?></h6>
                    <p><?= $f[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════ TESTIMONIALS ══════════════════════════════════════ -->
<section class="section section--navy">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="label-eyebrow" style="color:var(--sky-light);">Guest Stories</span>
            <h2 class="heading-xl" style="color:var(--white);">What Our Guests Say</h2>
        </div>

        <div class="row g-4 row-cols-1 row-cols-md-3">
            <?php
            $tests = [
                ['"The sea view from our balcony at sunrise was worth every centime. Pure magic."',      'Sophie L.',   'Deluxe Room · Paris'],
                ['"Staff remembered our anniversary and filled the room with roses. Truly exceptional."','Marco B.',   'Executive Room · Milan'],
                ['"Our honeymoon here set the bar impossibly high. The spa alone deserves five stars."', 'Isabelle D.', 'Presidential Suite · Lyon'],
            ];
            foreach ($tests as $t):
            ?>
            <div class="col fade-in-up">
                <div class="testimonial-card">
                    <div class="testimonial-card__quote">&ldquo;</div>
                    <p class="testimonial-card__text"><?= $t[0] ?></p>
                    <div class="testimonial-card__author">
                        <div class="testimonial-card__avatar"><?= $t[1][1] ?></div>
                        <div>
                            <div class="testimonial-card__name"><?= $t[1] ?></div>
                            <div class="testimonial-card__stay"><?= $t[2] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
