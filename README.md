# 🏛️ SIMADA
### Sistem Informasi Pengadaan Barang/Jasa Kabupaten Kebumen

<p align="center">
  <strong>Sistem Informasi Pengadaan Barang/Jasa</strong><br>
  Sekretariat Daerah Kabupaten Kebumen
</p>

<p align="center">
  <img src="https://img.shields.io/badge/SIMADA-Procurement%20Information%20System-2563EB?style=for-the-badge" alt="SIMADA">
  <img src="https://img.shields.io/badge/PHP-Native-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Server-Apache-D22128?style=for-the-badge&logo=apache&logoColor=white" alt="Apache">
  <img src="https://img.shields.io/badge/Environment-XAMPP-FB7A24?style=for-the-badge" alt="XAMPP">
  <img src="https://img.shields.io/badge/Status-In%20Development-D97706?style=for-the-badge" alt="Development">
</p>

<p align="center">
  <em>Integrated Procurement Management, Monitoring, and Information System</em>
</p>

---

## 📌 01. Project Overview

**SIMADA (Sistem Informasi Pengadaan Barang/Jasa)** merupakan aplikasi berbasis web yang dikembangkan untuk mendukung pengelolaan, pemantauan, dan penyajian informasi pengadaan barang/jasa pada Sekretariat Daerah Kabupaten Kebumen.

SIMADA dirancang sebagai sistem informasi terintegrasi yang membantu pengelolaan data paket pengadaan, informasi anggaran, nilai kontrak, status paket, serta informasi pengguna dalam satu platform.

Sistem ini diharapkan dapat membantu proses administrasi pengadaan menjadi lebih terstruktur, mempermudah pemantauan perkembangan paket, dan menyediakan informasi yang mendukung pengambilan keputusan.

> **Project Status:** Development  
> SIMADA merupakan sistem pendukung pengelolaan informasi dan tidak dimaksudkan untuk menggantikan SPSE maupun sistem resmi pengadaan pemerintah lainnya.

## 🎯 02. Project Objectives

Pengembangan SIMADA memiliki beberapa tujuan utama:

1. **Centralized Information** — Mengintegrasikan informasi paket pengadaan dalam satu sistem.
2. **Procurement Monitoring** — Mempermudah pemantauan perkembangan dan status paket.
3. **Budget Management** — Mendukung pencatatan pagu anggaran dan nilai kontrak.
4. **User Management** — Mengelola pengguna berdasarkan peran dan kewenangan.
5. **Data Transparency** — Mendukung penyajian informasi pengadaan sesuai hak akses.
6. **Administrative Efficiency** — Membantu meningkatkan efisiensi pengelolaan administrasi pengadaan.
7. **Accountability** — Mendukung dokumentasi dan penelusuran aktivitas sistem.

## 🚀 03. Key Features

| No. | Modul | Deskripsi |
|---:|---|---|
| 01 | Dashboard | Menampilkan ringkasan informasi dan statistik paket pengadaan. |
| 02 | Paket Pengadaan | Mengelola informasi paket pengadaan barang/jasa. |
| 03 | Budget Management | Mengelola pagu anggaran dan nilai kontrak paket. |
| 04 | Procurement Status | Menampilkan status dan perkembangan paket pengadaan. |
| 05 | User Management | Mengelola akun pengguna dan informasi role. |
| 06 | Authentication | Menangani proses login, logout, dan sesi pengguna. |
| 07 | Database Management | Menyimpan dan mengelola data aplikasi melalui MySQL. |

Fitur tambahan seperti pengelolaan dokumen, notifikasi, audit log, dan integrasi eksternal merupakan bagian dari ruang pengembangan sistem sesuai implementasi berikutnya.

## 🏗️ 04. System Architecture

SIMADA menggunakan pendekatan pemisahan antara antarmuka, logika aplikasi, dan penyimpanan data.

```text
                 SIMADA SYSTEM
                       |
          +-------------------------+
          |       PRESENTATION     |
          |                         |
          |     HTML / CSS / JS     |
          |       Web Browser       |
          +-------------------------+
                       |
          +-------------------------+
          |     APPLICATION LAYER   |
          |                         |
          |       PHP Native        |
          |                         |
          |  Authentication        |
          |  Dashboard              |
          |  Procurement Management |
          |  User Management        |
          +-------------------------+
                       |
          +-------------------------+
          |        DATA LAYER       |
          |                         |
          |         MySQL           |
          |                         |
          |     SIMADA Database     |
          +-------------------------+
```

