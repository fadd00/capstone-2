<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Ustadz') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Selamat Datang, {{ auth()->user()->name }}</h3>
                <p class="text-sm text-gray-600 mb-6">Portal Akademik Ustadz PPTQ Ibnu Juraimi</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <a href="{{ route('ustadz.presensi-kegiatan.create') }}" class="p-6 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                        <h4 class="font-bold text-indigo-900 text-lg mb-1">Check-in Presensi GPS</h4>
                        <p class="text-sm text-indigo-700">Lakukan presensi kehadiran kegiatan pondok berbasis geolokasi pesantren.</p>
                    </a>
                    <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="p-6 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <h4 class="font-bold text-green-900 text-lg mb-1">Riwayat Presensi Kegiatan</h4>
                        <p class="text-sm text-green-700">Lihat rekap presensi dan input kehadiran massal santri peserta.</p>
                    </a>
                    <a href="{{ route('ustadz.setoran-hafalan.index') }}" class="p-6 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <h4 class="font-bold text-blue-900 text-lg mb-1">Setoran Hafalan (Ziyadah)</h4>
                        <p class="text-sm text-blue-700">Catat dan pantau setoran hafalan baru santri binaan.</p>
                    </a>
                    <a href="{{ route('ustadz.murojaah.index') }}" class="p-6 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 transition">
                        <h4 class="font-bold text-teal-900 text-lg mb-1">Murojaah Hafalan</h4>
                        <p class="text-sm text-teal-700">Catat pengulangan hafalan santri binaan.</p>
                    </a>
                </div>

                <div class="border-t pt-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Jadwal / Kegiatan Pondok yang Ditugaskan</h4>
                    @if($kegiatanList->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($kegiatanList as $kegiatan)
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                    <h5 class="font-bold text-gray-900 text-base mb-1">{{ $kegiatan->nama_kegiatan }}</h5>
                                    <p class="text-xs text-gray-500 mb-2">Tingkatan: {{ strtoupper($kegiatan->tingkatan) }} | Kelas: {{ $kegiatan->kelas ?? 'Semua' }}</p>
                                    <p class="text-xs text-gray-600 line-clamp-2">{{ $kegiatan->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada kegiatan pondok yang ditugaskan kepada Anda.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
