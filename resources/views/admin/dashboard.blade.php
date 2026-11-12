<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Administrator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Selamat Datang di Portal Admin PPTQ Ibnu Juraimi</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('admin.ppdb.index') }}" class="p-6 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <h4 class="font-bold text-blue-900 text-lg mb-2">Kelola PPDB</h4>
                        <p class="text-sm text-blue-700">Verifikasi berkas, unduh ZIP, ekspor data pendaftar.</p>
                    </a>
                    <a href="{{ route('admin.statusForm') }}" class="p-6 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <h4 class="font-bold text-green-900 text-lg mb-2">Jadwal PPDB</h4>
                        <p class="text-sm text-green-700">Atur tanggal buka dan tutup pendaftaran santri baru.</p>
                    </a>
                    <a href="{{ route('admin.notifikasi') }}" class="p-6 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition">
                        <h4 class="font-bold text-purple-900 text-lg mb-2">Broadcast WhatsApp</h4>
                        <p class="text-sm text-purple-700">Kirim pesan siaran massal via Fonnte Gateway.</p>
                    </a>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Pondok Akademik</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a href="{{ route('admin.santri.index') }}" class="p-6 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <h4 class="font-bold text-indigo-900 text-lg mb-2">Data Santri</h4>
                        <p class="text-sm text-indigo-700">Kelola master data santri, NIS, tingkatan, dan kelas.</p>
                    </a>
                    <a href="{{ route('admin.ustadz.index') }}" class="p-6 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
                        <h4 class="font-bold text-amber-900 text-lg mb-2">Data Ustadz</h4>
                        <p class="text-sm text-amber-700">Kelola akun dan profil tenaga pengajar pondok.</p>
                    </a>
                    <a href="{{ route('admin.wali-murid.index') }}" class="p-6 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                        <h4 class="font-bold text-emerald-900 text-lg mb-2">Data Wali Murid</h4>
                        <p class="text-sm text-emerald-700">Kelola akun wali murid dan integrasi santri binaan.</p>
                    </a>
                    <a href="{{ route('admin.kegiatan.index') }}" class="p-6 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                        <h4 class="font-bold text-teal-900 text-lg mb-2">Kegiatan Pondok</h4>
                        <p class="text-sm text-teal-700">Kelola jadwal dan daftar kegiatan akademik pesantren.</p>
                    </a>
                    <a href="{{ route('admin.penugasan-kegiatan.index') }}" class="p-6 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition">
                        <h4 class="font-bold text-rose-900 text-lg mb-2">Penugasan Kegiatan</h4>
                        <p class="text-sm text-rose-700">Atur penugasan kegiatan pondok ke ustadz pengampu.</p>
                    </a>
                    <a href="{{ route('admin.santri-binaan.index') }}" class="p-6 bg-cyan-50 border border-cyan-200 rounded-lg hover:bg-cyan-100 transition">
                        <h4 class="font-bold text-cyan-900 text-lg mb-2">Penugasan Santri Binaan</h4>
                        <p class="text-sm text-cyan-700">Atur santri binaan untuk ustadz pembina.</p>
                    </a>
                </div>

                <h3 class="text-lg font-medium text-gray-900 mt-8 mb-4">Laporan Global Pesantren</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <a href="{{ route('admin.laporan.presensi-ustadz') }}" class="p-6 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition">
                        <h4 class="font-bold text-sky-900 text-lg mb-2">Presensi Ustadz</h4>
                        <p class="text-sm text-sky-700">Rekapitulasi presensi kegiatan ustadz global.</p>
                    </a>
                    <a href="{{ route('admin.laporan.presensi-santri') }}" class="p-6 bg-violet-50 border border-violet-200 rounded-lg hover:bg-violet-100 transition">
                        <h4 class="font-bold text-violet-900 text-lg mb-2">Presensi Santri</h4>
                        <p class="text-sm text-violet-700">Rekapitulasi kehadiran santri per kegiatan dan tingkatan.</p>
                    </a>
                    <a href="{{ route('admin.laporan.hafalan') }}" class="p-6 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <h4 class="font-bold text-indigo-900 text-lg mb-2">Laporan Ziyadah</h4>
                        <p class="text-sm text-indigo-700">Rekapitulasi setoran hafalan baru santri.</p>
                    </a>
                    <a href="{{ route('admin.laporan.murojaah') }}" class="p-6 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                        <h4 class="font-bold text-emerald-900 text-lg mb-2">Laporan Murojaah</h4>
                        <p class="text-sm text-emerald-700">Rekapitulasi pengulangan hafalan santri.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
