<?php
// includes/footer.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$base  = str_repeat('../', max(0, $depth - 1));
if (strpos($_SERVER['PHP_SELF'], '/pages/') !== false) {
    $base = '../';
} elseif ($_SERVER['PHP_SELF'] === '/estrella/index.php' || basename(dirname($_SERVER['PHP_SELF'])) === 'estrella') {
    $base = '';
}
?>

<!-- FOOTER -->
<footer class="estrella-footer">
    <div class="container">
        <div class="row footer-top py-5">
            <!-- Brand -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="footer-brand mb-3">
                    <span class="brand-star gold">✦</span>
                    <span class="footer-brand-text">ESTRELLA</span>
                </div>
                <p class="footer-tagline">Timeless elegance and<br>exceptional hospitality<br>in every detail.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-heading">QUICK LINKS</h6>
                <ul class="footer-links">
                    <li><a href="<?= $base ?>index.php">Home</a></li>
                    <li><a href="<?= $base ?>pages/rooms.php">Rooms</a></li>
                    <li><a href="<?= $base ?>pages/booking.php">Booking</a></li>
                    <li><a href="<?= $base ?>pages/facilities.php">Facilities</a></li>
                    <li><a href="<?= $base ?>pages/about.php">About Us</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h6 class="footer-heading">CONTACT US</h6>
                <ul class="footer-contact">
                    <li><i class="bi bi-telephone"></i> +62 21 6017 8120</li>
                    <li><i class="bi bi-envelope"></i> <a href="mailto:info@estrella.com">info@estrella.com</a></li>
                    <li><i class="bi bi-geo-alt"></i> Jl. Ocean View No. 99<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bali, Indonesia</li>
                </ul>
            </div>

            <!-- Social -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="footer-heading">FOLLOW US</h6>
                <div class="footer-social">
                    <a href="#" class="social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="social-icon" aria-label="X / Twitter"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2025 Estrella Hotel. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
