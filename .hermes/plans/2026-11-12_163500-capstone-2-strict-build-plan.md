# RENCANA TEKNIS STRICT — CAPSTONE-2 (UNIFIED PESANTREN & PPDB ONLINE)
## Sistem Informasi Manajemen PPTQ Ibnu Juraimi & PPDB Online Terintegrasi

| Field | Nilai |
|---|---|
| **Doc ID** | PLAN-C2-STRICT-v2.0 |
| **Tanggal** | 2026-11-12 |
| **Status** | **NORMATIF & BEKU** untuk Seluruh Milestone M0–M6 |
| **Pemilik** | fadd |
| **Workspace** | /home/fadd/Documents/vscode/capstone-2/ |
| **Acuan Normatif Utama** | `dokum-cp1.md` (Administrasi PPTQ Ibnu Juraimi) & `dokum-ppdb.md` (PPDB Online & Landing CMS) |
| **Acuan Dokumen Proyek** | `ORIGINAL_REQUEST.md`, `PROJECT.md`, Survey 1 (Spec Gaps), Survey 2 (Extraneous Features) |
| **Bahasa** | Indonesia untuk domain & bisnis; identifier kode tetap Bahasa Inggris |

### Daftar Isi

0. Kontrol Dokumen & Prinsip Integritas
1. Tujuan, Ruang Lingkup & Batasan Sistem
2. Ground Truth & Keputusan Arsitektur Terpadu
3. Arsitektur, Lapisan & Struktur Direktori
4. Skema Data Terpadu (16 Tabel Domain + Spatie + Framework)
5. RBAC 4-Role Terpadu (Admin, Ustadz, Wali Murid, Calon Santri)
6. Kontrak Rute Lengkap (Publik, Calon Santri, Admin Akademik & PPDB, Ustadz, Wali Murid)
7. SubSistem PPDB Online: CMS, Registrasi, Upload 10 Berkas & Guard Jadwal
8. Presensi Ustadz Geolocation (Haversine 150m) & Presensi Santri Bulk
9. Tahfidz Ziyadah & Murojaah Al-Qur'an
10. Portal Wali Murid & Laporan Global Akademik
11. Alur Promosi Pendaftar Lulus ke Santri Akademik
12. Integrasi Eksternal: Fonnte WhatsApp Gateway & Transaksional Email
13. Kontrak Tampilan UI, Blade Components & Desain
14. Standar Kualitas, Gate DoD & Perintah Verifikasi
15. Fase Pembangunan & Milestone (M0–M6)
16. Matriks Pengujian Lengkap (Unit, Feature, E2E & Hardening)
17. Register Risiko & Mitigasi
18. Anti-Pattern & Pemeriksaan CI
19. Change Log (v1.0 -> v2.0 Alignment)
Appendix A — Enum & Label
Appendix B — Konfigurasi & Variabel Lingkungan (.env)
Appendix C — Konvensi Penamaan Kode & File
Appendix D — Cheatsheet Perintah Eksekusi

---

## 0. Kontrol Dokumen & Prinsip Integritas

### 0.1 Arti Kata Kunci (RFC 2119 Ringkas)
| Kata | Arti |
|---|---|
| **WAJIB** | Syarat mutlak. Melanggar = penolakan hasil kerja (reject). |
| **DILARANG** | Larangan mutlak. Melanggar = revert kode dan koreksi segera. |
| **SEBAIKNYA** | Rekomendasi teknis kuat; penyimpangan wajib didokumentasikan. |
| **BOLEH** | Opsional, tidak memengaruhi kepatuhan kontrak spesifikasi. |

### 0.2 Urutan Otoritas Saat Terjadi Konflik
1. **Governing Prompt & Acceptance Criteria** (`ORIGINAL_REQUEST.md`): Mencakup 100% persyaratan `dokum-cp1.md` dan `dokum-ppdb.md` tanpa fitur liar di luar kedua dokumen.
2. **Dokumen Spesifikasi Asli**: `dokum-cp1.md` dan `dokum-ppdb.md`.
3. **Dokumen Ini** (`.hermes/plans/2026-11-12_163500-capstone-2-strict-build-plan.md` v2.0).
4. `PROJECT.md` pada direktori teamwork state.

### 0.3 Prinsip Integritas & Anti-Shortcut
- **DILARANG** melakukan kecurangan kode, hardcoding hasil test, membuat implementasi palsu/facade yang tidak menyimpan state riil di database.
- Semua tabel dan fitur harus benar-benar berfungsi di backend Laravel dan database relasional.
- Seluruh verifikasi harus didukung oleh pengujian programmatic riil (PHPUnit/Pest).

---

## 1. Tujuan, Ruang Lingkup & Batasan Sistem

### 1.1 Tujuan Produk
Membangun satu aplikasi monolitik modern berbasis **Laravel 11/13 + PHP 8.3+** yang menyatukan dua pilar utama pesantren secara terpadu dan tanpa redundansi:
1. **Sistem Administrasi Akademik & Tahfidz PPTQ Ibnu Juraimi** (bersumber dari `dokum-cp1.md`):
   - Pengelolaan master data Santri, Ustadz, Wali Murid, dan Kegiatan Pondok.
   - Penugasan Ustadz ke Kegiatan Pondok dan penetapan Santri Binaan tahfidz.
   - Presensi Ustadz berbasis Geolocation Geofence lingkaran Haversine (radius 150 meter).
   - Presensi Santri secara massal (bulk) per kegiatan pondok.
   - Pencatatan capaian hafalan baru Al-Qur'an (Setoran Ziyadah) dan pengulangan hafalan (Murojaah).
   - Laporan global rekapitulasi presensi dan hafalan santri (dapat difilter dan diekspor ke PDF dan Excel).
   - Portal Wali Murid untuk monitoring hafalan mingguan dan riwayat kehadiran anak secara real-time.
2. **Sistem Penerimaan Peserta Didik Baru (PPDB Online) & Public CMS** (bersumber dari `dokum-ppdb.md`):
   - Portal Publik Landing Page CMS (Slider Carousel, Poin Keunggulan, Live Counter Statistik, Kontak Resmi, dan Informasi Pengumuman).
   - Formulir pendaftaran calon santri online terproteksi jadwal dinamis (`CheckFormStatus` middleware).
   - Multi-upload 10 berkas persyaratan legal/akademik ke storage privat.
   - Pembuatan akun otomatis calon santri (`role: calon_santri`) dengan password acak 8 karakter, konfirmasi pendaftaran, dan notifikasi email kredensial (`RegisterSuccessMail`).
   - Dashboard Calon Santri untuk memantau status seleksi (`Menunggu`, `Lulus`, `Tidak Lulus`) dan mengunduh berkas unggahan secara aman (IDOR-safe).
   - Dashboard Admin PPDB dengan visualisasi chart rasio gender pendaftar (Chart.js doughnut).
   - Verifikasi berkas pendaftar dan integrasi WhatsApp via Fonnte Gateway API untuk notifikasi status instan dan broadcast pengumuman massal.
   - Pengunduhan arsip seluruh berkas per pendaftar dalam format ZIP tunggal on-demand.
   - Rekapitulasi pendaftar ke spreadsheet Excel (`PendaftarExport`) dan dokumen cetak PDF A4 landscape.
   - Pengaturan jadwal pendaftaran (buka/tutup otomatis) dan manajemen konten CMS publik.
   - Manajemen akun administrator dengan proteksi self-deletion dan fitur reset password.
   - Alur promosi santri baru: mempromosikan pendaftar yang `Lulus` langsung menjadi `Santri` aktif dengan NIS di modul pondok akademik.

### 1.2 Ruang Lingkup Sesuai Spesifikasi (Masuk vs Keluar)

