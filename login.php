<?php
require_once __DIR__ . '/config.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (Auth::login($identifier, $password)) {
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Username/email atau kata sandi tidak sesuai. Pastikan menggunakan akun yang terdaftar.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login — SIMADA Kabupaten Kebumen</title>

    <!-- Font Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .password-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-field input {
            width: 100%;
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            border: 0;
            background: transparent;
            cursor: pointer;
            font-size: 18px;
            padding: 6px;
            color: #294252;
        }
    </style>
</head>

<body class="login-split-page">

<div class="login-wrapper">

    <div class="login-brand-panel">

        <div class="login-brand-top">

            <img src="assets/img/logo-simada.png"
                 alt="SIMADA Kabupaten Kebumen"
                 class="login-brand-logo"
                 onerror="this.style.display='none'; document.getElementById('login-brand-fallback').style.display='block';">

            <div id="login-brand-fallback"
                 style="display:none;"
                 class="login-fallback-text">

                <span class="fallback-stripes">||</span>
                <strong>SIMADA</strong> KABUPATEN KEBUMEN

            </div>

            <span class="system-badge">
                SISTEM PENDUKUNG SPSE
            </span>

        </div>

        <div class="login-brand-center">

            <h1 class="brand-hero-title">
                Pengadaan lebih terarah dan transparan.
            </h1>

            <p class="brand-hero-desc">
                Kelola paket pengadaan, pantau progres, hasilkan dokumen,
                dan analisis kinerja dari satu ruang kerja terintegrasi
                Pemerintah Kabupaten Kebumen.
            </p>

            <div class="brand-feature-list">

                <div class="feature-item">
                    <span class="feature-num">01</span>
                    <span>Monitoring paket pengadaan secara menyeluruh</span>
                </div>

                <div class="feature-item">
                    <span class="feature-num">02</span>
                    <span>Pelaporan pagu dan realisasi nilai kontrak</span>
                </div>

                <div class="feature-item">
                    <span class="feature-num">03</span>
                    <span>Otorisasi dokumen dan kesiapan berkas pengadaan</span>
                </div>

            </div>

        </div>

        <div class="login-brand-bottom">
            <small>
                © 2026 Bagian Pengadaan Barang/Jasa
                Sekretariat Daerah Kabupaten Kebumen
            </small>
        </div>

    </div>


    <!-- KOLOM KANAN: FORM LOGIN -->
    <div class="login-form-panel">

        <div class="form-container">

            <div class="form-header">

                <span class="portal-badge">
                    PORTAL INTERNAL · DATA SIMULASI
                </span>

                <h2 class="form-title">
                    Selamat datang kembali
                </h2>

                <p class="form-sub">
                    Masuk menggunakan akun yang terdaftar
                    untuk mengakses dashboard pengadaan.
                </p>

            </div>


            <!-- PESAN ERROR -->
            <?php if ($error): ?>

                <div class="alert alert-danger" role="alert">

                    <span style="
                        display:inline-block;
                        width:18px;
                        height:18px;
                        border-radius:50%;
                        background:var(--danger);
                        color:#fff;
                        text-align:center;
                        line-height:18px;
                        font-weight:800;
                        font-size:11px;
                    ">!</span>

                    <span><?= e($error) ?></span>

                </div>

            <?php endif; ?>


            <!-- FORM LOGIN -->
            <form method="post"
                  action="login.php"
                  class="auth-form"
                  autocomplete="off">

                <!-- USERNAME / EMAIL -->
                <div class="form-group">

                    <label for="identifier">
                        Username atau Gmail
                    </label>

                    <input
                        id="identifier"
                        type="text"
                        name="identifier"
                        required
                        autocomplete="off"
                        placeholder="Masukkan username atau email"
                        value=""
                    >

                </div>


                <!-- PASSWORD -->
                <div class="form-group">

                    <div class="label-row">

                        <label for="password">
                            Kata Sandi
                        </label>

                    </div>

                    <div class="password-field">

                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Masukkan kata sandi"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            onclick="togglePassword('password', this)"
                            aria-label="Tampilkan password"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <!-- TOMBOL LOGIN -->
                <button class="btn-primary btn-block" type="submit">

                    Masuk ke Dashboard Pengadaan →

                </button>

            </form>

        </div>

    </div>

</div>


<!-- JAVASCRIPT -->
<script>

function togglePassword(id, button) {

    const field = document.getElementById(id);

    field.type = field.type === 'password'
        ? 'text'
        : 'password';

    button.setAttribute(
        'aria-label',
        field.type === 'password'
            ? 'Tampilkan password'
            : 'Sembunyikan password'
    );

}

</script>

</body>
</html>