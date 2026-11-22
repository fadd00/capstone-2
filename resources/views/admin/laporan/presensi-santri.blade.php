<x-app-layout title="Laporan Presensi Santri Global">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan Presensi Santri Global') }}
            </h1>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-santri', 'format' => 'pdf'])) }}" class="px-3 py-1.5 bg-red-700 text-white text-xs font-semibold rounded-md hover:bg-red-800">
                    Ekspor PDF
                </a>
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-santri', 'format' => 'excel'])) }}" class="px-3 py-1.5 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">
                    Ekspor Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6">
                <search class="block mb-6 pb-6 border-b">
                    <form method="GET" action="{{ route('admin.laporan.presensi-santri') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label for="filter-status" class="block text-xs font-medium text-gray-700 mb-1">Status Kehadiran</label>
                            <select id="filter-status" name="status" class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Semua Status --</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter-tingkatan" class="block text-xs font-medium text-gray-700 mb-1">Tingkatan</label>
                            <select id="filter-tingkatan" name="tingkatan" class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Semua Tingkatan --</option>
                                <option value="tsanawiyah" {{ request('tingkatan') == 'tsanawiyah' ? 'selected' : '' }}>Tsanawiyah</option>
                                <option value="aliyah" {{ request('tingkatan') == 'aliyah' ? 'selected' : '' }}>Aliyah</option>
                                <option value="takhassus" {{ request('tingkatan') == 'takhassus' ? 'selected' : '' }}>Takhassus</option>
                            </select>
                        </div>

                        <div>
                            <label for="filter-santri-id" class="block text-xs font-medium text-gray-700 mb-1">ID Santri</label>
                            <input id="filter-santri-id" type="text" inputmode="numeric" name="santri_id" value="{{ request('santri_id') }}" placeholder="Filter ID Santri..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div>
                            <label for="filter-kegiatan-id" class="block text-xs font-medium text-gray-700 mb-1">ID Kegiatan</label>
                            <input id="filter-kegiatan-id" type="text" inputmode="numeric" name="kegiatan_pondok_id" value="{{ request('kegiatan_pondok_id') }}" placeholder="Filter ID Kegiatan..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Filter</button>
                            <a href="{{ route('admin.laporan.presensi-santri') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs rounded-md hover:bg-gray-50">Reset</a>
                        </div>
                    </form>
                </search>

                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="lap-presensi-santri-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                        <caption id="lap-presensi-santri-caption" class="sr-only">Rekap laporan presensi santri global</caption>
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Santri</th>
                                <th scope="col" class="px-4 py-3">Tingkatan / Kelas</th>
                                <th scope="col" class="px-4 py-3">Kegiatan Pondok</th>
                                <th scope="col" class="px-4 py-3">Ustadz Pengampu</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($presensiSantriList as $presensi)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Tanggal" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $presensi->presensiKegiatan?->tanggal ? \Carbon\Carbon::parse($presensi->presensiKegiatan->tanggal)->format('Y-m-d') : '-' }}
                                    </td>
                                    <td data-label="Santri" class="px-4 py-3 font-semibold text-gray-900">{{ $presensi->santri->nama ?? '-' }}</td>
                                    <td data-label="Tingkatan / Kelas" class="px-4 py-3 text-xs">{{ strtoupper($presensi->santri->tingkatan ?? '-') }} / {{ $presensi->santri->kelas ?? '-' }}</td>
                                    <td data-label="Kegiatan Pondok" class="px-4 py-3 font-medium text-emerald-800">{{ $presensi->presensiKegiatan?->kegiatanPondok?->nama_kegiatan ?? '-' }}</td>
                                    <td data-label="Ustadz Pengampu" class="px-4 py-3 text-xs text-gray-600">{{ $presensi->presensiKegiatan?->ustadz?->name ?? '-' }}</td>
                                    <td data-label="Status" class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                                            {{ $presensi->status === 'hadir' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $presensi->status === 'izin' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $presensi->status === 'sakit' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $presensi->status === 'alpha' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ $presensi->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada rekaman presensi santri yang sesuai filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
