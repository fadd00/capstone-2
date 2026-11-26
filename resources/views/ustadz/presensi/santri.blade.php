<x-app-layout title="Presensi Santri">
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4">
            <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Presensi Santri: ') . ($session->kegiatanPondok->nama_kegiatan ?? 'Kegiatan') }}
            </h1>
            <a href="{{ route('ustadz.presensi-kegiatan.show', $session->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                Kembali ke Detail Sesi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
            @elseif ($session->status === 'invalid')
                <div role="alert" class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">Check-in sesi ini berada di luar radius pesantren, sehingga presensi santri tidak dapat disimpan.</div>
            @endif

            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <dl class="mb-6 p-4 bg-gray-50 border rounded-lg text-sm text-gray-700 flex flex-wrap gap-6">
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Tanggal</dt>
                        <dd class="font-semibold">{{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d F Y') : '-' }} ({{ $session->hari }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Jam</dt>
                        <dd class="font-semibold">{{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 uppercase">Target Tingkatan</dt>
                        <dd class="font-semibold">{{ strtoupper($session->kegiatanPondok->tingkatan ?? 'Semua') }} {{ $session->kegiatanPondok->kelas ? '- Kelas ' . $session->kegiatanPondok->kelas : '' }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('ustadz.presensi-santri.store', $session->id) }}">
                    @csrf

                    <div class="responsive-table-wrapper mb-6" tabindex="0" role="region" aria-labelledby="presensi-santri-caption">
                        <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                            <caption id="presensi-santri-caption" class="sr-only">Daftar input status kehadiran santri</caption>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">NIS</th>
                                    <th scope="col" class="px-6 py-3 text-left font-medium text-gray-600 uppercase text-xs">Nama Santri</th>
                                    <th scope="col" class="px-6 py-3 text-center font-medium text-gray-600 uppercase text-xs">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($santriList as $santri)
                                    @php
                                        $currentStatus = $existingAttendance[$santri->id] ?? 'hadir';
                                    @endphp
                                    <tr>
                                        <td data-label="NIS" class="px-6 py-4 whitespace-nowrap font-mono text-gray-600">{{ $santri->nis }}</td>
                                        <td data-label="Nama Santri" class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $santri->nama }}</td>
                                        <td data-label="Status Kehadiran" class="px-6 py-4 whitespace-nowrap text-center">
                                            <fieldset class="inline-flex flex-wrap justify-end sm:justify-center gap-4">
                                                <legend class="sr-only">Status kehadiran {{ $santri->nama }}</legend>
                                                @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $statusKey => $label)
                                                    <label for="status-{{ $santri->id }}-{{ $statusKey }}" class="inline-flex items-center text-xs">
                                                        <input id="status-{{ $santri->id }}-{{ $statusKey }}"
                                                               type="radio"
                                                               name="presensi[{{ $santri->id }}]"
                                                               value="{{ $statusKey }}"
                                                               {{ $currentStatus === $statusKey ? 'checked' : '' }}
                                                               class="text-emerald-700 border-gray-300 focus:ring-emerald-600">
                                                        <span class="ms-1.5 text-gray-700">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </fieldset>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada santri pada tingkatan/kelas kegiatan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="submit" class="px-6 py-2 bg-emerald-700 text-white rounded-md hover:bg-emerald-800 font-medium text-sm">
                            Simpan Presensi Santri
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
