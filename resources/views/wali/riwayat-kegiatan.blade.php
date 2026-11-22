<x-app-layout title="Riwayat Kehadiran Santri">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Kehadiran Santri') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b">
                    <div>
                        <h2 id="wali-kehadiran-caption" class="text-lg font-bold text-gray-900">Catatan Kehadiran Kegiatan Pondok</h2>
                        <p class="text-xs text-gray-600">Log kehadiran kegiatan harian santri yang dicatat oleh ustadz pengampu</p>
                    </div>
                </div>

                <nav aria-label="Filter santri" class="flex flex-wrap items-center gap-2 mb-6">
                    <a href="{{ route('wali-murid.riwayat-kegiatan.index') }}"
                       @if(!request('santri_id')) aria-current="page" @endif
                       class="px-3 py-1.5 rounded-md text-xs font-medium {{ !request('santri_id') ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Semua Santri</a>
                    @if(request('santri_id'))
                        @if($selectedChild = $children->firstWhere('id', request('santri_id')))
                            <span aria-current="page" class="px-3 py-1.5 rounded-md text-xs bg-emerald-700 text-white font-medium">{{ $selectedChild->nama }}</span>
                        @endif
                    @else
                        @foreach($children as $child)
                            <a href="{{ route('wali-murid.riwayat-kegiatan.index', ['santri_id' => $child->id]) }}"
                               class="px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $child->nama }}</a>
                        @endforeach
                    @endif
                </nav>

                <div class="responsive-table-wrapper border rounded-lg" tabindex="0" role="region" aria-labelledby="wali-kehadiran-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                        <caption class="sr-only">Catatan kehadiran kegiatan pondok santri</caption>
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tanggal &amp; Waktu</th>
                                <th scope="col" class="px-4 py-3">Santri</th>
                                <th scope="col" class="px-4 py-3">Kegiatan Pondok</th>
                                <th scope="col" class="px-4 py-3">Ustadz Pengampu</th>
                                <th scope="col" class="px-4 py-3">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($presensiList as $presensi)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Tanggal & Waktu" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $presensi->presensiKegiatan?->tanggal ? \Carbon\Carbon::parse($presensi->presensiKegiatan->tanggal)->format('d/m/Y') : '-' }}
                                        <span class="text-gray-500 text-xs ms-1">({{ $presensi->presensiKegiatan?->jam_mulai ?? '-' }})</span>
                                    </td>
                                    <td data-label="Santri" class="px-4 py-3 font-medium text-gray-900">{{ $presensi->santri->nama ?? '-' }}</td>
                                    <td data-label="Kegiatan Pondok" class="px-4 py-3 font-semibold text-gray-800">{{ $presensi->presensiKegiatan?->kegiatanPondok?->nama_kegiatan ?? '-' }}</td>
                                    <td data-label="Ustadz Pengampu" class="px-4 py-3">{{ $presensi->presensiKegiatan?->ustadz?->name ?? '-' }}</td>
                                    <td data-label="Status Kehadiran" class="px-4 py-3">
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
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada riwayat kehadiran santri.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
