<?php
require_once __DIR__ . '/config.php';

Auth::requireLogin();

$user = Auth::user();
$role = Auth::role();
$flashSuccess = '';
$flashError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_package') {
        // Hilangkan prefix Rp dan pemisah ribuan sebelum validasi/penyimpanan.
        $_POST['budget'] = preg_replace('/[^0-9]/', '', $_POST['budget'] ?? '0');
        $_POST['contract_value'] = preg_replace('/[^0-9]/', '', $_POST['contract_value'] ?? '0');
        if ($role !== 'PPK' && $role !== 'Admin') {
            $flashError = 'Hanya role PPK atau Administrator yang berwenang menambahkan paket pengadaan.';
        } else {
            $res = Dashboard::createPackage($_POST, Auth::id());
            if ($res === true) {
                $flashSuccess = 'Paket pengadaan baru berhasil didaftarkan dan diajukan ke antrean persetujuan!';
            } else {
                $flashError = $res;
            }
        }
    }

    elseif ($action === 'approve_package') {
        if ($role !== 'PA-KPA' && $role !== 'Admin') {
            $flashError = 'Hanya Pengguna Anggaran (PA/KPA) atau Administrator yang berwenang menyetujui pengadaan.';
        } else {
            $pkgId    = (int)($_POST['package_id'] ?? 0);
            $decision = trim($_POST['decision'] ?? '');
            $rupCode  = trim($_POST['rup_code'] ?? 'Paket');

            $res = Dashboard::processApproval($pkgId, $decision, Auth::id());
            if ($res === true) {
                if ($decision === 'Disetujui') {
                    $flashSuccess = "Paket {$rupCode} berhasil di-ACC / Disetujui oleh {$user['name']}! Rekam persetujuan tersimpan ke database approvals.";
                } else {
                    $flashSuccess = "Paket {$rupCode} telah ditolak oleh {$user['name']} dan dicatat ke riwayat approvals.";
                }
            } else {
                $flashError = $res;
            }
        }
    }
}

$dashboard = new Dashboard();

$stats            = $dashboard->getStats();
$methods          = $dashboard->getMethodComposition();
$opdList          = $dashboard->getOpdBreakdown();
$approvals        = $dashboard->getApprovals(5);
$actionPkgs       = $dashboard->getActionNeededPackages(5);
$pendingApprovals = $dashboard->getPendingApprovals();
$latestPkgs       = $dashboard->getLatestPackages(25);
$pres             = $dashboard->getRolePresentation();
$nextRup          = Dashboard::getNextRupCode();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pengadaan — SIMADA Kabupaten Kebumen</title>
    <!-- Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<aside class="sidebar">
    <!-- Brand Header dengan Logo SIMADA Kebumen -->
    <div class="brand">
        <a href="dashboard.php" class="brand-link">
            <img src="assets/img/logo-simada.png" alt="SIMADA Kabupaten Kebumen" class="brand-logo"
                 onerror="this.style.display='none'; document.getElementById('brand-fallback').style.display='flex';">
            <div id="brand-fallback" class="brand-fallback" style="display:none;">
                <span class="fallback-stripes">||</span>
                <div>
                    <strong class="fallback-title">SIMADA</strong>
                    <small class="fallback-sub">KABUPATEN KEBUMEN</small>
                </div>
            </div>
        </a>
    </div>

    <!-- Ruang Kerja Navigation (Sesuai SDD Section 7 & 12) -->
    <p class="nav-caption">RUANG KERJA</p>
    <nav class="nav-menu">
        <a class="nav-link active" href="dashboard.php">
            <span class="nav-num">01</span>
            <span class="nav-title">Dashboard</span>
        </a>
        <a class="nav-link" href="#paket-terbaru">
            <span class="nav-num">02</span>
            <span class="nav-title">Paket Pengadaan</span>
        </a>
        <?php if ($role === 'Admin'): ?>
            <a class="nav-link" href="tambah-user.php" title="Kelola & Tambah Pengguna Baru">
                <span class="nav-num">03</span>
                <span class="nav-title">Pengguna & Role</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer Info -->
    <div class="sidebar-footer">
        <div class="user-role-card">
            <span class="role-badge-mini"><?= e($role) ?></span>
            <p class="user-name"><?= e($user['name']) ?></p>
            <small class="user-opd">Setda Kabupaten Kebumen</small>
        </div>
        <div class="ukpbj-note">
            <small>SIMADA · UKPBJ</small>
            <span>Lingkungan Pemerintah Kabupaten Kebumen</span>
        </div>
    </div>
</aside>

