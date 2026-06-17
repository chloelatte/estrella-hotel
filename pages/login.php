<?php
// pages/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/config.php';
$page_title = 'Login';
$base = '../';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Prepared statement
        $stmt = $conn->prepare("SELECT id, full_name, username, email, password, role FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}

include '../includes/header.php';
?>

<section class="auth-section">
    <div class="container py-5" style="position:relative;z-index:2;">
        <div class="auth-card mx-auto">
            <!-- Brand: ✦ ESTRELLA — sparkle + name inline, no subtitle -->
            <div class="text-center mb-4">
                <a href="../index.php" class="auth-brand-inline">
                    <span class="auth-brand-star">✦</span>
                    <span class="auth-brand-name">ESTRELLA</span>
                </a>
            </div>

            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Please log in to your account.</p>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" id="loginError" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle me-2"></i>Akun berhasil dibuat. Silakan login.
            </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="login.php" novalidate>
                <!-- Username -->
                <div class="mb-3">
                    <label class="form-label" for="username">Username atau Email</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Masukkan username atau email"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username" required>
                    <div class="invalid-feedback"></div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="position-relative">
                        <input type="password" id="password" name="password" class="form-control pe-5"
                            placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="toggle-password btn p-0 position-absolute end-0 top-50 translate-middle-y me-3 text-muted"
                            data-target="password" aria-label="Toggle password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback"></div>
                </div>

                <!-- Remember + Forgot -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember" style="font-size:.85rem;">Remember me</label>
                    </div>
                    <a href="#" style="font-size:.85rem;color:var(--gold);">Forgot password?</a>
                </div>

                <button type="submit" class="btn-harbor w-100 py-2" style="font-size:.85rem;letter-spacing:.15em;">
                    LOGIN
                </button>
            </form>

            <p class="text-center mt-4 mb-0" style="font-size:.88rem;color:var(--gray-muted);">
                Don't have an account?
                <a href="register.php" style="color:var(--gold);font-weight:500;">Create account.</a>
            </p>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
