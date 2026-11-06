-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Nov 2026 pada 18.26
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simada`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `approvals`
--

CREATE TABLE `approvals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `approver_id` bigint(20) UNSIGNED NOT NULL,
  `decision` varchar(50) NOT NULL,
  `decided_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `approvals`
--

INSERT INTO `approvals` (`id`, `package_id`, `approver_id`, `decision`, `decided_at`) VALUES
(1, 2, 2, 'Disetujui', '2026-02-10 10:00:00'),
(2, 3, 2, 'Disetujui', '2026-03-20 13:15:00'),
(3, 5, 2, 'Disetujui', '2026-05-12 09:30:00'),
(4, 11, 1, 'Ditolak', '2026-11-02 23:18:16'),
(5, 4, 8, 'Ditolak', '2026-11-04 11:23:35'),
(6, 12, 8, 'Disetujui', '2026-11-04 12:14:10'),
(7, 14, 8, 'Ditolak', '2026-11-04 12:15:43'),
(8, 13, 8, 'Ditolak', '2026-11-04 12:15:46'),
(9, 15, 8, 'Ditolak', '2026-11-04 12:16:58');

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `activity` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `document`
--

CREATE TABLE `document` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `uploader_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notification`
--

CREATE TABLE `notification` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED DEFAULT NULL,
  `channel` varchar(20) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `opd`
--

CREATE TABLE `opd` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `opd`
--

INSERT INTO `opd` (`id`, `name`) VALUES
(1, 'Bagian Pengadaan Barang/Jasa'),
(2, 'Dinas Komunikasi dan Informatika'),
(3, 'Dinas Pendidikan'),
(4, 'Dinas Kesehatan'),
(5, 'Sekretariat Daerah'),
(6, 'Inspektorat Daerah');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `packages`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `packages` (
`id` bigint(20) unsigned
,`user_id` bigint(20) unsigned
,`rup_code` varchar(50)
,`package_name` varchar(255)
,`opd` varchar(150)
,`method` varchar(100)
,`budget` decimal(18,2)
,`contract_value` decimal(18,2)
,`status` varchar(50)
,`created_at` date
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `package_personnel`
--

CREATE TABLE `package_personnel` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `position` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `procurement_method`
--

CREATE TABLE `procurement_method` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `procurement_method`
--

INSERT INTO `procurement_method` (`id`, `name`) VALUES
(3, 'e-Purchasing'),
(1, 'e-Tendering'),
(2, 'Pengadaan Langsung');

-- --------------------------------------------------------

--
-- Struktur dari tabel `procurement_package`
--

CREATE TABLE `procurement_package` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `opd_id` bigint(20) UNSIGNED NOT NULL,
  `ppk_id` bigint(20) UNSIGNED NOT NULL,
  `method_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `budget` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Perencanaan',
  `rup_code` varchar(50) DEFAULT NULL,
  `contract_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `procurement_package`
--

INSERT INTO `procurement_package` (`id`, `opd_id`, `ppk_id`, `method_id`, `name`, `budget`, `status`, `rup_code`, `contract_value`, `created_at`) VALUES
(1, 2, 3, 1, 'Pengadaan Perangkat Jaringan', 425000000.00, 'Proses', 'RUP-2601', 398000000.00, '2026-01-12'),
(2, 5, 3, 2, 'Perawatan Gedung Pelayanan', 285000000.00, 'Selesai', 'RUP-2602', 276000000.00, '2026-02-08'),
(3, 3, 4, 3, 'Peralatan Kantor Terpadu', 160000000.00, 'Selesai', 'RUP-2603', 155000000.00, '2026-03-19'),
(4, 2, 4, 1, 'Pengadaan Infrastruktur LAN', 320000000.00, 'Ditolak', 'RUP-2604', 0.00, '2026-04-05'),
(5, 4, 3, 3, 'Pengadaan ATK Tahunan', 98000000.00, 'Selesai', 'RUP-2605', 94000000.00, '2026-05-11'),
(6, 5, 4, 2, 'Pemeliharaan Ruang Layanan', 125000000.00, 'Proses', 'RUP-2606', 118000000.00, '2026-06-21'),
(7, 3, 3, 3, 'Perangkat Presentasi', 75000000.00, 'Selesai', 'RUP-2607', 72000000.00, '2026-07-09'),
(8, 2, 4, 2, 'Perbaikan Jaringan Gedung', 210000000.00, 'Draft', 'RUP-2608', 0.00, '2026-08-14'),
(9, 3, 3, 1, 'Pengadaan Komputer', 450000000.00, 'Proses', 'RUP-2609', 425000000.00, '2026-09-03'),
(10, 5, 4, 3, 'Perlengkapan Ruang Rapat', 85000000.00, 'Selesai', 'RUP-2610', 81000000.00, '2026-10-02'),
(11, 6, 4, 1, 'Pengadaan contoh', 99999999999999.99, 'Ditolak', 'RUP-2611', 0.00, '2026-11-02'),
(12, 1, 5, 1, 'kopdes', 20000000.00, 'Proses', 'RUP-2612', 0.00, '2026-11-04'),
(13, 1, 1, 2, 'server data', 1000000.00, 'Ditolak', 'RUP-2613', 5000000.00, '2026-11-04'),
(14, 1, 10, 1, 'pengadaan iphone burgundy', 67000000.00, 'Ditolak', 'RUP-2614', 12000.00, '2026-11-04'),
(15, 1, 1, 1, 'perawatan lahan', 3500000.00, 'Ditolak', 'RUP-2615', 0.00, '2026-11-04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `progress`
--

CREATE TABLE `progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `package_id` bigint(20) UNSIGNED NOT NULL,
  `stage` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `role`
