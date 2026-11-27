<x-app-layout title="Dashboard Ustadz">
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-900 leading-tight">
            {{ __('Dashboard Ustadz') }}
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section aria-labelledby="quick-actions-heading">
                <h2 id="quick-actions-heading" class="sr-only">Aksi Cepat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('ustadz.presensi-kegiatan.create') }}" class="dashboard-card dashboard-accent-blue p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Check-in Presensi GPS</h3>
                        <p class="text-xs text-gray-600 mt-1">Catat kehadiran kegiatan dengan geolokasi pesantren.</p>
                    </a>
                    <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="dashboard-card dashboard-accent-teal p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Riwayat Presensi</h3>
                        <p class="text-xs text-gray-600 mt-1">Lihat rekap dan input kehadiran massal santri.</p>
                    </a>
                    <a href="{{ route('ustadz.setoran-hafalan.index') }}" class="dashboard-card dashboard-accent-gold p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Setoran Hafalan (Ziyadah)</h3>
                        <p class="text-xs text-gray-600 mt-1">Catat setoran hafalan baru santri binaan.</p>
                    </a>
                    <a href="{{ route('ustadz.murojaah.index') }}" class="dashboard-card dashboard-accent-blue p-4 bg-white border border-gray-200 rounded-lg hover:border-gray-400 transition">
                        <h3 class="font-semibold text-gray-900">Murojaah Hafalan</h3>
                        <p class="text-xs text-gray-600 mt-1">Catat pengulangan hafalan santri binaan.</p>
                    </a>
                </div>
            </section>

            <section class="dashboard-panel bg-white border border-gray-200 rounded-lg p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Kegiatan yang Ditugaskan</h2>
                @if($kegiatanList->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($kegiatanList as $kegiatan)
                            <div class="dashboard-record p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ $kegiatan->nama_kegiatan }}</h3>
                                <p class="text-xs text-gray-600 mt-1">Tingkatan: {{ strtoupper($kegiatan->tingkatan) }} | Kelas: {{ $kegiatan->kelas ?? 'Semua' }}</p>
                                <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ $kegiatan->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="dashboard-empty p-4 text-sm text-gray-600">Belum ada kegiatan pondok yang ditugaskan kepada Anda.</p>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
