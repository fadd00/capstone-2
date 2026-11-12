<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Sesi Presensi: ') . ($session->kegiatanPondok->nama_kegiatan ?? 'Kegiatan') }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                    Presensi Santri
                </a>
                <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow sm:rounded-lg space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Kegiatan Pondok</span>
                        <p class="text-lg font-semibold text-gray-900">{{ $session->kegiatanPondok->nama_kegiatan ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Status Validasi GPS</span>
                        <p class="text-base font-bold uppercase {{ $session->status === 'valid' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $session->status }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Tanggal & Hari</span>
                        <p class="text-base text-gray-800">{{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d F Y') : '-' }} ({{ $session->hari }})</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Waktu</span>
                        <p class="text-base text-gray-800">{{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Koordinat GPS</span>
                        <p class="text-sm font-mono text-gray-700">{{ $session->latitude }}, {{ $session->longitude }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase">Ustadz Pengampu</span>
                        <p class="text-base text-gray-800">{{ $session->ustadz->name ?? '-' }}</p>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <span class="text-xs text-gray-500 uppercase">Materi / Catatan Pembelajaran</span>
                    <p class="text-base text-gray-800 mt-1">{{ $session->materi ?? 'Tidak ada catatan materi.' }}</p>
                </div>

                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-800">Rekap Kehadiran Santri ({{ $session->presensiSantri->count() }} Santri)</h4>
                        <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">
                            Kelola Kehadiran Santri &rarr;
                        </a>
                    </div>

                    @if($session->presensiSantri->isNotEmpty())
                        <div class="divide-y divide-gray-100 text-sm">
                            @foreach($session->presensiSantri as $presensi)
                                <div class="py-2 flex justify-between items-center">
                                    <span>{{ $presensi->santri->nama ?? 'Santri' }} (NIS: {{ $presensi->santri->nis ?? '-' }})</span>
                                    <span class="font-semibold uppercase text-xs px-2 py-0.5 rounded
                                        @if($presensi->status === 'hadir') bg-green-100 text-green-800
                                        @elseif($presensi->status === 'izin') bg-blue-100 text-blue-800
                                        @elseif($presensi->status === 'sakit') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $presensi->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Belum ada presensi santri yang diinputkan untuk sesi ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
