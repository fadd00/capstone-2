<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Riwayat Presensi Kegiatan Ustadz') }}
            </h2>
            <a href="{{ route('ustadz.presensi-kegiatan.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                + Check-in Presensi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Tanggal / Hari</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Kegiatan</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Jam</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Status GPS</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Materi</th>
                                <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($sessions as $session)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">
                                        {{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d/m/Y') : '-' }}
                                        <span class="text-xs text-gray-500 block">{{ $session->hari }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-800">
                                        {{ $session->kegiatanPondok->nama_kegiatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($session->status === 'valid')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 uppercase">Valid (Di Lokasi)</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 uppercase">Invalid (Luar Radius)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 max-w-xs truncate">
                                        {{ $session->materi ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                                            Presensi Santri
                                        </a>
                                        <a href="{{ route('ustadz.presensi-kegiatan.show', $session->id) }}" class="text-gray-600 hover:text-gray-900 text-xs">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada sesi presensi kegiatan yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
