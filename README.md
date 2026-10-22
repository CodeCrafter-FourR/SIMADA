# SIMADA
### Sistem Informasi Pengadaan Barang

<p align="center">
  <strong>Sistem Informasi Pengadaan Barang/Jasa Kabupaten Kebumen</strong>
  <br>
  Sistem terintegrasi untuk pengelolaan paket, pemantauan progres, dokumentasi, dan pelaporan pengadaan.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Project-SIMADA-2563EB?style=for-the-badge" alt="Project SIMADA">
  <img src="https://img.shields.io/badge/Backend-PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Status-In%20Development-D97706?style=for-the-badge" alt="In Development">
</p>

---

## Overview

SIMADA (Sistem Informasi Pengadaan Barang/Jasa) merupakan rancangan aplikasi berbasis web untuk mendukung koordinasi, pengelolaan, pemantauan, dokumentasi, dan pelaporan kegiatan pengadaan barang/jasa pada Bagian Pengadaan Barang/Jasa Sekretariat Daerah Kabupaten Kebumen.

Sistem ini dirancang untuk menyediakan informasi pengadaan yang lebih terstruktur, mempermudah pemantauan perkembangan paket, serta mendukung transparansi informasi sesuai dengan kewenangan pengguna.

SIMADA berfungsi sebagai sistem pendukung dan tidak dimaksudkan untuk menggantikan SPSE.

> **Project Status:** Perancangan dan pengembangan sistem. Tampilan dashboard yang disertakan dalam prototipe menggunakan data simulasi.

## Key Features

| Fitur | Deskripsi |
|---|---|
| User Management | Pengelolaan akun, role, autentikasi, dan hak akses pengguna. |
| Procurement Management | Pengelolaan paket, OPD, PPK, metode, pagu, dan status pengadaan. |
| Procurement Tracking | Pemantauan tahapan dan perkembangan paket pengadaan. |
| Document Management | Pengelolaan metadata dan dokumen pendukung pengadaan. |
| Dashboard & Reporting | Ringkasan statistik, anggaran, progres, dan laporan pengadaan. |
| Notification System | Rancangan notifikasi melalui WhatsApp dan Email. |
| Audit Logging | Pencatatan aktivitas pengguna untuk mendukung penelusuran dan audit. |
| Public Information | Penyajian informasi pengadaan yang dapat diakses masyarakat. |
| External Integration | Rancangan integrasi dengan layanan dan sistem pengadaan eksternal. |
| Personnel Verification | Dukungan pemeriksaan personel dan SKP sesuai ketentuan yang berlaku. |

## System Architecture

SIMADA dirancang menggunakan pendekatan *three-tier architecture* yang terdiri dari tiga lapisan utama.

```text
┌──────────────────────────────────────┐
│              APP LAYER               │
│      Presentation & User Access      │
│        Web Browser / PWA             │
└──────────────────────────────────────┘
                   |
┌──────────────────────────────────────┐
│             LOGIC LAYER              │
│        Business Rules & Services     │
│ Authentication, Procurement, Tracking│
│ Documents, Reports, Notifications    │
└──────────────────────────────────────┘
                   |
┌──────────────────────────────────────┐
│              DATA LAYER              │
│         Persistence & Storage        │
│       MySQL / PostgreSQL             │
└──────────────────────────────────────┘
```

### App Layer
Menangani antarmuka pengguna melalui web browser atau Progressive Web App (PWA).

### Logic Layer
Menangani aturan bisnis, autentikasi, hak akses, paket pengadaan, tracking, dokumen, notifikasi, laporan, integrasi API, dan audit logging.

### Data Layer
Menyimpan data aplikasi secara terstruktur menggunakan basis data relasional.

## User Roles

SIMADA dirancang untuk enam aktor utama.

| Aktor | Tanggung Jawab |
|---|---|
| Administrator | Mengelola pengguna, role, konfigurasi, dan audit sistem. |
| PA/KPA | Mengakses dashboard dan laporan sesuai kewenangan. |
| PPK | Mengelola dan memantau paket, dokumen, serta progres sesuai kewenangan. |
| Pokja Pemilihan | Mendukung proses tender, dokumentasi, berita acara, dan crosscheck. |
| Pejabat Pengadaan | Mendukung proses pengadaan langsung, e-Purchasing, dan dokumentasi terkait. |
| Masyarakat | Mengakses informasi pengadaan yang tersedia untuk publik. |

## Database Design

Database SIMADA dirancang menggunakan sepuluh tabel utama yang saling terhubung melalui primary key dan foreign key.

| No. | Table | Description |
|---:|---|---|
| 1 | `ROLE` | Menyimpan peran pengguna. |
| 2 | `USER` | Menyimpan akun dan informasi pengguna. |
| 3 | `OPD` | Menyimpan data Organisasi Perangkat Daerah. |
| 4 | `PROCUREMENT_METHOD` | Menyimpan referensi metode pengadaan. |
| 5 | `PROCUREMENT_PACKAGE` | Menyimpan informasi utama paket pengadaan. |
| 6 | `PACKAGE_PERSONNEL` | Menghubungkan personel dengan paket pengadaan. |
| 7 | `PROGRESS` | Menyimpan tahapan dan status progres pengadaan. |
| 8 | `DOCUMENT` | Menyimpan metadata dokumen pengadaan. |
| 9 | `NOTIFICATION` | Menyimpan catatan notifikasi dan statusnya. |
| 10 | `AUDIT_LOG` | Menyimpan catatan aktivitas pengguna. |

### Entity Relationships

