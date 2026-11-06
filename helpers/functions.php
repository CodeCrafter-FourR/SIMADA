<?php

// Escape HTML untuk mencegah XSS
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format angka menjadi Rupiah Indonesia
function rupiah($amount): string
{
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

// Mengembalikan CSS class badge berdasarkan status paket
function statusClass(string $status): string
{
    return match ($status) {
        'Selesai', 'Disetujui' => 'success',
        'Ditolak' => 'danger',
        'Menunggu Persetujuan' => 'warning',
        default => 'info',
    };
}

// Mengembalikan label berdasarkan status paket
function statusLabel(string $status): string
{
    return match ($status) {
        'Selesai' => '✔ Selesai',
        'Disetujui' => '✔ Disetujui',
        'Ditolak' => '✘ Ditolak',
        'Menunggu Persetujuan' => '⏳ Menunggu',
        'Proses' => '⚙ Proses',
        'Draft' => '📝 Draft',
        default => $status,
    };
}

?>