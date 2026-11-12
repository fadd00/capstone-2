# Capstone 2 — Plan Integrasi PPDB Mitra + Capstone 1 (Presensi & Tahfidz)

**Tanggal plan:** 3 Nov 2026
**Status:** planning only — belum ada kode yang ditulis
**Workspace rencana:** `~/Documents/vscode/capstone-2/`
**Vault note:** `~/Documents/Obsidian Vault/projects/capstone-2.md`

---

## 1. Goal

Membangun satu aplikasi baru (capstone-2) yang:
1. Menjadi sistem presensi & monitoring tahfidz PPTQ Muhammadiyah Ibnu Juraimi (inti capstone fadd, port dari CAPSTONE-1).
2. Bisa **membaca** database PPDB milik mitra yang sudah live (hosting `wisn4487_kizfa`) tanpa mengubahnya sama sekali.
3. Bisa **mengimpor data pendaftar yang saat ini tersebar di Excel/spreadsheet** mitra ke dalam skema internal yang bersih.
4. Lebih clean dari dua pendahulunya: satu RBAC (Spatie), satu CSS pipeline (Tailwind saja), satu deployable.

**Non-goal (fase ini):** menggantikan aplikasi PPDB mitra, menulis ke DB mitra, migrasi total storan file mitra, SSO terpadu.

---

## 2. Ground truth (bukan dari dokumen)

Semua fakta di bawah diverifikasi dari kode/dump, bukan dari `DOCUMENTATION.md`.

| Objek | Sumber verifikasi | Fakta |
|---|---|---|
| CAPSTONE-1 | `composer.json` | PHP ^8.3, Laravel ^13.8, spatie/laravel-permission ^8.1, Tinker 3.0, PHPUnit 12.5.12, Breeze 2.4 (require-dev) |
| CAPSTONE-1 | `database/migrations/` | `santri` (nis integer **unique**, wali_murid_id→users), `kegiatan_pondong` + `tingkatan` enum(tsanawiyah/aliyah/takhassus/semua), `ustadz_kegiatan`, `ustadz_santri`, `presensi_kegiatan` (lat/lng decimal 10,7 + status valid/invalid/pending), `presensi_santri` (hadir/izin/sakit/alpha), `setoran_hafalan` (juz int, surat varchar, ayat varchar, nilai decimal 5,2), `murojaah` |
| CAPSTONE-1 | `routes/web.php` | 3 grup: admin, ustadz, wali-murid via `RoleMiddleware` custom, bukan `@can` |
| PPDB mitra | `.env` | MySQL `wisn4487_kizfa` @127.0.0.1, APP_URL `https://muhammadiyah.wisatasemoyo.site`, FONNTE_API_KEY ada |
| PPDB mitra | `composer.json` | PHP ^8.2, Laravel ^12.0, dompdf ^3.1, maatwebsite/excel 4.x-dev. **Tidak ada Spatie** |
| PPDB mitra | dump `pptq_sahabatqu (3).sql` | 16 tabel, `pendaftarans` 41 kolom, `users.role` varchar tunggal, **0 baris pendaftar**, 1 user admin |
| PPDB mitra | dump vs migration repo | 13 migration identik, dump = hasil `migrate --seed` instalasi bersih |
| Data pendaftar nyata | keterangan fadd, 3 Nov 2026 | **Ada di Excel/spreadsheet**, bukan di DB. DB live masih kosong / baru deploy |

### Konsekuensi besar dari ground truth ini
- **Tidak perlu migrasi data historis dari DB mitra** — DB-nya kosong. Bridge sync ke DB mitra praktis hanya berguna untuk pendaftar ke depan (setelah PPDB dipakai lagi).
- **Pekerjaan data nyata = impor Excel.** Ini yang harus jadi salah satu modul fase utama, bukan afterthought.
- Skema mitra tetap penting sebagai acuan bentuk data pendaftar (nama kolom, enum status, kunci nisn/nik).

---

## 3. Collision table (keputusan yang harus diambil)

