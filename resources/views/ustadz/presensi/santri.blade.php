<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Presensi Santri: ') . ($session->kegiatanPondok->nama_kegiatan ?? 'Kegiatan') }}
            </h2>
            <a href="{{ route('ustadz.presensi-kegiatan.show', $session->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                Kembali ke Detail Sesi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <div class="mb-6 p-4 bg-gray-50 border rounded-lg text-sm text-gray-700 flex flex-wrap gap-6">
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Tanggal</span>
                        <strong>{{ $session->tanggal ? \Carbon\Carbon::parse($session->tanggal)->format('d F Y') : '-' }} ({{ $session->hari }})</strong>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Jam</span>
                        <strong>{{ $session->jam_mulai }} {{ $session->jam_selesai ? '- ' . $session->jam_selesai : '' }}</strong>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase block">Target Tingkatan</span>
                        <strong>{{ strtoupper($session->kegiatanPondok->tingkatan ?? 'Semua') }} {{ $session->kegiatanPondok->kelas ? '- Kelas ' . $session->kegiatanPondok->kelas : '' }}</strong>
                    </div>
                </div>

                <form method="POST" action="{{ route('ustadz.presensi-santri.store', $session->id) }}">
                    @csrf

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">NIS</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Nama Santri</th>
                                    <th class="px-6 py-3 text-center font-medium text-gray-500 uppercase">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($santriList as $santri)
                                    @php
                                        $currentStatus = $existingAttendance[$santri->id] ?? 'hadir';
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-600">{{ $santri->nis }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $santri->nama }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-4">
                                            @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $statusKey => $label)
                                                <label class="inline-flex items-center text-xs">
                                                    <input type="radio" name="presensi[{{ $santri->id }}]" value="{{ $statusKey }}"
                                                        {{ $currentStatus === $statusKey ? 'checked' : '' }}
                                                        class="text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                                    <span class="ml-1 text-gray-700">{{ $label }}</span>
                                                </label>
                                            @endforeach
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
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 font-medium text-sm">
                            Simpan Presensi Santri
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
