<?php
// pages/admin/admin_guard.php
// Include this at the top of every admin page
require_once '../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../pages/login.php?redirect=admin');
    exit;
}
?>