| Area | Sistem A (capstone-2, barunya) | Sistem B (PPDB mitra) | Keputusan |
|---|---|---|---|
| Tabel `users` | Spatie RBAC (`roles`, `permissions`, `model_has_roles`) | `users.role` varchar tunggal | **A menang.** Role nama: `admin`, `ustadz`, `wali_murid` (dari CP1). Pendaftar tidak login; hanya jadi baris `santri`. |
| Entitas bisnis | `santri` (nis unique, kelas, tingkatan, wali_murid_id) | `pendaftarans` (41 kolom termasuk 10 file) | **pendaftarans = sumber, santri = turunan.** Hanya status `Lulus` yang jadi santri. |
| Kunci identitas | `santri.nis` (integer, WAJIB) | `nisn` varchar(10) nullable | **Gap kritis:** PPDB tidak punya `nis`. Perlu kolom `nis` manual/generate saat penerimaan. |
| Pengenal alternatif | — | `nik_santri`, `email`, `no_hp` | Impor Excel pakai `nik_santri`+`nama_lengkap` sebagai kunci cek duplikat sebelum NIS diberikan |
| Engine DB | MySQL (produksi hosting) | MySQL 8.0.30 | **Sama.** Satu server DB bisa dipakai, dua database terpisah. |
| Framework | Laravel 13 / PHP 8.3 | Laravel 12 / PHP 8.2 | **A menang** (13). B tidak diubah. |
| Authorisation | Spatie + Policy | `RoleMiddleware` cek kolom | **A menang.** |
| CSS | Tailwind saja | Tailwind + Bootstrap CDN | **A menang** (buang Bootstrap). |
| Fitur export | (belum ada di CP1) | dompdf + maatwebsite/excel | **Port ke A**, karena impor Excel memang butuh maatwebsite. |
| Notifikasi WA | (belum ada) | Fonnte API | **Opsional fase akhir.** Jangan kirim broadcast dari dev. |
| Deployment | shared hosting / VPS | shared hosting Rumahweb | A = DB sendiri, B tetap di tempatnya. Dokumen "Vercel+Supabase" di `dokum-cp1.md` **dinyatakan stale**, diabaikan. |

### 3 tabrakan skema yang wajib diingat saat coding
1. `users` beda bentuk → **jangan** pernah copy migration `users` PPDB ke A.
2. `pendaftarans` ≠ `santri` → **jangan** jadikan satu tabel. Impor = transformasi, bukan copy kolom.
3. `nis` ada di CP1 tapi tidak ada di PPDB → butuh proses "penetapan NIS" sebagai langkah eksplisit, bukan auto-generate diam-diam.

---

## 4. Arsitektur yang dipilih (Opsi A: bridge + impor)

```
                    ┌──────────────────────────────┐
   Excel mitra ────►│  Modul Impor (Excel)         │
                    │  preview → validasi → commit │
                    └───────────┬──────────────────┘
                                │ upsert
                                ▼
  ┌──────────────────────────────────────────────────┐
  │  capstone-2 (Laravel 13, MySQL: db_capstone2)    │
  │  users(Spatie) · santri · kegiatan · presensi     │
  │  · setoran_hafalan · murojaah · import_logs       │
  └───────────┬──────────────────────────────────────┘
              │ koneksi read-only (config: ppdb)
              ▼
  ┌──────────────────────────────────────────────────┐
  │  DB PPDB mitra (MySQL: wisn4487_kizfa)           │
  │  pendaftarans · users · status_forms · dst.       │
  │  BACA SAJA — tidak pernah ditulis app baru        │
  └──────────────────────────────────────────────────┘
```

Dua jalur masuk data:
- **Jalur 1 (sekarang, utama): impor Excel.** Mitra mengirim spreadsheet; admin mengunggah; sistem memetakan → preview → commit.
- **Jalur 2 (nanti, pelengkap): sync DB read-only.** Untuk pendaftar baru setelah PPDB mitra dipakai lagi. Delta by `updated_at`.

---

## 5. Fase pekerjaan

| Fase | Isi | Butuh mitra? | Estimasi |
|---|---|---|---|
| 0 | Inventaris & kontrak: minta contoh Excel asli, daftar kolom, aturan penerimaan (kapan jadi santri, siapa menetapkan NIS) | ✅ ya | 1 sesi |
| 1 | Scaffold capstone-2 (Laravel 13, Breeze, Spatie, Tailwind, MySQL, dua koneksi DB) | tidak | 1 sesi |
| 2 | Skema inti: santri, kegiatan, ustadz_kegiatan, ustadz_santri, presensi_kegiatan, presensi_santri, setoran_hafalan, murojaah + seeder role | tidak | 1 sesi |
| 3 | Auth + RBAC 3 role + layout dashboard bersih (Tailwind, tanpa Bootstrap) | tidak | 1–2 sesi |
| 4 | **Modul Impor Excel** (adapter, validasi, preview, commit, log) | butuh contoh file | 2 sesi |
| 5 | Port modul CP1: presensi ustadz (Haversine), presensi santri, setoran hafalan, murojaah | tidak | 2–3 sesi |
| 6 | Laporan + export (PDF/Excel) untuk admin & wali | tidak | 1 sesi |
| 7 | Bridge read-only ke DB PPDB + `ppdb:sync-santri` terjadwal (opsional, kalau PPDB dipakai lagi) | ✅ ya (user DB read-only) | 1 sesi |
| 8 | Hardening: audit log, rate limit, backup, deploy | tidak | 1 sesi |

