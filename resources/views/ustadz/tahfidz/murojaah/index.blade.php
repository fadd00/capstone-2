<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Murojaah Hafalan') }}
            </h2>
            <a href="{{ route('ustadz.murojaah.create') }}" class="px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700">
                + Input Murojaah Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 uppercase font-semibold text-xs">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Santri</th>
                                <th class="px-4 py-3">Juz / Surat / Ayat</th>
                                <th class="px-4 py-3">Nilai</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($murojaahList as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $item->santri->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $item->surat }} (Juz {{ $item->juz }}, Ayat {{ $item->ayat }})</td>
                                    <td class="px-4 py-3 font-bold text-green-600">{{ $item->nilai ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $item->status_selesai ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $item->status_selesai ? 'Selesai' : 'Belum Selesai' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('ustadz.murojaah.show', $item->id) }}" class="text-green-600 hover:text-green-900 font-medium">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada catatan murojaah hafalan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($murojaahList->hasPages())
                    <div class="mt-4">
                        {{ $murojaahList->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
