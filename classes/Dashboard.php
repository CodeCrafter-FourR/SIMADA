<?php

class Dashboard
{
    private array $packages = [];
    private string $role = '';
    private int $userId = 0;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->role = Auth::role();
        $this->userId = Auth::id();

        if ($this->role === 'PPK') {
            $this->packages = $db->query(
                'SELECT * FROM packages WHERE user_id = ? ORDER BY created_at DESC, id DESC',
                [$this->userId]
            );
        } else {
            $this->packages = $db->query(
                'SELECT * FROM packages ORDER BY created_at DESC, id DESC'
            );
        }
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function getStats(): array
    {
        $packages = $this->packages;
        $total = count($packages);

        $totalBudget = array_sum(array_map(fn($p) => (float)$p['budget'], $packages));
        $totalContract = array_sum(array_map(fn($p) => (float)$p['contract_value'], $packages));

        $completed = count(array_filter($packages, fn($p) => $p['status'] === 'Selesai'));
        $inProgress = count(array_filter($packages, fn($p) => $p['status'] === 'Proses'));
        $pendingApproval = count(array_filter($packages, fn($p) => $p['status'] === 'Menunggu Persetujuan'));
        $approved = count(array_filter($packages, fn($p) => $p['status'] === 'Disetujui'));
        $draft = count(array_filter($packages, fn($p) => $p['status'] === 'Draft'));

        $contractedPackages = array_filter($packages, fn($p) => (float)$p['contract_value'] > 0);
        $contractedBudget = array_sum(array_map(fn($p) => (float)$p['budget'], $contractedPackages));

        $savings = max(0, $contractedBudget - $totalContract);
        $savingsPercent = $contractedBudget > 0 ? round(($savings / $contractedBudget) * 100, 1) : 0;

        return [
            'totalPackages' => $total,
            'totalBudget' => $totalBudget,
            'totalContract' => $totalContract,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'pendingApproval' => $pendingApproval,
            'approved' => $approved,
            'draft' => $draft,
            'savings' => $savings,
            'savingsPercent' => $savingsPercent,
            'completionRate' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }

    public function getOpdBreakdown(): array
    {
        $opdMap = [];

        foreach ($this->packages as $pkg) {
            $opd = $pkg['opd'] ?: 'Lainnya';

            if (!isset($opdMap[$opd])) {
                $opdMap[$opd] = [
                    'opd' => $opd,
                    'totalPackages' => 0,
                    'totalBudget' => 0.0,
                    'totalContract' => 0.0,
                    'completed' => 0,
                    'inProgress' => 0,
                ];
            }

            $opdMap[$opd]['totalPackages']++;
            $opdMap[$opd]['totalBudget'] += (float)$pkg['budget'];
            $opdMap[$opd]['totalContract'] += (float)$pkg['contract_value'];

            if ($pkg['status'] === 'Selesai') {
                $opdMap[$opd]['completed']++;
            } elseif ($pkg['status'] === 'Proses') {
                $opdMap[$opd]['inProgress']++;
            }
        }

        uasort($opdMap, fn($a, $b) => $b['totalBudget'] <=> $a['totalBudget']);

        return array_values($opdMap);
    }

    public function getMethodComposition(): array
    {
        $methods = [
            'e-Tendering' => [
                'count' => 0,
                'budget' => 0.0,
                'badge' => 'Tender Terbuka',
            ],
            'Pengadaan Langsung' => [
                'count' => 0,
                'budget' => 0.0,
                'badge' => 'Langsung < 200 Jt',
            ],
            'e-Purchasing' => [
                'count' => 0,
                'budget' => 0.0,
                'badge' => 'Katalog Elektronik',
            ],
        ];

        $totalCount = count($this->packages);

        foreach ($this->packages as $pkg) {
            $m = $pkg['method'];

            if (isset($methods[$m])) {
                $methods[$m]['count']++;
                $methods[$m]['budget'] += (float)$pkg['budget'];
            }
        }

        foreach ($methods as $key => &$data) {
            $data['percent'] = $totalCount > 0 ? round(($data['count'] / $totalCount) * 100) : 0;
        }

        return $methods;
    }

    public function getActionNeededPackages(int $limit = 5): array
    {
        if ($this->role === 'PA-KPA' || $this->role === 'Admin') {
            $actionableStatuses = ['Menunggu Persetujuan'];
        } else {
            $actionableStatuses = ['Draft', 'Ditolak', 'Disetujui', 'Proses'];
        }

        $actionPackages = array_filter(
            $this->packages,
            fn($package) => in_array($package['status'] ?? '', $actionableStatuses, true)
        );

        return array_slice(array_values($actionPackages), 0, max(0, $limit));
    }

    public function getApprovals(int $limit = 5): array
    {
        $db = Database::getInstance();

        try {
            $sql = "SELECT a.*, p.rup_code, p.package_name, p.budget, u.name as approver_name
                    FROM approvals a
                    JOIN packages p ON a.package_id = p.id
                    JOIN users u ON a.approver_id = u.id
                    ORDER BY a.decided_at DESC
                    LIMIT " . (int)$limit;

            return $db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    public function getPendingApprovals(): array
    {
        $db = Database::getInstance();

        try {
            $sql = "SELECT p.*, u.name as creator_name
                    FROM packages p
                    JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'Menunggu Persetujuan'
                    ORDER BY p.id DESC";

            return $db->query($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    public static function processApproval(int $packageId, string $decision, int $approverId): bool|string
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        if (!in_array($decision, ['Disetujui', 'Ditolak'], true)) {
            return 'Keputusan tidak valid.';
        }

        $pkg = $db->queryOne(
            'SELECT id, rup_code, status FROM procurement_package WHERE id = ? LIMIT 1',
            [$packageId]
        );

        if (!$pkg) {
            return 'Paket pengadaan tidak ditemukan.';
        }

        if (($pkg['status'] ?? '') !== 'Menunggu Persetujuan') {
            return 'Paket ini tidak berada dalam antrean persetujuan.';
        }

        $newStatus = ($decision === 'Disetujui') ? 'Proses' : 'Ditolak';

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'UPDATE procurement_package SET status = ? WHERE id = ?'
            );
            $stmt->execute([$newStatus, $packageId]);

            $stmt = $pdo->prepare(
                'INSERT INTO approvals (package_id, approver_id, decision, decided_at)
                 VALUES (?, ?, ?, NOW())'
            );
            $stmt->execute([$packageId, $approverId, $decision]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return 'Gagal memproses persetujuan: ' . $e->getMessage();
        }
    }

    public static function createPackage(array $data, int $userId): bool|string
    {
        $db = Database::getInstance();

        $rupCode = trim((string)($data['rup_code'] ?? ''));
        $packageName = trim((string)($data['package_name'] ?? ''));
        $opdName = trim((string)($data['opd'] ?? ''));
        $methodName = trim((string)($data['method'] ?? ''));
        // Simpan sebagai string digit agar nilai DECIMAL(18,2) tidak kehilangan presisi.
        $budget = preg_replace('/\D+/', '', (string)($data['budget'] ?? '0')) ?: '0';
        $contractVal = preg_replace('/\D+/', '', (string)($data['contract_value'] ?? '0')) ?: '0';
        $status = trim((string)($data['status'] ?? 'Menunggu Persetujuan'));

        if ($packageName === '' || $opdName === '' || $methodName === '' || (float)$budget <= 0) {
            return 'Mohon lengkapi Nama Paket, OPD, Metode, dan Pagu Anggaran (> 0).';
        }

        // Batas DECIMAL(18,2): maksimal 16 digit sebelum koma.
        if (strlen(ltrim($budget, '0')) > 16 || strlen(ltrim($contractVal, '0')) > 16) {
            return 'Nilai pagu atau kontrak melebihi batas kolom DECIMAL(18,2).';
        }

        $opdRow = $db->queryOne('SELECT id FROM opd WHERE name = ? LIMIT 1', [$opdName]);
        if (!$opdRow) {
            return 'OPD tidak ditemukan pada tabel opd. Pilih OPD yang tersedia di database.';
        }

        $methodRow = $db->queryOne(
            'SELECT id FROM procurement_method WHERE name = ? LIMIT 1',
            [$methodName]
        );
        if (!$methodRow) {
            return 'Metode pengadaan tidak ditemukan pada tabel procurement_method.';
        }

        if ($rupCode === '') {
            $lastPkg = $db->queryOne('SELECT id FROM procurement_package ORDER BY id DESC LIMIT 1');
            $nextNum = ($lastPkg ? (int)$lastPkg['id'] : 0) + 1;
            $rupCode = 'RUP-26' . str_pad((string)$nextNum, 2, '0', STR_PAD_LEFT);
        }

        $exist = $db->queryOne(
            'SELECT id FROM procurement_package WHERE rup_code = ? LIMIT 1',
            [$rupCode]
        );
        if ($exist) {
            return "Kode RUP '{$rupCode}' sudah digunakan pada paket lain.";
        }

        $allowedStatuses = ['Draft', 'Menunggu Persetujuan', 'Disetujui', 'Proses', 'Selesai', 'Ditolak'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'Menunggu Persetujuan';
        }

        // Foreign key ppk_id mengarah ke user.id; pastikan akun masih ada.
        $ppk = $db->queryOne('SELECT id FROM user WHERE id = ? LIMIT 1', [$userId]);
        if (!$ppk) {
            return 'Akun PPK tidak ditemukan pada tabel user.';
        }

        try {
            $db->query(
                'INSERT INTO procurement_package
                    (opd_id, ppk_id, method_id, rup_code, name, budget, contract_value, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    (int)$opdRow['id'],
                    $userId,
                    (int)$methodRow['id'],
                    $rupCode,
                    $packageName,
                    $budget,
                    $contractVal,
                    $status,
                    date('Y-m-d'),
                ]
            );

            return true;
        } catch (Throwable $e) {
            return 'Gagal menyimpan paket pengadaan: ' . $e->getMessage();
        }
    }

    public static function getNextRupCode(): string
    {
        $db = Database::getInstance();
        $lastPkg = $db->queryOne('SELECT id FROM procurement_package ORDER BY id DESC LIMIT 1');
        $nextNum = ($lastPkg ? (int)$lastPkg['id'] : 0) + 1;

        return 'RUP-26' . str_pad((string)$nextNum, 2, '0', STR_PAD_LEFT);
    }

    public function getLatestPackages(int $limit = 20): array
    {
        return array_slice($this->packages, 0, $limit);
    }

    public function getRolePresentation(): array
    {
        $user = Auth::user();
        $name = $user['name'] ?? 'Pengguna';

        switch ($this->role) {
            case 'Admin':
                return [
                    'badge' => 'Administrator Sistem PBJ',
                    'title' => 'Pusat Kendali Pengadaan',
                    'subtitle' => 'Pengawasan menyeluruh terhadap seluruh OPD di Lingkungan Pemerintah Kabupaten Kebumen',
                    'tagline' => 'Akses Penuh: Monitoring seluruh paket pengadaan, verifikasi sistem, dan audit log.',
                    'highlightLabel' => 'Cakupan Monitoring',
                    'highlightVal' => 'Seluruh OPD Kebumen',
                ];

            case 'PA-KPA':
                return [
                    'badge' => 'Pengguna Anggaran (PA / KPA)',
                    'title' => 'Ringkasan Eksekutif & Otorisasi Pagu',
                    'subtitle' => 'Pemantauan efektivitas alokasi pagu anggaran, realisasi kontrak, dan persetujuan pengadaan',
                    'tagline' => 'Kewenangan Otorisasi: Pengawasan efisiensi belanja dan persetujuan (ACC) paket strategis.',
                    'highlightLabel' => 'Fokus Utama',
                    'highlightVal' => 'Efisiensi & Otorisasi Pagu',
                ];

            case 'PPK':
            default:
                return [
                    'badge' => 'Pejabat Pembuat Komitmen (PPK)',
                    'title' => 'Ruang Kerja Pengadaan PPK',
                    'subtitle' => 'Monitoring berkas, pembuatan paket baru, dan penyelesaian paket dalam penugasan Anda',
                    'tagline' => 'Lingkup Kerja: Mengelola paket yang ditugaskan kepada ' . $name . ', input HPS, dan kontrak.',
                    'highlightLabel' => 'Unit Penugasan',
                    'highlightVal' => 'Paket Terpilih Anda',
                ];
        }
    }
}