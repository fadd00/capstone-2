<x-app-layout title="Riwayat Presensi Kegiatan">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Riwayat Presensi Kegiatan Ustadz') }}
            </h1>
            <a href="{{ route('ustadz.presensi-kegiatan.create') }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                + Check-in Presensi Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="ustadz-presensi-caption">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption id="ustadz-presensi-caption" class="sr-only">Daftar riwayat sesi presensi kegiatan ustadz</caption>
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Tanggal / Hari</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Kegiatan</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Jam</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Foto Bukti</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Status GPS</th>
                                <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Materi</th>
                                <th scope="col" class="px-6 py-3 text-right font-medium text-gray-600 uppercase text-xs">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($sessions as $session)
                                <tr>
                                    <td data-label="Tanggal / Hari" class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">
                                        {{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d/m/Y') : '-' }}
                                        <span class="text-xs text-gray-500 block">{{ $session->hari }}</span>
                                    </td>
                                    <td data-label="Kegiatan" class="px-6 py-4 whitespace-nowrap text-gray-800">
                                        {{ $session->kegiatanPondok->nama_kegiatan ?? '-' }}
                                    </td>
                                    <td data-label="Jam" class="px-6 py-4 whitespace-nowrap text-gray-600">
                                        {{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}
                                    </td>
                                    <td data-label="Foto Bukti" class="px-6 py-4">
                                        @if($session->foto_bukti)
                                            <a href="{{ route('presensi-kegiatan.photo', $session->id) }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ route('presensi-kegiatan.photo', $session->id) }}" alt="Bukti foto kehadiran" loading="lazy" class="h-14 w-20 rounded border border-gray-200 object-cover">
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Status GPS" class="px-6 py-4 whitespace-nowrap">
                                        @if($session->status === 'valid')
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 uppercase">Valid (Di Lokasi)</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 uppercase">Invalid (Luar Radius)</span>
                                        @endif
                                    </td>
                                    <td data-label="Materi" class="px-6 py-4 text-gray-600 max-w-xs truncate">
                                        {{ $session->materi ?? '-' }}
                                    </td>
                                    <td data-label="Aksi" class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="text-emerald-700 hover:text-emerald-900 font-medium text-xs">
                                            Presensi Santri
                                        </a>
                                        <a href="{{ route('ustadz.presensi-kegiatan.show', $session->id) }}" class="text-gray-700 hover:text-gray-900 text-xs">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada sesi presensi kegiatan yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
