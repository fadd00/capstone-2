<x-app-layout title="Laporan Presensi Ustadz Global">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan Presensi Ustadz Global') }}
            </h1>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-ustadz', 'format' => 'pdf'])) }}" class="px-3 py-1.5 bg-red-700 text-white text-xs font-semibold rounded-md hover:bg-red-800">
                    Ekspor PDF
                </a>
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-ustadz', 'format' => 'excel'])) }}" class="px-3 py-1.5 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">
                    Ekspor Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6">
                <search class="block mb-6 pb-6 border-b">
                    <form method="GET" action="{{ route('admin.laporan.presensi-ustadz') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="filter-ustadz-id" class="block text-xs font-medium text-gray-700 mb-1">ID Ustadz</label>
                            <input id="filter-ustadz-id" type="text" inputmode="numeric" name="ustadz_id" value="{{ request('ustadz_id') }}" placeholder="Filter ID Ustadz..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div>
                            <label for="filter-kegiatan-id" class="block text-xs font-medium text-gray-700 mb-1">ID Kegiatan</label>
                            <input id="filter-kegiatan-id" type="text" inputmode="numeric" name="kegiatan_pondok_id" value="{{ request('kegiatan_pondok_id') }}" placeholder="Filter ID Kegiatan..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div>
                            <label for="filter-bulan" class="block text-xs font-medium text-gray-700 mb-1">Bulan</label>
                            <input id="filter-bulan" type="month" name="bulan" value="{{ request('bulan') }}" class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="px-4 py-2 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Filter</button>
                            <a href="{{ route('admin.laporan.presensi-ustadz') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs rounded-md hover:bg-gray-50">Reset</a>
                        </div>
                    </form>
                </search>

                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="lap-presensi-ustadz-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                        <caption id="lap-presensi-ustadz-caption" class="sr-only">Rekap laporan presensi ustadz global</caption>
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Hari &amp; Jam</th>
                                <th scope="col" class="px-4 py-3">Ustadz</th>
                                <th scope="col" class="px-4 py-3">Kegiatan Pondok</th>
                                <th scope="col" class="px-4 py-3">Foto Bukti</th>
                                <th scope="col" class="px-4 py-3">Materi</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($presensiList as $presensi)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Tanggal" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $presensi->tanggal ? \Carbon\Carbon::parse($presensi->tanggal)->format('Y-m-d') : '-' }}
                                    </td>
                                    <td data-label="Hari & Jam" class="px-4 py-3 text-xs">{{ $presensi->hari ?? '-' }}, {{ $presensi->jam_mulai ?? '-' }} - {{ $presensi->jam_selesai ?? '-' }}</td>
                                    <td data-label="Ustadz" class="px-4 py-3 font-semibold text-gray-900">{{ $presensi->ustadz->name ?? '-' }}</td>
                                    <td data-label="Kegiatan Pondok" class="px-4 py-3 font-medium text-emerald-800">{{ $presensi->kegiatanPondok->nama_kegiatan ?? '-' }}</td>
                                    <td data-label="Foto Bukti" class="px-4 py-3">
                                        @if($presensi->foto_bukti)
                                            <a href="{{ route('presensi-kegiatan.photo', $presensi->id) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ route('presensi-kegiatan.photo', $presensi->id) }}" alt="Bukti foto kehadiran" loading="lazy" class="h-14 w-20 rounded border border-gray-200 object-cover">
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Materi" class="px-4 py-3 text-xs text-gray-600">{{ $presensi->materi ?? '-' }}</td>
                                    <td data-label="Status" class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $presensi->status === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $presensi->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada rekaman presensi ustadz yang sesuai filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
