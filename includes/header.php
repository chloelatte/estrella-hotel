<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF'], '.php');
// Mark "booking" as active for all booking flow pages
$booking_pages = ['booking', 'booking_addons', 'booking_payment', 'booking_confirm'];
$is_booking_flow = in_array($current_page, $booking_pages);

// Determine base path for assets
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base = '../';
} elseif ($_SERVER['PHP_SELF'] === '/estrella/index.php' || basename(dirname($_SERVER['PHP_SELF'])) === 'estrella') {
    $base = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' – ' : '' ?>Estrella Hotel &amp; Resort</title>
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>assets/favicon.svg">

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Playfair Display + DM Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light estrella-navbar fixed-top" id="mainNavbar">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand estrella-brand" href="<?= $base ?>index.php">
            <div class="brand-top-row">
                <span class="brand-star">✦</span>
                <span class="brand-text">ESTRELLA</span>
            </div>
            <span class="brand-sub">HOTEL &amp; RESORT</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'index') ? 'active' : '' ?>" href="<?= $base ?>index.php">HOME</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'rooms') ? 'active' : '' ?>" href="<?= $base ?>pages/rooms.php">ROOMS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'facilities') ? 'active' : '' ?>" href="<?= $base ?>pages/facilities.php">FACILITIES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($is_booking_flow ?? false) ? 'active' : '' ?>" href="<?= $base ?>pages/booking.php">BOOKING</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'about') ? 'active' : '' ?>" href="<?= $base ?>pages/about.php">ABOUT US</a>
                </li>
            </ul>

            <!-- Auth -->
            <div class="navbar-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-gold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION['full_name'] ?? 'Guest') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= $base ?>pages/admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= $base ?>pages/my_bookings.php"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base ?>pages/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= $base ?>pages/login.php" class="btn btn-outline-gold">LOGIN</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