--

CREATE TABLE `role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'Administrator'),
(6, 'Masyarakat'),
(2, 'PA/KPA'),
(5, 'Pejabat Pengadaan'),
(4, 'Pokja Pemilihan'),
(3, 'PPK');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `role_id`, `name`, `username`, `email`, `password_hash`) VALUES
(1, 1, 'Administrator SIMADA', 'admin', 'admin@simada.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 2, 'PA KPA SIMADA', 'pakpa', 'pakpa@simada.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3, 3, 'PPK Kominfo', 'ppk1', 'ppk1@simada.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(4, 3, 'PPK Pendidikan', 'ppk2', 'ppk2@simada.test', '$2y$10$ch1fCrHwq6HNQCLubdaoA.2K2z8wn7yWhAfJaXQwL4vVGaDBjnhIi'),
(5, 3, 'REHAN RIZKI RAHMAT RAMADHAN', 'rehan', 'rehanramadehan@gmail.com', '$2y$10$huYV/3rhNvJgY7E8lR9xz..RlD/b3KJaGAq5X5y3gooEOjm/2K6S2'),
(7, 1, 'awan', 'awan', 'awan@awam.com', '$2y$10$2hBSWm0qwEagUBPkrwmSOe43KPQsjF9E9fBNLVI9gkn9C3sPs10aK'),
(8, 1, 'sakhila Yunara devras', 'narakhila', 'yunarakhilll@gmail.com', '$2y$10$nwbrf8e050AcPu/e.BC1mOOmZHohBEFlSyZVVIi0fyh5xvbnc/OSS'),
(9, 2, 'taufiqur', 'tau10292rah', 'taufiqurahman1111@gmail.com', '$2y$10$u0rOlqHIYLgn.APLJX8LcOlWoaF.o0BXGG/eq3XfawCgt43.97fFi'),
(10, 3, 'antika permai', 'tika', 'antikaprm@gmail.com', '$2y$10$UeGHsT6mSQFD6G4/nTPuKeZzBxTzT0sXFEogY7uGJ8xmZncNG216i'),
(11, 3, 'shakyawan bagus wijaya', 'seki', 'cloud111@gmail.com', '$2y$10$evfeGVOSUMBFIRmNXmJaGOecLhMNXb/7qBRkxDfC8T3SDaP8o7jPe'),
(12, 3, 'ayundia permai', 'ynda', 'ayndaprm@gmail.com', '$2y$10$eafo8nMDOqDiB6Bb9Zju/OSEn34zwbBc5FaIXBs7JFNJYuDmJ3lx2');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `users`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `users` (
`id` bigint(20) unsigned
,`name` varchar(150)
,`username` varchar(150)
,`email` varchar(150)
,`password` varchar(255)
,`role` varchar(50)
,`created_at` datetime
);

-- --------------------------------------------------------

--
-- Struktur untuk view `packages`
--
DROP TABLE IF EXISTS `packages`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `packages`  AS SELECT `p`.`id` AS `id`, `p`.`ppk_id` AS `user_id`, `p`.`rup_code` AS `rup_code`, `p`.`name` AS `package_name`, `o`.`name` AS `opd`, `m`.`name` AS `method`, `p`.`budget` AS `budget`, `p`.`contract_value` AS `contract_value`, `p`.`status` AS `status`, `p`.`created_at` AS `created_at` FROM ((`procurement_package` `p` left join `opd` `o` on(`o`.`id` = `p`.`opd_id`)) left join `procurement_method` `m` on(`m`.`id` = `p`.`method_id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `users`
--
DROP TABLE IF EXISTS `users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users`  AS SELECT `u`.`id` AS `id`, `u`.`name` AS `name`, substring_index(`u`.`email`,'@',1) AS `username`, `u`.`email` AS `email`, `u`.`password_hash` AS `password`, `r`.`name` AS `role`, cast(NULL as datetime) AS `created_at` FROM (`user` `u` left join `role` `r` on(`r`.`id` = `u`.`role_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_approvals_package` (`package_id`),
  ADD KEY `idx_approvals_approver` (`approver_id`);

--
-- Indeks untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indeks untuk tabel `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_document_package` (`package_id`),
  ADD KEY `fk_document_uploader` (`uploader_id`);

--
-- Indeks untuk tabel `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user` (`user_id`),
  ADD KEY `fk_notification_package` (`package_id`);

--
-- Indeks untuk tabel `opd`
--
ALTER TABLE `opd`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `package_personnel`
--
ALTER TABLE `package_personnel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_personnel_package` (`package_id`),
  ADD KEY `fk_personnel_user` (`user_id`);

--
-- Indeks untuk tabel `procurement_method`
--
ALTER TABLE `procurement_method`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `procurement_package`
--
ALTER TABLE `procurement_package`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_package_opd` (`opd_id`),
  ADD KEY `fk_package_ppk` (`ppk_id`),
  ADD KEY `fk_package_method` (`method_id`);

--
-- Indeks untuk tabel `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_progress_package` (`package_id`);

--
-- Indeks untuk tabel `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_user_role` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `document`
--
ALTER TABLE `document`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notification`
--
ALTER TABLE `notification`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `opd`
--
ALTER TABLE `opd`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `package_personnel`
--
ALTER TABLE `package_personnel`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `procurement_method`
--
ALTER TABLE `procurement_method`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `procurement_package`
--
ALTER TABLE `procurement_package`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `progress`
--
ALTER TABLE `progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `fk_approvals_approver` FOREIGN KEY (`approver_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_approvals_package` FOREIGN KEY (`package_id`) REFERENCES `procurement_package` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_document_package` FOREIGN KEY (`package_id`) REFERENCES `procurement_package` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_document_uploader` FOREIGN KEY (`uploader_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_package` FOREIGN KEY (`package_id`) REFERENCES `procurement_package` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `package_personnel`
--
ALTER TABLE `package_personnel`
  ADD CONSTRAINT `fk_personnel_package` FOREIGN KEY (`package_id`) REFERENCES `procurement_package` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_personnel_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `procurement_package`
--
ALTER TABLE `procurement_package`
  ADD CONSTRAINT `fk_package_method` FOREIGN KEY (`method_id`) REFERENCES `procurement_method` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_package_opd` FOREIGN KEY (`opd_id`) REFERENCES `opd` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_package_ppk` FOREIGN KEY (`ppk_id`) REFERENCES `user` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `fk_progress_package` FOREIGN KEY (`package_id`) REFERENCES `procurement_package` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
