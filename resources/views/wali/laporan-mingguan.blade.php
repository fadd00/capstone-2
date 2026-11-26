<x-app-layout title="Laporan Mingguan Tahfidz">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Mingguan Tahfidz') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Rekapitulasi Setoran &amp; Murojaah</h2>
                        <p class="text-xs text-gray-600">Periode: {{ $startDate->toDateString() }} s/d {{ $endDate->toDateString() }}</p>
                    </div>

                    <search>
                        <form method="GET" action="{{ route('wali-murid.laporan-mingguan.index') }}" class="flex flex-wrap items-center gap-2">
                            @if(request('santri_id'))
                                <input type="hidden" name="santri_id" value="{{ request('santri_id') }}">
                            @endif
                            <label for="tanggal_mulai" class="sr-only">Tanggal Mulai</label>
                            <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', $startDate->toDateString()) }}" class="text-xs border-gray-300 rounded-md">
                            <span class="text-xs text-gray-600">s/d</span>
                            <label for="tanggal_selesai" class="sr-only">Tanggal Selesai</label>
                            <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai', $endDate->toDateString()) }}" class="text-xs border-gray-300 rounded-md">
                            <button type="submit" class="px-3 py-1.5 bg-emerald-700 text-white text-xs font-semibold rounded-md hover:bg-emerald-800">Filter</button>
                        </form>
                    </search>
                </div>

                <nav aria-label="Filter santri" class="flex flex-wrap items-center gap-2 mb-6">
                    <a href="{{ route('wali-murid.laporan-mingguan.index', request()->only(['tanggal_mulai', 'tanggal_selesai'])) }}"
                       @if(!request('santri_id')) aria-current="page" @endif
                       class="px-3 py-1.5 rounded-md text-xs font-medium {{ !request('santri_id') ? 'bg-emerald-700 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Semua Santri</a>
                    @if(request('santri_id'))
                        @if($selectedChild = $children->firstWhere('id', request('santri_id')))
                            <span aria-current="page" class="px-3 py-1.5 rounded-md text-xs bg-emerald-700 text-white font-medium">{{ $selectedChild->nama }}</span>
                        @endif
                    @else
                        @foreach($children as $child)
                            <a href="{{ route('wali-murid.laporan-mingguan.index', array_merge(request()->only(['tanggal_mulai', 'tanggal_selesai']), ['santri_id' => $child->id])) }}"
                               class="px-3 py-1.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200">{{ $child->nama }}</a>
                        @endforeach
                    @endif
                </nav>

                <section class="mb-8">
                    <h3 id="wali-ziyadah-caption" class="font-bold text-gray-800 text-base mb-3 flex items-center">
                        <span aria-hidden="true" class="w-2.5 h-2.5 bg-emerald-700 rounded-full inline-block me-2"></span>
                        Setoran Hafalan Baru (Ziyadah)
                    </h3>
                    <div class="responsive-table-wrapper border rounded-lg" tabindex="0" role="region" aria-labelledby="wali-ziyadah-caption">
                        <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                            <caption class="sr-only">Rekap setoran hafalan baru ziyadah</caption>
                            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Santri</th>
                                    <th scope="col" class="px-4 py-3">Juz / Surat / Ayat</th>
                                    <th scope="col" class="px-4 py-3">Nilai</th>
                                    <th scope="col" class="px-4 py-3">Ustadz Pembimbing</th>
                                    <th scope="col" class="px-4 py-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($setoranList as $setoran)
                                    <tr class="hover:bg-gray-50">
                                        <td data-label="Tanggal" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $setoran->tanggal ? \Carbon\Carbon::parse($setoran->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td data-label="Santri" class="px-4 py-3 font-medium text-gray-900">{{ $setoran->santri->nama ?? '-' }}</td>
                                        <td data-label="Juz / Surat / Ayat" class="px-4 py-3 font-semibold text-gray-800">{{ $setoran->surat }} (Juz {{ $setoran->juz }}, Ayat {{ $setoran->ayat }})</td>
                                        <td data-label="Nilai" class="px-4 py-3 font-bold text-emerald-700">{{ $setoran->nilai }}</td>
                                        <td data-label="Ustadz Pembimbing" class="px-4 py-3">{{ $setoran->ustadz->name ?? '-' }}</td>
                                        <td data-label="Catatan" class="px-4 py-3 italic text-gray-600">{{ $setoran->catatan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada riwayat setoran ziyadah pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h3 id="wali-murojaah-caption" class="font-bold text-gray-800 text-base mb-3 flex items-center">
                        <span aria-hidden="true" class="w-2.5 h-2.5 bg-green-600 rounded-full inline-block me-2"></span>
                        Murojaah Hafalan
                    </h3>
                    <div class="responsive-table-wrapper border rounded-lg" tabindex="0" role="region" aria-labelledby="wali-murojaah-caption">
                        <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-xs text-gray-600">
                            <caption class="sr-only">Rekap murojaah hafalan</caption>
                            <thead class="bg-gray-50 text-gray-700 uppercase font-semibold">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Tanggal</th>
                                    <th scope="col" class="px-4 py-3">Santri</th>
                                    <th scope="col" class="px-4 py-3">Juz / Surat / Ayat</th>
                                    <th scope="col" class="px-4 py-3">Nilai</th>
                                    <th scope="col" class="px-4 py-3">Ustadz Pembimbing</th>
                                    <th scope="col" class="px-4 py-3">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($murojaahList as $murojaah)
                                    <tr class="hover:bg-gray-50">
                                        <td data-label="Tanggal" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $murojaah->tanggal ? \Carbon\Carbon::parse($murojaah->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td data-label="Santri" class="px-4 py-3 font-medium text-gray-900">{{ $murojaah->santri->nama ?? '-' }}</td>
                                        <td data-label="Juz / Surat / Ayat" class="px-4 py-3 font-semibold text-gray-800">{{ $murojaah->surat }} (Juz {{ $murojaah->juz }}, Ayat {{ $murojaah->ayat }})</td>
                                        <td data-label="Nilai" class="px-4 py-3 font-bold text-green-700">{{ $murojaah->nilai }}</td>
                                        <td data-label="Ustadz Pembimbing" class="px-4 py-3">{{ $murojaah->ustadz->name ?? '-' }}</td>
                                        <td data-label="Catatan" class="px-4 py-3 italic text-gray-600">{{ $murojaah->catatan ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada riwayat murojaah pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
