<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenugasanKegiatanController;
use App\Http\Controllers\Admin\PenugasanSantriController;
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\UstadzController;
use App\Http\Controllers\Admin\WaliMuridController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Ustadz\PresensiSantriController;
use App\Http\Controllers\Ustadz\PresensiUstadzController;
use App\Http\Controllers\Ustadz\TahfidzController;
use App\Http\Controllers\WaliMurid\WaliMuridDashboardController;
use App\Http\Controllers\WaliMurid\WaliMuridReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Entry (Login Only)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('home');
})->name('home');

/*
|--------------------------------------------------------------------------
| Admin Suite (Guarded by auth & role:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Admin Account Management
    Route::get('/akun', [AccountController::class, 'index'])->name('akun.index');
    Route::post('/akun', [AccountController::class, 'store'])->name('akun.store');
    Route::delete('/akun/{id}', [AccountController::class, 'destroy'])->name('akun.destroy');
    Route::post('/akun/{id}/reset', [AccountController::class, 'resetPassword'])->name('akun.reset.update');

    // Master Data Pondok Akademik
    Route::resource('santri', SantriController::class);
    Route::resource('ustadz', UstadzController::class);
    Route::resource('wali-murid', WaliMuridController::class);
    Route::resource('kegiatan', KegiatanController::class);

    // Penugasan Ustadz & Santri Binaan
    Route::get('/penugasan-kegiatan', [PenugasanKegiatanController::class, 'index'])->name('penugasan-kegiatan.index');
    Route::put('/penugasan-kegiatan/{ustadz}', [PenugasanKegiatanController::class, 'update'])->name('penugasan-kegiatan.update');
    Route::get('/santri-binaan', [PenugasanSantriController::class, 'index'])->name('santri-binaan.index');
    Route::put('/santri-binaan/{ustadz}', [PenugasanSantriController::class, 'update'])->name('santri-binaan.update');

    // Global Academic Reports
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/presensi-ustadz', [LaporanController::class, 'presensiUstadz'])->name('presensi-ustadz');
        Route::get('/presensi-santri', [LaporanController::class, 'presensiSantri'])->name('presensi-santri');
        Route::get('/hafalan', [LaporanController::class, 'hafalan'])->name('hafalan');
        Route::get('/murojaah', [LaporanController::class, 'murojaah'])->name('murojaah');
        Route::get('/export', [LaporanController::class, 'export'])->name('export');
    });
});

Route::middleware('auth')->get(
    '/presensi-kegiatan/{id}/foto',
    [PresensiUstadzController::class, 'photo']
)->name('presensi-kegiatan.photo');

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

    // Presensi Santri Bulk
    Route::get('/presensi-santri/{session}', [PresensiSantriController::class, 'create'])->name('presensi-santri.create');
    Route::post('/presensi-santri/{session}', [PresensiSantriController::class, 'store'])->name('presensi-santri.store');

    // Tahfidz Tracking
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
| Dashboard Redirect & Profile
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();
    $home = match (true) {
        $user->hasRole('admin') => 'admin.dashboard',
        $user->hasRole('ustadz') => 'ustadz.dashboard',
        $user->hasRole('wali_murid') => 'wali-murid.dashboard',
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