<main class="main">

    <!-- Topbar Header dengan Fitur Live Search Interaktif -->
    <header class="topbar">
        <div class="topbar-search" id="search-wrapper">
            <!-- Ikon SVG Polos Minimalis -->
            <svg class="search-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="quick-search"
                   placeholder="Cari kode RUP, nama paket, atau OPD..."
                   oninput="handleSearch(this.value)"
                   onfocus="handleSearch(this.value)"
                   onkeydown="handleSearchKey(event)"
                   autocomplete="off">
            <button type="button" id="search-clear-btn" class="search-clear-btn" onclick="clearSearch()" style="display:none;" title="Bersihkan pencarian">✕</button>

            <!-- Dropdown Hasil Pencarian Langsung -->
            <div id="search-dropdown" class="search-dropdown" style="display: none;">
                <div class="search-dropdown-header">
                    <span id="search-count-text">Hasil Pencarian</span>
                    <small>Klik untuk menuju paket</small>
                </div>
                <div class="search-dropdown-list" id="search-results-list"></div>
                <div class="search-dropdown-footer">
                    <span>Tekan <kbd>Enter</kbd> untuk filter tabel lengkap</span>
                </div>
            </div>
        </div>

        <div class="topbar-right">
            <?php if ($role === 'PPK' || $role === 'Admin'): ?>
                <!-- Tombol Tambah Pengadaan Cepat -->
                <button type="button" class="btn-add-quick" onclick="openAddModal()">
                    <span style="font-weight:900; font-size:14px;">+</span> Tambah Paket Pengadaan
                </button>
            <?php endif; ?>

            <div class="ta-badge">
                <span class="ta-dot"></span>
                <span>Tahun Anggaran <strong>2026</strong></span>
            </div>

            <div class="profile-chip">
                <div class="avatar"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></div>
                <div class="profile-info">
                    <strong><?= e($user['name']) ?></strong>
                    <span class="profile-role-tag"><?= e($pres['badge']) ?></span>
                </div>
            </div>

            <a class="btn-logout" href="logout.php" title="Keluar dari sesi ini">Keluar</a>
        </div>
    </header>

    <!-- Isi Halaman Dashboard -->
    <div class="content">

        <!-- Flash Alert Notifikasi Aksi -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success" style="padding:14px 20px; border-radius:10px; background:var(--success-bg); border:1px solid #bce6dc; color:var(--success); font-weight:700; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:16px;">✓</span>
                <span><?= e($flashSuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger" style="margin-bottom:20px;">
                <span style="font-size:16px;">✕</span>
                <span><?= e($flashError) ?></span>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb & Page Header -->
        <div class="page-header-row">
            <div>
                <nav class="breadcrumb">
                    <span>SIMADA</span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">Dashboard Pengadaan</span>
                </nav>
                <h1 class="page-title"><?= e($pres['title']) ?></h1>
                <p class="page-subtitle"><?= e($pres['subtitle']) ?></p>
            </div>
            <div class="header-tags">
                <span class="demo-pill">DATA SIMULASI</span>
                <span class="scope-pill"><?= e($pres['highlightVal']) ?></span>
            </div>
        </div>

        <!-- Role Focus Banner dengan Gradasi Elegan -->
        <div class="role-focus-banner banner-<?= strtolower(str_replace('-', '', $role)) ?>">
            <div class="banner-left">
                <div class="banner-icon-box">
                    <?php if ($role === 'Admin'): ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    <?php elseif ($role === 'PA-KPA'): ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="21" x2="21" y2="21"></line>
                            <line x1="9" y1="8" x2="9" y2="17"></line>
                            <line x1="15" y1="8" x2="15" y2="17"></line>
                            <path d="M4 8l8-5 8 5"></path>
                            <rect x="2" y="17" width="20" height="4"></rect>
                        </svg>
                    <?php else: ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="banner-title"><?= e($pres['badge']) ?></h3>
                    <p class="banner-desc"><?= e($pres['tagline']) ?></p>
                </div>
            </div>
            <div class="banner-right">
                <div class="banner-stat-box">
                    <span class="stat-caption">Status Akses</span>
                    <strong class="stat-value text-success">Aktif & Terverifikasi</strong>
                </div>
                <?php if ($role === 'PA-KPA'): ?>
                <div class="banner-stat-box">
                    <span class="stat-caption">Pagu Dikelola</span>
                    <strong class="stat-value text-gradient-gold"><?= rupiah($stats['totalBudget']) ?></strong>
                </div>
                <?php elseif ($role === 'PPK'): ?>
                <div class="banner-stat-box">
                    <span class="stat-caption">Tugas Anda</span>
                    <strong class="stat-value text-gradient-teal"><?= $stats['totalPackages'] ?> Paket Pengadaan</strong>
                </div>
                <?php else: ?>
                <div class="banner-stat-box">
                    <span class="stat-caption">Integritas Sistem</span>
                    <strong class="stat-value text-success">Optimal (3 Layanan)</strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if (($role === 'PA-KPA' || $role === 'Admin') && !empty($pendingApprovals)): ?>
        <section class="card-panel" style="margin-bottom:24px; border-left: 5px solid var(--gold-dark); background: linear-gradient(135deg, #ffffff 0%, #fffdf8 100%);">
            <div class="card-header" style="background: transparent;">
                <div>
                    <h2 class="card-title" style="color:var(--gold-dark); display:flex; align-items:center; gap:8px;">
                        <span>📋</span> Antrean Otorisasi / ACC Pengadaan (Menunggu Persetujuan PA-KPA)
                    </h2>
                    <p class="card-desc">Paket pengadaan berikut telah diajukan oleh PPK dan memerlukan persetujuan otorisasi Anda.</p>
                </div>
                <span class="chip-number" style="background:var(--gold-light); color:var(--gold-dark);">
                    <?= count($pendingApprovals) ?> Paket Perlu Tindakan
                </span>
            </div>

            <div class="table-responsive" style="padding: 0 20px 20px;">
                <table class="packages-table" style="background:#ffffff; border-radius:10px; border:1px solid var(--border-soft);">
                    <thead>
                        <tr>
                            <th>Kode RUP</th>
                            <th>Nama Paket Pengadaan</th>
                            <th>OPD Pengusul</th>
                            <th>Metode</th>
                            <th class="text-right">Pagu Anggaran</th>
                            <th class="text-center" style="width: 220px;">Aksi Otorisasi (ACC)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingApprovals as $p): ?>
                        <tr>
                            <td><span class="rup-badge"><?= e($p['rup_code']) ?></span></td>
                            <td>
                                <strong><?= e($p['package_name']) ?></strong>
                                <div class="package-date">Pengusul: <?= e($p['creator_name']) ?></div>
                            </td>
                            <td><?= e($p['opd']) ?></td>
                            <td><span class="method-tag"><?= e($p['method']) ?></span></td>
                            <td class="text-right font-semibold text-navy"><?= rupiah($p['budget']) ?></td>
                            <td class="text-center">
                                <div style="display:flex; gap:8px; justify-content:center;">
                                    <!-- Form ACC / Setujui -->
                                    <form method="post" action="dashboard.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI (ACC) paket <?= e($p['rup_code']) ?>?');">
                                        <input type="hidden" name="action" value="approve_package">
                                        <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="rup_code" value="<?= e($p['rup_code']) ?>">
                                        <input type="hidden" name="decision" value="Disetujui">
                                        <button type="submit" class="btn-acc" title="Setujui Paket Ini">
                                            ✓ ACC / Setujui
                                        </button>
                                    </form>

                                    <!-- Form Tolak -->
                                    <form method="post" action="dashboard.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK paket <?= e($p['rup_code']) ?>?');">
                                        <input type="hidden" name="action" value="approve_package">
                                        <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                                        <input type="hidden" name="rup_code" value="<?= e($p['rup_code']) ?>">
                                        <input type="hidden" name="decision" value="Ditolak">
                                        <button type="submit" class="btn-reject" title="Tolak Paket Ini">
                                            ✕ Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
        <div class="kpis-grid">
            <!-- Kartu 1: Paket Terdaftar -->
            <article class="kpi-card kpi-card-1">
                <div class="kpi-head">
                    <span class="kpi-label">
                        <?= $role === 'PPK' ? 'Paket Tanggung Jawab Anda' : 'Paket Terdaftar' ?>
                    </span>
                    <div class="kpi-icon-plain">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                </div>
                <div class="kpi-body">
                    <span class="kpi-number"><?= $stats['totalPackages'] ?></span>
                    <span class="kpi-unit">Paket</span>
                </div>
                <div class="kpi-foot">
                    <span class="status-dot dot-info"></span>
                    <span class="kpi-desc">
                        <?= $role === 'PPK' ? 'Ditugaskan ke akun PPK Anda' : 'Total paket aktif TA 2026' ?>
                    </span>
                </div>
            </article>

            <!-- Kartu 2: Total Pagu Anggaran -->
            <article class="kpi-card kpi-card-2">
                <div class="kpi-head">
                    <span class="kpi-label">
                        <?= $role === 'PPK' ? 'Total Pagu Paket Saya' : 'Total Pagu Anggaran' ?>
                    </span>
                    <div class="kpi-icon-plain">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="M7 15h0M2 9.5h20"></path>
                        </svg>
                    </div>
                </div>
                <div class="kpi-body">
                    <span class="kpi-number"><?= rupiah($stats['totalBudget']) ?></span>
                </div>
                <div class="kpi-foot">
                    <span class="status-dot dot-teal"></span>
                    <span class="kpi-desc">Alokasi pagu APBD Kabupaten Kebumen</span>
                </div>
            </article>

            <!-- Kartu 3: Nilai Kontrak / Realisasi -->
            <article class="kpi-card kpi-card-3">
                <div class="kpi-head">
                    <span class="kpi-label">
                        <?= $role === 'PPK' ? 'Realisasi Kontrak Saya' : 'Realisasi Nilai Kontrak' ?>
                    </span>
                    <div class="kpi-icon-plain">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="kpi-body">
                    <span class="kpi-number"><?= rupiah($stats['totalContract']) ?></span>
                </div>
                <div class="kpi-foot">
                    <span class="status-dot dot-green"></span>
                    <span class="kpi-desc">
                        <?= $stats['totalBudget'] > 0 ? round(($stats['totalContract'] / $stats['totalBudget']) * 100, 1) : 0 ?>% dari total pagu
                    </span>
                </div>
            </article>

            <!-- Kartu 4: Efisiensi (PA-KPA) / Paket Selesai (Admin/PPK) -->
            <?php if ($role === 'PA-KPA'): ?>
            <article class="kpi-card kpi-highlight-gradient">
                <div class="kpi-head">
                    <span class="kpi-label">Efisiensi Belanja / Hemat</span>
                    <div class="kpi-icon-plain icon-gold">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="kpi-body">
                    <span class="kpi-number text-gradient-teal"><?= rupiah($stats['savings']) ?></span>
                </div>
                <div class="kpi-foot">
                    <span class="status-dot dot-green"></span>
                    <span class="kpi-desc">Hemat <?= $stats['savingsPercent'] ?>% terhadap pagu berkontrak</span>
                </div>
            </article>
            <?php else: ?>
            <article class="kpi-card kpi-card-4">
                <div class="kpi-head">
                    <span class="kpi-label">
                        <?= $role === 'PPK' ? 'Paket Selesai Dikerjakan' : 'Paket Selesai' ?>
                    </span>
                    <div class="kpi-icon-plain icon-teal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="kpi-body">
                    <span class="kpi-number"><?= $stats['completed'] ?></span>
                    <span class="kpi-unit">/ <?= $stats['totalPackages'] ?> Paket</span>
                </div>
                <div class="kpi-foot">
                    <span class="status-dot dot-green"></span>
                    <span class="kpi-desc"><?= $stats['completionRate'] ?>% tingkat penyelesaian</span>
                </div>
            </article>
            <?php endif; ?>
        </div>
        <div class="executive-grid">

            <!-- Panel Kiri: Konten Spesifik Role -->
            <?php if ($role === 'Admin' || $role === 'PA-KPA'): ?>
            <!-- REKAPITULASI OPD KEBUMEN -->
            <section class="card-panel">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Rekapitulasi Pengadaan per Satuan Kerja (OPD)</h2>
                        <p class="card-desc">Distribusi jumlah paket, pagu anggaran, dan realisasi kontrak antar dinas</p>
                    </div>
                    <span class="opd-count-tag"><?= count($opdList) ?> Satker Terdata</span>
                </div>

                <div class="table-responsive">
                    <table class="opd-table">
                        <thead>
                            <tr>
                                <th>Satuan Kerja (OPD)</th>
                                <th class="text-center">Jml Paket</th>
                                <th class="text-right">Total Pagu</th>
                                <th class="text-right">Nilai Kontrak</th>
                                <th class="text-center">Status Selesai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($opdList as $opd): ?>
                            <tr>
                                <td>
                                    <strong><?= e($opd['opd']) ?></strong>
                                    <div class="opd-sub">Pemerintah Kabupaten Kebumen</div>
                                </td>
                                <td class="text-center font-bold">
                                    <span class="chip-number"><?= $opd['totalPackages'] ?></span>
                                </td>
                                <td class="text-right text-navy font-semibold"><?= rupiah($opd['totalBudget']) ?></td>
                                <td class="text-right text-green font-semibold"><?= rupiah($opd['totalContract']) ?></td>
                                <td class="text-center">
                                    <span class="completion-pill">
                                        <?= $opd['completed'] ?> / <?= $opd['totalPackages'] ?> Selesai
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php else: ?>
            <!-- DASHBOARD KHUSUS PPK: KESIAPAN PROSES & BERKAS -->
            <section class="card-panel">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Status Penugasan & Kesiapan Berkas PPK</h2>
                        <p class="card-desc">Monitoring tahapan administrasi paket pengadaan yang ditugaskan kepada Anda</p>
                    </div>
                    <button type="button" class="btn-primary" style="padding:6px 14px; font-size:12px;" onclick="openAddModal()">
                        + Tambah Paket Baru
                    </button>
                </div>

                <div class="ppk-status-grid">
                    <div class="ppk-stat-box">
                        <span class="stat-title">Draft Persiapan</span>
                        <strong class="stat-number text-muted"><?= $stats['draft'] ?></strong>
                        <small>Penyusunan HPS & KAK</small>
                    </div>
                    <div class="ppk-stat-box">
                        <span class="stat-title">Menunggu Persetujuan</span>
                        <strong class="stat-number text-warning"><?= $stats['pendingApproval'] ?></strong>
                        <small>Menunggu ACC PA/KPA</small>
                    </div>
                    <div class="ppk-stat-box">
                        <span class="stat-title">Dalam Proses Pemilihan</span>
                        <strong class="stat-number text-info"><?= $stats['inProgress'] ?></strong>
                        <small>Tahap lelang/pembelian</small>
                    </div>
                    <div class="ppk-stat-box">
                        <span class="stat-title">Kontrak Selesai</span>
                        <strong class="stat-number text-success"><?= $stats['completed'] ?></strong>
                        <small>Pekerjaan rampung</small>
                    </div>
                </div>

                <!-- Checklist Tugas PPK -->
                <div class="ppk-checklist-box">
                    <h4 class="checklist-title">Pedoman Alur Kerja PPK (Standar PBJ Kebumen)</h4>
                    <div class="checklist-items">
                        <div class="check-item done">
                            <span class="check-badge">✓</span>
                            <div>
                                <strong>1. Penyusunan Spesifikasi Teknis & HPS</strong>
                                <p>Pastikan HPS telah disahkan sebelum pengajuan ke Bagian Pengadaan Barang/Jasa.</p>
                            </div>
                        </div>
                        <div class="check-item ongoing">
                            <span class="check-badge">◷</span>
                            <div>
                                <strong>2. Pengajuan Otorisasi & Penetapan Pokja</strong>
                                <p>Paket berstatus 'Menunggu Persetujuan' akan diverifikasi dan di-ACC oleh PA-KPA.</p>
                            </div>
                        </div>
                        <div class="check-item pending">
                            <span class="check-badge">✎</span>
                            <div>
                                <strong>3. Penandatanganan Kontrak & Berita Acara</strong>
                                <p>Gunakan modul Berita Acara (UC-14/15) dan Surat Pemberitahuan setelah tender selesai.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Panel Kanan: Komposisi Metode & Status Otorisasi -->
            <div class="side-panel-stack">

                <!-- Widget Komposisi Metode -->
                <section class="card-panel">
                    <div class="card-header no-border">
                        <div>
                            <h2 class="card-title">Komposisi Metode Pengadaan</h2>
                            <p class="card-desc">Distribusi alokasi berdasarkan jenis mekanisme PBJ</p>
                        </div>
                    </div>

                    <div class="method-list">
                        <?php foreach ($methods as $methodName => $m): ?>
                        <div class="method-card">
                            <div class="method-header-row">
                                <div>
                                    <strong class="method-name"><?= e($methodName) ?></strong>
                                    <span class="method-caption"><?= e($m['badge']) ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="method-percent"><?= $m['percent'] ?>%</span>
                                    <small class="method-count"><?= $m['count'] ?> Paket</small>
                                </div>
                            </div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill fill-<?= strtolower(explode('-', explode(' ', $methodName)[0])[0]) ?>" style="width: <?= $m['percent'] ?>%;"></div>
                            </div>
                            <div class="method-pagu-foot">
                                <span>Pagu: <?= rupiah($m['budget']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Widget Riwayat Persetujuan / Catatan Sistem (Approvals) -->
                <section class="card-panel">
                    <div class="card-header no-border">
                        <div>
                            <h2 class="card-title">
                                <?= $role === 'PA-KPA' ? 'Catatan Otorisasi Terakhir' : 'Riwayat Persetujuan Paket' ?>
                            </h2>
                            <p class="card-desc">Status validasi dan keputusan persetujuan dokumen pengadaan</p>
                        </div>
                    </div>

                    <div class="approvals-feed">
                        <?php if (empty($approvals)): ?>
                            <div class="empty-feed">Belum ada catatan persetujuan tersimpan.</div>
                        <?php else: ?>
                            <?php foreach ($approvals as $app): ?>
                            <div class="approval-item">
                                <div class="approval-icon-box <?= $app['decision'] === 'Disetujui' ? 'icon-approved' : 'icon-rejected' ?>">
                                    <?= $app['decision'] === 'Disetujui' ? '✓' : '✕' ?>
                                </div>
                                <div class="approval-content">
                                    <div class="approval-title">
                                        <strong><?= e($app['rup_code']) ?></strong> — <?= e($app['package_name']) ?>
                                    </div>
                                    <div class="approval-meta">
                                        <span>Oleh: <?= e($app['approver_name']) ?></span>
                                        <span class="meta-dot">·</span>
                                        <span><?= date('d M Y H:i', strtotime($app['decided_at'])) ?></span>
                                    </div>
                                </div>
                                <span class="badge <?= $app['decision'] === 'Disetujui' ? 'success' : 'danger' ?>">
                                    <?= e($app['decision']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </div>
        <section class="card-panel" id="paket-terbaru">
            <div class="card-header table-header-flex">
                <div>
                    <h2 class="card-title">
                        <?= $role === 'PPK' ? 'Daftar Paket Pengadaan Anda' : 'Daftar Paket Pengadaan Terbaru' ?>
                    </h2>
                    <p class="card-desc" id="table-subtitle">
                        <?= $role === 'PPK'
                            ? 'Menampilkan seluruh paket dalam tanggung jawab akun PPK yang aktif.'
                            : 'Menampilkan paket pengadaan lintas OPD Pemerintah Kabupaten Kebumen TA 2026.' ?>
                    </p>
                </div>

                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <?php if ($role === 'PPK' || $role === 'Admin'): ?>
                        <button type="button" class="btn-primary" style="padding:6px 14px; font-size:12px;" onclick="openAddModal()">
                            + Tambah Paket
                        </button>
                    <?php endif; ?>

                    <!-- Tab Filter Status -->
                    <div class="filter-tabs" id="filter-tabs">
                        <button class="tab-btn active" onclick="filterByStatus('all', this)">Semua (<span id="count-all"><?= count($latestPkgs) ?></span>)</button>
                        <button class="tab-btn" onclick="filterByStatus('Proses', this)">Proses</button>
                        <button class="tab-btn" onclick="filterByStatus('Menunggu Persetujuan', this)">Menunggu</button>
                        <button class="tab-btn" onclick="filterByStatus('Disetujui', this)">Disetujui</button>
                        <button class="tab-btn" onclick="filterByStatus('Selesai', this)">Selesai</button>
                        <button class="tab-btn" onclick="filterByStatus('Draft', this)">Draft</button>
                    </div>
                </div>
            </div>

            <!-- Tabel Data Paket -->
            <div class="table-responsive">
                <table class="packages-table" id="packages-table">
                    <thead>
                        <tr>
                            <th style="width: 45px;">No</th>
                            <th style="width: 110px;">Kode RUP</th>
                            <th>Nama Paket Pengadaan</th>
                            <th>OPD / Pelaksana</th>
                            <th>Metode Pengadaan</th>
                            <th class="text-right">Pagu Anggaran</th>
                            <th class="text-right">Nilai Kontrak</th>
                            <th class="text-center" style="width: 140px;">Status</th>
                            <?php if ($role === 'PA-KPA' || $role === 'Admin'): ?>
                                <th class="text-center" style="width: 110px;">Otorisasi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="packages-tbody">
                        <?php if (empty($latestPkgs)): ?>
                        <tr id="empty-row">
                            <td colspan="<?= ($role === 'PA-KPA' || $role === 'Admin') ? 9 : 8 ?>" class="empty-state">
                                <div class="empty-state-content">
                                    <span class="empty-symbol">⌕</span>
                                    <strong>Tidak ada paket ditemukan</strong>
                                    <p>Belum ada data paket pengadaan pada cakupan hak akses akun Anda.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($latestPkgs as $idx => $pkg): ?>
                            <tr class="pkg-row" id="row-<?= e($pkg['rup_code']) ?>"
                                data-rup="<?= strtolower(e($pkg['rup_code'])) ?>"
                                data-status="<?= e($pkg['status']) ?>"
                                data-search="<?= strtolower(e($pkg['rup_code'] . ' ' . $pkg['package_name'] . ' ' . $pkg['opd'] . ' ' . $pkg['method'] . ' ' . $pkg['status'])) ?>">
                                <td class="text-center text-muted row-number"><?= $idx + 1 ?></td>
                                <td>
                                    <span class="rup-badge"><?= e($pkg['rup_code']) ?></span>
                                </td>
                                <td>
                                    <strong class="package-name"><?= e($pkg['package_name']) ?></strong>
                                    <div class="package-date">Dibuat: <?= date('d/m/Y', strtotime($pkg['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="opd-name"><?= e($pkg['opd']) ?></span>
                                </td>
                                <td>
                                    <span class="method-tag"><?= e($pkg['method']) ?></span>
                                </td>
                                <td class="text-right font-semibold text-navy">
                                    <?= rupiah($pkg['budget']) ?>
                                </td>
                                <td class="text-right font-semibold <?= (float)$pkg['contract_value'] > 0 ? 'text-green' : 'text-muted' ?>">
                                    <?= (float)$pkg['contract_value'] > 0 ? rupiah($pkg['contract_value']) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= statusClass($pkg['status']) ?>">
                                        <?= e($pkg['status']) ?>
                                    </span>
                                </td>
                                <?php if ($role === 'PA-KPA' || $role === 'Admin'): ?>
                                <td class="text-center">
                                    <?php if ($pkg['status'] === 'Menunggu Persetujuan'): ?>
                                        <form method="post" action="dashboard.php" style="display:inline;" onsubmit="return confirm('ACC / Setujui paket <?= e($pkg['rup_code']) ?>?');">
                                            <input type="hidden" name="action" value="approve_package">
                                            <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                            <input type="hidden" name="rup_code" value="<?= e($pkg['rup_code']) ?>">
                                            <input type="hidden" name="decision" value="Disetujui">
                                            <button type="submit" class="btn-acc-mini" title="ACC Paket">✓ ACC</button>
                                        </form>
                                    <?php elseif ($pkg['status'] === 'Disetujui' || $pkg['status'] === 'Proses' || $pkg['status'] === 'Selesai'): ?>
                                        <span style="font-size:11px; color:var(--teal-main); font-weight:700;">✓ Sah</span>
                                    <?php else: ?>
                                        <span style="font-size:11px; color:var(--ink-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                            <!-- Baris hasil kosong pencarian dinamis -->
                            <tr id="no-search-results" style="display: none;">
                                <td colspan="<?= ($role === 'PA-KPA' || $role === 'Admin') ? 9 : 8 ?>" class="empty-state">
                                    <div class="empty-state-content">
                                        <span class="empty-symbol">⌕</span>
                                        <strong>Pencarian tidak menemukan paket yang cocok</strong>
                                        <p id="no-search-message">Silakan periksa kembali kata kunci yang dimasukkan.</p>
                                        <button type="button" class="btn-reset-search" onclick="clearSearch()">Reset Pencarian</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="table-foot">
                <span class="table-counter" id="table-counter">
                    Menampilkan <strong id="visible-count"><?= count($latestPkgs) ?></strong> dari <?= count($latestPkgs) ?> paket dalam cakupan akses <strong><?= e($role) ?></strong>.
                </span>
                <span class="demo-tag">DATA SIMULASI KEBUMEN TA 2026</span>
            </div>
        </section>

        <!-- Footer Identitas Aplikasi -->
        <footer class="app-footer">
            <p>
                <strong>SIMADA</strong> — Sistem Informasi Manajemen Aset & Pengadaan Barang/Jasa Pemerintah Kabupaten Kebumen.<br>
                Implementasi Desain Perangkat Lunak: <strong>UC-08 (Dashboard Pengadaan) & UC-02 (Kelola Paket Pengadaan)</strong> · Seluruh data adalah <strong>data simulasi</strong>.
            </p>
        </footer>

    </div>
</main>
<div id="modal-add-package" class="modal-backdrop" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Tambah Paket Pengadaan Baru</h3>
                <p class="modal-subtitle">Daftarkan paket pengadaan baru untuk diajukan ke Bagian Pengadaan Barang/Jasa.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeAddModal()">✕</button>
        </div>

        <form method="post" action="dashboard.php" class="modal-body" onsubmit="return validatePackageForm();">
            <input type="hidden" name="action" value="create_package">

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="modal-rup">Kode RUP (Rencana Umum Pengadaan)</label>
                    <input type="text" id="modal-rup" name="rup_code" required value="<?= e($nextRup) ?>" placeholder="Contoh: RUP-2611">
                    <small style="font-size:11px; color:var(--ink-muted);">Kode unik pengenal paket pengadaan.</small>
                </div>

                <div class="form-group">
                    <label for="modal-opd">Satuan Kerja / OPD Pelaksana</label>
                    <select id="modal-opd" name="opd" required style="width:100%; padding:11px 14px; border:1px solid var(--border-line); border-radius:8px; background:#fbfdfd;">
                        <option value="Bagian Pengadaan Barang/Jasa">Bagian Pengadaan Barang/Jasa</option>
                        <option value="Dinas Komunikasi dan Informatika">Dinas Komunikasi dan Informatika</option>
                        <option value="Sekretariat Daerah">Sekretariat Daerah</option>
                        <option value="Dinas Pendidikan">Dinas Pendidikan</option>
                        <option value="Dinas Kesehatan">Dinas Kesehatan</option>
                        <option value="Dinas PUPR">Dinas PUPR</option>
                        <option value="Inspektorat Daerah">Inspektorat Daerah</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="modal-name">Nama Kegiatan / Paket Pengadaan</label>
                <input type="text" id="modal-name" name="package_name" required placeholder="Contoh: Pengadaan Server Data Center & Lisensi Cloud">
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="modal-method">Metode Pengadaan</label>
                    <select id="modal-method" name="method" required style="width:100%; padding:11px 14px; border:1px solid var(--border-line); border-radius:8px; background:#fbfdfd;">
                        <option value="e-Tendering">e-Tendering (Tender Terbuka SPSE)</option>
                        <option value="Pengadaan Langsung">Pengadaan Langsung (< Rp 200 Juta)</option>
                        <option value="e-Purchasing">e-Purchasing (Katalog Elektronik)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="modal-status">Status Awal Paket</label>
                    <select id="modal-status" name="status" required style="width:100%; padding:11px 14px; border:1px solid var(--border-line); border-radius:8px; background:#fbfdfd;">
                        <option value="Menunggu Persetujuan">Menunggu Persetujuan (Diajukan ke PA/KPA)</option>
                        <option value="Draft">Draft (Dalam Penyusunan HPS & KAK)</option>
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="modal-budget">Pagu Anggaran (Rp)</label>
                    <input type="text" inputmode="numeric" id="modal-budget" name="budget" class="rupiah-input" required placeholder="Rp 350.000.000">
                    <small style="font-size:11px; color:var(--ink-muted);">Nominal pagu APBD murni / PAK.</small>
                </div>

                <div class="form-group">
                    <label for="modal-contract">Nilai Kontrak (Rp, Opsional)</label>
                    <input type="text" inputmode="numeric" id="modal-contract" name="contract_value" class="rupiah-input" value="0" placeholder="Rp 0">
                    <small style="font-size:11px; color:var(--ink-muted);">Isi jika kontrak sudah terealisasi.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Batal</button>
                <button type="submit" class="btn-primary" style="padding:11px 24px;">
                    Simpan & Daftarkan Paket →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const allPackages = <?= json_encode(array_map(function($p) {
    return [
        'id'             => $p['id'],
        'rup_code'       => $p['rup_code'],
        'package_name'   => $p['package_name'],
        'opd'            => $p['opd'],
        'method'         => $p['method'],
        'budget'         => rupiah($p['budget']),
        'contract_value' => (float)$p['contract_value'] > 0 ? rupiah($p['contract_value']) : '-',
        'status'         => $p['status'],
        'status_class'   => statusClass($p['status']),
    ];
}, $latestPkgs), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

// Format nominal otomatis sebagai Rupiah dengan titik pemisah ribuan.
document.querySelectorAll('.rupiah-input').forEach(function(input) {
    function formatRupiah() {
        const digits = input.value.replace(/[^0-9]/g, '');
        input.value = digits ? 'Rp ' + digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
    }
    input.addEventListener('input', formatRupiah);
    if (input.value) formatRupiah();
});

let currentStatusFilter = 'all';

function filterByStatus(status, btnElement) {
    currentStatusFilter = status;
    var tabs = document.querySelectorAll('#filter-tabs .tab-btn');
    tabs.forEach(function(b) { b.classList.remove('active'); });
    if (btnElement) btnElement.classList.add('active');

    applyFilters();
}

function handleSearch(query) {
    var raw = query || document.getElementById('quick-search').value;
    var q = raw.toLowerCase().trim();
    var clearBtn = document.getElementById('search-clear-btn');
    var dropdown = document.getElementById('search-dropdown');
    var list = document.getElementById('search-results-list');
    var countText = document.getElementById('search-count-text');

    if (q.length > 0) {
        clearBtn.style.display = 'block';
    } else {
        clearBtn.style.display = 'none';
    }

    if (q.length === 0) {
        dropdown.style.display = 'none';
        applyFilters();
        return;
    }

    var matches = allPackages.filter(function(pkg) {
        var str = (pkg.rup_code + ' ' + pkg.package_name + ' ' + pkg.opd + ' ' + pkg.method + ' ' + pkg.status).toLowerCase();
        return str.indexOf(q) !== -1;
    });

    list.innerHTML = '';
    if (matches.length === 0) {
        countText.textContent = 'Tidak ada hasil';
        list.innerHTML = '<div class="dropdown-empty">Tidak ada paket yang cocok dengan "<strong>' + escapeHtml(raw) + '</strong>"</div>';
    } else {
        countText.textContent = 'Ditemukan ' + matches.length + ' paket';
        matches.slice(0, 5).forEach(function(pkg) {
            var item = document.createElement('div');
            item.className = 'dropdown-item';
            item.onclick = function() {
                selectPackageFromSearch(pkg.rup_code);
            };
            item.innerHTML =
                '<div class="dropdown-item-header">' +
                    '<span class="dropdown-rup">' + escapeHtml(pkg.rup_code) + '</span>' +
                    '<span class="badge ' + escapeHtml(pkg.status_class) + '">' + escapeHtml(pkg.status) + '</span>' +
                '</div>' +
                '<div class="dropdown-pkg-name">' + highlightQuery(pkg.package_name, q) + '</div>' +
                '<div class="dropdown-meta">' +
                    '<span>' + highlightQuery(pkg.opd, q) + '</span>' +
                    '<span class="meta-dot">·</span>' +
                    '<strong>' + escapeHtml(pkg.budget) + '</strong>' +
                '</div>';
            list.appendChild(item);
        });

        if (matches.length > 5) {
            var more = document.createElement('div');
            more.className = 'dropdown-more-note';
            more.textContent = '+ ' + (matches.length - 5) + ' paket lainnya (lihat pada tabel di bawah)';
            list.appendChild(more);
        }
    }

    dropdown.style.display = 'block';
    applyFilters();
}

function applyFilters() {
    var query = (document.getElementById('quick-search').value || '').toLowerCase().trim();
    var rows = document.querySelectorAll('#packages-tbody tr.pkg-row');
    var visibleCount = 0;

    rows.forEach(function(row) {
        var rowStatus = row.getAttribute('data-status');
        var rowSearch = row.getAttribute('data-search') || '';

        var matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
        var matchQuery = (query === '' || rowSearch.indexOf(query) !== -1);

        if (matchStatus && matchQuery) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    var counterEl = document.getElementById('visible-count');
    if (counterEl) counterEl.textContent = visibleCount;

    var noResultRow = document.getElementById('no-search-results');
    if (noResultRow) {
        if (visibleCount === 0 && rows.length > 0) {
            noResultRow.style.display = '';
            var msg = document.getElementById('no-search-message');
            if (msg) {
                msg.textContent = 'Tidak ditemukan paket yang sesuai dengan kata kunci "' + query + '"' +
                                  (currentStatusFilter !== 'all' ? ' pada status ' + currentStatusFilter : '') + '.';
            }
        } else {
            noResultRow.style.display = 'none';
        }
    }
}

function selectPackageFromSearch(rupCode) {
    document.getElementById('search-dropdown').style.display = 'none';
    filterByStatus('all', document.querySelector('#filter-tabs .tab-btn:first-child'));

    var targetRow = document.getElementById('row-' + rupCode);
    if (targetRow) {
        targetRow.style.display = '';
        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        targetRow.classList.add('row-pulse-highlight');
        setTimeout(function() {
            targetRow.classList.remove('row-pulse-highlight');
        }, 3000);
    }
}

function handleSearchKey(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        document.getElementById('search-dropdown').style.display = 'none';
        var tableSection = document.getElementById('paket-terbaru');
        if (tableSection) {
            tableSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else if (event.key === 'Escape') {
        document.getElementById('search-dropdown').style.display = 'none';
    }
}

function clearSearch() {
    var input = document.getElementById('quick-search');
    input.value = '';
    document.getElementById('search-clear-btn').style.display = 'none';
    document.getElementById('search-dropdown').style.display = 'none';
    applyFilters();
    input.focus();
}

document.addEventListener('click', function(e) {
    var searchWrapper = document.getElementById('search-wrapper');
    if (searchWrapper && !searchWrapper.contains(e.target)) {
        var dropdown = document.getElementById('search-dropdown');
        if (dropdown) dropdown.style.display = 'none';
    }
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function highlightQuery(text, query) {
    if (!query || !text) return escapeHtml(text);
    var cleanText = escapeHtml(text);
    var cleanQuery = escapeHtml(query);
    var reg = new RegExp('(' + cleanQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return cleanText.replace(reg, '<mark class="search-highlight">$1</mark>');
}

function openAddModal() {
    document.getElementById('modal-add-package').style.display = 'flex';
    document.getElementById('modal-name').focus();
}

function closeAddModal() {
    document.getElementById('modal-add-package').style.display = 'none';
}

function validatePackageForm() {
    var name = document.getElementById('modal-name').value.trim();
    // Input ditampilkan sebagai "Rp 9.000.000", jadi validasi harus
    // mengambil digitnya saja, bukan memakai parseFloat pada string Rupiah.
    var budgetRaw = document.getElementById('modal-budget').value;
    var budgetDigits = budgetRaw.replace(/[^0-9]/g, '');
    var budget = budgetDigits === '' ? 0 : Number(budgetDigits);

    if (!name) {
        alert('Nama paket pengadaan wajib diisi.');
        return false;
    }
    if (!Number.isFinite(budget) || budget <= 0) {
        alert('Pagu anggaran harus berupa nominal angka valid lebih besar dari 0.');
        return false;
    }
    return true;
}
</script>

</body>
</html>
