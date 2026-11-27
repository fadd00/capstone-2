## Ringkasan Perubahan

- **Tema visual:** sidebar navy glassy dengan aksen emas pesantren; kanvas utama bergradasi slate-biru, ambient orbs berpola geometris ber-parallax, serta kartu dashboard putih semi-transparan dengan aksen royal blue, teal, dan gold.
- **Portal setelah login:** sidebar responsif per role, topbar kaca tipis, header akun, indikator menu aktif, serta gaya global untuk kartu, form, tabel, status, tombol, dan empty state di seluruh halaman yang memakai layout aplikasi. Dashboard Admin, Ustadz, dan Wali Murid tersedia; view khusus Calon Santri belum ada di repository, dan halaman wali berada di `resources/views/wali`.
- **Portal masuk:** panel login responsif dengan pengenalan pesantren pada layar lebar; tampilan ringkas tetap digunakan pada layar kecil.
- **Animasi:** Lenis untuk smooth scroll, GSAP dan ScrollTrigger untuk reveal judul/kartu/baris tabel/form, serta Motion untuk interaksi tombol, kartu, dialog, dan drawer sidebar.
- **Aksesibilitas:** animasi dikurangi saat `prefers-reduced-motion` aktif; navigasi keyboard, skip link, modal native, dan status form yang telah ada tetap didukung.
- **Kontrak aplikasi:** perubahan visual tidak mengubah route, action/method form, variabel Blade, atau looping data yang sudah digunakan halaman admin, ustadz, dan wali murid.

Asset tetap dikelola oleh Vite melalui `@vite(['resources/css/app.css', 'resources/js/app.js'])`. Dependensi frontend baru tercatat di `package.json` dan `package-lock.json`.

## Pemeriksaan Setelah Perubahan

Jalankan dari root repository:

```powershell
npm run build
php artisan view:cache
php artisan view:clear
git diff --check
```

Pemeriksaan visual yang disarankan:

1. Buka portal masuk di desktop dan ponsel; pastikan tidak ada overflow horizontal.
2. Login sebagai Admin, Ustadz, dan Wali Murid; periksa sidebar dan menu aktif tiap role.
3. Periksa halaman daftar/form: pencarian, filter, tabel responsif, tombol aksi, dan pesan flash.
4. Di halaman presensi santri, pastikan pilihan Hadir/Izin/Sakit/Alpha dan submit presensi tetap berfungsi.
5. Uji drawer mobile, dropdown akun, dialog konfirmasi, dan pengaturan `prefers-reduced-motion`.

Verifikasi perubahan dashboard: `npm run build`, `php artisan view:cache`, dan `php artisan test` berhasil; suite test melaporkan 247 test lulus dengan 1.101 assertion.
