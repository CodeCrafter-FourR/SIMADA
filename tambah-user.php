
<?php
require_once __DIR__ . '/config.php';

Auth::requireLogin();

// Fitur pengelolaan pengguna hanya dapat diakses oleh Administrator.
if (Auth::role() !== 'Admin') {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Akses Ditolak — SIMADA</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0; min-height: 100vh; display: grid; place-items: center;
                padding: 24px; background: #f3f7f9; color: #123047;
                font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            }
            .access-card {
                width: min(100%, 480px); padding: 36px; text-align: center;
                background: #fff; border: 1px solid #dce7ed; border-radius: 16px;
                box-shadow: 0 8px 28px rgba(16, 50, 72, .08);
            }
            .access-icon {
                width: 54px; height: 54px; display: grid; place-items: center;
                margin: 0 auto 18px; border-radius: 50%;
                background: #fff0f0; color: #b42318; font-size: 26px; font-weight: 800;
            }
            h1 { margin: 0 0 10px; font-size: 22px; }
            p { margin: 0 0 24px; color: #62788a; font-size: 14px; line-height: 1.7; }
            a {
                display: inline-block; padding: 11px 20px; border-radius: 8px;
                background: #108b78; color: #fff; text-decoration: none;
                font-size: 13px; font-weight: 700;
            }
        </style>
    </head>
    <body>
        <main class="access-card">
            <div class="access-icon">!</div>
            <h1>Akses Ditolak</h1>
            <p>Fitur Pengguna &amp; Role hanya dapat diakses oleh Admin SIMADA. Silakan kembali ke dashboard sesuai hak akses akun Anda.</p>
            <a href="dashboard.php">Kembali ke Dashboard</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

$success = '';
$error = '';

$roles = [
    1 => 'Admin',
    2 => 'PA-KPA',
    3 => 'PPK'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = (int)($_POST['role_id'] ?? 0);

    if ($name === '' || $username === '' || $email === '' || $password === '' || $role_id === 0) {
        $error = 'Seluruh kolom wajib diisi.';
    } elseif (!isset($roles[$role_id])) {
        $error = 'Role pengguna tidak valid.';
    } elseif (strlen($password) < 4) {
        $error = 'Password minimal 4 karakter.';
    } else {
        $result = Auth::createUser(
            $name,
            $username,
            $email,
            $password,
            $roles[$role_id]
        );

        if ($result === true) {
            $success = "Pengguna {$name} berhasil ditambahkan.";
            $_POST = [];
        } else {
            $error = $result;
        }
    }
}

$db = Database::getInstance();

$users = $db->query(
    'SELECT id, role_id, name, username, email, password_hash
     FROM `user`
     ORDER BY id DESC'
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Pengguna — SIMADA Kabupaten Kebumen</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .user-mgmt-container {
            max-width: 1100px;
            margin: 36px auto;
            padding: 0 20px;
        }

        .mgmt-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border-line);
            box-shadow: 0 4px 16px rgba(16,50,72,.05);
            padding: 28px;
            margin-bottom: 28px;
        }

        .mgmt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-line);
        }

        .mgmt-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--ink-title);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .btn-back {
            padding: 8px 16px;
            border-radius: 8px;
            background: var(--bg-page);
            border: 1px solid var(--border-line);
            color: var(--navy-main);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .alert-success,
        .alert-error {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .alert-success {
            background: var(--success-bg);
            border: 1px solid #bce6dc;
            color: var(--success);
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #f5c2c2;
            color: #b42318;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .packages-table {
            width: 100%;
            border-collapse: collapse;
        }

        .packages-table th,
        .packages-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-line);
            font-size: 12px;
            white-space: nowrap;
        }

        .packages-table th {
            background: #f4f8fa;
            color: var(--navy-main);
            font-weight: 700;
        }

        .hash-badge {
            font-size: 10px;
            font-family: monospace;
            padding: 4px 6px;
            border-radius: 4px;
            background: #eef3f6;
            color: var(--navy-main);
        }

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
        }

        @media(max-width:700px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }

            .mgmt-card {
                padding: 18px;
            }

            .mgmt-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body style="background:var(--bg-page);">

