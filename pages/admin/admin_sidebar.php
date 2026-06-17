<?php
// pages/admin/admin_sidebar.php
$admin_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' – ' : '' ?>Admin · Estrella Hotel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { background: #f5f0e8; }
        .admin-wrapper { display:flex; min-height:100vh; }
        .admin-sidebar {
            width: 250px; flex-shrink:0;
            background: var(--ocean, #2B4A6F);
            color:#fff; display:flex; flex-direction:column;
            position:fixed; top:0; left:0; height:100vh; overflow-y:auto; z-index:1000;
        }
        .admin-sidebar .sidebar-brand {
            padding:1.5rem 1.25rem;
            border-bottom:1px solid rgba(255,255,255,.1);
            font-family:var(--font-display,'Cormorant Garamond'),serif;
        }
        .admin-sidebar .sidebar-brand .brand-star { color:var(--gold,#A8834A); font-size:1.2rem; }
        .admin-sidebar .nav-link {
            color:rgba(255,255,255,.75); padding:.65rem 1.25rem;
            display:flex; align-items:center; gap:.75rem;
            font-size:.875rem; font-family:'Jost',sans-serif;
            border-left:3px solid transparent;
            transition:all .2s;
        }
        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color:#fff; background:rgba(255,255,255,.1);
            border-left-color:var(--gold,#A8834A);
        }
        .admin-sidebar .nav-link i { font-size:1rem; width:1.2rem; }
        .admin-sidebar .sidebar-section {
            padding:.5rem 1.25rem .25rem;
            font-size:.7rem; letter-spacing:.1em; text-transform:uppercase;
            color:rgba(255,255,255,.4); margin-top:.5rem;
        }
        .admin-main { margin-left:250px; flex:1; display:flex; flex-direction:column; }
        .admin-topbar {
            background:#fff; border-bottom:1px solid #e5e0d8;
            padding:.75rem 1.5rem; display:flex; align-items:center;
            justify-content:space-between; position:sticky; top:0; z-index:500;
        }
        .admin-content { padding:1.5rem; flex:1; }
        @media(max-width:768px) {
            .admin-sidebar { width:200px; }
            .admin-main { margin-left:0; }
            .admin-sidebar { transform:translateX(-100%); transition:transform .3s; }
            .admin-sidebar.show { transform:translateX(0); }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <span class="brand-star">✦</span>
        <span class="ms-2 fw-semibold">ESTRELLA</span><br>
        <small style="font-size:.7rem;opacity:.6;font-family:'Jost',sans-serif;letter-spacing:.05em;">ADMIN PANEL</small>
    </div>
    <nav class="py-2">
        <div class="sidebar-section">Main</div>
        <a href="dashboard.php" class="nav-link <?= $admin_page==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <div class="sidebar-section">Data</div>
        <a href="reservations.php" class="nav-link <?= $admin_page==='reservations'?'active':'' ?>">
            <i class="bi bi-calendar-check"></i> Reservations
        </a>
        <a href="rooms.php" class="nav-link <?= $admin_page==='rooms'?'active':'' ?>">
            <i class="bi bi-door-open"></i> Rooms
        </a>
        <a href="users.php" class="nav-link <?= $admin_page==='users'?'active':'' ?>">
            <i class="bi bi-people"></i> Users
        </a>
        <div class="sidebar-section">System</div>
        <a href="../../pages/logout.php" class="nav-link text-danger-soft">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>

<!-- MAIN -->
<div class="admin-main">
    <!-- Top Bar -->
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-semibold" style="font-family:'Jost',sans-serif;"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Welcome, <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></strong></span>
            <a href="../../index.php" class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
                <i class="bi bi-eye me-1"></i>View Site
            </a>
        </div>
    </div>
    <div class="admin-content">
