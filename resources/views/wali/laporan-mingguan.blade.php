<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Mingguan Tahfidz') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Rekapitulasi Setoran & Murojaah</h3>
                        <p class="text-xs text-gray-500">Periode: {{ $startDate->toDateString() }} s/d {{ $endDate->toDateString() }}</p>
                    </div>

                    <form method="GET" action="{{ route('wali-murid.laporan-mingguan.index') }}" class="flex flex-wrap items-center gap-2">
                        @if(request('santri_id'))
                            <input type="hidden" name="santri_id" value="{{ request('santri_id') }}">
                        @endif
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', $startDate->toDateString()) }}" class="text-xs border-gray-300 rounded-md">
                        <span class="text-xs text-gray-500">s/d</span>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai', $endDate->toDateString()) }}" class="text-xs border-gray-300 rounded-md">
                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700">Filter</button>
                    </form>
                </div>

                {{-- Filter Santri Pills --}}
                <div class="flex items-center space-x-2 mb-6">
                    <a href="{{ route('wali-murid.laporan-mingguan.index', request()->only(['tanggal_mulai', 'tanggal_selesai'])) }}" class="px-3 py-1.5 rounded-md text-xs {{ !request('santri_id') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Semua Santri</a>
                    @if(request('santri_id'))
                        @if($selectedChild = $children->firstWhere('id', request('santri_id')))
                            <span class="px-3 py-1.5 rounded-md text-xs bg-indigo-600 text-white font-medium">{{ $selectedChild->nama }}</span>
                        @endif
                    @else
                        @foreach($children as $child)
                            <a href="{{ route('wali-murid.laporan-mingguan.index', array_merge(request()->only(['tanggal_mulai', 'tanggal_selesai']), ['santri_id' => $child->id])) }}" class="px-3 py-1.5 rounded-md text-xs bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $child->nama }}</a>
                        @endforeach
                    @endif
                </div>

                {{-- Tabel Setoran Hafalan Baru --}}
                <div class="mb-8">
                    <h4 class="font-bold text-gray-800 text-base mb-3 flex items-center">
                        <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full inline-block mr-2"></span>
                        Setoran Hafalan Baru (Ziyadah)
                    </h4>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Santri</th>
                                    <th class="px-4 py-3">Juz / Surat / Ayat</th>
                                    <th class="px-4 py-3">Nilai</th>
                                    <th class="px-4 py-3">Ustadz Pembimbing</th>
                                    <th class="px-4 py-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($setoranList as $setoran)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $setoran->tanggal ? \Carbon\Carbon::parse($setoran->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $setoran->santri->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $setoran->surat }} (Juz {{ $setoran->juz }}, Ayat {{ $setoran->ayat }})</td>
                                        <td class="px-4 py-3 font-bold text-indigo-600">{{ $setoran->nilai }}</td>
                                        <td class="px-4 py-3">{{ $setoran->ustadz->name ?? '-' }}</td>
                                        <td class="px-4 py-3 italic text-gray-500">{{ $setoran->catatan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada riwayat setoran ziyadah pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tabel Murojaah Hafalan --}}
                <div>
                    <h4 class="font-bold text-gray-800 text-base mb-3 flex items-center">
                        <span class="w-2.5 h-2.5 bg-green-600 rounded-full inline-block mr-2"></span>
                        Murojaah Hafalan
                    </h4>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Santri</th>
                                    <th class="px-4 py-3">Juz / Surat / Ayat</th>
                                    <th class="px-4 py-3">Nilai</th>
                                    <th class="px-4 py-3">Ustadz Pembimbing</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($murojaahList as $murojaah)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $murojaah->tanggal ? \Carbon\Carbon::parse($murojaah->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $murojaah->santri->nama ?? '-' }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $murojaah->surat }} (Juz {{ $murojaah->juz }}, Ayat {{ $murojaah->ayat }})</td>
                                        <td class="px-4 py-3 font-bold text-green-600">{{ $murojaah->nilai }}</td>
                                        <td class="px-4 py-3">{{ $murojaah->ustadz->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada riwayat murojaah pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
