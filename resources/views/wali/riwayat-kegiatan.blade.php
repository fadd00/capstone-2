<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Kehadiran Santri') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Catatan Kehadiran Kegiatan Pondok</h3>
                        <p class="text-xs text-gray-500">Log kehadiran kegiatan harian santri yang dicatat oleh ustadz pengampu</p>
                    </div>
                </div>

                {{-- Filter Santri Pills --}}
                <div class="flex items-center space-x-2 mb-6">
                    <a href="{{ route('wali-murid.riwayat-kegiatan.index') }}" class="px-3 py-1.5 rounded-md text-xs {{ !request('santri_id') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Semua Santri</a>
                    @if(request('santri_id'))
                        @if($selectedChild = $children->firstWhere('id', request('santri_id')))
                            <span class="px-3 py-1.5 rounded-md text-xs bg-indigo-600 text-white font-medium">{{ $selectedChild->nama }}</span>
                        @endif
                    @else
                        @foreach($children as $child)
                            <a href="{{ route('wali-murid.riwayat-kegiatan.index', ['santri_id' => $child->id]) }}" class="px-3 py-1.5 rounded-md text-xs bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $child->nama }}</a>
                        @endforeach
                    @endif
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-3">Tanggal & Waktu</th>
                                <th class="px-4 py-3">Santri</th>
                                <th class="px-4 py-3">Kegiatan Pondok</th>
                                <th class="px-4 py-3">Ustadz Pengampu</th>
                                <th class="px-4 py-3">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($presensiList as $presensi)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $presensi->presensiKegiatan?->tanggal ? \Carbon\Carbon::parse($presensi->presensiKegiatan->tanggal)->format('d/m/Y') : '-' }}
                                        <span class="text-gray-400 text-xs ml-1">({{ $presensi->presensiKegiatan?->jam_mulai ?? '-' }})</span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $presensi->santri->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $presensi->presensiKegiatan?->kegiatanPondok?->nama_kegiatan ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $presensi->presensiKegiatan?->ustadz?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold
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
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada riwayat kehadiran santri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
