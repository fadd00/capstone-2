<x-app-layout title="Dashboard Administrator">
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-900 leading-tight">
            {{ __('Dashboard Administrator') }}
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section>
                <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Akademik &amp; Presensi</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('admin.santri.index') }}" class="dashboard-card dashboard-accent-blue p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Data Santri</h3>
                        <p class="text-xs text-gray-600 mt-1">Master data santri, NIS, tingkatan, dan kelas.</p>
                    </a>
                    <a href="{{ route('admin.ustadz.index') }}" class="dashboard-card dashboard-accent-teal p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Data Ustadz</h3>
                        <p class="text-xs text-gray-600 mt-1">Akun dan profil tenaga pengajar.</p>
                    </a>
                    <a href="{{ route('admin.wali-murid.index') }}" class="dashboard-card dashboard-accent-gold p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Data Wali Murid</h3>
                        <p class="text-xs text-gray-600 mt-1">Akun wali murid dan relasi santri.</p>
                    </a>
                    <a href="{{ route('admin.kegiatan.index') }}" class="dashboard-card dashboard-accent-blue p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Kegiatan Pondok</h3>
                        <p class="text-xs text-gray-600 mt-1">Daftar kegiatan akademik pesantren.</p>
                    </a>
                    <a href="{{ route('admin.penugasan-kegiatan.index') }}" class="dashboard-card dashboard-accent-teal p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Penugasan Kegiatan</h3>
                        <p class="text-xs text-gray-600 mt-1">Tugaskan kegiatan ke ustadz pengampu.</p>
                    </a>
                    <a href="{{ route('admin.santri-binaan.index') }}" class="dashboard-card dashboard-accent-gold p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Santri Binaan</h3>
                        <p class="text-xs text-gray-600 mt-1">Atur santri binaan per ustadz pembina.</p>
                    </a>
                </div>
            </section>

            <section>
                <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Laporan Global</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.laporan.presensi-ustadz') }}" class="dashboard-card dashboard-accent-teal p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Presensi Ustadz</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap kehadiran kegiatan ustadz.</p>
                    </a>
                    <a href="{{ route('admin.laporan.presensi-santri') }}" class="dashboard-card dashboard-accent-gold p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Presensi Santri</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap kehadiran santri per kegiatan.</p>
                    </a>
                    <a href="{{ route('admin.laporan.hafalan') }}" class="dashboard-card dashboard-accent-blue p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Laporan Ziyadah</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap setoran hafalan baru santri.</p>
                    </a>
                    <a href="{{ route('admin.laporan.murojaah') }}" class="dashboard-card dashboard-accent-teal p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Laporan Murojaah</h3>
                        <p class="text-xs text-gray-600 mt-1">Rekap pengulangan hafalan santri.</p>
                    </a>
                </div>
            </section>

            <section>
                <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Pengaturan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.akun.index') }}" class="dashboard-card dashboard-accent-gold p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Akun Admin</h3>
                        <p class="text-xs text-gray-600 mt-1">Kelola akun administrator sistem.</p>
                    </a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