### Application Layer

Menangani proses bisnis aplikasi, termasuk autentikasi, pengelolaan paket, pengambilan data dashboard, serta fungsi-fungsi aplikasi lainnya.

### Presentation Layer

Menampilkan antarmuka aplikasi melalui browser menggunakan HTML, CSS, dan JavaScript.

### Data Layer

Mengelola penyimpanan data secara terstruktur menggunakan MySQL.

## 💻 05. Technology Stack

Teknologi yang digunakan dalam pengembangan SIMADA:

| Komponen | Teknologi |
|---|---|
| Backend | PHP Native |
| Programming Paradigm | Object-Oriented Programming (OOP) |
| Frontend | HTML5, CSS3, JavaScript |
| Database | MySQL / MariaDB |
| Web Server | Apache |
| Local Development | XAMPP |
| Database Administration | phpMyAdmin |
| Version Control | Git |
| Code Repository | GitHub |

**Catatan:** Project saat ini menggunakan PHP Native dengan struktur class tersendiri, bukan Laravel atau CodeIgniter.

## 📁 06. Project Structure

Berikut struktur direktori project SIMADA:

```text
ujianrpl/
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   └── img/
│       └── logo-simada.png
│
├── classes/
│   ├── Auth.php
│   ├── Dashboard.php
│   └── Database.php
│
├── helpers/
│   └── functions.php
│
├── sql/
│   └── simada.sql
│
├── config.php
├── dashboard.php
├── index.html
├── index.php
├── login.php
├── logout.php
├── tambah-user.php
│
└── README.md
```

### Directory Description

| File / Directory | Fungsi |
|---|---|
| `assets/` | Menyimpan aset pendukung aplikasi. |
| `assets/css/` | Menyimpan stylesheet dan desain antarmuka. |
| `assets/img/` | Menyimpan logo dan gambar aplikasi. |
| `classes/` | Menyimpan class untuk proses aplikasi. |
| `Auth.php` | Menangani autentikasi pengguna. |
| `Dashboard.php` | Menangani pengambilan dan pengolahan data dashboard. |
| `Database.php` | Menangani koneksi dan operasi database. |
| `helpers/` | Menyimpan fungsi bantuan aplikasi. |
| `functions.php` | Menyediakan fungsi format dan utilitas aplikasi. |
| `sql/` | Menyimpan file database SQL. |
| `simada.sql` | File struktur dan data database SIMADA. |
| `config.php` | Menyimpan konfigurasi utama aplikasi. |
| `dashboard.php` | Halaman dashboard utama. |
| `index.php` | Entry point aplikasi PHP. |
| `index.html` | Halaman HTML. |
| `login.php` | Halaman login pengguna. |
| `logout.php` | Proses logout pengguna. |
| `tambah-user.php` | Halaman/proses penambahan pengguna. |
| `README.md` | Dokumentasi project. |

## 🗄️ 07. Database Design

SIMADA menggunakan database MySQL dengan nama:

```sql
simada
```

Database dirancang untuk mendukung pengelolaan informasi pengguna dan paket pengadaan.

### Main Database Tables

| No. | Table | Description |
|---:|---|---|
| 01 | `role` | Menyimpan informasi role pengguna. |
| 02 | `user` | Menyimpan akun dan informasi pengguna. |
| 03 | `opd` | Menyimpan data Organisasi Perangkat Daerah. |
| 04 | `procurement_method` | Menyimpan referensi metode pengadaan. |
| 05 | `procurement_package` | Menyimpan informasi utama paket pengadaan. |
| 06 | `package_personnel` | Menghubungkan personel dengan paket pengadaan. |
| 07 | `progress` | Menyimpan informasi perkembangan paket. |
| 08 | `document` | Menyimpan metadata dokumen pengadaan. |
| 09 | `notification` | Menyimpan informasi notifikasi. |
| 10 | `audit_log` | Menyimpan catatan aktivitas pengguna. |