- `ROLE` memiliki relasi one-to-many dengan `USER`.
- `OPD` memiliki relasi one-to-many dengan `PROCUREMENT_PACKAGE`.
- `PROCUREMENT_METHOD` memiliki relasi one-to-many dengan `PROCUREMENT_PACKAGE`.
- `USER` sebagai PPK memiliki relasi one-to-many dengan `PROCUREMENT_PACKAGE`.
- `USER` dan `PROCUREMENT_PACKAGE` memiliki relasi many-to-many melalui `PACKAGE_PERSONNEL`.
- `PROCUREMENT_PACKAGE` memiliki relasi one-to-many dengan `PROGRESS`, `DOCUMENT`, dan `NOTIFICATION`.
- `USER` memiliki relasi one-to-many dengan `AUDIT_LOG`.

## Dashboard

Dashboard SIMADA dirancang untuk memberikan ringkasan kondisi pengadaan melalui komponen berikut:

- Jumlah paket terdaftar.
- Total pagu anggaran.
- Nilai kontrak.
- Jumlah paket selesai.
- Tren pendaftaran paket per bulan.
- Komposisi metode pengadaan.
- Daftar paket pengadaan terbaru.

Angka yang muncul pada prototipe dashboard merupakan data simulasi untuk kebutuhan desain dan pengujian antarmuka. Nilai aktual akan bergantung pada data yang tersedia setelah implementasi.

## Technology Stack

| Component | Technology |
|---|---|
| Backend | PHP 8.1+ |
| Framework | Laravel atau CodeIgniter |
| Frontend | HTML5, CSS3, JavaScript |
| Database | MySQL atau PostgreSQL |
| Web Server | Apache |
| Development Environment | XAMPP atau lingkungan PHP setara |
| Server Operating System | Linux/Unix |

Teknologi di atas merupakan rancangan awal. Pilihan framework dan konfigurasi final mengikuti implementasi proyek.

## Getting Started

### Prerequisites

Pastikan perangkat pengembangan telah memiliki:

- PHP 8.1 atau lebih baru.
- Apache atau web server setara.
- MySQL/MariaDB atau PostgreSQL.
- Ekstensi PHP sesuai kebutuhan aplikasi.
- Composer apabila diperlukan oleh framework.

### Database Setup

1. Jalankan Apache dan MySQL/MariaDB melalui XAMPP Control Panel.
2. Buka phpMyAdmin melalui `http://localhost/phpmyadmin`.
3. Buat database bernama `simada`, atau impor file SQL yang tersedia pada repository.
4. Periksa struktur tabel dan relasi melalui menu Designer.
5. Konfigurasikan koneksi database setelah kode aplikasi tersedia.

> Catatan: database yang berhasil dibuat belum berarti aplikasi siap dijalankan. Implementasi backend, frontend, dependensi, dan konfigurasi aplikasi tetap diperlukan.

## External Integrations

SIMADA dirancang untuk mendukung integrasi dengan layanan berikut:

| Sistem / Layanan | Tujuan |
|---|---|
| SIRUP | Mendukung pertukaran informasi rencana umum pengadaan. |
| SPSE / LPSE | Mendukung kebutuhan integrasi informasi proses pengadaan. |
| Katalog Elektronik | Mendukung kebutuhan informasi e-Purchasing. |
| WhatsApp API | Mendukung pengiriman notifikasi. |
| Email / SMTP | Mendukung komunikasi dan notifikasi melalui email. |
| Digital Signature | Mendukung rancangan pengesahan dokumen digital. |

Implementasi integrasi memerlukan konfirmasi API, akses resmi, kredensial, dan ketentuan teknis dari masing-masing penyedia layanan.

## Security Considerations

Aspek keamanan yang direncanakan mencakup:

- Autentikasi dan pengelolaan sesi pengguna.
- Role-Based Access Control (RBAC).
- Pembatasan akses berdasarkan kewenangan.
- Penyimpanan password dalam bentuk hash.
- Validasi input dan perlindungan terhadap serangan umum aplikasi web.
- Audit log untuk penelusuran aktivitas.
- Pengamanan kredensial dan konfigurasi integrasi.
- Pembatasan akses terhadap dokumen dan informasi publik.

## Repository Structure

Struktur repository yang disarankan:

```text
SIMADA/
├── README.md
├── docs/
│   ├── SRS.pdf
│   ├── SDD.pdf
│   ├── architecture.png
│   ├── use-case.png
│   └── erd.png
├── database/
│   └── simada.sql
├── app/
├── public/
├── resources/
├── routes/
├── tests/
└── .gitignore
```

Struktur direktori aplikasi dapat berubah sesuai framework yang digunakan.

## Project Documentation

Dokumentasi proyek mencakup:

- Software Requirements Specification (SRS).
- Software Design Document (SDD).
- System Architecture Diagram.
- Use Case Diagram.
- Entity Relationship Diagram (ERD).
- User Interface Prototype.
- Database Schema dan Data Dictionary.

## Project Objectives

SIMADA bertujuan mendukung pengelolaan pengadaan barang/jasa melalui:

1. Pengelolaan informasi paket yang lebih terstruktur.
2. Pemantauan progres pengadaan secara terpusat.
3. Dokumentasi dan pelaporan yang lebih mudah ditelusuri.
4. Penyediaan ringkasan informasi untuk pengambilan keputusan.
5. Dukungan transparansi informasi publik sesuai kewenangan.
6. Pencatatan aktivitas untuk mendukung audit dan akuntabilitas.

---

<p align="center">
  <strong>SIMADA</strong>
  <br>
  Sistem Informasi Pengadaan Barang/Jasa
  <br>
  Sekretariat Daerah Kabupaten Kebumen
</p>

<p align="center">
  <sub>Dokumentasi berdasarkan rancangan SDD SIMADA. Fitur yang tercantum merupakan ruang lingkup rancangan dan tidak seluruhnya berarti telah diimplementasikan.</sub>
</p>