Total realistis: ~10–13 sesi. Fase 0 dan 4 adalah yang paling bergantung pada mitra.

---

## 6. Rincian teknis per fase

### Fase 0 — Inventaris & kontrak data (BLOKIR SEMUA)
Yang diminta ke mitra (satu pesan):
1. **File Excel asli** (satu saja, boleh dianonimkan) — supaya struktur kolom nyata terlihat.
2. **Daftar kolom + arti** kalau header Excel tidak self-explanatory.
3. **Aturan bisnis:**
   - Pendaftar berstatus apa yang dianggap jadi santri (Lulus saja, atau Lulus + sudah daftar ulang/bayar)?
   - Siapa yang menetapkan NIS? Nomor dari pondok atau digenerate sistem?
   - Kelas & tingkatan ditentukan saat penerimaan atau nanti?
   - Apakah ada data santri lama (existing student) yang juga harus masuk lewat Excel?
4. **Akses DB read-only** (kalau jalur 2 mau dipakai nanti): user MySQL dengan grant `SELECT` saja.
5. **Izin tertulis** bahwa data santri boleh disimpan di DB aplikasi baru.

Verifikasi keluar: tabel mapping kolom Excel → kolom `santri` disetujui mitra.

### Fase 1 — Scaffold
```
composer create-project laravel/laravel capstone-2 "^13.0"
cd capstone-2
composer require spatie/laravel-permission maatwebsite/excel barryvdh/laravel-dompdf
composer require --dev laravel/breeze && php artisan breeze:install blade
```
Konfigurasi wajib:
- `config/database.php`: tambah koneksi `ppdb` (read-only) di samping `mysql`.
- `.env`: `DB_DATABASE=capstone2`, `DB_PPDB_HOST/PORT/DATABASE/USERNAME/PASSWORD` (kosong dulu).
- PHP 8.3, MySQL 8. Node: Vite + Tailwind 3.1 + Alpine 3.4 (sama dengan CP1), **tanpa Bootstrap**.
- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

Verifikasi: `php artisan about` menampilkan Laravel 13 + driver mysql; `/login` (Breeze) tampil; `php artisan permission:cache-reset` jalan tanpa error.

### Fase 2 — Skema inti
Port migration dari CAPSTONE-1 dengan penyesuaian:
- `santri`: `nis` integer **unique** (dari CP1) + tambah `nik` varchar(16) nullable unique, `nisn` varchar(10) nullable, `jenis_kelamin` enum, `tingkatan` enum(tsanawiyah/aliyah/takhassus), `kelas` nullable, `wali_murid_id`, `tanggal_lahir`, `asal_sekolah`, `status` enum(aktif/lulus/keluar), `sumber` enum(manual/impor_excel/sync_ppdb).
- `ppdb_import_logs`: `id, filename, total_rows, inserted, updated, skipped, failed, errors json, committed_at, user_id`.
- `ppdb_sync_logs` (disiapkan untuk fase 7): `id, executed_at, last_updated_at_pulled, rows_affected`.
- Pertimbangkan tabel referensi `quran_surahs` (114 baris) supaya `setoran_hafalan.surat` bisa divalidasi, bukan string bebas seperti di CP1.

Verifikasi: `php artisan migrate:fresh` bersih; `SHOW TABLES` memuat 8 tabel inti + 3 tabel Spatie; `SHOW CREATE TABLE santri` memperlihatkan unique index pada `nis` dan `nik`.

### Fase 3 — Auth + RBAC + layout
- Role: `admin`, `ustadz`, `wali_murid` (nama sama dengan CP1 → mudah port).
- `RoleMiddleware` tetap dipakai untuk route group (sama pola CP1) tapi otorisasi detail pakai Policy + `@can`.
- Layout: satu design system Tailwind (sidebar + topbar), bukan 4 layout terpisah.
- Verifikasi: 3 akun uji, tiap role ditolak di grup route yang bukan haknya (assert 403), bukan redirect diam-diam.