> **Database Note:** `packages` merupakan VIEW pada skema yang digunakan, sedangkan tabel utama untuk penyimpanan data paket adalah `procurement_package`. Struktur dan relasi database mengikuti file `sql/simada.sql`.

### Main Database Relationships

- `role` → `user`
- `opd` → `procurement_package`
- `procurement_method` → `procurement_package`
- `user` → `procurement_package` sebagai PPK
- `user` ↔ `procurement_package` melalui `package_personnel`
- `procurement_package` → `progress`
- `procurement_package` → `document`
- `procurement_package` → `notification`
- `user` → `audit_log`

## 📊 08. Dashboard Overview

Dashboard SIMADA dirancang sebagai pusat informasi untuk menampilkan ringkasan pengadaan barang/jasa.

Komponen informasi dashboard dapat mencakup:

- Total paket pengadaan.
- Total pagu anggaran.
- Total nilai kontrak.
- Jumlah paket berdasarkan status.
- Statistik paket pengadaan.
- Informasi paket terbaru.

Data yang ditampilkan mengikuti informasi yang tersedia di database. Apabila masih menggunakan data simulasi, angka pada dashboard tidak mewakili data pengadaan resmi.

## 👥 09. User Roles

Sistem dirancang untuk mendukung pengelolaan akses berdasarkan peran pengguna.

| Role | Deskripsi |
|---|---|
| Administrator | Mengelola pengguna dan konfigurasi sistem. |
| PA/KPA | Mengakses informasi dan laporan sesuai kewenangan. |
| PPK | Mengelola dan memantau paket sesuai kewenangan. |
| Pokja Pemilihan | Mendukung kegiatan pemilihan dan dokumentasi pengadaan. |
| Pejabat Pengadaan | Mendukung proses pengadaan sesuai kewenangan. |
| Masyarakat | Mengakses informasi yang disediakan untuk publik. |

Ketersediaan role dan hak akses mengikuti konfigurasi serta implementasi aplikasi.

## ⚙️ 10. Installation & Setup

Ikuti langkah berikut untuk menjalankan SIMADA pada lingkungan lokal.

### System Requirements

- Windows atau sistem operasi yang mendukung PHP.
- XAMPP.
- PHP 8.1 atau lebih baru (disarankan).
- Apache Web Server.
- MySQL / MariaDB.
- Browser modern.

### Step 1 — Clone Repository

Buka Git Bash atau terminal, kemudian jalankan:

```bash
git clone https://github.com/CodeCrafter-FourR/SIMADA.git
```

Masuk ke direktori project:

```bash
cd SIMADA
```

Jika menggunakan source code lokal, letakkan folder project di:

```text
C:\xampp\htdocs\ujianrpl
```

### Step 2 — Start XAMPP

Buka XAMPP Control Panel, kemudian aktifkan:

- Apache
- MySQL

Pastikan keduanya berjalan dengan baik.

### Step 3 — Create Database

Buka phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Buat database baru dengan nama:

```text
simada
```

### Step 4 — Import SQL Database

1. Pilih database `simada`.
2. Buka menu **Import**.
3. Pilih file `sql/simada.sql`.
4. Klik **Import** atau **Go**.
5. Tunggu sampai proses import selesai.
6. Periksa tabel database yang berhasil dibuat.

### Step 5 — Configure Database

Periksa konfigurasi koneksi database pada `config.php`.

Konfigurasi standar XAMPP:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'simada');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

Sesuaikan username dan password apabila konfigurasi MySQL berbeda.

### Step 6 — Run Application

Buka browser dan akses:

```text
http://localhost/ujianrpl/
```

Jika diperlukan, akses halaman login secara langsung:

```text
http://localhost/ujianrpl/login.php
```

Gunakan akun yang tersedia pada database hasil import.

## 🔐 11. Security Considerations

Keamanan merupakan salah satu aspek penting dalam pengembangan SIMADA.

Beberapa aspek keamanan yang perlu diterapkan dan diuji:

