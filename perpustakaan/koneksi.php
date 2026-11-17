<?php
session_start();

$host = "localhost:3309";
$user = "root";
$pass = "";
$db   = "db_perpustakaan";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Konfigurasi Tarif Denda Per Hari (Rp)
$tarif_denda_per_hari = 1000;

// Fungsi proteksi halaman admin
function cek_login() {
    if (!isset($_SESSION['login_user'])) {
        header("Location: login.php");
        exit;
    }
}

// Fungsi Header & Navigasi Modern
function tampilkan_header($judul_halaman) {
    $is_login     = isset($_SESSION['login_user']);
    $nama_petugas = $is_login ? $_SESSION['nama_petugas'] : 'Pengunjung Publik';
    $username     = $is_login ? $_SESSION['login_user'] : 'guest';
    $current_page = basename($_SERVER['PHP_SELF']);
    $inisial      = strtoupper(substr($nama_petugas, 0, 1));
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($judul_halaman) ?> — LookBook</title>
        <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Bootstrap 5.3 & Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <style>
            :root {
                --primary: #4338ca;
                --primary-dark: #312e81;
                --primary-soft: #eef2ff;
                --bg-body: #f8fafc;
                --surface: #ffffff;
                --border-color: #e2e8f0;
                --text-main: #0f172a;
                --text-muted: #64748b;
                --sidebar-width: 260px;
            }
            body {
                font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
                background-color: var(--bg-body);
                color: var(--text-main);
                font-size: 0.925rem;
                overflow-x: hidden;
            }
            .font-mono {
                font-family: 'JetBrains Mono', monospace;
                font-size: 0.82rem;
            }
            /* Layout Sidebar untuk Petugas */
            .app-wrapper {
                display: flex;
                min-height: 100vh;
            }
            .sidebar {
                width: var(--sidebar-width);
                background: var(--surface);
                border-right: 1px solid var(--border-color);
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1030;
            }
            .sidebar-brand {
                padding: 1.25rem 1.5rem;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-decoration: none;
                color: var(--text-main);
            }
            .brand-icon {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.15rem;
                box-shadow: 0 4px 10px rgba(67, 56, 202, 0.25);
            }
            .sidebar-menu {
                padding: 1rem 0.85rem;
                flex-grow: 1;
                overflow-y: auto;
            }
            .menu-label {
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #94a3b8;
                padding: 0.5rem 0.75rem 0.35rem;
            }
            .nav-sidebar-link {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.65rem 0.9rem;
                border-radius: 10px;
                color: #475569;
                text-decoration: none;
                font-weight: 500;
                margin-bottom: 0.25rem;
                transition: all 0.18s ease;
            }
            .nav-sidebar-link:hover {
                background-color: #f1f5f9;
                color: var(--primary);
            }
            .nav-sidebar-link.active {
                background-color: var(--primary-soft);
                color: var(--primary);
                font-weight: 600;
            }
            .nav-sidebar-link i {
                font-size: 1.1rem;
            }
            .sidebar-footer {
                padding: 1rem;
                border-top: 1px solid var(--border-color);
                background: #fcfcfd;
            }
            .main-area {
                flex-grow: 1;
                margin-left: var(--sidebar-width);
                display: flex;
                flex-direction: column;
                min-width: 0;
            }
            .topbar {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--border-color);
                padding: 0.85rem 1.75rem;
                position: sticky;
                top: 0;
                z-index: 1020;
            }
            .content-container {
                padding: 1.75rem;
                max-width: 1400px;
                width: 100%;
                margin: 0 auto;
            }
            /* Komponen UI Modern */
            .card {
                background: var(--surface);
                border: 1px solid var(--border-color);
                border-radius: 14px;
                box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            }
            .card-header-clean {
                padding: 1.15rem 1.35rem;
                border-bottom: 1px solid var(--border-color);
                background: transparent;
            }
            .btn-primary {
                background-color: var(--primary);
                border-color: var(--primary);
                font-weight: 500;
                border-radius: 9px;
            }
            .btn-primary:hover {
                background-color: var(--primary-dark);
                border-color: var(--primary-dark);
            }
            .btn {
                border-radius: 9px;
                font-weight: 500;
            }
            .form-control, .form-select {
                border-radius: 9px;
                border-color: #cbd5e1;
                padding: 0.55rem 0.85rem;
                font-size: 0.9rem;
            }
            .form-control:focus, .form-select:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.12);
            }
            .form-label {
                font-weight: 600;
                color: #334155;
                margin-bottom: 0.35rem;
            }
            /* Tabel Modern */
            .table-modern {
                margin-bottom: 0;
            }
            .table-modern thead th {
                background-color: #f8fafc;
                color: #475569;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid var(--border-color);
                padding: 0.85rem 1rem;
            }
            .table-modern tbody td {
                padding: 0.85rem 1rem;
                border-bottom: 1px solid #f1f5f9;
                vertical-align: middle;
            }
            .table-modern tbody tr:hover {
                background-color: #f8fafc;
            }
            /* Soft Badges */
            .badge-soft-primary { background: #eef2ff; color: #4338ca; }
            .badge-soft-success { background: #ecfdf5; color: #059669; }
            .badge-soft-warning { background: #fffbeb; color: #d97706; }
            .badge-soft-danger  { background: #fef2f2; color: #dc2626; }
            .badge-soft-slate   { background: #f1f5f9; color: #475569; }
            .badge-pill {
                padding: 0.4em 0.75em;
                border-radius: 999px;
                font-weight: 600;
                font-size: 0.76rem;
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }
            .kpi-icon {
                width: 46px;
                height: 46px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.3rem;
            }
            @media (max-width: 991.98px) {
                .sidebar { display: none; }
                .main-area { margin-left: 0; }
                .content-container { padding: 1.15rem; }
            }
        </style>
    </head>
    <body>

    <?php if ($is_login): ?>
    <!-- TAMPILAN WORKSPACE PETUGAS (SIDEBAR + TOPBAR) -->
    <div class="app-wrapper">
        <!-- Sidebar Desktop -->
        <aside class="sidebar">
            <a href="fitur_tambahan.php" class="sidebar-brand">
                <div class="brand-icon"><i class="bi bi-book-half"></i></div>
                <div>
                    <div class="fw-bold fs-6 lh-1">LookBook</div>
                    <small class="text-muted" style="font-size:0.72rem;">Library Automation</small>
                </div>
            </a>

            <div class="sidebar-menu">
                <div class="menu-label">Ringkasan & Layanan</div>
                <a href="fitur_tambahan.php" class="nav-sidebar-link <?= $current_page=='fitur_tambahan.php' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i> Dashboard & Bebas Pustaka
                </a>
                <a href="sirkulasi.php" class="nav-sidebar-link <?= $current_page=='sirkulasi.php' ? 'active' : '' ?>">
                    <i class="bi bi-arrow-left-right"></i> Sirkulasi Peminjaman
                </a>

                <div class="menu-label mt-3">Master Data</div>
                <a href="bibliografi.php" class="nav-sidebar-link <?= $current_page=='bibliografi.php' ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark"></i> Katalog Bibliografi
                </a>
                <a href="anggota.php" class="nav-sidebar-link <?= $current_page=='anggota.php' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Keanggotaan
                </a>

                <div class="menu-label mt-3">Portal & Sistem</div>
                <a href="opac.php" class="nav-sidebar-link <?= $current_page=='opac.php' ? 'active' : '' ?>">
                    <i class="bi bi-search"></i> OPAC (Katalog Publik)
                </a>
                <a href="login.php" class="nav-sidebar-link <?= $current_page=='login.php' ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock"></i> Kelola Akun Petugas
                </a>
            </div>

            <div class="sidebar-footer">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:36px;height:36px;">
                            <?= $inisial ?>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-semibold text-truncate small"><?= htmlspecialchars($nama_petugas) ?></div>
                            <div class="text-muted" style="font-size:0.72rem;">@<?= htmlspecialchars($username) ?></div>
                        </div>
                    </div>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger" title="Logout" onclick="return confirm('Yakin ingin keluar dari sistem?')">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Offcanvas Sidebar Mobile -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" style="width:270px;">
            <div class="offcanvas-header border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="brand-icon"><i class="bi bi-book-half"></i></div>
                    <span class="fw-bold">SIM-PERPUS</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column justify-content-between p-3">
                <div>
                    <div class="menu-label">Menu Utama</div>
                    <a href="fitur_tambahan.php" class="nav-sidebar-link <?= $current_page=='fitur_tambahan.php' ? 'active' : '' ?>"><i class="bi bi-grid-1x2"></i> Dashboard & Bebas Pustaka</a>
                    <a href="sirkulasi.php" class="nav-sidebar-link <?= $current_page=='sirkulasi.php' ? 'active' : '' ?>"><i class="bi bi-arrow-left-right"></i> Sirkulasi Peminjaman</a>
                    <a href="bibliografi.php" class="nav-sidebar-link <?= $current_page=='bibliografi.php' ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i> Katalog Bibliografi</a>
                    <a href="anggota.php" class="nav-sidebar-link <?= $current_page=='anggota.php' ? 'active' : '' ?>"><i class="bi bi-people"></i> Keanggotaan</a>
                    <a href="opac.php" class="nav-sidebar-link <?= $current_page=='opac.php' ? 'active' : '' ?>"><i class="bi bi-search"></i> OPAC Publik</a>
                    <a href="login.php" class="nav-sidebar-link <?= $current_page=='login.php' ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i> Kelola Akun</a>
                </div>
                <a href="logout.php" class="btn btn-danger w-100 mt-3" onclick="return confirm('Yakin ingin logout?')">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout Sistem
                </a>
            </div>
        </div>

        <!-- Area Konten Utama -->
        <div class="main-area">
            <header class="topbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light border d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <div>
                        <h1 class="h5 fw-bold mb-0"><?= htmlspecialchars($judul_halaman) ?></h1>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge-pill badge-soft-success d-none d-sm-inline-flex">
                        <i class="bi bi-circle-fill" style="font-size:0.5rem;"></i> Sistem Automasi Aktif
                    </span>
                    <span class="badge-pill badge-soft-slate d-none d-md-inline-flex">
                        <i class="bi bi-calendar3"></i> <?= date('d M Y') ?>
                    </span>
                </div>
            </header>
            <main class="content-container">

    <?php else: ?>
    <!-- TAMPILAN PORTAL PUBLIK (BELUM LOGIN: OPAC & LOGIN) -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="opac.php">
                <div class="brand-icon" style="width:34px;height:34px;font-size:1rem;"><i class="bi bi-book-half"></i></div>
                <div>
                    <span class="fw-bold text-dark">LookBook</span>
                    <span class="badge badge-soft-primary ms-1 d-none d-sm-inline" style="font-size:0.7rem;">OPAC Portal</span>
                </div>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="opac.php" class="btn btn-sm <?= $current_page=='opac.php' ? 'btn-light fw-semibold text-primary' : 'btn-link text-decoration-none text-secondary' ?>">
                    <i class="bi bi-search me-1"></i> Katalog OPAC
                </a>
                <a href="login.php" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login Petugas
                </a>
            </div>
        </div>
    </nav>
    <main class="container py-4">
    <?php endif;
}

function tampilkan_footer() {
    $is_login = isset($_SESSION['login_user']);
    if ($is_login) {
        echo '</main></div></div>';
    } else {
        echo '</main>';
    }
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>';
    echo '</body></html>';
}
?>