### Fase 4 — Modul Impor Excel (jantung pekerjaan)
Alur: **unggah → deteksi header → mapping → preview (dry-run) → commit → log**.

Aturan mapping awal (dari skema `pendaftarans` mitra):
| Kolom Excel (calon) | Kolom internal `santri` | Aturan |
|---|---|---|
| nama_lengkap | nama | wajib, trim, title-case |
| jenis_kelamin | jenis_kelamin | normalisasi L/P/LAKI-LAKI → enum |
| nisn | nisn | nullable, 10 digit; kalau ada dipakai sebagai kunci cek duplikat |
| nik_santri / nik | nik | nullable; kunci duplikat kedua |
| tempat_lahir + tanggal_lahir | tempat_lahir, tanggal_lahir | parse tanggal (Excel serial & format Indonesia dd/mm/yyyy) |
| asal_sekolah | asal_sekolah | opsional |
| nama_ayah / nama_ibu / no_hp_ayah | wali (buat akun wali_murid) | akun wali dibuat dari email/no_hp, password diacak + kirim via mail |
| tingkatan / kelas | tingkatan, kelas | map ke enum; isi default `tsanawiyah` bila kosong + tandai perlu review |
| jumlah_hafalan | (catatan awal setoran / kolom `hafalan_awal`) | varchar → parse angka juz/ayat, sisanya ke catatan |
| nis | — | **tidak diimpor**; ditetapkan admin setelah validasi |

Aturan keselamatan:
- **Dry-run wajib**: tidak ada INSERT sebelum admin lihat hasil preview.
- Duplikat dicek berurutan: `nik` → `nisn` → (`nama`+`tanggal_lahir`) → tandai "perlu review" (bukan auto-merge).
- Baris gagal disimpan di `errors` JSON, bisa diunduh jadi CSV perbaikan.
- Impor bersifat idempotent: impor ulang file yang sama tidak menggandakan data.
- Semua operasi dalam transaction per batch (chunk 100), tidak pernah all-or-nothing untuk file ratusan baris.

Verifikasi:
- Test: impor file 3 baris (1 baru, 1 duplikat nik, 1 tanggal rusak) → hasil `inserted=1, skipped=1, failed=1` dan log benar.
- Test: impor file sama dua kali → `inserted=0` di run kedua.

### Fase 5 — Port modul CP1
- Presensi ustadz: form + `GeolocationService` Haversine, `config/pptq.php` koordinat Yogyakarta + radius 150 m, **validasi radius aktif** (jangan ulangi bug "return true"), filter GPS accuracy >20 m ditolak, time window, rate limit 3/10 menit.
- Presensi santri: bulk per kegiatan, unique (kegiatan, santri).
- Setoran hafalan & murojaah: validasi surah 1–114 + batas ayat lewat tabel referensi, edit window 24 jam + audit, progres = ayat unik (nilai ≥ 60) ÷ 6236.
- Verifikasi: test Haversine (titik sama 0 m, beda 3.26 km benar), test double submit ditolak, test rate limit mengembalikan 429.

### Fase 6 — Laporan & export
- Laporan presensi ustadz/santri per periode, laporan hafalan per santri, rekap per kelas.
- Export PDF (dompdf, landscape A4) dan Excel (maatwebsite) — pola diambil dari PPDB mitra tapi CSS-nya Tailwind.
- Verifikasi: export 1 periode menghasilkan file terbuka normal; angka di PDF = angka di layar (assert count).

### Fase 7 — Bridge DB PPDB (opsional)
- `config/database.php` koneksi `ppdb` read-only, `'options' => [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET SESSION TRANSACTION READ ONLY']`.
- Model `App\Ppdb\Pendaftaran` (bukan di `App\Models`) supaya jelas batasnya.
- Command `ppdb:sync-santri`: ambil `status='Lulus' AND updated_at > last_sync`, upsert ke `santri` (kunci `nik`/`nisn`), catat `ppdb_sync_logs`. `tries=3`.
- Scheduler: 02.00 harian.
- Verifikasi: jalankan pada DB dump lokal (import dump ke MySQL lokal), pastikan 0 baris tersentuh karena `pendaftarans` kosong; lalu tambah 1 baris dummy di DB lokal → sync menghasilkan 1 santri baru.

