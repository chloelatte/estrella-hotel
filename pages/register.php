<?php
// pages/register.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Create Account';
$base = '../';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = htmlspecialchars(trim($_POST['full_name']        ?? ''), ENT_QUOTES, 'UTF-8');
    $email            = htmlspecialchars(trim($_POST['email']            ?? ''), ENT_QUOTES, 'UTF-8');
    $username         = htmlspecialchars(trim($_POST['reg_username']     ?? ''), ENT_QUOTES, 'UTF-8');
    $phone            = htmlspecialchars(trim($_POST['phone']            ?? ''), ENT_QUOTES, 'UTF-8');
    $password         = trim($_POST['reg_password']     ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Server-side validation
    if (empty($full_name) || empty($email) || empty($username) || empty($password)) {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        // Cek duplikasi email / username
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email atau username sudah digunakan.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2  = $conn->prepare("INSERT INTO users (full_name, email, username, password, phone, role) VALUES (?, ?, ?, ?, ?, 'guest')");
            $stmt2->bind_param('sssss', $full_name, $email, $username, $hashed, $phone);

            if ($stmt2->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<section class="auth-section">
    <div class="container py-5" style="position:relative;z-index:2;">
        <div class="auth-card mx-auto" style="max-width:480px;">
            <!-- Brand: ✦ ESTRELLA — sparkle + name inline, no subtitle -->
            <div class="text-center mb-4">
                <a href="../index.php" class="auth-brand-inline">
                    <span class="auth-brand-star">✦</span>
                    <span class="auth-brand-name">ESTRELLA</span>
                </a>
            </div>
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join Estrella for exclusive benefits.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="register.php" novalidate>
                <div class="row g-3">
                    <!-- Full Name -->
                    <div class="col-12">
                        <label class="form-label" for="full_name">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                            placeholder="Your full name"
                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Email -->
                    <div class="col-12">
                        <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="your@email.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Username -->
                    <div class="col-md-6">
                        <label class="form-label" for="reg_username">Username <span class="text-danger">*</span></label>
                        <input type="text" id="reg_username" name="reg_username" class="form-control"
                            placeholder="Choose a username"
                            value="<?= htmlspecialchars($_POST['reg_username'] ?? '') ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                            placeholder="+62..."
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <!-- Password -->
                    <div class="col-md-6">
                        <label class="form-label" for="reg_password">Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="reg_password" name="reg_password" class="form-control pe-5"
                                placeholder="Min. 6 characters" required>
                            <button type="button" class="toggle-password btn p-0 position-absolute end-0 top-50 translate-middle-y me-3 text-muted"
                                data-target="reg_password" aria-label="Toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label class="form-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control pe-5"
                                placeholder="Repeat password" required>
                            <button type="button" class="toggle-password btn p-0 position-absolute end-0 top-50 translate-middle-y me-3 text-muted"
                                data-target="confirm_password" aria-label="Toggle password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <button type="submit" class="btn-harbor w-100 py-2 mt-4" style="font-size:.85rem;letter-spacing:.15em;">
                    CREATE ACCOUNT
                </button>
            </form>

            <p class="text-center mt-4 mb-0" style="font-size:.88rem;color:var(--gray-muted);">
                Already have an account?
                <a href="login.php" style="color:var(--gold);font-weight:500;">Login here.</a>
            </p>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
