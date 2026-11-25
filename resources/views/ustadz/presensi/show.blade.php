<x-app-layout title="Detail Sesi Presensi">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Sesi Presensi: ') . ($session->kegiatanPondok->nama_kegiatan ?? 'Kegiatan') }}
            </h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="px-4 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 text-sm font-medium">
                    Presensi Santri
                </a>
                <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg space-y-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Kegiatan Pondok</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $session->kegiatanPondok->nama_kegiatan ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Status Validasi GPS</dt>
                        <dd class="text-base font-bold uppercase {{ $session->status === 'valid' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $session->status }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Tanggal &amp; Hari</dt>
                        <dd class="text-base text-gray-800">{{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d F Y') : '-' }} ({{ $session->hari }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Waktu</dt>
                        <dd class="text-base text-gray-800">{{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Koordinat GPS</dt>
                        <dd class="text-sm font-mono text-gray-700">{{ $session->latitude }}, {{ $session->longitude }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Ustadz Pengampu</dt>
                        <dd class="text-base text-gray-800">{{ $session->ustadz->name ?? '-' }}</dd>
                    </div>
                </dl>

                <section class="border-t pt-4">
                    <h2 class="text-xs text-gray-500 uppercase font-medium">Materi / Catatan Pembelajaran</h2>
                    <p class="text-base text-gray-800 mt-1">{{ $session->materi ?? 'Tidak ada catatan materi.' }}</p>
                </section>

                @if($session->foto_bukti)
                    <section class="border-t pt-4">
                        <h2 class="text-xs text-gray-500 uppercase font-medium">Foto Bukti Kehadiran</h2>
                        <a href="{{ route('presensi-kegiatan.photo', $session->id) }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                            <img src="{{ route('presensi-kegiatan.photo', $session->id) }}" alt="Bukti foto kehadiran" class="max-h-96 rounded-md border border-gray-200 object-contain">
                        </a>
                    </section>
                @endif

                <section class="border-t pt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="font-semibold text-gray-800">Rekap Kehadiran Santri ({{ $session->presensiSantri->count() }} Santri)</h2>
                        <a href="{{ route('ustadz.presensi-santri.create', $session->id) }}" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium">
                            Kelola Kehadiran Santri &rarr;
                        </a>
                    </div>

                    @if($session->presensiSantri->isNotEmpty())
                        <ul class="divide-y divide-gray-100 text-sm">
                            @foreach($session->presensiSantri as $presensi)
                                <li class="py-2 flex justify-between items-center">
                                    <span>{{ $presensi->santri->nama ?? 'Santri' }} (NIS: {{ $presensi->santri->nis ?? '-' }})</span>
                                    <span class="font-semibold uppercase text-xs px-2 py-0.5 rounded
                                        @if($presensi->status === 'hadir') bg-green-100 text-green-800
                                        @elseif($presensi->status === 'izin') bg-blue-100 text-blue-800
                                        @elseif($presensi->status === 'sakit') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $presensi->status }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-600">Belum ada presensi santri yang diinputkan untuk sesi ini.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
