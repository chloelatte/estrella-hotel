<?php
// pages/facilities.php
$page_title = 'Facilities';
require_once '../includes/config.php';
require_once '../includes/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero" style="background-image: url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">Our Facilities</h1>
        <p class="page-hero__sub">World-class amenities for an unforgettable stay</p>
    </div>
</section>

<section class="py-5">
    <div class="container">

        <!-- Intro -->
        <div class="text-center mb-5">
            <p class="section-label text-harbor">WHAT WE OFFER</p>
            <h2 class="section-title">Experience Luxury at Every Turn</h2>
            <p class="text-muted mx-auto" style="max-width:600px;">From our infinity pool overlooking the ocean to our world-class spa, every facility at Estrella Hotel is designed to exceed your expectations.</p>
        </div>

        <!-- Facilities Grid -->
        <?php
        $facilities = [
            [
                'icon'  => 'bi-water',
                'title' => 'Infinity Pool',
                'desc'  => 'Dive into our stunning infinity pool with panoramic ocean views. Open daily from 7 AM to 10 PM.',
                'img'   => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=700&q=80',
                'badge' => 'Outdoor',
            ],
            [
                'icon'  => 'bi-heart-pulse',
                'title' => 'Spa & Wellness',
                'desc'  => 'Rejuvenate your body and mind with our signature treatments, including massages, facials, and hydrotherapy.',
                'img'   => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=700&q=80',
                'badge' => 'Premium',
            ],
            [
                'icon'  => 'bi-cup-hot',
                'title' => 'Fine Dining Restaurant',
                'desc'  => 'Savor exquisite Mediterranean cuisine crafted by our Executive Chef using the finest local ingredients.',
                'img'   => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=700&q=80',
                'badge' => 'Restaurant',
            ],
            [
                'icon'  => 'bi-activity',
                'title' => 'Fitness Center',
                'desc'  => 'Stay active with our state-of-the-art fitness equipment, yoga studio, and personal training sessions.',
                'img'   => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=700&q=80',
                'badge' => '24/7',
            ],
            [
                'icon'  => 'bi-building',
                'title' => 'Conference & Events',
                'desc'  => 'Host your meetings, weddings, and special events in our elegant banquet hall and conference rooms.',
                'img'   => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=700&q=80',
                'badge' => 'Business',
            ],
            [
                'icon'  => 'bi-car-front',
                'title' => 'Concierge & Transport',
                'desc'  => 'Our dedicated concierge team and private airport transfer service ensure seamless travel from arrival to departure.',
                'img'   => 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=700&q=80',
                'badge' => 'Service',
            ],
        ];
        ?>

        <div class="row g-4">
            <?php foreach ($facilities as $f): ?>
            <div class="col-md-6 col-lg-4 fade-in-up">
                <div class="card h-100 border-0 shadow-sm facility-card overflow-hidden">
                    <div class="position-relative" style="height:200px;overflow:hidden;">
                        <img src="<?= $f['img'] ?>" alt="<?= $f['title'] ?>" 
                             class="w-100 h-100" style="object-fit:cover;transition:transform .4s;">
                        <span class="badge position-absolute top-0 end-0 m-3" 
                              style="background:var(--gold);color:#fff;font-size:.7rem;letter-spacing:.05em;">
                            <?= $f['badge'] ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi <?= $f['icon'] ?> text-harbor me-2" style="font-size:1.4rem;"></i>
                            <h5 class="fw-semibold mb-0"><?= $f['title'] ?></h5>
                        </div>
                        <p class="text-muted small mb-0"><?= $f['desc'] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Stats -->
        <div class="card border-0 shadow-sm mt-5 p-4 p-md-5">
            <div class="row g-4 text-center">
                <?php
                $stats = [
                    ['num' => '20+', 'label' => 'Years of Excellence'],
                    ['num' => '150', 'label' => 'Luxury Rooms'],
                    ['num' => '98%', 'label' => 'Guest Satisfaction'],
                    ['num' => '24/7', 'label' => 'Concierge Service'],
                ];
                foreach ($stats as $s):
                ?>
                <div class="col-6 col-md-3">
                    <h2 class="font-display text-harbor mb-1"><?= $s['num'] ?></h2>
                    <p class="text-muted small mb-0"><?= $s['label'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA -->
        <div class="text-center mt-5">
            <p class="text-muted mb-3">Ready to experience world-class luxury?</p>
            <a href="booking.php" class="btn-harbor btn-lg me-2">Book Your Stay</a>
            <a href="rooms.php" class="btn-outline-harbor btn-lg">View Rooms</a>
        </div>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