<div class="user-mgmt-container">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <img src="assets/img/logo-simada.png" alt="Logo" style="height:36px;border-radius:4px;">
            <span style="font-weight:800;color:var(--navy-main);font-size:16px;">SIMADA · KELOLA PENGGUNA</span>
        </div>

        <div>
            <a href="login.php" class="btn-back">← Kembali</a>
            <a href="dashboard.php" class="btn-back" style="background:var(--teal-gradient);color:#fff;margin-left:8px;">Dashboard →</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert-success">
            ✓ <?= e($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error">
            ✕ <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="mgmt-card">

        <div class="mgmt-header">
            <div>
                <h2 class="mgmt-title">Tambah Pengguna Baru</h2>
                <p style="font-size:12px;color:var(--ink-muted);margin:0;">
                    Password akan otomatis dienkripsi menggunakan Bcrypt.
                </p>
            </div>
            <span class="scope-pill">Auto-Hash Bcrypt Aktif</span>
        </div>

        <form method="post" action="tambah-user.php">

            <div class="form-grid-2">

                <div class="form-group">
                    <label for="name">Nama Lengkap Pengguna</label>
                    <input type="text" id="name" name="name" required placeholder="Contoh: Administrator SIMADA" value="<?= e($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required pattern="[A-Za-z0-9._-]{3,30}" placeholder="Contoh: admin" value="<?= e($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" required placeholder="Contoh: admin@simada.test" value="<?= e($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi">
                        <button type="button" class="toggle-password" onclick="togglePassword('password',this)">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role_id">Hak Akses / Role</label>
                    <select id="role_id" name="role_id" required style="width:100%;padding:11px 14px;border:1px solid var(--border-line);border-radius:8px;background:#fbfdfd;">
                        <option value="">Pilih Role</option>
                        <option value="1" <?= ($_POST['role_id'] ?? '') == '1' ? 'selected' : '' ?>>Administrator</option>
                        <option value="2" <?= ($_POST['role_id'] ?? '') == '2' ? 'selected' : '' ?>>PA-KPA</option>
                        <option value="3" <?= ($_POST['role_id'] ?? '') == '3' ? 'selected' : '' ?>>PPK</option>
                    </select>
                </div>

            </div>

            <div style="margin-top:20px;text-align:right;">
                <button type="submit" class="btn-primary" style="padding:12px 28px;font-size:13px;">
                    Simpan & Daftarkan Pengguna →
                </button>
            </div>

        </form>
    </div>

    <div class="mgmt-card">

        <div class="mgmt-header">
            <div>
                <h2 class="mgmt-title">Daftar Akun Pengguna</h2>
                <p style="font-size:12px;color:var(--ink-muted);margin:0;">
                    Data pengguna dari database simada, tabel user.
                </p>
            </div>

            <span class="chip-number"><?= count($users) ?> Akun Terdaftar</span>
        </div>

        <div class="table-responsive">
            <table class="packages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status Password</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $isBcrypt = str_starts_with($u['password_hash'], '$2y$') || str_starts_with($u['password_hash'], '$2a$');
                        $roleName = $roles[(int)$u['role_id']] ?? 'Tidak diketahui';
                        ?>

                        <tr>
                            <td><?= e($u['id']) ?></td>
                            <td><strong><?= e($u['name']) ?></strong></td>
                            <td><code><?= e($u['username']) ?></code></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($roleName) ?></td>
                            <td>
                                <?php if ($isBcrypt): ?>
                                    <span class="badge success">✓ Bcrypt Hashed</span>
                                <?php else: ?>
                                    <span class="badge warning">Belum Bcrypt</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <?php if (count($users) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Belum ada pengguna.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<script>
function togglePassword(id, button) {
    const field = document.getElementById(id);
    field.type = field.type === 'password' ? 'text' : 'password';
    button.setAttribute(
        'aria-label',
        field.type === 'password' ? 'Tampilkan password' : 'Sembunyikan password'
    );
}
</script>

</body>
</html>
