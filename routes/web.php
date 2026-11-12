<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\FormScheduleController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenugasanKegiatanController;
use App\Http\Controllers\Admin\PenugasanSantriController;
use App\Http\Controllers\Admin\PpdbController as AdminPpdbController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\UstadzController;
use App\Http\Controllers\Admin\WaliMuridController;
use App\Http\Controllers\Ppdb\ApplicantDashboardController;
use App\Http\Controllers\Ppdb\PublicPpdbController;
use App\Http\Controllers\Ppdb\RegistrationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Ustadz\PresensiSantriController;
use App\Http\Controllers\Ustadz\PresensiUstadzController;
use App\Http\Controllers\Ustadz\TahfidzController;
use App\Http\Controllers\WaliMurid\WaliMuridDashboardController;
use App\Http\Controllers\WaliMurid\WaliMuridReportController;
use App\Http\Middleware\CheckFormStatus;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public CMS Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPpdbController::class, 'home'])->name('home');
Route::get('/informasi', [PublicPpdbController::class, 'informasi'])->name('informasi');
Route::get('/contact', [PublicPpdbController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| PPDB Registration Flow (Guarded by CheckFormStatus schedule middleware)
|--------------------------------------------------------------------------
*/
Route::middleware([CheckFormStatus::class])->group(function () {
    Route::get('/pendaftaran', [RegistrationController::class, 'create'])->name('pendaftaran');
    Route::post('/pendaftaran', [RegistrationController::class, 'store'])->name('pendaftaran.store');
});

Route::get('/register-success', [RegistrationController::class, 'success'])->name('register.success');

/*
|--------------------------------------------------------------------------
| Calon Santri Portal (Guarded by auth & role:calon_santri)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:calon_santri'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [ApplicantDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pendaftar/{id}/file/{field}', [ApplicantDashboardController::class, 'downloadFile'])->name('file');
});

/*
|--------------------------------------------------------------------------
| Admin Suite (Guarded by auth & role:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // PPDB Core Actions
    Route::get('/ppdb', [AdminPpdbController::class, 'index'])->name('ppdb.index');
    Route::get('/pendaftar/{id}/file/{field}', [AdminPpdbController::class, 'downloadFile'])->name('file');
    Route::post('/verifikasi/{id}', [AdminPpdbController::class, 'verifikasi'])->name('verifikasi');
    Route::post('/pendaftar/{id}/verifikasi', [AdminPpdbController::class, 'verifikasi']);

    // Notifikasi & Broadcast WhatsApp Fonnte
    Route::get('/notifikasi', [AdminPpdbController::class, 'notifikasi'])->name('notifikasi');
    Route::post('/notifikasi', [AdminPpdbController::class, 'kirimNotifikasi'])->name('kirimNotifikasi');
    Route::post('/kirim-notifikasi', [AdminPpdbController::class, 'kirimNotifikasi']);

    // ZIP & Single/Bulk Deletion
    Route::get('/pendaftar/{id}/download-zip', [AdminPpdbController::class, 'downloadZip'])->name('download-zip');
    Route::delete('/pendaftar-hapus-semua', [AdminPpdbController::class, 'destroyAll'])->name('destroyAll');
    Route::delete('/pendaftar/hapus-semua', [AdminPpdbController::class, 'destroyAll']);
    Route::delete('/pendaftar/{id}', [AdminPpdbController::class, 'destroy'])->name('destroy');

    // Exports (Excel & PDF)
    Route::get('/export/excel', [AdminPpdbController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [AdminPpdbController::class, 'exportPdf'])->name('export.pdf');

    // Promotion Workflow to Santri
    Route::post('/pendaftar/{id}/promosikan', [AdminPpdbController::class, 'promosikan'])->name('pendaftar.promosikan');

    // Form Schedule Control
    Route::get('/status-form', [FormScheduleController::class, 'index'])->name('statusForm');
    Route::post('/status-form', [FormScheduleController::class, 'update'])->name('updateStatusForm');

    // CMS Management
    // 1. Carousel
    Route::get('/carousel', [CmsController::class, 'carouselIndex'])->name('carousel.index');
    Route::post('/carousel', [CmsController::class, 'carouselStore'])->name('carousel.store');
    Route::put('/carousel/{id}', [CmsController::class, 'carouselUpdate'])->name('carousel.update');
    Route::delete('/carousel/{id}', [CmsController::class, 'carouselDestroy'])->name('carousel.destroy');

    // 2. Keunggulan
    Route::get('/keunggulan', [CmsController::class, 'keunggulanIndex'])->name('keunggulan.index');
    Route::post('/keunggulan', [CmsController::class, 'keunggulanStore'])->name('keunggulan.store');
    Route::put('/keunggulan/{id}', [CmsController::class, 'keunggulanUpdate'])->name('keunggulan.update');
    Route::delete('/keunggulan/{id}', [CmsController::class, 'keunggulanDestroy'])->name('keunggulan.destroy');

    // 3. Statistik
    Route::get('/statistik', [CmsController::class, 'statistikEdit'])->name('statistik.edit');
    Route::put('/statistik', [CmsController::class, 'statistikUpdate'])->name('statistik.update');

    // 4. Informasi
    Route::get('/informasi', [CmsController::class, 'informasiIndex'])->name('informasi.index');
    Route::post('/informasi', [CmsController::class, 'informasiStore'])->name('informasi.store');
    Route::put('/informasi/{id}', [CmsController::class, 'informasiUpdate'])->name('informasi.update');
    Route::delete('/informasi/{id}', [CmsController::class, 'informasiDestroy'])->name('informasi.destroy');

    // 5. Kontak
    Route::get('/kontak', [CmsController::class, 'kontak'])->name('kontak');
    Route::put('/kontak', [CmsController::class, 'kontakUpdate'])->name('kontak.update');

    // Admin Account Management
    Route::get('/akun', [AccountController::class, 'index'])->name('akun.index');
    Route::post('/akun', [AccountController::class, 'store'])->name('akun.store');
    Route::delete('/akun/{id}', [AccountController::class, 'destroy'])->name('akun.destroy');
    Route::post('/akun/{id}/reset', [AccountController::class, 'resetPassword'])->name('akun.reset.update');

    // Master Data Pondok Akademik (M4)
    Route::resource('santri', SantriController::class);
    Route::resource('ustadz', UstadzController::class);
    Route::resource('wali-murid', WaliMuridController::class);
    Route::resource('kegiatan', KegiatanController::class);

    // Penugasan Ustadz & Santri Binaan (M4)
    Route::get('/penugasan-kegiatan', [PenugasanKegiatanController::class, 'index'])->name('penugasan-kegiatan.index');
    Route::put('/penugasan-kegiatan/{ustadz}', [PenugasanKegiatanController::class, 'update'])->name('penugasan-kegiatan.update');
    Route::get('/santri-binaan', [PenugasanSantriController::class, 'index'])->name('santri-binaan.index');
    Route::put('/santri-binaan/{ustadz}', [PenugasanSantriController::class, 'update'])->name('santri-binaan.update');

    // Global Academic Reports (M5 - F38, F39, F40, P03, P04)
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/presensi-ustadz', [LaporanController::class, 'presensiUstadz'])->name('presensi-ustadz');
        Route::get('/presensi-santri', [LaporanController::class, 'presensiSantri'])->name('presensi-santri');
        Route::get('/hafalan', [LaporanController::class, 'hafalan'])->name('hafalan');
        Route::get('/murojaah', [LaporanController::class, 'murojaah'])->name('murojaah');
        Route::get('/export', [LaporanController::class, 'export'])->name('export');
    });
});

/*
|--------------------------------------------------------------------------
| Ustadz Portal (Guarded by auth & role:ustadz)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ustadz'])->prefix('ustadz')->name('ustadz.')->group(function () {
    Route::get('/dashboard', [PresensiUstadzController::class, 'dashboard'])->name('dashboard');
    Route::get('/presensi-kegiatan', [PresensiUstadzController::class, 'index'])->name('presensi-kegiatan.index');
    Route::get('/presensi-kegiatan/create', [PresensiUstadzController::class, 'create'])->name('presensi-kegiatan.create');
    Route::post('/presensi-kegiatan', [PresensiUstadzController::class, 'store'])->name('presensi-kegiatan.store');
    Route::get('/presensi-kegiatan/{id}', [PresensiUstadzController::class, 'show'])->name('presensi-kegiatan.show');

    // Presensi Santri Bulk (M4)
    Route::get('/presensi-santri/{session}', [PresensiSantriController::class, 'create'])->name('presensi-santri.create');
    Route::post('/presensi-santri/{session}', [PresensiSantriController::class, 'store'])->name('presensi-santri.store');

    // Tahfidz Tracking (M5 - F36, F37, B05, S03)
    Route::get('/setoran-hafalan', [TahfidzController::class, 'indexSetoran'])->name('setoran-hafalan.index');
    Route::get('/setoran-hafalan/create', [TahfidzController::class, 'createSetoran'])->name('setoran-hafalan.create');
    Route::post('/setoran-hafalan', [TahfidzController::class, 'storeSetoran'])->name('setoran-hafalan.store');
    Route::get('/setoran-hafalan/{id}', [TahfidzController::class, 'showSetoran'])->name('setoran-hafalan.show');

    Route::get('/murojaah', [TahfidzController::class, 'indexMurojaah'])->name('murojaah.index');
    Route::get('/murojaah/create', [TahfidzController::class, 'createMurojaah'])->name('murojaah.create');
    Route::post('/murojaah', [TahfidzController::class, 'storeMurojaah'])->name('murojaah.store');
    Route::get('/murojaah/{id}', [TahfidzController::class, 'showMurojaah'])->name('murojaah.show');
});

/*
|--------------------------------------------------------------------------
| Wali Murid Portal (Guarded by auth & role:wali_murid)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:wali_murid'])->prefix('wali-murid')->name('wali-murid.')->group(function () {
    Route::get('/dashboard', [WaliMuridDashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan-mingguan', [WaliMuridReportController::class, 'laporanMingguan'])->name('laporan-mingguan.index');
    Route::get('/riwayat-kegiatan', [WaliMuridReportController::class, 'riwayatKehadiran'])->name('riwayat-kegiatan.index');
});

/*
|--------------------------------------------------------------------------
| Breeze Default Dashboard & Profile
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();
    $home = match (true) {
        $user->hasRole('admin') => 'admin.dashboard',
        $user->hasRole('ustadz') => 'ustadz.dashboard',
        $user->hasRole('wali_murid') => 'wali-murid.dashboard',
        $user->hasRole('calon_santri') => 'user.dashboard',
        default => null,
    };

    return $home ? redirect()->route($home) : view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
