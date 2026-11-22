<div align="center">

# SIM PPTQ Ibnu Juraimi

Sistem Informasi Manajemen Akademik, Presensi &amp; Monitoring Tahfidz Santri

![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-^8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?style=flat-square&logo=sqlite&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)

</div>

---

Sistem informasi pesantren yang berfokus pada **Pondok Akademik**: master data (santri, ustadz, wali murid, kegiatan pondok), penugasan kegiatan &amp; santri binaan, presensi ustadz berbasis GPS (Haversine), presensi santri massal, pencatatan tahfidz (ziyadah &amp; murojaah), laporan global (ekspor PDF/Excel), serta portal monitoring wali murid.

**Stack:** Laravel 13, PHP ^8.3 (disarankan 8.5), Composer 2.11+, SQLite, Breeze (Blade), Spatie Permission 8, Laravel Excel 4, DomPDF 3, Vite + Tailwind CSS.

---

## Prasyarat

### Linux
- PHP ≥ 8.3 (disarankan PHP 8.5) dengan ekstensi: `sqlite3`/`pdo_sqlite`, `mbstring`, `xml`, `zip`, `gd`, `fileinfo`
- Composer ≥ 2.11
- Node.js ≥ 18 dan npm

### Windows
- PHP ≥ 8.4 (disarankan PHP 8.5) dengan ekstensi aktif: `pdo_sqlite`, `sqlite3`, `openssl`, `mbstring`, `fileinfo`, `gd`, `zip`, `curl`
> **Catatan Pengguna Windows (PHP Standalone/Winget):** Salin `php.ini-development` menjadi `php.ini`, lalu aktifkan `extension_dir = "ext"` beserta ekstensi di atas dengan menghapus tanda titik koma (`;`).

---

## Instalasi

### Linux / macOS
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
```

### Windows (PowerShell)
```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
npm run build
```

---

## Menjalankan

Jalankan server Laravel + Vite sekaligus (kompatibel **Windows, Linux, dan macOS**, tanpa Redis dan tanpa polling queue/`pail`):

```bash
composer run dev
```

Atau langsung lewat npm: `npm run dev:all`, atau terpisah:

```bash
php artisan serve        # http://127.0.0.1:8000
npm run dev              # opsional, hot reload frontend (127.0.0.1:5173)
```

*(Khusus Linux/macOS dengan `ext-pcntl`, tersedia juga `composer run dev:full` untuk menyertakan `queue:listen` dan `pail`.)*

---

## Konfigurasi `.env`

| Variabel | Fungsi |
|----------|--------|
| `PESANTREN_LAT`, `PESANTREN_LNG` | Koordinat pesantren untuk presensi ustadz (Haversine) |
| `PESANTREN_RADIUS` | Radius presensi dalam meter (default `150`) |
| `MAIL_*` | Pengiriman email (default `MAIL_MAILER=log`, email tertulis di `storage/logs`) |

---

## Akun Seeder

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@ibnujuraimi.sch.id` | `password` |
| Admin | `admin@pptq.test` | `password` |
| Ustadz | `ustadz1@pptq.test` … `ustadz5@pptq.test` | `password` |
| Wali murid | `wali1@pptq.test`, `wali2@pptq.test` | `password` |

**Ganti seluruh password default sebelum dipakai di produksi.**

---

## Halaman Utama

- **Portal Masuk:** `/` dan `/login` (registrasi mandiri dinonaktifkan; akun dibuat oleh Admin)
- **Admin:** `/admin/...` (master data santri/ustadz/wali/kegiatan, penugasan, laporan global PDF/Excel, manajemen akun admin)
- **Ustadz:** `/ustadz/...` (check-in GPS, presensi santri massal, setoran ziyadah &amp; murojaah)
- **Wali murid:** `/wali-murid/...` (dashboard santri, laporan mingguan tahfidz, riwayat kehadiran)

---

## Testing & Code Style

```bash
./vendor/bin/phpunit tests/Feature/ModernWebAndPpdbRemovalTest.php
./vendor/bin/pint --test                    # cek gaya kode
```

---

## Reset Database

```bash
php artisan migrate:fresh --seed
```

## Catatan Tambahan untuk Linux
- Pastikan ekstensi PHP yang diperlukan terpasang (contoh Fedora: `sudo dnf install php-pdo php-mbstring php-xml php-gd php-zip`, Ubuntu/Debian: `sudo apt-get install php8.5-sqlite3 php8.5-xml php8.5-mbstring php8.5-gd php8.5-zip`).
- Jika menggunakan Nginx, buat konfigurasi virtual host yang mengarahkan ke `public/` dan set `fastcgi_pass` ke PHP-FPM.
- Untuk SQLite, pastikan direktori `storage/` dapat ditulis (`chmod -R 775 storage bootstrap/cache`).
- Jalankan `php artisan storage:link` untuk membuat symbolic link ke folder `public/storage`.

## Catatan Tambahan untuk Windows
- **Ekstensi wajib agar `composer install` / `composer update` berjalan:** Pastikan di `php.ini` baris `extension_dir = "ext"` aktif dan ekstensi berikut tidak dikomentari (`;`): `curl`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, `zip`. *(Ekstensi `gd` dan `zip` dibutuhkan oleh `phpoffice/phpspreadsheet` — jika mati, `composer update` akan gagal dengan error platform requirement.)*
- **Error SSL saat `composer update` di Windows:** Unduh `cacert.pem` dari `https://curl.se/ca/cacert.pem`, lalu arahkan `curl.cainfo` dan `openssl.cafile` di `php.ini` ke path file tersebut.
- **Tidak perlu Redis:** Default `.env.example` menggunakan `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, dan `DB_CONNECTION=sqlite` (`WAL` + `busy_timeout=5000`), sehingga tidak membutuhkan layanan Redis maupun polling worker.
- Pastikan `composer --version` minimal `2.8+` (`composer self-update`) dan `php -v` minimal `8.3` (disarankan `8.5`).
- Untuk SQLite, beri izin tulis penuh pada folder `database/` dan `storage/`.