- Authentication untuk memastikan identitas pengguna.
- Session Management untuk mengelola sesi login.
- Role-Based Access Control (RBAC) untuk membatasi akses berdasarkan role.
- Password Hashing untuk penyimpanan password.
- Input Validation untuk memvalidasi data yang dikirim pengguna.
- Prepared Statements untuk mengurangi risiko SQL Injection.
- Output Escaping untuk mengurangi risiko Cross-Site Scripting (XSS).
- Access Control untuk membatasi akses halaman dan data.
- Audit Logging untuk mendukung penelusuran aktivitas.

Jangan menyimpan password, token, atau kredensial sensitif secara langsung di repository publik.

## 🔗 12. Future Development

Pengembangan SIMADA dapat dilanjutkan dengan beberapa peningkatan berikut:

| Area | Rencana Pengembangan |
|---|---|
| Procurement Tracking | Pemantauan tahapan pengadaan secara lebih terperinci. |
| Document Management | Pengelolaan dan penyimpanan dokumen pendukung. |
| Notification System | Notifikasi melalui WhatsApp dan Email. |
| Reporting | Pengembangan laporan dan ekspor data. |
| Audit Trail | Penyempurnaan pencatatan aktivitas pengguna. |
| Public Information | Penyajian informasi pengadaan untuk masyarakat. |
| External Integration | Integrasi dengan layanan pengadaan eksternal. |
| User Experience | Peningkatan tampilan dan kemudahan penggunaan. |

Rencana pengembangan tersebut bukan berarti seluruh fitur telah tersedia atau aktif pada versi saat ini.

## 🌐 13. External System Integration

SIMADA memiliki potensi pengembangan integrasi dengan beberapa layanan eksternal:

| Sistem | Tujuan Integrasi |
|---|---|
| SIRUP | Mendukung pertukaran informasi rencana umum pengadaan. |
| SPSE / LPSE | Mendukung kebutuhan informasi proses pengadaan. |
| Katalog Elektronik | Mendukung informasi e-Purchasing. |
| WhatsApp API | Pengiriman notifikasi. |
| Email / SMTP | Pengiriman informasi melalui email. |
| Digital Signature | Dukungan pengesahan dokumen digital. |

Integrasi hanya dapat diimplementasikan setelah tersedia akses resmi, dokumentasi API, kredensial, serta persetujuan teknis dari penyedia layanan.

## 📚 14. Project Documentation

Dokumentasi pendukung pengembangan SIMADA dapat mencakup:

- Software Requirements Specification (SRS).
- Software Design Document (SDD).
- System Architecture Diagram.
- Use Case Diagram.
- Entity Relationship Diagram (ERD).
- Database Schema.
- Data Dictionary.
- User Interface Design.
- Testing Documentation.

## 🤝 15. Development & Contribution

Pengembangan project dilakukan secara bertahap dengan memperhatikan konsistensi struktur kode, keamanan aplikasi, dan kesesuaian database.

Kontribusi pengembangan dapat mencakup:

- Perbaikan bug.
- Penyempurnaan antarmuka.
- Pengembangan modul.
- Optimasi database.
- Peningkatan keamanan.
- Pengujian fungsionalitas.

Setiap perubahan sebaiknya diuji terlebih dahulu sebelum diterapkan ke versi utama.

## 📌 16. Project Information

| Informasi | Detail |
|---|---|
| Project Name | SIMADA |
| Full Name | Sistem Informasi Pengadaan Barang/Jasa |
| Organization | Sekretariat Daerah Kabupaten Kebumen |
| Application Type | Web-Based Application |
| Backend | PHP Native |
| Database | MySQL |
| Development Environment | XAMPP |
| Repository | GitHub |
| Project Status | In Development |

## 🔗 17. Repository

Source code SIMADA tersedia melalui repository GitHub:

**[CodeCrafter-FourR / SIMADA](https://github.com/CodeCrafter-FourR/SIMADA)**

---

<p align="center">
  <strong>🏛️ SIMADA</strong><br>
  Sistem Informasi Pengadaan Barang/Jasa<br>
  Sekretariat Daerah Kabupaten Kebumen
</p>

<p align="center">
  <em>Supporting Better Procurement Information Management.</em>
</p>

<p align="center">
  <sub>© 2026 SIMADA — Project Documentation</sub>
</p>
