# SIM PPTQ Ibnu Juraimi & PPDB

Sistem informasi pesantren: PPDB online (pendaftaran, verifikasi, notifikasi WhatsApp, ekspor) dan Pondok Akademik (master data, penugasan, presensi GPS, tracking tahfidz, portal wali murid).
Lingkup fitur mengacu pada [dokum-cp1.md](dokum-cp1.md) dan [dokum-ppdb.md](dokum-ppdb.md).

**Stack:** Laravel 11, PHP ^8.2, SQLite, Breeze (Blade), Spatie Permission, Laravel Excel, DomPDF, Vite + Tailwind.

## Prasyarat

**Linux**
- PHP >= 8.2 dengan ekstensi: `sqlite3`/`pdo_sqlite`, `mbstring`, `xml`, `zip`, `gd`, `fileinfo`
- Composer
- Node.js >= 18 dan npm

**Windows**
- PHP >= 8.4 dengan ekstensi aktif: `pdo_sqlite`, `sqlite3`, `openssl`, `mbstring`, `fileinfo`, `gd`, `zip`, dan `curl`
  > **Catatan Pengguna Windows (PHP Standalone/Winget):** Salin `php.ini-development` menjadi `php.ini`, lalu aktifkan `extension_dir = "ext"` beserta ekstensi di atas dengan menghapus tanda titik koma (`;`).

## Instalasi

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # file SQLite
php artisan migrate --seed
npm run build
```

## Menjalankan

```bash
php artisan serve        # http://127.0.0.1:8000
npm run dev              # opsional, hot reload saat mengembangkan frontend
```

Atau sekaligus (server + queue + log + vite): `composer run dev`.

 > *Catatan Pengguna Windows:* Perintah `composer run dev` menjalankan `php artisan pail` yang membutuhkan ekstensi `pcntl` (khusus Linux/macOS). Di Windows, pesan error pada proses `[logs]` dapat diabaikan karena server utama tetap berjalan, atau gunakan `php artisan serve` secara langsung.


`QUEUE_CONNECTION=database` — jalankan `php artisan queue:listen` jika ada proses yang memakai queue.

## Konfigurasi `.env`

| Variabel | Fungsi |
|----------|--------|
| `FONNTE_TOKEN` | Token API Fonnte untuk notifikasi & broadcast WhatsApp (kosongkan jika tidak dipakai) |
| `PESANTREN_LAT`, `PESANTREN_LNG` | Koordinat pesantren untuk presensi ustadz (Haversine) |
| `PESANTREN_RADIUS` | Radius presensi dalam meter (default `150`) |
| `MAIL_*` | Pengiriman email (default `MAIL_MAILER=log`, email tertulis di `storage/logs`) |

## Akun Seeder

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@ibnujuraimi.sch.id` | `password` |
| Admin | `admin@pptq.test` | `password` |
| Ustadz | `ustadz1@pptq.test` … `ustadz5@pptq.test` | `password` |
| Wali murid | `wali1@pptq.test`, `wali2@pptq.test` | `password` |

Akun calon santri dibuat otomatis saat mendaftar lewat form PPDB (`/pendaftaran`).
Ganti seluruh password default sebelum dipakai di produksi.

## Halaman Utama

- Publik: `/` (beranda), `/informasi`, `/contact`, `/pendaftaran`
- Calon santri: `/user/dashboard` setelah login
- Admin: `/admin/...` (PPDB, master data, laporan)
- Ustadz: `/ustadz/...`; wali murid: `/wali-murid/...`

## Testing & Code Style

```bash
php artisan test --testsuite=Unit,Feature   # 384 test, SQLite in-memory
./vendor/bin/pint --test                    # cek gaya kode
```

Test dikelompokkan dalam `tests/Feature/Tier1_FeatureCoverage` (F01–F45), `Tier2_Boundary` (B01–B07), `Tier3_Pairwise` (P01–P05), dan `Tier4_Scenarios` (S01–S04).

## Reset Database

```bash
php artisan migrate:fresh --seed
```
