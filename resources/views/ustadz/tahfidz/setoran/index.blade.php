<x-app-layout title="Setoran Hafalan Baru (Ziyadah)">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Setoran Hafalan Baru (Ziyadah)') }}
            </h1>
            <a href="{{ route('ustadz.setoran-hafalan.create') }}" class="px-4 py-2 bg-emerald-700 text-white text-sm font-semibold rounded-md hover:bg-emerald-800">
                + Input Setoran Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-gray-200 sm:rounded-lg p-6">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="setoran-table-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                        <caption id="setoran-table-caption" class="sr-only">Daftar setoran hafalan baru ziyadah</caption>
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tanggal</th>
                                <th scope="col" class="px-4 py-3">Santri</th>
                                <th scope="col" class="px-4 py-3">Juz / Surat / Ayat</th>
                                <th scope="col" class="px-4 py-3">Nilai</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($setoranList as $item)
                                <tr class="hover:bg-gray-50">
                                    <td data-label="Tanggal" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td data-label="Santri" class="px-4 py-3 font-medium text-gray-900">{{ $item->santri->nama ?? '-' }}</td>
                                    <td data-label="Juz / Surat / Ayat" class="px-4 py-3 font-semibold text-gray-800">{{ $item->surat }} (Juz {{ $item->juz }}, Ayat {{ $item->ayat }})</td>
                                    <td data-label="Nilai" class="px-4 py-3 font-bold text-emerald-700">{{ $item->nilai ?? '-' }}</td>
                                    <td data-label="Status" class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $item->status_selesai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $item->status_selesai ? 'Selesai' : 'Belum Selesai' }}
                                        </span>
                                    </td>
                                    <td data-label="Aksi" class="px-4 py-3 text-center justify-center">
                                        <a href="{{ route('ustadz.setoran-hafalan.show', $item->id) }}" class="text-emerald-700 hover:text-emerald-900 font-medium">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada catatan setoran hafalan baru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($setoranList->hasPages())
                    <div class="mt-4">
                        {{ $setoranList->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