| Modul / Komponen | Status | Sumber Spesifikasi | Keterangan |
|---|:---:|---|---|
| Autentikasi Breeze & Spatie RBAC 4-Role (`admin`, `ustadz`, `wali_murid`, `calon_santri`) | **MASUK** | `dokum-cp1.md` §3, `dokum-ppdb.md` §6.1 | Satu sistem akun terpadu |
| Master Data Akademik (Santri, Ustadz, Wali, Kegiatan, Penugasan) | **MASUK** | `dokum-cp1.md` §5.2 | NIS `VARCHAR(50)` UNIQUE |
| Presensi Ustadz Geofence Haversine (150m) | **MASUK** | `dokum-cp1.md` §6 | Pusat: -7.797068, 110.370529 |
| Presensi Santri Bulk | **MASUK** | `dokum-cp1.md` §5.3 | Mass status hadir/izin/sakit/alpha |
| Tahfidz Setoran Ziyadah & Murojaah | **MASUK** | `dokum-cp1.md` §5.3 | Nilai 0-100, surat & ayat text |
| Laporan Global Akademik & Ekspor PDF/Excel | **MASUK** | `dokum-cp1.md` §5.2 | Dompdf landscape & Maatwebsite |
| Portal & Dashboard Wali Murid | **MASUK** | `dokum-cp1.md` §5.4 | Monitoring anak binaan wali |
| Landing Page CMS Publik (Carousel, Keunggulan, Statistik, Info, Kontak) | **MASUK** | `dokum-ppdb.md` §5.1, §7.1 | Beranda interaktif dinamis |
| Form Pendaftaran Online & Jadwal Guard | **MASUK** | `dokum-ppdb.md` §3.1, §6.3 | Middleware `CheckFormStatus` |
| Multi-Upload 10 Dokumen ke Storage Privat | **MASUK** | `dokum-ppdb.md` §9.2 | 7 berkas wajib, 3 opsional |
| Auto Akun Calon Santri & Email Kredensial | **MASUK** | `dokum-ppdb.md` §6.2, §8.2 | Password random 8 karakter |
| Dashboard Calon Santri & Unduh Berkas IDOR-Safe | **MASUK** | `dokum-ppdb.md` §5.2, §6.4 | Tracker status & file stream |
| Admin PPDB Dashboard & Gender Ratio Chart | **MASUK** | `dokum-ppdb.md` §2.3, §5.3 | Doughnut chart Chart.js |
| Verifikasi Status Pendaftar & Notifikasi WA Fonnte | **MASUK** | `dokum-ppdb.md` §8.1 | POST api.fonnte.com/send |
| Broadcast WhatsApp Massal Fonnte | **MASUK** | `dokum-ppdb.md` §8.1 | Kirim pesan WA ke filter status |
| Unduh Berkas Pendaftar ZIP Tunggal | **MASUK** | `dokum-ppdb.md` §9.3 | ZipArchive + deleteFileAfterSend |
| Ekspor Rekapitulasi PPDB (Excel & PDF) | **MASUK** | `dokum-ppdb.md` §5.3, §7.3 | PendaftarExport & Dompdf landscape |
| Kontrol Jadwal Form & CMS Admin | **MASUK** | `dokum-ppdb.md` §5.4 | Pengaturan tanggal buka/tutup & CRUD CMS |
| Manajemen Akun Admin | **MASUK** | `dokum-ppdb.md` §5.3 Q9 | Cegah hapus diri sendiri |
| Promosi Pendaftar Lulus ke Santri Akademik | **MASUK** | `dokum-ppdb.md` §1.2, `dokum-cp1.md` §4 | Pendaftar Lulus -> Santri pondok |
| SubSistem Impor Excel Santri (`ImportController`, `ppdb_import_logs`, dll.) | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #1) | **DILARANG** diimplementasikan |
| SubSistem NIS State Machine (`NisAssignmentService`, status `draft`) | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #2) | **DILARANG** diimplementasikan |
| SubSistem Audit Logging (`system_audit_logs`, `AuditLogger`) | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #3) | **DILARANG** diimplementasikan |
| SubSistem Bridge DB Eksternal (`ppdb_sync_logs`, `PpdbSyncSantri`) | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #4) | **DILARANG** diimplementasikan |
| Tabel `quran_surahs` & Rumus Indeks 6.236 Ayat | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #5) | **DILARANG** diimplementasikan |
| Aturan Batas Edit 24 Jam Setoran/Murojaah | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #6) | **DILARANG** diimplementasikan |
| Batasan Jam Geofence (05:00-21:00) & Batas Akurasi 20m | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #7) | **DILARANG** diimplementasikan |
| Kolom `users.is_active` & Middleware `EnsureUserActive` | **KELUAR (PRUNED)** | Extraneous (Survey 2 §4 #8) | **DILARANG** diimplementasikan |

---

## 2. Ground Truth & Keputusan Arsitektur Terpadu

### 2.1 Ground Truth Terpadu
1. **Framework & Runtime**: PHP 8.3+, Laravel 11/13 monolitik, Spatie Laravel Permission untuk RBAC.
2. **Database Engine**: Relasional (MySQL / SQLite / PostgreSQL). Seluruh migrasi database dibuat agnostik terhadap dialek SQL spesifik driver sehingga berjalan sempurna baik pada SQLite (lokal/test) maupun MySQL (produksi).
3. **Penyimpanan Berkas**: Berkas publik (banner carousel) di `public` storage disk (`storage/app/public/carousel/`); berkas sensitif pendaftaran (10 dokumen pendaftar) di `local` private storage (`storage/app/private/pendaftaran/{field}/`) yang hanya dapat diakses melalui controller dengan proteksi otorisasi IDOR.
4. **NIS Santri**: Kolom `santri.nis` adalah `VARCHAR(50) UNIQUE NOT NULL`. Mendukung format nomor induk pesantren standar, termasuk angka 7 digit seperti `'2026001'` yang tercantum pada seeder resmi `dokum-cp1.md`.
5. **Geofence Presensi**: Pusat koordinat pesantren `-7.797068, 110.370529`, radius validasi maksimum `150.0` meter menggunakan kalkulasi Great-Circle Haversine murni. Presensi ustadz mencatat status `valid` jika jarak <= 150 meter, dan `invalid` jika > 150 meter.
6. **Integrasi WhatsApp**: Menggunakan provider Fonnte API (`https://api.fonnte.com/send`) melalui HTTP client resmi Laravel (`Http::withHeaders(...)`). Kredensial token dikelola via `.env` (`FONNTE_TOKEN`).
7. **Email Transaksional**: Menggunakan Laravel Mail (`Illuminate\Support\Facades\Mail`) dengan mailable `RegisterSuccessMail`. Pada environment lokal/test menggunakan driver `log` atau `array`.

### 2.2 Keputusan Arsitektur Terpadu (D-xx)
- **D-01 (Unified Monolith)**: Menggabungkan modul CP1 dan modul PPDB dalam satu instalasi Laravel monolitik terpadu di direktori proyek ini.
- **D-02 (4 Spatie Roles)**: Menetapkan 4 role resmi pada guard `web`:
  1. `admin`: Pengelola master data, penugasan, laporan akademik, verifikasi PPDB, WhatsApp, ekspor berkas, dan CMS.
  2. `ustadz`: Pengajar tahfidz yang melakukan presensi GPS, presensi santri per kegiatan, dan input setoran/murojaah santri binaan.
  3. `wali_murid`: Orang tua yang memantau rekapitulasi capaian hafalan dan kehadiran santri miliknya.
  4. `calon_santri`: Calon peserta didik baru yang memantau kelulusan seleksi PPDB dan mengunduh berkas pendaftarannya.
- **D-03 (Driver-Agnostic Migrations)**: Seluruh tabel dirancang menggunakan schema builder standar Laravel tanpa raw SQL yang bergantung pada MySQL atau PostgreSQL tertentu.
- **D-04 (Authentic Promotion Flow)**: Pendaftar yang telah diverifikasi dengan status `Lulus` dapat dipromosikan oleh admin ke tabel `santri` melalui aksi `POST /admin/pendaftar/{id}/promosikan`, mengisi nama, NIS pondok yang diinput admin, tingkatan, kelas, dan menghubungkan foreign key wali murid bila ada.
- **D-05 (IDOR Protection)**: Pengunduhan berkas calon santri pada `/user/pendaftar/{id}/file/{field}` memverifikasi kepemilikan data: `pendaftaran->user_id === auth()->id()`. Pelanggaran langsung menghasilkan HTTP 403 Forbidden.
- **D-06 (Automated Account Generation)**: Saat formulir pendaftaran berhasil disubmit publik, sistem secara transaksional membuat record `User` dengan email pendaftar, random password 8 karakter (`Str::random(8)`), memberikan role `calon_santri`, menghubungkan `pendaftarans.user_id = $user->id`, mengirim email kredensial, dan menampilkan kredensial satu kali pada halaman konfirmasi `/register-success`.
- **D-07 (ZIP Archive Packaging)**: Admin dapat mengunduh seluruh 10 berkas pendaftar dalam bentuk `.zip` tunggal menggunakan `ZipArchive` PHP dengan pembersihan otomatis file temporary via `response()->download($zipPath)->deleteFileAfterSend(true)`.

---

## 3. Arsitektur, Lapisan & Struktur Direktori

### 3.1 Lapisan Sistem (Clean Monolith)

```
[Web Browser / Klien]
        │
[HTTP Routing (routes/web.php, routes/auth.php)]
        │  (Middleware: auth, role, check.form.status)
[Form Requests (Validasi ketat & otorisasi)]
        │
[Controllers (Tipis, orkestrasi & respon HTTP)]
        │
[Application Services (Logika Bisnis Inti)]
  - GeolocationService (Haversine 150m)
  - FonnteService (WhatsApp API)
  - PendaftarPromotionService (Lulus -> Santri)
  - ZipArchiveService (Packaging dokumen)
  - DocumentStorageService (Upload privat 10 berkas)
  - ReportService (Query rekapitulasi & ekspor)
        │
[Domain Models, Policies & Spatie RBAC]
        │
[Database Migrations & Relational Schema (16 Tabel Domain)]
```

### 3.2 Struktur Direktori Proyek

```
capstone-2/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── SantriController.php
│   │   │   │   ├── UstadzController.php
│   │   │   │   ├── WaliMuridController.php
│   │   │   │   ├── KegiatanPondokController.php
│   │   │   │   ├── UstadzKegiatanController.php
│   │   │   │   ├── UstadzSantriController.php
│   │   │   │   ├── LaporanController.php
│   │   │   │   ├── PpdbController.php
│   │   │   │   ├── FormScheduleController.php
│   │   │   │   ├── CmsController.php
│   │   │   │   └── AdminAkunController.php
│   │   │   ├── Auth/ (Breeze standard suite)
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── VerifyEmailController.php
│   │   │   │   └── PasswordController.php
│   │   │   ├── Ppdb/
│   │   │   │   ├── PublicPpdbController.php
│   │   │   │   ├── RegistrationController.php
│   │   │   │   └── ApplicantDashboardController.php
│   │   │   ├── Ustadz/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── PresensiKegiatanController.php
│   │   │   │   ├── PresensiSantriController.php
│   │   │   │   ├── SetoranHafalanController.php
│   │   │   │   └── MurojaahController.php
│   │   │   ├── WaliMurid/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── LaporanMingguanController.php
│   │   │   │   └── RiwayatKegiatanController.php
│   │   │   └── ProfileController.php
│   │   ├── Middleware/
│   │   │   └── CheckFormStatus.php
│   │   └── Requests/
│   │       ├── Admin/*.php
│   │       ├── Ppdb/*.php
│   │       └── Ustadz/*.php
│   ├── Mail/
│   │   └── RegisterSuccessMail.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Santri.php
│   │   ├── KegiatanPondok.php
│   │   ├── PresensiKegiatan.php
│   │   ├── PresensiSantri.php
│   │   ├── SetoranHafalan.php
│   │   ├── Murojaah.php
│   │   ├── Pendaftaran.php
│   │   ├── StatusForm.php
│   │   ├── Informasi.php
│   │   ├── Kontak.php
│   │   ├── Statistik.php
│   │   ├── Carousel.php
│   │   └── Keunggulan.php
│   ├── Policies/
│   │   ├── SantriPolicy.php
│   │   ├── PresensiKegiatanPolicy.php
│   │   ├── PresensiSantriPolicy.php
│   │   ├── SetoranHafalanPolicy.php
│   │   ├── MurojaahPolicy.php
│   │   └── PendaftaranPolicy.php
│   └── Services/
│       ├── GeolocationService.php
│       ├── FonnteService.php
│       ├── PendaftarPromotionService.php
│       ├── ZipArchiveService.php
│       ├── DocumentStorageService.php
│       └── ReportService.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── pptq.php
│   └── services.php
├── database/
│   ├── migrations/ (16 Canonical Domain Migrations)
│   ├── seeders/
│   │   ├── RoleSeeder.php
│   │   ├── AdminUserSeeder.php
│   │   ├── CmsSeeder.php
│   │   ├── DemoPesantrenSeeder.php
│   │   └── DatabaseSeeder.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── public/ (Landing page CMS, Informasi, Kontak, Form Pendaftaran)
│   │   ├── user/ (Dashboard calon santri)
│   │   ├── admin/ (Dashboard, Santri, Ustadz, Wali, Kegiatan, Penugasan, Laporan, PPDB, CMS)
│   │   ├── ustadz/ (Dashboard, Presensi, Presensi Santri, Setoran, Murojaah)
│   │   ├── wali/ (Dashboard, Laporan Mingguan, Riwayat Kehadiran)
│   │   └── auth/ (Breeze authentication views)
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   ├── web.php
│   ├── auth.php
│   └── console.php
└── tests/
    ├── Feature/
    └── Unit/
```

---

## 4. Skema Data Terpadu (16 Tabel Domain)

Sistem mengelola 16 tabel domain (9 tabel Administrasi PPTQ + 7 tabel PPDB Online & CMS) ditambah 5 tabel Spatie Permission dan tabel framework standar Laravel.

### 4.1 Tabel Administrasi PPTQ (`dokum-cp1.md`)

#### 1. `users`
Tabel akun untuk seluruh aktor (Admin, Ustadz, Wali Murid, Calon Santri).
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(255) NOT NULL
- `email`: VARCHAR(255) NOT NULL UNIQUE
- `email_verified_at`: TIMESTAMP NULL
- `password`: VARCHAR(255) NOT NULL
- `remember_token`: VARCHAR(100) NULL
- `timestamps`

#### 2. `santri`
Data santri aktif yang menempuh pendidikan di pondok pesantren.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `nama`: VARCHAR(255) NOT NULL
- `nis`: VARCHAR(50) NOT NULL UNIQUE *(Mendukung format angka 7 digit seperti '2026001')*
- `tingkatan`: ENUM('tsanawiyah', 'aliyah', 'takhassus') NOT NULL DEFAULT 'tsanawiyah'
- `kelas`: VARCHAR(50) NULL
- `wali_murid_id`: BIGINT UNSIGNED NULL -> FK ke `users.id` (ON DELETE SET NULL)
- `tanggal_lahir`: DATE NULL
- `timestamps`
- *Index:* `(tingkatan, kelas)`, `(wali_murid_id)`

#### 3. `kegiatan_pondok`
Daftar kegiatan halaqah, sholat berjamaah, kajian kitab, dan tahfidz.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `nama_kegiatan`: VARCHAR(255) NOT NULL
- `deskripsi`: TEXT NULL
- `tingkatan`: ENUM('semua', 'tsanawiyah', 'aliyah', 'takhassus') NOT NULL DEFAULT 'semua'
- `kelas`: VARCHAR(50) NULL
- `timestamps`

#### 4. `ustadz_kegiatan` (Pivot Penugasan Kegiatan)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `kegiatan_pondok_id`: BIGINT UNSIGNED NOT NULL -> FK ke `kegiatan_pondok.id` (ON DELETE CASCADE)
- `created_at`: TIMESTAMP NOT NULL
- *Unique Constraint:* `UNIQUE(user_id, kegiatan_pondok_id)`

#### 5. `ustadz_santri` (Pivot Santri Binaan Tahfidz)
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `santri_id`: BIGINT UNSIGNED NOT NULL -> FK ke `santri.id` (ON DELETE CASCADE)
- `created_at`: TIMESTAMP NOT NULL
- *Unique Constraint:* `UNIQUE(user_id, santri_id)`

#### 6. `presensi_kegiatan`
Sesi presensi kehadiran ustadz pada kegiatan pondok dengan koordinat GPS.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `kegiatan_pondok_id`: BIGINT UNSIGNED NOT NULL -> FK ke `kegiatan_pondok.id` (ON DELETE CASCADE)
- `ustadz_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `tanggal`: DATE NOT NULL
- `hari`: VARCHAR(20) NOT NULL
- `jam_mulai`: TIME NOT NULL
- `jam_selesai`: TIME NULL
- `materi`: TEXT NULL
- `latitude`: DECIMAL(10, 7) NULL
- `longitude`: DECIMAL(10, 7) NULL
- `status`: ENUM('valid', 'invalid', 'pending') NOT NULL DEFAULT 'pending'
- `created_at`: TIMESTAMP NOT NULL
- `updated_at`: TIMESTAMP NULL
- *Unique Constraint:* `UNIQUE(kegiatan_pondok_id, ustadz_id, tanggal)`

#### 7. `presensi_santri`
Kehadiran santri per sesi kegiatan pondok.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `presensi_kegiatan_id`: BIGINT UNSIGNED NOT NULL -> FK ke `presensi_kegiatan.id` (ON DELETE CASCADE)
- `santri_id`: BIGINT UNSIGNED NOT NULL -> FK ke `santri.id` (ON DELETE CASCADE)
- `status`: ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'alpha'
- `created_at`: TIMESTAMP NOT NULL
- `updated_at`: TIMESTAMP NULL
- *Unique Constraint:* `UNIQUE(presensi_kegiatan_id, santri_id)`

#### 8. `setoran_hafalan`
Capaian setoran penambahan hafalan baru (ziyadah).
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `santri_id`: BIGINT UNSIGNED NOT NULL -> FK ke `santri.id` (ON DELETE CASCADE)
- `ustadz_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `tanggal`: DATE NOT NULL
- `juz`: INT NOT NULL *(Nilai 1-30)*
- `surat`: VARCHAR(100) NOT NULL *(Nama surat, e.g. 'Al-Baqarah')*
- `ayat`: VARCHAR(50) NOT NULL *(Rentang ayat, e.g. '1-25')*
- `nilai`: DECIMAL(5, 2) NULL *(Skala 0-100)*
- `status_selesai`: BOOLEAN NOT NULL DEFAULT 0 *(1 jika tuntas, 0 jika perlu diulang)*
- `catatan`: TEXT NULL
- `created_at`: TIMESTAMP NOT NULL
- `updated_at`: TIMESTAMP NULL
- *Index:* `(santri_id, tanggal)`, `(ustadz_id, tanggal)`

#### 9. `murojaah`
Riwayat pengulangan dan pemeliharaan kelancaran hafalan santri.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `santri_id`: BIGINT UNSIGNED NOT NULL -> FK ke `santri.id` (ON DELETE CASCADE)
- `ustadz_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `tanggal`: DATE NOT NULL
- `juz`: INT NOT NULL
- `surat`: VARCHAR(100) NOT NULL
- `ayat`: VARCHAR(50) NOT NULL
- `nilai`: DECIMAL(5, 2) NULL
- `status_selesai`: BOOLEAN NOT NULL DEFAULT 0
- `created_at`: TIMESTAMP NOT NULL
- `updated_at`: TIMESTAMP NULL
- *Index:* `(santri_id, tanggal)`, `(ustadz_id, tanggal)`

---

### 4.2 Tabel PPDB Online & Landing CMS (`dokum-ppdb.md`)

#### 10. `pendaftarans`
Data lengkap 41 kolom formulir pendaftaran calon santri, orang tua, sekolah asal, berkas, dan status verifikasi.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id`: BIGINT UNSIGNED NOT NULL -> FK ke `users.id` (ON DELETE CASCADE)
- `nama_lengkap`: VARCHAR(255) NOT NULL
- `jenis_kelamin`: ENUM('Laki-laki', 'Perempuan') NOT NULL
- `email`: VARCHAR(255) NOT NULL
- `no_hp`: VARCHAR(20) NOT NULL
- `alamat`: TEXT NOT NULL
- `tempat_lahir`: VARCHAR(100) NULL
- `tanggal_lahir`: DATE NULL
- `nisn`: VARCHAR(10) NULL
- `nik_santri`: VARCHAR(255) NULL
- `no_kk`: VARCHAR(255) NULL
- `nama_ayah`: VARCHAR(255) NULL
- `nik_ayah`: VARCHAR(255) NULL
- `pekerjaan_ayah`: VARCHAR(100) NULL
- `penghasilan_ayah`: VARCHAR(255) NULL
- `no_hp_ayah`: VARCHAR(255) NULL
- `nama_ibu`: VARCHAR(255) NULL
- `nik_ibu`: VARCHAR(255) NULL
- `pekerjaan_ibu`: VARCHAR(100) NULL
- `penghasilan_ibu`: VARCHAR(255) NULL
- `no_hp_ibu`: VARCHAR(255) NULL
- `asal_sekolah`: VARCHAR(255) NULL
- `alamat_asal_sekolah`: VARCHAR(255) NULL
- `jumlah_hafalan`: VARCHAR(100) NULL
- `prestasi`: VARCHAR(255) NULL
- `pas_photo`: VARCHAR(255) NULL
- `bukti_transfer`: VARCHAR(255) NULL
- `formulir_pendaftaran`: VARCHAR(255) NULL
- `surat_pernyataan`: VARCHAR(255) NULL
- `akta_kelahiran`: VARCHAR(255) NULL
- `kartu_keluarga`: VARCHAR(255) NULL
- `ijazah_terakhir`: VARCHAR(255) NULL
- `raport_terakhir`: VARCHAR(255) NULL
- `skhun`: VARCHAR(255) NULL
- `sktm`: VARCHAR(255) NULL
- `berkas`: VARCHAR(255) NULL
- `status`: ENUM('Menunggu', 'Lulus', 'Tidak Lulus') NOT NULL DEFAULT 'Menunggu'
- `tanggal_daftar`: TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
- `santri_id`: BIGINT UNSIGNED NULL -> FK ke `santri.id` (ON DELETE SET NULL)
- `timestamps`
- *Index:* `(user_id)`, `(status)`, `(santri_id)`

#### 11. `status_forms`
Kontrol jadwal pembukaan dan penutupan form pendaftaran santri baru secara otomatis.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `status`: ENUM('Buka', 'Tutup') NOT NULL DEFAULT 'Buka'
- `tanggal_buka`: DATE NULL
- `tanggal_tutup`: DATE NULL
- `timestamps`

#### 12. `informasis`
Artikel informasi dan pengumuman seleksi pada portal publik.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `judul`: VARCHAR(255) NOT NULL
- `isi`: TEXT NOT NULL
- `timestamps`

#### 13. `kontaks`
Informasi kontak resmi dan alamat fisik lembaga pesantren.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `alamat`: VARCHAR(255) NOT NULL
- `telepon`: VARCHAR(20) NOT NULL
- `email`: VARCHAR(255) NOT NULL
- `timestamps`

#### 14. `statistiks`
Angka rekam jejak institusi untuk live counter di beranda.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `santri_aktif`: INT NOT NULL DEFAULT 110
- `asatidzah_bersanad`: INT NOT NULL DEFAULT 30
- `tahun_berkhidmat`: INT NOT NULL DEFAULT 13
- `timestamps`

#### 15. `carousels`
Daftar banner gambar slider di beranda publik.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `image`: VARCHAR(255) NOT NULL
- `caption`: VARCHAR(255) NULL
- `order`: INT NOT NULL DEFAULT 0
- `timestamps`

#### 16. `keunggulans`
Kartu poin-poin keunggulan pesantren di beranda publik.
- `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `judul`: VARCHAR(255) NOT NULL
- `deskripsi_singkat`: VARCHAR(255) NOT NULL
- `deskripsi_detail`: TEXT NOT NULL
- `order`: INT NOT NULL DEFAULT 0
- `timestamps`

---

## 5. RBAC 4-Role Terpadu (Spatie Laravel-Permission)

Sistem menggunakan 4 role pada guard `web`:
1. `admin`: Administrator pesantren dan panitia PPDB.
2. `ustadz`: Tenaga pengajar dan pembina tahfidz.
3. `wali_murid`: Orang tua/wali dari santri terdaftar.
4. `calon_santri`: Calon peserta didik baru yang telah mendaftar melalui web form.

### 5.1 Matriks Izin & Otorisasi Lengkap
| Fitur / Kemampuan | admin | ustadz | wali_murid | calon_santri | Catatan Otorisasi |
|---|:---:|:---:|:---:|:---:|---|
| Akses Dashboard Peran | ✅ | ✅ | ✅ | ✅ | Masing-masing diarahkan ke dashboard role miliknya |
| Akses Beranda Publik & CMS | ✅ | ✅ | ✅ | ✅ | Rute publik terbuka untuk tamu dan seluruh role |
| Form Pendaftaran PPDB | Publik | ❌ | ❌ | ❌ | Tamu mengisi form; jika login dialihkan ke dashboard |
| Kelola Master Akademik (Santri, Ustadz, Wali, Kegiatan) | ✅ | ❌ | ❌ | ❌ | 403 untuk selain admin |
| Penugasan Ustadz & Santri Binaan | ✅ | ❌ | ❌ | ❌ | 403 untuk selain admin |
| Presensi Ustadz (Geolocation Haversine 150m) | ❌ | ✅ | ❌ | ❌ | Hanya ustadz yang ditugaskan pada kegiatan tersebut |
| Presensi Santri Bulk | ❌ | ✅ | ❌ | ❌ | Hanya ustadz pemilik sesi presensi kegiatan |
| Input Setoran Ziyadah & Murojaah | ❌ | ✅ | ❌ | ❌ | Hanya untuk santri binaan tahfidz ustadz tersebut |
| Lihat Laporan Global Akademik & Ekspor | ✅ | ❌ | ❌ | ❌ | Laporan presensi & tahfidz seluruh santri |
| Dashboard & Laporan Mingguan Wali Murid | ❌ | ❌ | ✅ | ❌ | Scoped strictly ke `santri.wali_murid_id == auth()->id()` |
| Riwayat Kegiatan Santri (Wali) | ❌ | ❌ | ✅ | ❌ | Scoped ke anak kandung/binaan wali terkait |
| Dashboard Calon Santri | ❌ | ❌ | ❌ | ✅ | Menampilkan status seleksi pendaftarannya |
| Unduh Dokumen Sendiri (IDOR Safe) | ❌ | ❌ | ❌ | ✅ | Strict check: `pendaftaran->user_id == auth()->id()` |
| Verifikasi Kelulusan PPDB | ✅ | ❌ | ❌ | ❌ | Admin mengubah status (`Menunggu`, `Lulus`, `Tidak Lulus`) |
| Promosikan Pendaftar Lulus ke Santri | ✅ | ❌ | ❌ | ❌ | Admin mentransfer biodata pendaftar ke tabel `santri` |
| Broadcast WhatsApp Massal via Fonnte | ✅ | ❌ | ❌ | ❌ | Admin mengirim pesan notifikasi seleksi |
| Unduh Berkas ZIP Pendaftar | ✅ | ❌ | ❌ | ❌ | Admin mengemas seluruh berkas 1 pendaftar ke ZIP |
| Ekspor Rekapitulasi PPDB (Excel & PDF) | ✅ | ❌ | ❌ | ❌ | Admin mengunduh rekapitulasi 25 kolom pendaftar |
| Kelola Jadwal Form & Konten CMS | ✅ | ❌ | ❌ | ❌ | Admin mengatur carousel, keunggulan, statistik, dsb. |
| Kelola Akun Administrator | ✅ | ❌ | ❌ | ❌ | Admin mengelola sesama admin (dicegah hapus diri sendiri) |

---

## 6. Kontrak Rute Lengkap

Seluruh rute sistem terdaftar secara deklaratif pada `routes/web.php` dan `routes/auth.php`.

### 6.1 Rute Publik, Tamu & Autentikasi Dasar
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/` | `home` | `Ppdb\PublicPpdbController@home` | `web` | Beranda portal publik dengan CMS dinamis |
| GET | `/informasi` | `informasi` | `Ppdb\PublicPpdbController@informasi` | `web` | Halaman artikel pengumuman & jadwal |
| GET | `/contact` | `contact` | `Ppdb\PublicPpdbController@contact` | `web` | Halaman informasi kontak resmi pondok |
| GET | `/pendaftaran` | `pendaftaran` | `Ppdb\RegistrationController@create` | `web`, `check.form.status` | Form pendaftaran calon santri online |
| POST | `/pendaftaran` | `pendaftaran.store` | `Ppdb\RegistrationController@store` | `web` | Submit pendaftaran, upload berkas, auto akun |
| GET | `/register-success` | `register.success` | `Ppdb\RegistrationController@success` | `web` | Flash kredensial akun calon santri |
| GET | `/dashboard` | `dashboard` | `DashboardController@index` | `auth` | Dispatcher redirect otomatis sesuai role |
| GET | `/login` | `login` | `Auth\AuthenticatedSessionController@create` | `guest` | Form login Breeze |
| POST | `/login` | `login.store` | `Auth\AuthenticatedSessionController@store` | `guest` | Proses autentikasi email & password |
| POST | `/logout` | `logout` | `Auth\AuthenticatedSessionController@destroy` | `auth` | Menghancurkan session login |
| GET | `/register` | `register` | `Auth\RegisteredUserController@create` | `guest` | Form registrasi standar Breeze |
| POST | `/register` | `register.store` | `Auth\RegisteredUserController@store` | `guest` | Proses registrasi user |
| GET | `/forgot-password` | `password.request` | `Auth\PasswordResetLinkController@create` | `guest` | Form permohonan link reset password |
| POST | `/forgot-password` | `password.email` | `Auth\PasswordResetLinkController@store` | `guest` | Kirim link reset password ke email |
| GET | `/reset-password/{token}` | `password.reset` | `Auth\NewPasswordController@create` | `guest` | Form ubah password dari token |
| POST | `/reset-password` | `password.store` | `Auth\NewPasswordController@store` | `guest` | Simpan password baru |
| GET | `/verify-email` | `verification.notice` | `Auth\EmailVerificationPromptController` | `auth` | Tampilan pengingat verifikasi email |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `Auth\VerifyEmailController` | `auth`, `signed`, `throttle:6,1` | Verifikasi token tautan email |
| POST | `/email/verification-notification` | `verification.send` | `Auth\EmailVerificationNotificationController@store` | `auth`, `throttle:6,1` | Kirim ulang email verifikasi |
| GET | `/confirm-password` | `password.confirm` | `Auth\ConfirmablePasswordController@show` | `auth` | Konfirmasi sandi sebelum aksi sensitif |
| POST | `/confirm-password` | — | `Auth\ConfirmablePasswordController@store` | `auth` | Verifikasi sandi saat konfirmasi |
| PUT | `/password` | `password.update` | `Auth\PasswordController@update` | `auth` | Ganti password dari dalam profil |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | `auth` | Form edit profil pengguna |
| PATCH | `/profile` | `profile.update` | `ProfileController@update` | `auth` | Update data nama & email profil |
| DELETE | `/profile` | `profile.destroy` | `ProfileController@destroy` | `auth` | Hapus akun pengguna |
| GET | `/up` | — | Framework Health Check | — | Status liveness framework |

---

### 6.2 Rute Calon Santri (`role:calon_santri`)
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/user/dashboard` | `user.dashboard` | `Ppdb\ApplicantDashboardController@index` | `auth`, `role:calon_santri` | Dashboard status verifikasi pendaftar |
| GET | `/user/pendaftar/{id}/file/{field}` | `user.file` | `Ppdb\ApplicantDashboardController@downloadFile` | `auth`, `role:calon_santri` | Unduh berkas milik sendiri (IDOR-safe) |

---

### 6.3 Rute Admin: Akademik & Laporan (`role:admin`)
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/admin/dashboard` | `admin.dashboard` | `Admin\DashboardController@index` | `auth`, `role:admin` | Dashboard statistik akademik |
| GET | `/admin/santri` | `admin.santri.index` | `Admin\SantriController@index` | `auth`, `role:admin` | Daftar master santri |
| GET | `/admin/santri/create` | `admin.santri.create` | `Admin\SantriController@create` | `auth`, `role:admin` | Form tambah santri |
| POST | `/admin/santri` | `admin.santri.store` | `Admin\SantriController@store` | `auth`, `role:admin` | Simpan santri baru dengan NIS |
| GET | `/admin/santri/{santri}` | `admin.santri.show` | `Admin\SantriController@show` | `auth`, `role:admin` | Detail profil santri |
| GET | `/admin/santri/{santri}/edit` | `admin.santri.edit` | `Admin\SantriController@edit` | `auth`, `role:admin` | Form edit data santri |
| PUT | `/admin/santri/{santri}` | `admin.santri.update` | `Admin\SantriController@update` | `auth`, `role:admin` | Update data santri |
| DELETE | `/admin/santri/{santri}` | `admin.santri.destroy` | `Admin\SantriController@destroy` | `auth`, `role:admin` | Hapus data santri |
| GET | `/admin/ustadz` | `admin.ustadz.index` | `Admin\UstadzController@index` | `auth`, `role:admin` | Daftar master ustadz |
| GET | `/admin/ustadz/create` | `admin.ustadz.create` | `Admin\UstadzController@create` | `auth`, `role:admin` | Form tambah ustadz |
| POST | `/admin/ustadz` | `admin.ustadz.store` | `Admin\UstadzController@store` | `auth`, `role:admin` | Simpan akun ustadz baru |
| GET | `/admin/ustadz/{ustadz}` | `admin.ustadz.show` | `Admin\UstadzController@show` | `auth`, `role:admin` | Detail profil ustadz & penugasan |
| GET | `/admin/ustadz/{ustadz}/edit` | `admin.ustadz.edit` | `Admin\UstadzController@edit` | `auth`, `role:admin` | Form edit data ustadz |
| PUT | `/admin/ustadz/{ustadz}` | `admin.ustadz.update` | `Admin\UstadzController@update` | `auth`, `role:admin` | Update akun ustadz |
| DELETE | `/admin/ustadz/{ustadz}` | `admin.ustadz.destroy` | `Admin\UstadzController@destroy` | `auth`, `role:admin` | Hapus akun ustadz |
| GET | `/admin/wali-murid` | `admin.wali-murid.index` | `Admin\WaliMuridController@index` | `auth`, `role:admin` | Daftar master wali murid |
| GET | `/admin/wali-murid/create` | `admin.wali-murid.create` | `Admin\WaliMuridController@create` | `auth`, `role:admin` | Form tambah wali murid |
| POST | `/admin/wali-murid` | `admin.wali-murid.store` | `Admin\WaliMuridController@store` | `auth`, `role:admin` | Simpan akun wali murid |
| GET | `/admin/wali-murid/{waliMurid}` | `admin.wali-murid.show` | `Admin\WaliMuridController@show` | `auth`, `role:admin` | Detail wali murid & daftar anak |
| GET | `/admin/wali-murid/{waliMurid}/edit` | `admin.wali-murid.edit` | `Admin\WaliMuridController@edit` | `auth`, `role:admin` | Form edit wali murid |
| PUT | `/admin/wali-murid/{waliMurid}` | `admin.wali-murid.update` | `Admin\WaliMuridController@update` | `auth`, `role:admin` | Update wali murid |
| DELETE | `/admin/wali-murid/{waliMurid}` | `admin.wali-murid.destroy` | `Admin\WaliMuridController@destroy` | `auth`, `role:admin` | Hapus wali murid |
| GET | `/admin/kegiatan` | `admin.kegiatan.index` | `Admin\KegiatanPondokController@index` | `auth`, `role:admin` | Daftar kegiatan pondok |
| GET | `/admin/kegiatan/create` | `admin.kegiatan.create` | `Admin\KegiatanPondokController@create` | `auth`, `role:admin` | Form tambah kegiatan |
| POST | `/admin/kegiatan` | `admin.kegiatan.store` | `Admin\KegiatanPondokController@store` | `auth`, `role:admin` | Simpan kegiatan pondok |
| GET | `/admin/kegiatan/{kegiatan}` | `admin.kegiatan.show` | `Admin\KegiatanPondokController@show` | `auth`, `role:admin` | Detail kegiatan pondok |
| GET | `/admin/kegiatan/{kegiatan}/edit` | `admin.kegiatan.edit` | `Admin\KegiatanPondokController@edit` | `auth`, `role:admin` | Form edit kegiatan pondok |
| PUT | `/admin/kegiatan/{kegiatan}` | `admin.kegiatan.update` | `Admin\KegiatanPondokController@update` | `auth`, `role:admin` | Update kegiatan pondok |
| DELETE | `/admin/kegiatan/{kegiatan}` | `admin.kegiatan.destroy` | `Admin\KegiatanPondokController@destroy` | `auth`, `role:admin` | Hapus kegiatan pondok |
| GET | `/admin/penugasan-kegiatan` | `admin.penugasan-kegiatan.index` | `Admin\UstadzKegiatanController@index` | `auth`, `role:admin` | Form sinkronisasi penugasan kegiatan |
| PUT | `/admin/penugasan-kegiatan/{ustadz}` | `admin.penugasan-kegiatan.update` | `Admin\UstadzKegiatanController@update` | `auth`, `role:admin` | Simpan relasi many-to-many ustadz kegiatan |
| GET | `/admin/santri-binaan` | `admin.santri-binaan.index` | `Admin\UstadzSantriController@index` | `auth`, `role:admin` | Form sinkronisasi santri binaan tahfidz |
| PUT | `/admin/santri-binaan/{ustadz}` | `admin.santri-binaan.update` | `Admin\UstadzSantriController@update` | `auth`, `role:admin` | Simpan relasi many-to-many ustadz santri |
| GET | `/admin/laporan/presensi-ustadz` | `admin.laporan.presensi-ustadz` | `Admin\LaporanController@presensiUstadz` | `auth`, `role:admin` | Laporan rekap kehadiran ustadz |
| GET | `/admin/laporan/presensi-santri` | `admin.laporan.presensi-santri` | `Admin\LaporanController@presensiSantri` | `auth`, `role:admin` | Laporan rekap kehadiran santri |
| GET | `/admin/laporan/hafalan` | `admin.laporan.hafalan` | `Admin\LaporanController@hafalan` | `auth`, `role:admin` | Laporan rekap setoran ziyadah |
| GET | `/admin/laporan/murojaah` | `admin.laporan.murojaah` | `Admin\LaporanController@murojaah` | `auth`, `role:admin` | Laporan rekap kelancaran murojaah |
| GET | `/admin/laporan/{laporan}/export/{format}` | `admin.laporan.export` | `Admin\LaporanController@export` | `auth`, `role:admin` | Ekspor file PDF landscape / XLSX |

---

### 6.4 Rute Admin: PPDB, WhatsApp, Ekspor & CMS (`role:admin`)
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/admin/ppdb` | `admin.ppdb.index` | `Admin\PpdbController@index` | `auth`, `role:admin` | Dashboard PPDB & tabel seluruh pendaftar |
| GET | `/admin/pendaftar/{id}` | `admin.detail` | `Admin\PpdbController@show` | `auth`, `role:admin` | Detail biodata pendaftar & preview berkas |
| GET | `/admin/pendaftar/{id}/file/{field}` | `admin.file` | `Admin\PpdbController@downloadFile` | `auth`, `role:admin` | Unduh salah satu berkas pendaftar |
| POST | `/admin/pendaftar/{id}/verifikasi` | `admin.verifikasi` | `Admin\PpdbController@verifikasi` | `auth`, `role:admin` | Ubah status pendaftar & kirim notifikasi WA |
| POST | `/admin/pendaftar/{id}/promosikan` | `admin.pendaftar.promosikan` | `Admin\PpdbController@promosikan` | `auth`, `role:admin` | Promosi pendaftar Lulus ke Santri Akademik |
| GET | `/admin/pendaftar/{id}/download-zip` | `admin.download-zip` | `Admin\PpdbController@downloadZip` | `auth`, `role:admin` | Kemas & unduh seluruh 10 berkas ke ZIP |
| DELETE | `/admin/pendaftar/{id}` | `admin.destroy` | `Admin\PpdbController@destroy` | `auth`, `role:admin` | Hapus 1 pendaftar, file, dan akun usernya |
| DELETE | `/admin/pendaftar/hapus-semua` | `admin.destroyAll` | `Admin\PpdbController@destroyAll` | `auth`, `role:admin` | Purge transaksi seluruh data pendaftar |
| GET | `/admin/export/excel` | `admin.export.excel` | `Admin\PpdbController@exportExcel` | `auth`, `role:admin` | Ekspor rekap pendaftar ke spreadsheet Excel |
| GET | `/admin/export/pdf` | `admin.export.pdf` | `Admin\PpdbController@exportPDF` | `auth`, `role:admin` | Ekspor rekap pendaftar ke PDF A4 Landscape |
| GET | `/admin/notifikasi` | `admin.notifikasi` | `Admin\PpdbController@notifikasi` | `auth`, `role:admin` | Halaman form broadcast WhatsApp massal |
| POST | `/admin/notifikasi` | `admin.kirimNotifikasi` | `Admin\PpdbController@kirimNotifikasi` | `auth`, `role:admin` | Eksekusi broadcast WA via Fonnte API |
| GET | `/admin/status-form` | `admin.statusForm` | `Admin\FormScheduleController@index` | `auth`, `role:admin` | Pengaturan jadwal pembukaan form PPDB |
| POST | `/admin/status-form` | `admin.updateStatusForm` | `Admin\FormScheduleController@update` | `auth`, `role:admin` | Simpan tanggal buka & tutup pendaftaran |
| GET | `/admin/carousel` | `admin.carousel.index` | `Admin\CmsController@carouselIndex` | `auth`, `role:admin` | Daftar banner carousel beranda |
| GET | `/admin/carousel/create` | `admin.carousel.create` | `Admin\CmsController@carouselCreate` | `auth`, `role:admin` | Form upload banner carousel |
| POST | `/admin/carousel` | `admin.carousel.store` | `Admin\CmsController@carouselStore` | `auth`, `role:admin` | Simpan banner carousel baru |
| GET | `/admin/carousel/{id}/edit` | `admin.carousel.edit` | `Admin\CmsController@carouselEdit` | `auth`, `role:admin` | Form edit caption & urutan banner |
| PUT | `/admin/carousel/{id}` | `admin.carousel.update` | `Admin\CmsController@carouselUpdate` | `auth`, `role:admin` | Update gambar / urutan banner |
| DELETE | `/admin/carousel/{id}` | `admin.carousel.destroy` | `Admin\CmsController@carouselDestroy` | `auth`, `role:admin` | Hapus gambar banner carousel |
| GET | `/admin/keunggulan` | `admin.keunggulan.index` | `Admin\CmsController@keunggulanIndex` | `auth`, `role:admin` | Daftar poin keunggulan |
| GET | `/admin/keunggulan/create` | `admin.keunggulan.create` | `Admin\CmsController@keunggulanCreate` | `auth`, `role:admin` | Form tambah poin keunggulan |
| POST | `/admin/keunggulan` | `admin.keunggulan.store` | `Admin\CmsController@keunggulanStore` | `auth`, `role:admin` | Simpan poin keunggulan baru |
| GET | `/admin/keunggulan/{id}/edit` | `admin.keunggulan.edit` | `Admin\CmsController@keunggulanEdit` | `auth`, `role:admin` | Form edit poin keunggulan |
| PUT | `/admin/keunggulan/{id}` | `admin.keunggulan.update` | `Admin\CmsController@keunggulanUpdate` | `auth`, `role:admin` | Update poin keunggulan |
| DELETE | `/admin/keunggulan/{id}` | `admin.keunggulan.destroy` | `Admin\CmsController@keunggulanDestroy` | `auth`, `role:admin` | Hapus data poin keunggulan |
| GET | `/admin/statistik` | `admin.statistik.edit` | `Admin\CmsController@statistikEdit` | `auth`, `role:admin` | Form update angka live counter statistik |
| PUT | `/admin/statistik` | `admin.statistik.update` | `Admin\CmsController@statistikUpdate` | `auth`, `role:admin` | Simpan update statistik |
| GET | `/admin/informasi` | `admin.informasi.index` | `Admin\CmsController@informasiIndex` | `auth`, `role:admin` | Daftar pengumuman seleksi |
| GET | `/admin/informasi/create` | `admin.informasi.create` | `Admin\CmsController@informasiCreate` | `auth`, `role:admin` | Form tambah pengumuman baru |
| POST | `/admin/informasi` | `admin.informasi.store` | `Admin\CmsController@informasiStore` | `auth`, `role:admin` | Simpan pengumuman seleksi |
| GET | `/admin/informasi/{id}/edit` | `admin.informasi.edit` | `Admin\CmsController@informasiEdit` | `auth`, `role:admin` | Form edit pengumuman |
| PUT | `/admin/informasi/{id}` | `admin.informasi.update` | `Admin\CmsController@informasiUpdate` | `auth`, `role:admin` | Simpan perubahan pengumuman |
| DELETE | `/informasi/{id}` | `admin.informasi.destroy` | `Admin\CmsController@informasiDestroy` | `auth`, `role:admin` | Hapus pengumuman seleksi |
| GET | `/admin/kontak` | `admin.kontak` | `Admin\CmsController@kontakEdit` | `auth`, `role:admin` | Form pengaturan kontak resmi pesantren |
| PUT | `/admin/kontak` | `admin.kontak.update` | `Admin\CmsController@kontakUpdate` | `auth`, `role:admin` | Simpan update kontak resmi |
| GET | `/admin/akun` | `admin.akun.index` | `Admin\AdminAkunController@index` | `auth`, `role:admin` | Daftar akun administrator |
| GET | `/admin/akun/create` | `admin.akun.create` | `Admin\AdminAkunController@create` | `auth`, `role:admin` | Form registrasi admin baru |
| POST | `/admin/akun` | `admin.akun.store` | `Admin\AdminAkunController@store` | `auth`, `role:admin` | Simpan admin baru |
| GET | `/admin/akun/{id}/edit` | `admin.akun.edit` | `Admin\AdminAkunController@edit` | `auth`, `role:admin` | Form edit nama & email admin |
| PUT | `/admin/akun/{id}` | `admin.akun.update` | `Admin\AdminAkunController@update` | `auth`, `role:admin` | Simpan perubahan data admin |
| DELETE | `/admin/akun/{id}` | `admin.akun.destroy` | `Admin\AdminAkunController@destroy` | `auth`, `role:admin` | Hapus akun admin (Cegah self-deletion) |
| GET | `/akun/{id}/reset-password` | `admin.akun.reset` | `Admin\AdminAkunController@resetPasswordForm` | `auth`, `role:admin` | Form reset password admin |
| POST | `/akun/{id}/reset-password` | `admin.akun.reset.update` | `Admin\AdminAkunController@resetPasswordUpdate` | `auth`, `role:admin` | Simpan password baru admin |

---

### 6.5 Rute Ustadz (`role:ustadz`)
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/ustadz/dashboard` | `ustadz.dashboard` | `Ustadz\DashboardController@index` | `auth`, `role:ustadz` | Dashboard ustadz & jadwal kegiatan |
| GET | `/ustadz/presensi-kegiatan` | `ustadz.presensi-kegiatan.index` | `Ustadz\PresensiKegiatanController@index` | `auth`, `role:ustadz` | Riwayat presensi GPS diri sendiri |
| GET | `/ustadz/presensi-kegiatan/create` | `ustadz.presensi-kegiatan.create` | `Ustadz\PresensiKegiatanController@create` | `auth`, `role:ustadz` | Form presensi GPS pada kegiatan diampu |
| POST | `/ustadz/presensi-kegiatan` | `ustadz.presensi-kegiatan.store` | `Ustadz\PresensiKegiatanController@store` | `auth`, `role:ustadz` | Submit koordinat & hitung jarak Haversine |
| GET | `/ustadz/presensi-kegiatan/{presensiKegiatan}` | `ustadz.presensi-kegiatan.show` | `Ustadz\PresensiKegiatanController@show` | `auth`, `role:ustadz` | Detail sesi presensi kegiatan |
| GET | `/ustadz/presensi-santri/{presensiKegiatan}/create` | `ustadz.presensi-santri.create` | `Ustadz\PresensiSantriController@create` | `auth`, `role:ustadz` | Form presensi santri peserta kegiatan |
| POST | `/ustadz/presensi-santri/{presensiKegiatan}` | `ustadz.presensi-santri.store` | `Ustadz\PresensiSantriController@store` | `auth`, `role:ustadz` | Simpan massal kehadiran santri |
| GET | `/ustadz/setoran-hafalan` | `ustadz.setoran-hafalan.index` | `Ustadz\SetoranHafalanController@index` | `auth`, `role:ustadz` | Daftar setoran ziyadah santri binaan |
| GET | `/ustadz/setoran-hafalan/create` | `ustadz.setoran-hafalan.create` | `Ustadz\SetoranHafalanController@create` | `auth`, `role:ustadz` | Form input setoran hafalan baru |
| POST | `/ustadz/setoran-hafalan` | `ustadz.setoran-hafalan.store` | `Ustadz\SetoranHafalanController@store` | `auth`, `role:ustadz` | Simpan capaian hafalan baru santri |
| GET | `/ustadz/setoran-hafalan/{setoranHafalan}` | `ustadz.setoran-hafalan.show` | `Ustadz\SetoranHafalanController@show` | `auth`, `role:ustadz` | Detail capaian setoran hafalan |
| GET | `/ustadz/murojaah` | `ustadz.murojaah.index` | `Ustadz\MurojaahController@index` | `auth`, `role:ustadz` | Daftar pengulangan hafalan santri binaan |
| GET | `/ustadz/murojaah/create` | `ustadz.murojaah.create` | `Ustadz\MurojaahController@create` | `auth`, `role:ustadz` | Form input pengulangan hafalan (murojaah) |
| POST | `/ustadz/murojaah` | `ustadz.murojaah.store` | `Ustadz\MurojaahController@store` | `auth`, `role:ustadz` | Simpan capaian murojaah santri |
| GET | `/ustadz/murojaah/{murojaah}` | `ustadz.murojaah.show` | `Ustadz\MurojaahController@show` | `auth`, `role:ustadz` | Detail capaian pengulangan murojaah |

---

### 6.6 Rute Wali Murid (`role:wali_murid`)
| Method | URI | Nama Route | Controller & Action | Middleware | Keterangan |
|---|---|---|---|---|---|
| GET | `/wali-murid/dashboard` | `wali-murid.dashboard` | `WaliMurid\DashboardController@index` | `auth`, `role:wali_murid` | Dashboard ringkasan hafalan anak |
| GET | `/wali-murid/laporan-mingguan` | `wali-murid.laporan-mingguan.index` | `WaliMurid\LaporanMingguanController@index` | `auth`, `role:wali_murid` | Rincian setoran hafalan mingguan anak |
| GET | `/wali-murid/riwayat-kegiatan` | `wali-murid.riwayat-kegiatan.index` | `WaliMurid\RiwayatKegiatanController@index` | `auth`, `role:wali_murid` | Log riwayat kehadiran kegiatan santri |

---

## 7. SubSistem PPDB Online, Registrasi, Upload Berkas & CMS

### 7.1 CMS Landing Page Publik
1. **Beranda (`GET /`)**:
   - Menampilkan banner Carousel dinamis dari tabel `carousels` dengan urutan `order` ascending.
   - Menampilkan kartu keunggulan dari tabel `keunggulans`.
   - Menampilkan live counter statistik institusi dari tabel `statistiks` (`santri_aktif`, `asatidzah_bersanad`, `tahun_berkhidmat`).
   - Menampilkan rincian kontak dan peta dari tabel `kontaks`.
2. **Halaman Pengumuman (`GET /informasi`)**:
   - Menampilkan daftar pengumuman seleksi dari tabel `informasis` terpaginasi.
3. **Halaman Kontak (`GET /contact`)**:
   - Menampilkan detail kontak resmi dan form pesan/lokasi pesantren.

### 7.2 Guard Jadwal Pendaftaran (`CheckFormStatus` Middleware)
- Route `GET /pendaftaran` dilindungi oleh middleware `app/Http/Middleware/CheckFormStatus.php`.
- Middleware memeriksa record tunggal pada `status_forms`:
  - Jika `status == 'Tutup'`, atau
  - Jika tanggal hari ini `< tanggal_buka` atau `> tanggal_tutup` (bila tanggal diset):
  - Sistem langsung mengalihkan (redirect) tamu kembali ke `/` dengan flash alert peringatan: *"Pendaftaran telah ditutup otomatis berdasarkan jadwal"*.

### 7.3 Multi-Upload 10 Berkas Dokumen ke Storage Privat
Form pendaftaran menerima input 10 berkas dokumen pendaftaran yang divalidasi ketat (MIME: PDF, JPG, JPEG, PNG, max 2048 KB / 2 MB per berkas):
1. `pas_photo` (Wajib)
2. `bukti_transfer` (Wajib)
3. `formulir_pendaftaran` (Wajib)
4. `surat_pernyataan` (Wajib)
5. `akta_kelahiran` (Wajib)
6. `kartu_keluarga` (Wajib)
7. `raport_terakhir` (Wajib)
8. `ijazah_terakhir` (Opsional)
9. `skhun` (Opsional)
10. `sktm` (Opsional)

- Berkas disimpan menggunakan `DocumentStorageService` ke dalam direktori privat terisolasi: `storage/app/private/pendaftaran/{field}/`.
- **DILARANG** menyimpan berkas pendaftar di disk `public` demi perlindungan privasi dan PII data keluarga calon santri.

### 7.4 Pembuatan Akun Otomatis Calon Santri & Notifikasi Kredensial
Saat form pendaftaran disubmit:
1. Dijalankan dalam transaksi database (`DB::transaction`).
2. Dibuat password acak 8 karakter menggunakan `Str::random(8)`.
3. Dibuat akun pengguna baru pada `users`:
   - `name`: nama santri pendaftar
   - `email`: email pendaftar (wajib unik)
   - `password`: `Hash::make($randomPassword)`
   - Penetapan role: `$user->assignRole('calon_santri')`
4. Dibuat record pendaftaran pada `pendaftarans` dengan relasi `user_id = $user->id`, seluruh 41 field biodata, path berkas, dan `status = 'Menunggu'`.
5. Transaksional email `RegisterSuccessMail` dikirimkan ke email pendaftar berisi kredensial login (email dan plain-text password awal).
6. Pengguna diarahkan ke `/register-success` dengan flash session data untuk menampilkan informasi akun satu kali (one-time display).

### 7.5 Dashboard Calon Santri & Download Berkas IDOR-Safe
- Calon santri login dan mengakses `/user/dashboard`.
- Dashboard menampilkan status pendaftaran secara jelas dengan badge warna:
  - `Menunggu` (Badge Kuning)
  - `Lulus` (Badge Hijau)
  - `Tidak Lulus` (Badge Merah)
- Calon santri dapat mengunduh berkas unggahannya sendiri melalui `GET /user/pendaftar/{id}/file/{field}`.
- Otorisasi IDOR: Controller memeriksa secara mutlak:
  ```php
  $pendaftaran = Pendaftaran::findOrFail($id);
  if ($pendaftaran->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
      abort(403, 'Akses tidak sah ke berkas pendaftar.');
  }
  ```

---

## 8. Presensi Ustadz Geolocation & Presensi Santri Bulk

### 8.1 Presensi Ustadz Berbasis Geofence (Haversine Formula)
- Pusat koordinat pesantren (`dokum-cp1.md` §6):
  - Latitude: `-7.797068`
  - Longitude: `110.370529`
  - Radius Valid: `150.0` meter
- Kalkulasi jarak menggunakan formula Haversine Great-Circle di `GeolocationService`:
  ```php
  $earthRadius = 6371000; // dalam meter
  $latFrom = deg2rad($lat1);
  $lonFrom = deg2rad($lon1);
  $latTo = deg2rad($lat2);
  $lonTo = deg2rad($lon2);

  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;

  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
  ```
- **Aturan Evaluasi**:
  1. Ustadz wajib ditugaskan pada kegiatan tersebut di `ustadz_kegiatan` (jika tidak, abort 403).
  2. Cegah double-submit: Ustadz tidak dapat melakukan presensi ulang pada kegiatan dan tanggal yang sama (abort 409 / validasi).
  3. Status presensi ditentukan berdasarkan jarak:
     - Jika jarak <= 150.0 meter: `status = 'valid'`
     - Jika jarak > 150.0 meter: `status = 'invalid'`
  4. Koordinat ustadz dan status wajib tersimpan di `presensi_kegiatan`.

### 8.2 Presensi Santri Massal (Bulk UpdateOrCreate)
- Pada sesi presensi kegiatan yang telah divalidasi ustadz, ustadz mengakses form presensi santri `/ustadz/presensi-santri/{presensiKegiatan}/create`.
- Form menampilkan daftar santri yang sesuai dengan target `tingkatan` dan `kelas` kegiatan pondok.
- Ustadz mencatat status kehadiran masing-masing santri (`hadir`, `izin`, `sakit`, `alpha`).
- Data disimpan secara massal menggunakan metode `updateOrCreate` pada tabel `presensi_santri` berdasarkan kunci `(presensi_kegiatan_id, santri_id)`.
- Hanya ustadz pemilik sesi presensi kegiatan yang berhak menginput data (abort 403 untuk pengguna lain).

---

## 9. Tahfidz Ziyadah & Murojaah Al-Qur'an

### 9.1 Setoran Ziyadah (Hafalan Baru)
- Ustadz mencatat penambahan hafalan baru santri binaan melalui `/ustadz/setoran-hafalan/create`.
- **Aturan Validasi**:
  1. Santri wajib berada di bawah bimbingan ustadz terkait pada pivot `ustadz_santri` (jika tidak, abort 403).
  2. Nilai `juz` integer antara 1 sampai 30.
  3. Nama `surat` disimpan sebagai teks langsung (`VARCHAR(100)`, misal: `'Al-Baqarah'`).
  4. Rentang `ayat` disimpan sebagai teks langsung (`VARCHAR(50)`, misal: `'1-25'`).
  5. Nilai kualitas hafalan numerik (0 sampai 100).
  6. Kolom `status_selesai` (boolean): bernilai 1 jika setoran tuntas/lulus, 0 jika perlu diulang.
  7. Kolom `catatan` teks untuk masukan tajwid dan makharijul huruf dari ustadz.

### 9.2 Murojaah (Pengulangan Hafalan)
- Ustadz mencatat pengulangan hafalan santri binaan melalui `/ustadz/murojaah/create`.
- Menyimpan parameter: `santri_id`, `tanggal`, `juz`, `surat`, `ayat`, `nilai`, dan `status_selesai`.
- Memastikan kelancaran hafalan yang telah disetorkan sebelumnya terpelihara secara berkala.

---

## 10. Portal Wali Murid & Laporan Global Akademik

### 10.1 Portal Wali Murid
- Wali murid login dan diarahkan ke `/wali-murid/dashboard`.
- **Query Scoping**: Seluruh query data santri dan laporan dibatasi secara mutlak pada record santri yang memiliki `wali_murid_id === auth()->id()`. Akses data santri lain langsung menghasilkan HTTP 403 Forbidden.
- **Rincian Fitur**:
  1. **Dashboard Wali Murid (`GET /wali-murid/dashboard`)**: Menampilkan daftar anak kandung/binaan, total setoran ziyadah, total murojaah, serta capaian ayat dan surat terkini.
  2. **Laporan Mingguan (`GET /wali-murid/laporan-mingguan`)**: Menampilkan rekapitulasi capaian hafalan dan murojaah per pekan (Senin sampai Minggu) dilengkapi rincian nama ustadz pembina.
  3. **Riwayat Kehadiran Santri (`GET /wali-murid/riwayat-kegiatan`)**: Menampilkan log kehadiran santri pada setiap kegiatan pondok beserta statusnya (`hadir`, `izin`, `sakit`, `alpha`).

### 10.2 Laporan Global Akademik & Ekspor
Admin memiliki akses ke 4 laporan rekapitulasi komprehensif pada menu laporan akademik:
1. `GET /admin/laporan/presensi-ustadz`: Rekap kehadiran ustadz dengan filter bulan, nama ustadz, dan kegiatan pondok.
2. `GET /admin/laporan/presensi-santri`: Rekap kehadiran santri dengan filter bulan, kegiatan, santri, status, dan tingkatan.
3. `GET /admin/laporan/hafalan`: Rekap setoran hafalan baru dengan filter bulan, santri, ustadz, dan tingkatan.
4. `GET /admin/laporan/murojaah`: Rekap kelancaran murojaah santri dengan filter bulan, santri, ustadz, dan tingkatan.
- Seluruh laporan dapat diekspor langsung melalui route `GET /admin/laporan/{laporan}/export/{format}`:
  - Format `pdf`: Dokumen PDF A4 Landscape via Dompdf dengan styling tabel rapi.
  - Format `excel` (atau `xlsx`): Spreadsheet Excel via Maatwebsite Excel.

---

## 11. Alur Promosi Pendaftar Lulus ke Santri Akademik

Untuk menjembatani modul PPDB Online dengan modul Akademik Pesantren secara otentik:
1. **Kondisi Awal**: Calon santri berstatus `Lulus` pada tabel `pendaftarans`.
2. **Aksi Admin**: Pada halaman detail pendaftar (`GET /admin/pendaftar/{id}`), admin memiliki tombol tindakan *"Promosikan Menjadi Santri Pondok"* yang mengirimkan request `POST /admin/pendaftar/{id}/promosikan`.
3. **Payload / Input Promosi**:
   - `nis`: Nomor Induk Santri baru yang ditetapkan admin (tipe `VARCHAR(50)`, format pesantren e.g. `2026001`).
   - `tingkatan`: Jenjang pendidikan (`tsanawiyah`, `aliyah`, atau `takhassus`).
   - `kelas`: Kelas yang ditetapkan (e.g. `'7'`, `'8'`, `'10'`).
   - `wali_murid_id`: Akun wali murid bila sudah terdaftar di sistem (opsional).
4. **Logika Eksekusi Transaksional (`PendaftarPromotionService`)**:
   - Memastikan pendaftar memiliki status `'Lulus'` (jika belum, tolak dengan 422).
   - Memastikan pendaftar belum pernah dipromosikan (`santri_id` masih `NULL`).
   - Membuat record baru pada tabel `santri`:
     ```php
     $santri = Santri::create([
         'nama'          => $pendaftar->nama_lengkap,
         'nis'           => $request->nis,
         'tingkatan'     => $request->tingkatan,
         'kelas'         => $request->kelas,
         'wali_murid_id' => $request->wali_murid_id,
         'tanggal_lahir' => $pendaftar->tanggal_lahir,
     ]);
     ```
   - Memperbarui pendaftar dengan referensi santri:
     ```php
     $pendaftar->update(['santri_id' => $santri->id]);
     ```
5. **Hasil**: Santri baru langsung muncul di master santri pondok, dapat dimasukkan ke penugasan santri binaan tahfidz, dan dicatat kehadirannya dalam kegiatan harian.

---

## 12. Integrasi Eksternal: Fonnte WhatsApp & Email

### 12.1 Fonnte WhatsApp Gateway API
- Target endpoint Fonnte: `POST https://api.fonnte.com/send`
- Header autentikasi: `Authorization: {FONNTE_TOKEN}`
- Service wrapper: `app/Services/FonnteService.php`
- **Fitur 1: Notifikasi Perubahan Status Verifikasi**:
  - Saat admin memverifikasi status pendaftar (`POST /admin/pendaftar/{id}/verifikasi`), sistem otomatis memanggil Fonnte API untuk mengirim pesan ke nomor WhatsApp santri (`pendaftarans.no_hp`):
    - Format normalisasi nomor: mengubah format lokal `08xx` atau `+628xx` menjadi standar angka `628xx`.
    - Pesan memuat nama lengkap santri, status verifikasi terbaru (`Lulus`, `Tidak Lulus`, atau `Menunggu`), dan instruksi langkah berikutnya.
- **Fitur 2: Broadcast WhatsApp Massal**:
  - Pada menu `/admin/notifikasi`, admin dapat memfilter target pendaftar berdasarkan status dan menulis teks pesan pengumuman.
  - Sistem melakukan pengiriman pesan massal secara iteratif atau batch melalui Fonnte API dan menampilkan jumlah pesan berhasil dan gagal.

### 12.2 Transaksional Email (`RegisterSuccessMail`)
- Menggunakan Laravel Mail via Mailable `app/Mail/RegisterSuccessMail.php`.
- Dikirimkan saat pendaftaran online berhasil disubmit.
- Memuat informasi selamat datang, alamat login portal (`/login`), email terdaftar, dan password acak 8 karakter awal.

---

## 13. Kontrak Tampilan UI, Blade Components & Desain

### 13.1 Tampilan Publik & PPDB
- Layout Blade responsif dengan Tailwind CSS dan Alpine.js:
  - Header navigasi dengan menu Beranda, Informasi, Kontak, Form Pendaftaran, dan Tombol Login.
  - Beranda publik dengan dynamic slider carousel, live counter statistik, dan cards keunggulan institusi.
  - Formulir pendaftaran multi-step / terstruktur dengan input biodata siswa, orang tua, sekolah asal, dan dropzone upload 10 berkas.
  - Dashboard calon santri yang informatif dan bersih.

### 13.2 Tampilan Dashboard Administrasi & Portal Pengguna
- Layout dashboard monolitik terstruktur berdasarkan peran:
  - `admin`: Sidebar mencakup Master Data (Santri, Ustadz, Wali, Kegiatan), Penugasan, Laporan Akademik, Modul PPDB (Pendaftar, Verifikasi, Broadcast WA, Ekspor), dan CMS Management.
  - `ustadz`: Sidebar mencakup Jadwal Kegiatan, Presensi GPS, Presensi Santri, Setoran Ziyadah, dan Murojaah.
  - `wali_murid`: Sidebar mencakup Ringkasan Capaian Anak, Laporan Mingguan, dan Riwayat Kehadiran.
- Visualisasi Analytics: Menampilkan Gender Ratio Doughnut Chart di Admin PPDB menggunakan Chart.js murni.

---

## 14. Standar Kualitas, Gate DoD & Perintah Verifikasi

### 14.1 Perintah Verifikasi Wajib
Setiap fase dan deliverable wajib lulus seluruh pengujian programmatic berikut:
```bash
php artisan migrate:fresh --seed
php artisan test
vendor/bin/pint --test
npm run build
php artisan route:list
```

### 14.2 Definition of Done (DoD) Universal
- [ ] Database migration berjalan bersih tanpa error dari fresh state (`migrate:fresh`).
- [ ] Database seeder idempoten dan menginisialisasi 4 role, akun demo per role, data master CMS, dan santri binaan.
- [ ] Seluruh automated test (Unit & Feature) lulus 100% tanpa ada test yang di-skip atau di-comment out.
- [ ] Tidak ada hardcoded logic atau facade logic; seluruh CRUD dan alur bisnis berinteraksi dengan database nyata.
- [ ] Proteksi otorisasi Spatie RBAC dan IDOR protection aktif dan teruji pada seluruh route non-publik.
- [ ] Coding style rapi dan bersih mengikuti standar PSR-12 / Laravel Pint.

---

## 15. Fase Pembangunan & Milestone (M0–M6)

Pengerjaan proyek dibagi ke dalam 7 milestone berurutan:

| Milestone | Nama Milestone | Lingkup Pekerjaan | Estimasi / Ketergantungan |
|---|---|---|---|
| **M0** | Plan Alignment & Pruning | Memperbarui build plan secara in-place, memangkas 8 subsistem extraneous, dan mengintegrasikan seluruh spesifikasi `dokum-cp1.md` dan `dokum-ppdb.md`. | Prasyarat M1 |
| **M1** | Scaffolding, Core Infra, DB & RBAC | Setup framework, konfigurasi koneksi database, membuat 16 canonical migration domain, Spatie 4-role RBAC, Breeze authentication suite, dan database seeders. | M0 |
| **M2** | PPDB Public Portal, CMS & Online Registration | Implementasi Public Landing CMS, Middleware `CheckFormStatus`, Form Pendaftaran dengan 10-Berkas Upload, Auto Calon Santri Account, Email Kredensial, dan Dashboard Calon Santri. | M1 |
| **M3** | PPDB Admin, Verification, WhatsApp & Exports | PPDB Admin Dashboard, Gender Ratio Chart, Verifikasi Pendaftar, Notifikasi WA Fonnte & Broadcast, ZIP Download, Rekapitulasi Excel/PDF, dan Promosi Pendaftar Lulus ke Santri. | M2 |
| **M4** | Pondok Akademik: Master Data, Penugasan & Presensi | CRUD Master Santri (NIS `VARCHAR(50)`), Ustadz, Wali Murid, Kegiatan Pondok, Penugasan Ustadz & Santri Binaan, Geolocation Presensi (Haversine 150m), dan Presensi Santri Bulk. | M1 |
| **M5** | Tahfidz Tracking & Wali Murid Portal | Setoran Ziyadah, Murojaah, Laporan Global Akademik (Ustadz, Santri, Hafalan, Murojaah) dengan Ekspor PDF/Excel, Dashboard Wali Murid, Laporan Mingguan, dan Riwayat Kehadiran. | M4 |
| **M6** | E2E Test Suite & Adversarial Hardening | Eksekusi seluruh matriks test Tiers 1-4, verifikasi boundary cases (Tier 5), perbaikan seluruh defect, dan konfirmasi zero error. | M3, M5 |

---

## 16. Matriks Pengujian Lengkap

Pengujian mencakup Unit Tests dan Feature Tests yang menguji keaslian logika backend.

### 16.1 Unit Tests
| File Uji | Sasaran | Assertion / Skenario Kunci |
|---|---|---|
| `tests/Unit/GeolocationServiceTest.php` | Haversine Formula | Titik sama (0.00m); Titik utara 149.9m (jarak <= 150m); Titik utara 150.1m (jarak > 150m); Formula matematis riil tanpa mock hardcode. |
| `tests/Unit/FonnteServiceTest.php` | WhatsApp Gateway | Normalisasi nomor telepon (08xx -> 628xx); pembentukan payload POST; penanganan response sukses & gagal. |
| `tests/Unit/PendaftarPromotionServiceTest.php` | Promosi Santri | Memastikan calon santri `Lulus` dipromosikan ke `santri` dengan NIS string (`2026001`), dan status terhubung via `santri_id`. |

### 16.2 Feature Tests
| File Uji | Sasaran | Skenario Pengujian |
|---|---|---|
| `tests/Feature/SchemaTest.php` | Skema Database | Memastikan keberadaan seluruh 16 tabel domain, kolom wajib, tipe data, dan unique index (termasuk NIS `VARCHAR(50)`). |
| `tests/Feature/RbacMatrixTest.php` | Matriks Izin Spatie | Menguji akses 4 role (`admin`, `ustadz`, `wali_murid`, `calon_santri`) pada seluruh rute sistem (200 OK vs 403 Forbidden). |
| `tests/Feature/BreezeAuthTest.php` | Autentikasi Breeze | Login, logout, registrasi akun, reset password, dan pembaruan profil pengguna. |
| `tests/Feature/CheckFormStatusMiddlewareTest.php` | Guard Jadwal PPDB | Form dibuka saat tanggal aktif; form dialihkan (302 ke `/`) saat status Tutup atau di luar rentang tanggal. |
| `tests/Feature/PendaftaranUploadTest.php` | Form & 10 Berkas | Validasi 7 berkas wajib dan 3 berkas opsional, validasi ekstensi & ukuran maksimal 2MB, penyimpanan ke storage privat. |
| `tests/Feature/RegisterSuccessTest.php` | Auto Akun & Email | Pembuatan akun calon santri dengan password acak 8 karakter, mailable `RegisterSuccessMail`, dan view `/register-success`. |
| `tests/Feature/CalonSantriDashboardTest.php` | Dashboard & IDOR | Calon santri melihat statusnya; mengunduh berkas miliknya; 403 Forbidden jika mencoba mengunduh berkas santri lain. |
| `tests/Feature/AdminPpdbVerificationTest.php` | Verifikasi Status | Admin mengubah status pendaftar (`Menunggu` -> `Lulus`/`Tidak Lulus`) dan memicu pengiriman notifikasi WhatsApp Fonnte. |
| `tests/Feature/AdminFonnteBroadcastTest.php` | Broadcast WhatsApp | Admin mengirim pesan massal ke pendaftar terfilter status via Fonnte API. |
| `tests/Feature/AdminZipDownloadTest.php` | Unduh Berkas ZIP | Mengemas 10 berkas pendaftar ke dalam satu file .zip dan memastikan file temporary terhapus setelah unduhan selesai. |
| `tests/Feature/AdminExportTest.php` | Ekspor Dokumen | Ekspor pendaftar PPDB ke Excel (`.xlsx`) dan PDF A4 landscape; ekspor laporan akademik ke PDF dan Excel. |
| `tests/Feature/AdminPromotionTest.php` | Promosi Pendaftar | Admin mempromosikan pendaftar Lulus ke tabel `santri` dengan NIS yang ditentukan. |
| `tests/Feature/GeofencePresensiTest.php` | Presensi Ustadz GPS | Ustadz check-in dalam radius 150m -> status `valid`; di luar radius 150m -> status `invalid`; penolakan double-submit kegiatan yang sama. |
| `tests/Feature/PresensiSantriBulkTest.php` | Presensi Santri Bulk | Mass input kehadiran santri (hadir, izin, sakit, alpha) via updateOrCreate. |
| `tests/Feature/TahfidzSetoranTest.php` | Setoran Ziyadah | Input juz 1-30, nama surat & ayat text, nilai 0-100, batasan hanya untuk santri binaan tahfidz ustadz. |
| `tests/Feature/TahfidzMurojaahTest.php` | Murojaah Hafalan | Input pengulangan hafalan santri binaan. |
| `tests/Feature/WaliMuridDashboardTest.php` | Portal Wali Murid | Wali murid hanya melihat anak miliknya; 403 Forbidden jika mengakses anak wali lain; laporan mingguan & riwayat kehadiran. |
| `tests/Feature/CmsManagementTest.php` | CMS Admin | CRUD Carousel, Keunggulan, Informasi, Kontak, dan update live counter Statistik. |
| `tests/Feature/AdminAkunManagementTest.php` | Akun Admin | CRUD admin baru, form reset password, dan pencegahan menghapus akun admin sendiri. |

---

## 17. Register Risiko & Mitigasi

| Risiko | Pemicu | Mitigasi |
|---|---|---|
| Kegagalan format NIS | Input format 7 digit (`2026001`) atau alfanumerik | Kolom `santri.nis` menggunakan `VARCHAR(50) UNIQUE NOT NULL` tanpa CHECK constraint integer sempit. |
| Kebocoran dokumen pendaftar (PII) | Akses berkas oleh pengguna lain | Berkas disimpan di storage privat; download dilindungi otorisasi IDOR ketat (`user_id === auth()->id()`). |
| Kegagalan koneksi API Fonnte | Jaringan internet atau kuota token habis | Panggilan API dibungkus dalam try-catch dengan log error; kegagalan API WA tidak membatalkan update status verifikasi pendaftar. |
| Beban komputasi pembuatan ZIP | File ukuran besar saat dikemas ke ZIP | ZIP dibuat secara stream/temporary dan otomatis dibersihkan via `deleteFileAfterSend(true)`. |
| Pendaftaran di luar jadwal | Pengguna mengakses link langsung form | Dilindungi oleh middleware `CheckFormStatus` di tingkat routing HTTP. |
| Perbedaan dialek SQL antara dev dan test | Penggunaan query raw non-standar | Menggunakan Eloquent ORM murni dan Query Builder standar yang kompatibel dengan SQLite dan MySQL. |

---

## 18. Anti-Pattern & Pemeriksaan CI

Pemeriksaan otomatis pada script CI (`scripts/ci-checks.sh`):
1. **DILARANG** ada implementasi dummy/hardcoded test output pada kode produksi.
2. **DILARANG** mengembalikan nilai konstan `true` pada fungsi verifikasi geofence (fungsi Haversine wajib menghitung selisih koordinat riil).
3. **DILARANG** menonaktifkan CSRF protection atau otorisasi RBAC demi kelulusan test.
4. **DILARANG** menyimpan token API atau password rahasia dalam berkas `.env.example` atau commit git.
5. **DILARANG** membiarkan panggilan debugging (`dd()`, `dump()`, `var_dump()`) pada kode aplikasi.

---

## 19. Change Log (v1.0 -> v2.0 Alignment)

| Versi | Tanggal | Deskripsi Perubahan | Alasan Perubahan |
|---|---|---|---|
| v1.0 | 2026-11-12 | Rencana teknis awal berbasis Capstone-1 dengan fokus impor Excel. | Dokumen lama. |
| **v2.0** | **2026-11-12** | **Full Scope Alignment**: <br>1. Memangkas 8 subsistem extraneous (Impor Excel, NIS state machine, Audit log, DB Sync Bridge, Quran Surahs 6.236 verse math, 24-hr edit window, batasan jam geofence 05:00-21:00, user is_active).<br>2. Mengintegrasikan seluruh spesifikasi `dokum-ppdb.md` (7 tabel PPDB, 34+ route, landing CMS, pendaftaran online, upload 10 berkas, auto akun calon santri, Fonnte WA gateway, ZIP download, ekspor Excel/PDF, kontrol jadwal, promosi santri baru).<br>3. Mengubah tipe `santri.nis` menjadi `VARCHAR(50) UNIQUE NOT NULL`.<br>4. Memulihkan seluruh rute show dan Breeze auth routes sesuai `dokum-cp1.md`. | Memenuhi Requirement R1 & Acceptance Criteria `ORIGINAL_REQUEST.md` secara 100% tanpa fitur di luar dokumen. |

---

## Appendix A — Enum & Label

### A.1 Nilai Enum Sistem
- **Tingkatan**: `'tsanawiyah'`, `'aliyah'`, `'takhassus'` (pada `kegiatan_pondok` ditambah `'semua'`).
- **Jenis Kelamin (PPDB)**: `'Laki-laki'`, `'Perempuan'`.
- **Status Presensi Ustadz**: `'valid'`, `'invalid'`, `'pending'`.
- **Status Presensi Santri**: `'hadir'`, `'izin'`, `'sakit'`, `'alpha'`.
- **Status Pendaftaran PPDB**: `'Menunggu'`, `'Lulus'`, `'Tidak Lulus'`.
- **Status Form PPDB**: `'Buka'`, `'Tutup'`.

---

## Appendix B — Konfigurasi & Variabel Lingkungan (.env)

Variabel lingkungan yang dikonfigurasi pada `.env`:
```env
APP_NAME="SIM PPTQ Ibnu Juraimi & PPDB"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Atau mysql untuk shared hosting:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=capstone2
# DB_USERNAME=root
# DB_PASSWORD=

FILESYSTEM_DISK=local

# Koordinat Pusat Pesantren (Haversine Geofence)
PESANTREN_LAT=-7.797068
PESANTREN_LNG=110.370529
PESANTREN_RADIUS=150

# Integrasi WhatsApp Fonnte
FONNTE_TOKEN=

# Transaksional Email
MAIL_MAILER=log
MAIL_FROM_ADDRESS="info@ibnujuraimi.sch.id"
MAIL_FROM_NAME="PPTQ Ibnu Juraimi"
```

---

## Appendix C — Konvensi Penamaan Kode & File

- **Database Tables**: snake_case plural (e.g. `pendaftarans`, `kegiatan_pondoks`, `setoran_hafalans`).
- **Database Columns**: snake_case (e.g. `wali_murid_id`, `nama_lengkap`, `jam_mulai`).
- **Eloquent Models**: PascalCase singular (e.g. `Santri`, `Pendaftaran`, `PresensiKegiatan`).
- **Controllers**: PascalCase berakhiran `Controller` dalam namespace per modul (`Admin\`, `Ustadz\`, `WaliMurid\`, `Ppdb\`).
- **Routes**: dot.separated lowercase (e.g. `admin.ppdb.index`, `ustadz.setoran-hafalan.create`).
- **Views**: lowercase dot-separated folder dan blade file (e.g. `admin.santri.index`, `public.pendaftaran`).

---

## Appendix D — Cheatsheet Perintah Eksekusi

```bash
# 1. Menjalankan migrasi database dan pengisian data seeder
php artisan migrate:fresh --seed

# 2. Menjalankan seluruh pengujian (Unit & Feature)
php artisan test

# 3. Format kode sesuai standar PSR-12
vendor/bin/pint

# 4. Compile frontend Tailwind & JavaScript
npm run build

# 5. Menjalankan skrip CI checks integritas
bash scripts/ci-checks.sh
```
