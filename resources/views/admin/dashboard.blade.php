<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg text-gray-900 leading-tight">
            {{ __('Dashboard Administrator') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <section>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">PPDB & Pengaturan</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.ppdb.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Kelola PPDB</h4>
                        <p class="text-xs text-gray-600 mt-1">Verifikasi berkas, unduh ZIP, dan ekspor data pendaftar.</p>
                    </a>
                    <a href="{{ route('admin.statusForm') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Jadwal PPDB</h4>
                        <p class="text-xs text-gray-600 mt-1">Atur tanggal buka dan tutup pendaftaran.</p>
                    </a>
                    <a href="{{ route('admin.notifikasi') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Broadcast WhatsApp</h4>
                        <p class="text-xs text-gray-600 mt-1">Kirim pesan massal via Fonnte.</p>
                    </a>
                    <a href="{{ route('admin.akun.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Akun Admin</h4>
                        <p class="text-xs text-gray-600 mt-1">Kelola akun administrator sistem.</p>
                    </a>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Pondok Akademik</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="{{ route('admin.santri.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Data Santri</h4>
                        <p class="text-xs text-gray-600 mt-1">Master data santri, NIS, tingkatan, dan kelas.</p>
                    </a>
                    <a href="{{ route('admin.ustadz.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Data Ustadz</h4>
                        <p class="text-xs text-gray-600 mt-1">Akun dan profil tenaga pengajar.</p>
                    </a>
                    <a href="{{ route('admin.wali-murid.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Data Wali Murid</h4>
                        <p class="text-xs text-gray-600 mt-1">Akun wali murid dan relasi santri.</p>
                    </a>
                    <a href="{{ route('admin.kegiatan.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Kegiatan Pondok</h4>
                        <p class="text-xs text-gray-600 mt-1">Daftar kegiatan akademik pesantren.</p>
                    </a>
                    <a href="{{ route('admin.penugasan-kegiatan.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Penugasan Kegiatan</h4>
                        <p class="text-xs text-gray-600 mt-1">Tugaskan kegiatan ke ustadz pengampu.</p>
                    </a>
                    <a href="{{ route('admin.santri-binaan.index') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Santri Binaan</h4>
                        <p class="text-xs text-gray-600 mt-1">Atur santri binaan per ustadz pembina.</p>
                    </a>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Laporan Global</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.laporan.presensi-ustadz') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Presensi Ustadz</h4>
                        <p class="text-xs text-gray-600 mt-1">Rekap kehadiran kegiatan ustadz.</p>
                    </a>
                    <a href="{{ route('admin.laporan.presensi-santri') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Presensi Santri</h4>
                        <p class="text-xs text-gray-600 mt-1">Rekap kehadiran santri per kegiatan.</p>
                    </a>
                    <a href="{{ route('admin.laporan.hafalan') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Laporan Ziyadah</h4>
                        <p class="text-xs text-gray-600 mt-1">Rekap setoran hafalan baru santri.</p>
                    </a>
                    <a href="{{ route('admin.laporan.murojaah') }}" class="p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h4 class="font-semibold text-gray-900">Laporan Murojaah</h4>
                        <p class="text-xs text-gray-600 mt-1">Rekap pengulangan hafalan santri.</p>
                    </a>
                </div>
            </section>

            <section>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Konten Publik (CMS)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <a href="{{ route('admin.carousel.index') }}" class="p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-400 text-sm font-medium text-gray-900">Carousel</a>
                    <a href="{{ route('admin.keunggulan.index') }}" class="p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-400 text-sm font-medium text-gray-900">Keunggulan</a>
                    <a href="{{ route('admin.statistik.edit') }}" class="p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-400 text-sm font-medium text-gray-900">Statistik</a>
                    <a href="{{ route('admin.informasi.index') }}" class="p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-400 text-sm font-medium text-gray-900">Pengumuman</a>
                    <a href="{{ route('admin.kontak') }}" class="p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-400 text-sm font-medium text-gray-900">Kontak</a>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
