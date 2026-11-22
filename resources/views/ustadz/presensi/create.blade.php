<x-app-layout title="Check-in Presensi GPS">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Check-in Presensi Kegiatan (GPS Haversine)') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('ustadz.presensi-kegiatan.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="kegiatan_pondok_id" value="Pilih Kegiatan Pondok" />
                        <select id="kegiatan_pondok_id" name="kegiatan_pondok_id"
                                @if ($errors->has('kegiatan_pondok_id')) aria-invalid="true" aria-describedby="kegiatan-error" @endif
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm" required>
                            <option value="">-- Pilih Kegiatan yang Ditugaskan --</option>
                            @foreach($kegiatanList as $kegiatan)
                                <option value="{{ $kegiatan->id }}" {{ old('kegiatan_pondok_id') == $kegiatan->id ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }} ({{ strtoupper($kegiatan->tingkatan) }} {{ $kegiatan->kelas ? '- Kelas ' . $kegiatan->kelas : '' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error id="kegiatan-error" class="mt-2" :messages="$errors->get('kegiatan_pondok_id')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal" value="Tanggal Kegiatan" />
                            <x-text-input id="tanggal" name="tanggal" type="date" class="mt-1 block w-full" :value="old('tanggal', date('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('tanggal')" />
                        </div>
                        <div>
                            <x-input-label for="hari" value="Hari" />
                            <x-text-input id="hari" name="hari" type="text" class="mt-1 block w-full" :value="old('hari')" placeholder="Contoh: Senin" />
                            <x-input-error class="mt-2" :messages="$errors->get('hari')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="jam_mulai" value="Jam Mulai" />
                            <x-text-input id="jam_mulai" name="jam_mulai" type="time" class="mt-1 block w-full" :value="old('jam_mulai', date('H:i'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('jam_mulai')" />
                        </div>
                        <div>
                            <x-input-label for="jam_selesai" value="Jam Selesai (Opsional)" />
                            <x-text-input id="jam_selesai" name="jam_selesai" type="time" class="mt-1 block w-full" :value="old('jam_selesai')" />
                            <x-input-error class="mt-2" :messages="$errors->get('jam_selesai')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="materi" value="Materi / Catatan Kegiatan (Opsional)" />
                        <textarea id="materi" name="materi" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm" placeholder="Contoh: Bab Sholat Berjamaah">{{ old('materi') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('materi')" />
                    </div>

                    <fieldset class="border-t pt-4">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-2">
                            <legend class="text-sm font-semibold text-gray-700">Koordinat Geolocation GPS</legend>
                            <button type="button"
                                    data-geo-trigger
                                    data-geo-lat="latitude"
                                    data-geo-lng="longitude"
                                    data-geo-status="geo-status"
                                    class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md border border-gray-300 font-medium">
                                Ambil Lokasi Sekarang
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="latitude" value="Latitude" />
                                <x-text-input id="latitude" name="latitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono text-sm" :value="old('latitude', '-7.797068')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
                            </div>
                            <div>
                                <x-input-label for="longitude" value="Longitude" />
                                <x-text-input id="longitude" name="longitude" type="text" inputmode="decimal" class="mt-1 block w-full font-mono text-sm" :value="old('longitude', '110.370529')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                            </div>
                        </div>
                        <p id="geo-status" role="status" aria-live="polite" class="text-xs text-gray-600 mt-2">
                            Batas toleransi radius geofence: 150 meter dari titik pusat pesantren.
                        </p>
                    </fieldset>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Check-in Presensi</x-primary-button>
                        <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
