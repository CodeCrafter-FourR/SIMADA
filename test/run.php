<?php
ob_start();
require_once __DIR__ . '/bootstrap.php';

$passed = 0;
$failed = 0;
$results = [];
$createdUserIds = [];
$createdPackageIds = [];
$createdApprovalIds = [];
$pdo = test_db();

function check_test(string $name, callable $callback): void
{
    global $passed, $failed, $results;

    try {
        $detail = $callback();
        $passed++;
        $results[] = ['PASS', $name, is_string($detail) ? $detail : ''];
        echo "[PASS] {$name}" . (is_string($detail) && $detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    } catch (Throwable $e) {
        $failed++;
        $results[] = ['FAIL', $name, $e->getMessage()];
        echo "[FAIL] {$name} — {$e->getMessage()}" . PHP_EOL;
    }
}

function expect_true($condition, string $message = 'Expected true'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function expect_false($condition, string $message = 'Expected false'): void
{
    if ($condition) {
        throw new RuntimeException($message);
    }
}

function expect_contains(string $needle, $haystack, string $message = ''): void
{
    if (!is_string($haystack) || stripos($haystack, $needle) === false) {
        throw new RuntimeException($message ?: "Expected response to contain: {$needle}");
    }
}

function unique_tag(): string
{
    return 'T' . date('ymdHis') . bin2hex(random_bytes(2));
}

$tag = unique_tag();
$fixturePassword = 'SimadaTest#2026';
$fixtureUsername = strtolower($tag) . 'ppk';
$fixtureEmail = strtolower($tag) . '@simada.test';
$testUserId = null;
$approverId = null;

$opd = $pdo->query('SELECT name FROM opd ORDER BY id LIMIT 1')->fetchColumn();
$method = $pdo->query('SELECT name FROM procurement_method ORDER BY id LIMIT 1')->fetchColumn();

if (!$opd || !$method) {
    fwrite(STDERR, "Database test belum lengkap: tabel opd/procurement_method kosong. Import sql/simada.sql ke simada_test.\n");
    exit(2);
}

try {
    $stmt = $pdo->prepare('INSERT INTO `user` (role_id,name,username,email,password_hash) VALUES (3,?,?,?,?)');
    $stmt->execute([
        'SIMADA TEST PPK',
        $fixtureUsername,
        $fixtureEmail,
        password_hash($fixturePassword, PASSWORD_BCRYPT)
    ]);

    $testUserId = (int)$pdo->lastInsertId();
    $createdUserIds[] = $testUserId;

    $approverUsername = strtolower($tag) . 'pa';
    $approverEmail = strtolower($tag) . 'pa@simada.test';

    $stmt = $pdo->prepare('INSERT INTO `user` (role_id,name,username,email,password_hash) VALUES (2,?,?,?,?)');
    $stmt->execute([
        'SIMADA TEST PA-KPA',
        $approverUsername,
        $approverEmail,
        password_hash($fixturePassword, PASSWORD_BCRYPT)
    ]);

    $approverId = (int)$pdo->lastInsertId();
    $createdUserIds[] = $approverId;

    check_test('AUTH-01 Login menggunakan username dan password benar', function () use ($fixtureUsername, $fixturePassword, $testUserId) {
        expect_true(Auth::login($fixtureUsername, $fixturePassword), 'Login username valid gagal');
        expect_true(Auth::check(), 'Session tidak terbentuk');
        expect_true(Auth::id() === $testUserId, 'Session user ID tidak sesuai');
        expect_true(Auth::role() === 'PPK', 'Role session bukan PPK');
        expect_true(!isset($_SESSION['user']['password_hash']), 'Password hash terekspos pada session');
        return 'username, session, role, dan sanitasi session OK';
    });

    check_test('AUTH-02 Login menggunakan email dan password benar', function () use ($fixtureEmail, $fixturePassword) {
        expect_true(Auth::login($fixtureEmail, $fixturePassword), 'Login email valid gagal');
        expect_true(Auth::check(), 'Session tidak terbentuk');
        return 'login dengan email berhasil';
    });

    check_test('AUTH-03 Login dengan password salah ditolak', function () use ($fixtureUsername) {
        Auth::logout();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        expect_false(Auth::login($fixtureUsername, 'password-salah'), 'Password salah diterima');
        expect_false(Auth::check(), 'Session terbentuk setelah login gagal');
        return 'kredensial salah ditolak';
    });

    check_test('AUTH-04 Login dengan username tidak terdaftar ditolak', function () {
        Auth::logout();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        expect_false(Auth::login('username-tidak-ada-987654', 'apa-saja'), 'Username tidak dikenal diterima');
        return 'username tidak dikenal ditolak';
    });

    check_test('AUTH-05 Logout menghapus session pengguna', function () use ($fixtureUsername, $fixturePassword) {
        Auth::login($fixtureUsername, $fixturePassword);
        Auth::logout();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        expect_false(Auth::check(), 'Session masih aktif setelah logout');
        expect_true(Auth::user() === null, 'Auth::user masih mengembalikan data');
        return 'session kosong setelah logout';
    });

    check_test('USER-01 Membuat pengguna dengan data valid', function () use ($tag) {
        $u = strtolower($tag) . 'new';
        $e = strtolower($tag) . 'new@simada.test';

        $result = Auth::createUser('User Test', $u, $e, 'Pass1234', 'PPK');

        expect_true($result === true, is_string($result) ? $result : 'createUser gagal');

        $row = test_db()->prepare('SELECT id,role_id,password_hash FROM `user` WHERE username=?');
        $row->execute([$u]);
        $data = $row->fetch(PDO::FETCH_ASSOC);

        expect_true((int)$data['role_id'] === 3, 'Role ID tidak sesuai');
        expect_true(password_verify('Pass1234', $data['password_hash']), 'Password tidak tersimpan sebagai hash yang valid');
        expect_true($data['password_hash'] !== 'Pass1234', 'Password tersimpan plaintext');

        $GLOBALS['createdUserIds'][] = (int)$data['id'];

        return 'akun tersimpan dan password bcrypt terverifikasi';
    });

    check_test('USER-02 Kolom wajib kosong ditolak', function () {
        $r = Auth::createUser('', 'validuser', 'valid@simada.test', 'Pass1234', 'PPK');
        expect_contains('wajib', $r);
        return 'validasi kolom wajib aktif';
    });

    check_test('USER-03 Format username tidak valid ditolak', function () {
        $r = Auth::createUser('Nama', 'ab', 'ab@simada.test', 'Pass1234', 'PPK');
        expect_contains('Username', $r);
        return 'username kurang dari 3 karakter ditolak';
    });

    check_test('USER-04 Format email tidak valid ditolak', function () {
        $r = Auth::createUser('Nama', 'emailtest', 'bukan-email', 'Pass1234', 'PPK');
        expect_contains('email', $r);
        return 'email tidak valid ditolak';
    });

    check_test('USER-05 Password kurang dari 4 karakter ditolak', function () {
        $r = Auth::createUser('Nama', 'weakpass', 'weak@simada.test', '123', 'PPK');
        expect_contains('Password', $r);
        return 'password pendek ditolak';
    });

    check_test('USER-06 Role tidak dikenal ditolak', function () {
        $r = Auth::createUser('Nama', 'badrole', 'badrole@simada.test', 'Pass1234', 'SuperAdmin');
        expect_contains('Role', $r);
        return 'role di luar daftar ditolak';
    });

    check_test('USER-07 Username/email duplikat ditolak', function () use ($fixtureUsername) {
        $r = Auth::createUser('Duplikat', $fixtureUsername, 'another@simada.test', 'Pass1234', 'PPK');
        expect_contains('sudah terdaftar', $r);
        return 'duplikasi username ditolak';
    });

    $validPackage = function (string $suffix, string $status = 'Menunggu Persetujuan') use ($tag, $opd, $method) {
        return [
            'rup_code' => $tag . $suffix,
            'package_name' => 'SIMADA TEST ' . $suffix,
            'opd' => $opd,
            'method' => $method,
            'budget' => 'Rp 9.000.000',
            'contract_value' => 'Rp 8.500.000',
            'status' => $status,
        ];
    };

    check_test('PKG-01 Membuat paket dengan data valid', function () use ($validPackage, $testUserId, &$createdPackageIds) {
        $data = $validPackage('CREATE');
        $r = Dashboard::createPackage($data, $testUserId);

        expect_true($r === true, is_string($r) ? $r : 'createPackage gagal');

        $q = test_db()->prepare('SELECT id,budget,contract_value,status FROM procurement_package WHERE rup_code=?');
        $q->execute([$data['rup_code']]);
        $p = $q->fetch(PDO::FETCH_ASSOC);

        expect_true((bool)$p, 'Paket tidak ditemukan setelah disimpan');
        expect_true((float)$p['budget'] === 9000000.0, 'Nilai pagu tidak sesuai');
        expect_true((float)$p['contract_value'] === 8500000.0, 'Nilai kontrak tidak sesuai');

        $createdPackageIds[] = (int)$p['id'];

        return 'paket tersimpan, nilai Rupiah dikonversi benar';
    });

    check_test('PKG-02 Nama paket kosong ditolak', function () use ($validPackage, $testUserId) {
        $d = $validPackage('NONAME');
        $d['package_name'] = '';
        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('lengkapi', $r);
        return 'nama kosong ditolak';
    });

    check_test('PKG-03 Pagu nol ditolak', function () use ($validPackage, $testUserId) {
        $d = $validPackage('ZERO');
        $d['budget'] = 'Rp 0';
        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('Pagu', $r);
        return 'pagu nol ditolak';
    });

    check_test('PKG-04 OPD tidak terdaftar ditolak', function () use ($validPackage, $testUserId) {
        $d = $validPackage('BADOPD');
        $d['opd'] = 'OPD TIDAK ADA';
        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('OPD tidak ditemukan', $r);
        return 'referensi OPD divalidasi';
    });

    check_test('PKG-05 Metode tidak terdaftar ditolak', function () use ($validPackage, $testUserId) {
        $d = $validPackage('BADMETHOD');
        $d['method'] = 'Metode Tidak Ada';
        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('Metode pengadaan tidak ditemukan', $r);
        return 'referensi metode divalidasi';
    });

    check_test('PKG-06 Kode RUP duplikat ditolak', function () use ($validPackage, $testUserId, &$createdPackageIds) {
        $d = $validPackage('DUP');
        expect_true(Dashboard::createPackage($d, $testUserId) === true, 'Pembuatan paket pertama gagal');

        $q = test_db()->prepare('SELECT id FROM procurement_package WHERE rup_code=?');
        $q->execute([$d['rup_code']]);
        $id = (int)$q->fetchColumn();
        $createdPackageIds[] = $id;

        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('sudah digunakan', $r);

        return 'RUP duplikat ditolak';
    });

    check_test('PKG-07 Nilai melebihi DECIMAL(18,2) ditolak', function () use ($validPackage, $testUserId) {
        $d = $validPackage('OVERFLOW');
        $d['budget'] = '1234567890123456789';
        $r = Dashboard::createPackage($d, $testUserId);
        expect_contains('melebihi batas', $r);
        return 'batas nominal divalidasi';
    });

    check_test('PKG-08 Status tidak dikenal dinormalisasi', function () use ($validPackage, $testUserId, &$createdPackageIds) {
        $d = $validPackage('BADSTATUS', 'STATUS_ASAL');
        expect_true(Dashboard::createPackage($d, $testUserId) === true, 'Paket tidak tersimpan');

        $q = test_db()->prepare('SELECT id,status FROM procurement_package WHERE rup_code=?');
        $q->execute([$d['rup_code']]);
        $p = $q->fetch(PDO::FETCH_ASSOC);

        $createdPackageIds[] = (int)$p['id'];
        expect_true($p['status'] === 'Menunggu Persetujuan', 'Status fallback tidak sesuai');

        return 'status tidak dikenal menjadi Menunggu Persetujuan';
    });

    $makePending = function (string $suffix) use ($validPackage, $testUserId, &$createdPackageIds) {
        $d = $validPackage($suffix);
        $r = Dashboard::createPackage($d, $testUserId);

        if ($r !== true) {
            throw new RuntimeException(is_string($r) ? $r : 'Gagal membuat fixture paket');
        }

        $q = test_db()->prepare('SELECT id FROM procurement_package WHERE rup_code=?');
        $q->execute([$d['rup_code']]);
        $id = (int)$q->fetchColumn();

        $createdPackageIds[] = $id;

        return $id;
    };

    check_test('APR-01 Menyetujui paket pending mengubah status menjadi Proses', function () use ($makePending, $approverId) {
        $id = $makePending('APPROVE');
        $r = Dashboard::processApproval($id, 'Disetujui', $approverId);

        expect_true($r === true, is_string($r) ? $r : 'Approval gagal');

        $q = test_db()->prepare('SELECT status FROM procurement_package WHERE id=?');
        $q->execute([$id]);
        expect_true($q->fetchColumn() === 'Proses', 'Status tidak berubah ke Proses');

        $q = test_db()->prepare('SELECT COUNT(*) FROM approvals WHERE package_id=? AND decision=?');
        $q->execute([$id, 'Disetujui']);
        expect_true((int)$q->fetchColumn() === 1, 'Riwayat approval tidak tercatat');

        return 'status Proses dan riwayat approvals tersimpan';
    });

    check_test('APR-02 Menolak paket pending mengubah status menjadi Ditolak', function () use ($makePending, $approverId) {
        $id = $makePending('REJECT');
        $r = Dashboard::processApproval($id, 'Ditolak', $approverId);

        expect_true($r === true, is_string($r) ? $r : 'Reject gagal');

        $q = test_db()->prepare('SELECT status FROM procurement_package WHERE id=?');
        $q->execute([$id]);
        expect_true($q->fetchColumn() === 'Ditolak', 'Status tidak berubah ke Ditolak');

        return 'status Ditolak tercatat';
    });

    check_test('APR-03 Keputusan approval tidak valid ditolak', function () use ($makePending, $approverId) {
        $id = $makePending('INVALIDDEC');
        $r = Dashboard::processApproval($id, 'DISETUJUI', $approverId);
        expect_contains('tidak valid', $r);
        return 'decision tidak dikenal ditolak';
    });

    check_test('APR-04 ID paket tidak ditemukan ditolak', function () use ($approverId) {
        $r = Dashboard::processApproval(999999999, 'Disetujui', $approverId);
        expect_contains('tidak ditemukan', $r);
        return 'ID paket tidak valid ditolak';
    });

    check_test('APR-05 Paket di luar status antrean persetujuan ditolak', function () use ($makePending, $approverId) {
        $id = $makePending('WRONGSTATE');
        $q = test_db()->prepare("UPDATE procurement_package SET status='Draft' WHERE id=?");
        $q->execute([$id]);

        $r = Dashboard::processApproval($id, 'Disetujui', $approverId);
        expect_contains('tidak berada', $r);

        return 'status selain pending tidak bisa diproses';
    });

    check_test('DASH-01 PPK hanya melihat paket dengan user_id sendiri', function () use ($testUserId) {
        $_SESSION['user'] = ['id' => $testUserId, 'role' => 'PPK', 'name' => 'SIMADA TEST PPK'];
        $d = new Dashboard();

        foreach ($d->getPackages() as $p) {
            expect_true((int)$p['user_id'] === $testUserId, 'Paket user lain muncul pada dashboard PPK');
        }

        return 'filter user_id PPK sesuai';
    });

    check_test('DASH-02 Admin melihat seluruh paket', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $d = new Dashboard();

        $expected = (int)test_db()->query('SELECT COUNT(*) FROM procurement_package')->fetchColumn();
        expect_true(count($d->getPackages()) === $expected, 'Admin tidak menerima seluruh paket');

        return 'jumlah paket sesuai data tabel/view';
    });

    check_test('DASH-03 Statistik dashboard konsisten dengan paket', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $d = new Dashboard();
        $s = $d->getStats();

        expect_true($s['totalPackages'] === count($d->getPackages()), 'totalPackages tidak konsisten');
        expect_true(abs($s['totalBudget'] - array_sum(array_map(fn($p) => (float)$p['budget'], $d->getPackages()))) < 0.01, 'totalBudget tidak konsisten');

        return 'total paket dan total pagu dihitung dari data yang dibaca';
    });

    check_test('DASH-04 Komposisi metode pengadaan mengembalikan tiga kategori', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $m = (new Dashboard())->getMethodComposition();

        expect_true(isset($m['e-Tendering'], $m['Pengadaan Langsung'], $m['e-Purchasing']), 'Kategori metode tidak lengkap');

        return 'e-Tendering, Pengadaan Langsung, e-Purchasing tersedia';
    });

    check_test('DASH-05 Rincian OPD menghasilkan daftar terstruktur', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $rows = (new Dashboard())->getOpdBreakdown();

        expect_true(is_array($rows), 'Hasil rincian OPD bukan array');

        foreach ($rows as $r) {
            expect_true(isset($r['opd'], $r['totalPackages'], $r['totalBudget']), 'Struktur rincian OPD tidak lengkap');
        }

        return 'struktur breakdown OPD valid';
    });

    check_test('DASH-06 Daftar paket terbaru menghormati batas limit', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $d = new Dashboard();

        expect_true(count($d->getLatestPackages(3)) <= 3, 'Limit daftar terbaru tidak diterapkan');

        return 'limit 3 diterapkan';
    });

    check_test('DASH-07 Kode RUP berikutnya memiliki format RUP-26', function () {
        $code = Dashboard::getNextRupCode();
        expect_true((bool)preg_match('/^RUP-26\d+$/', $code), 'Format kode RUP tidak sesuai');

        return $code;
    });

    check_test('DASH-08 Presentasi role PPK sesuai role session', function () {
        $_SESSION['user'] = ['id' => 3, 'role' => 'PPK', 'name' => 'PPK Test'];
        $p = (new Dashboard())->getRolePresentation();

        expect_true(isset($p['title'], $p['subtitle'], $p['tagline']), 'Data presentasi role tidak lengkap');
        expect_contains('PPK', $p['title']);

        return 'role presentation PPK tersedia';
    });

    check_test('DASH-09 Data riwayat persetujuan dapat dibaca', function () {
        $_SESSION['user'] = ['id' => 1, 'role' => 'Admin', 'name' => 'Administrator'];
        $rows = (new Dashboard())->getApprovals(5);

        expect_true(is_array($rows), 'getApprovals tidak mengembalikan array');

        return 'query riwayat approvals dapat dijalankan';
    });

    check_test('DASH-10 Antrean persetujuan dapat dibaca', function () {
        $_SESSION['user'] = ['id' => 2, 'role' => 'PA-KPA', 'name' => 'PA Test'];
        $rows = (new Dashboard())->getPendingApprovals();

        expect_true(is_array($rows), 'getPendingApprovals tidak mengembalikan array');

        foreach ($rows as $r) {
            expect_true(($r['status'] ?? '') === 'Menunggu Persetujuan', 'Antrean memuat status yang bukan pending');
        }

        return 'query antrean pending dapat dijalankan';
    });

    check_test('HELPER-01 Escape HTML mencegah tag HTML aktif', function () {
        $out = e('<script>alert(1)</script>');
        expect_true(strpos($out, '&lt;script&gt;') !== false, 'HTML tidak di-escape');

        return 'helper e() melakukan escaping';
    });

    check_test('HELPER-02 Format Rupiah menghasilkan format Indonesia', function () {
        expect_true(rupiah(1250000) === 'Rp 1.250.000', 'Format rupiah tidak sesuai');

        return rupiah(1250000);
    });

    check_test('HELPER-03 Pemetaan class status berjalan', function () {
        expect_true(statusClass('Selesai') === 'success', 'Status selesai salah');
        expect_true(statusClass('Ditolak') === 'danger', 'Status ditolak salah');

        return 'success/danger sesuai status';
    });

} finally {
    if ($createdPackageIds) {
        $ids = array_values(array_unique(array_map('intval', $createdPackageIds)));
        $marks = implode(',', array_fill(0, count($ids), '?'));

        $q = $pdo->prepare("DELETE FROM approvals WHERE package_id IN ({$marks})");
        $q->execute($ids);

        $q = $pdo->prepare("DELETE FROM procurement_package WHERE id IN ({$marks})");
        $q->execute($ids);
    }

    if ($createdUserIds) {
        $ids = array_values(array_unique(array_map('intval', $createdUserIds)));
        $marks = implode(',', array_fill(0, count($ids), '?'));

        $q = $pdo->prepare("DELETE FROM approvals WHERE approver_id IN ({$marks})");
        $q->execute($ids);

        $q = $pdo->prepare("DELETE FROM `user` WHERE id IN ({$marks})");
        $q->execute($ids);
    }

    $_SESSION = [];
}

echo PHP_EOL . "================ HASIL PENGUJIAN ================" . PHP_EOL;
echo "Database : " . DB_NAME . PHP_EOL;
echo "PASS     : {$passed}" . PHP_EOL;
echo "FAIL     : {$failed}" . PHP_EOL;
echo "TOTAL    : " . ($passed + $failed) . PHP_EOL;
echo "==================================================" . PHP_EOL;

ob_end_flush();

exit($failed > 0 ? 1 : 0);