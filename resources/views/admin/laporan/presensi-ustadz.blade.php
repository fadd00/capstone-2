<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Laporan Presensi Ustadz Global') }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-ustadz', 'format' => 'pdf'])) }}" class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700">
                    Ekspor PDF
                </a>
                <a href="{{ route('admin.laporan.export', array_merge(request()->query(), ['laporan' => 'presensi-ustadz', 'format' => 'excel'])) }}" class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">
                    Ekspor Excel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                {{-- Form Filter --}}
                <form method="GET" action="{{ route('admin.laporan.presensi-ustadz') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 pb-6 border-b">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">ID Ustadz</label>
                        <input type="text" name="ustadz_id" value="{{ request('ustadz_id') }}" placeholder="Filter ID Ustadz..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">ID Kegiatan</label>
                        <input type="text" name="kegiatan_pondok_id" value="{{ request('kegiatan_pondok_id') }}" placeholder="Filter ID Kegiatan..." class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bulan</label>
                        <input type="month" name="bulan" value="{{ request('bulan') }}" class="w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700">Filter</button>
                        <a href="{{ route('admin.laporan.presensi-ustadz') }}" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs rounded-md hover:bg-gray-50">Reset</a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Hari & Jam</th>
                                <th class="px-4 py-3">Ustadz</th>
                                <th class="px-4 py-3">Kegiatan Pondok</th>
                                <th class="px-4 py-3">Materi</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($presensiList as $presensi)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $presensi->tanggal ? \Carbon\Carbon::parse($presensi->tanggal)->format('Y-m-d') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $presensi->hari ?? '-' }}, {{ $presensi->jam_mulai ?? '-' }} - {{ $presensi->jam_selesai ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $presensi->ustadz->name ?? '-' }}</td>
                                    <td class="px-4 py-3 font-medium text-indigo-700">{{ $presensi->kegiatanPondok->nama_kegiatan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $presensi->materi ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $presensi->status === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $presensi->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada rekaman presensi ustadz yang sesuai filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