### Fase 8 — Hardening & deploy
- `system_audit_logs` (append-only), rate limit login 5/5 menit, backup harian DB + storage.
- Panduan deploy shared hosting: docroot ke `public/`, `php artisan config:cache route:cache`, cron `* * * * * php artisan schedule:run`.
- Verifikasi: simulasi restore backup ke DB kosong, aplikasi tetap jalan.

---

## 7. File yang kemungkinan besar diubah/dibuat

```
capstone-2/
├── config/database.php            # +koneksi ppdb
├── config/pptq.php                # koordinat + radius + status presensi
├── database/migrations/           # 8 tabel inti + spatie + logs + quran_surahs
├── database/seeders/              # RoleSeeder, QuranSurahSeeder, DemoSeeder
├── app/Models/                    # Santri, KegiatanPondok, PresensiKegiatan, ...
├── app/Ppdb/                      # model read-only + adapter (batas DB mitra)
│   ├── Pendaftaran.php
│   └── PendaftaranAdapter.php
├── app/Imports/                   # SantriImport (maatwebsite), header mapping, validasi
├── app/Exports/                   # rekap presensi/hafalan
├── app/Services/GeolocationService.php
├── app/Console/Commands/PpdbSyncSantri.php
├── app/Http/Controllers/{Admin,Ustadz,WaliMurid,Import}/
├── app/Policies/                  # SantriPolicy, SetoranHafalanPolicy
├── resources/views/layouts/       # satu layout Tailwind
└── routes/web.php, routes/console.php
```
Repo mitra (`~/Documents/vscode/pendaftaran-pptq`) **tidak disentuh** sama sekali.

---

## 8. Risiko & mitos yang harus dihindari

| Risiko | Mitigasi |
|---|---|
| Dokumen CP1 menyebut Vercel + Supabase + PostgreSQL — semua salah | Diabaikan. Ikuti `.env` + vault: MySQL + shared hosting |
| `nis` tidak ada di data PPDB/Excel | Langkah penetapan NIS eksplisit; impor tanpa NIS wajib berstatus "draft santri" |
| Excel mitra berantakan (merged cell, kolom tidak konsisten) | Fase 0 wajib: satu contoh file asli sebelum desain adapter |
| Mitra ubah skema/kolom Excel kapan saja | Semua pemetaan di satu file konfigurasi mapping, bukan tersebar |
| Radius GPS: CP1 punya bug `isWithinRadius()` return true | Test wajib di fase 5, cek kode bukan cuma UI |
| Data pribadi santri (NIK, KK, akta) sensitif | Storage privat, akses berbasis policy, audit log akses |
| Terkirim WA/email massal dari dev box | `MAIL_MAILER=log`, Fonnte key tidak diisi di `.env` lokal |
| Menulis tanpa izin ke DB mitra | Koneksi `ppdb` read-only session, tidak ada migration/seed ke `wisn4487_kizfa` |

---

## 9. Open questions (perlu jawaban sebelum fase 4)

1. Aturan tepat kapan pendaftar menjadi santri (Lulus saja? Lulus + daftar ulang?) — menentukan filter impor & sync.
2. NIS ditetapkan manual oleh pondok atau digenerate sistem? Formatnya (digit, prefix tahun)?
3. Excel yang akan diimpor isinya data pendaftar (calon) atau sekaligus data santri aktif lama?
4. Tingkatan/kelas ikut di Excel atau ditentukan setelah penerimaan?
5. Apakah aplikasi baru harus bisa dilihat mitra (demo URL) selama pengembangan?
6. Target hosting capstone-2: shared hosting Rumahweb yang sama, VPS, atau lokal dulu untuk sidang?

---

## 10. Langkah berikutnya (yang bisa dikerjakan tanpa mitra)

Fase 1, 2, 3, 5, 6 bisa jalan paralel tanpa menunggu mitra. Yang benar-benar butuh mitra: **contoh Excel (fase 0/4)** dan **user DB read-only (fase 7)**.

Urutan eksekusi yang direkomendasikan:
1. Fase 1 (scaffold) → langsung terlihat hasilnya.
2. Fase 2 + 3 (skema + auth + layout) → fondasi bisa dipakai demo.
3. Fase 5 (port modul CP1) → nilai capstone terpenuhi.
4. Fase 4 setelah Excel diterima.
5. Fase 6, 7, 8 terakhir.
