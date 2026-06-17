<?php
// pages/about.php
$page_title = 'About Us';
require_once '../includes/config.php';
require_once '../includes/header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=80');">
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content text-center">
        <h1 class="page-hero__title">About Estrella Hotel</h1>
        <p class="page-hero__sub">Our story, passion, and commitment</p>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="py-5">
    <div class="container">

        <!-- Our Story -->
        <div class="card border-0 shadow-sm mb-5 p-4 p-md-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <p class="section-label text-harbor">OUR STORY</p>
                    <h2 class="section-title">A Legacy of Coastal Elegance</h2>
                    <p class="text-muted lh-lg">Since 2005, Estrella Hotel has been a luxury coastal retreat where elegance, comfort, and exceptional hospitality come together. Surrounded by breathtaking ocean views, we are dedicated to creating memorable experiences and unforgettable stays for every guest.</p>
                    <p class="text-muted lh-lg">Nestled along the pristine European coastline, our resort embodies the perfect fusion of timeless architecture and modern luxury. Every corner of Estrella Hotel tells a story of craftsmanship, passion, and dedication to excellence.</p>
                    <a href="rooms.php" class="btn-harbor mt-2">Explore Our Rooms</a>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=700&q=80" 
                         alt="Estrella Hotel Building" class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>

        <!-- Vision & Mission -->
        <div class="card border-0 shadow-sm mb-5">
            <div class="row g-0">
                <div class="col-md-6 p-5 text-center border-end">
                    <div class="mb-3">
                        <i class="bi bi-eye text-harbor" style="font-size:2.5rem;"></i>
                    </div>
                    <h3 class="font-display text-harbor mb-3">Vision</h3>
                    <p class="text-muted lh-lg">To be a leading luxury coastal hotel that combines elegance, comfort, and exceptional hospitality.</p>
                </div>
                <div class="col-md-6 p-5 text-center">
                    <div class="mb-3">
                        <i class="bi bi-bullseye text-harbor" style="font-size:2.5rem;"></i>
                    </div>
                    <h3 class="font-display text-harbor mb-3">Mission</h3>
                    <p class="text-muted lh-lg">To provide memorable stays through premium accommodations, personalized service, and a warm, welcoming atmosphere.</p>
                </div>
            </div>
        </div>

        <!-- Our Journey / Timeline -->
        <div class="card border-0 shadow-sm mb-5 p-4 p-md-5">
            <p class="section-label text-harbor">MILESTONES</p>
            <h2 class="section-title mb-5">Our Journey</h2>
            <div class="about-timeline">
                <!-- Outer wrapper provides the reference for the horizontal line -->
                <div class="timeline-track position-relative d-none d-md-block">
                    <div class="timeline-h-line"></div>
                </div>
                <div class="row g-0">
                    <?php
                    $milestones = [
                        ['year' => '2005', 'text' => 'Estrella Hotel opened its doors and welcomed its first guests.'],
                        ['year' => '2012', 'text' => 'New rooms and facilities were added to meet growing demand.'],
                        ['year' => '2018', 'text' => 'The hotel was upgraded with modern amenities and refreshed interiors.'],
                        ['year' => '2022', 'text' => 'Estrella Hotel became a recognized luxury coastal destination.'],
                    ];
                    foreach ($milestones as $i => $m):
                    ?>
                    <div class="col-6 col-md-3 text-center px-3 mb-4">
                        <!-- Dot sits on top of the line -->
                        <div class="timeline-dot-wrap">
                            <div class="timeline-dot-v2 mx-auto"></div>
                        </div>
                        <h4 class="font-display text-harbor mt-3"><?= $m['year'] ?></h4>
                        <p class="text-muted small"><?= $m['text'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Awards -->
        <div class="card border-0 shadow-sm mb-5 p-4 p-md-5 text-center">
            <p class="section-label text-harbor">RECOGNITION</p>
            <h2 class="section-title mb-5">Awards &amp; Recognitions</h2>
            <div class="row g-4">
                <?php
                $awards = [
                    ['icon' => 'bi-trophy', 'title' => 'Best Coastal Hotel', 'year' => '2022'],
                    ['icon' => 'bi-star',   'title' => 'Guest Choice Award', 'year' => '2023'],
                    ['icon' => 'bi-award',  'title' => 'Top Resort Destination', 'year' => '2024'],
                    ['icon' => 'bi-gem',    'title' => 'Design Excellence Award', 'year' => '2025'],
                ];
                foreach ($awards as $a):
                ?>
                <div class="col-6 col-md-3">
                    <div class="award-card p-4">
                        <i class="bi <?= $a['icon'] ?> text-harbor mb-3" style="font-size:2.5rem;"></i>
                        <h6 class="fw-semibold mb-1"><?= $a['title'] ?></h6>
                        <small class="text-muted"><?= $a['year'] ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Team -->
        <div class="card border-0 shadow-sm p-4 p-md-5">
            <p class="section-label text-harbor">OUR PEOPLE</p>
            <h2 class="section-title mb-5">Meet the Team</h2>
            <div class="row g-4 justify-content-center">
                <?php
                $team = [
                    ['name' => 'Alexandre Moreau',  'role' => 'General Manager',      'img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&q=80'],
                    ['name' => 'Sophie Laurent',    'role' => 'Head of Hospitality',  'img' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=300&q=80'],
                    ['name' => 'Marco Bellini',     'role' => 'Executive Chef',        'img' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&q=80'],
                    ['name' => 'Isabelle Dubois',   'role' => 'Spa Director',          'img' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&q=80'],
                ];
                foreach ($team as $t):
                ?>
                <div class="col-6 col-md-3">
                    <div class="team-member">
                        <div class="team-member__img-wrap">
                            <img src="<?= $t['img'] ?>" alt="<?= $t['name'] ?>">
                        </div>
                        <div class="team-member__info">
                            <h6 class="team-member__name"><?= $t['name'] ?></h6>
                            <span class="team-member__role"><?= $t['role'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
