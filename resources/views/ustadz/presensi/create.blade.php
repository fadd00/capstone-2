<x-app-layout title="Check-in Presensi GPS">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Check-in Presensi Kegiatan (GPS Haversine)') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('ustadz.presensi-kegiatan.store') }}" enctype="multipart/form-data" class="space-y-4">
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

                    @php($checkInNow = now()->locale('id'))
                    <section class="rounded-md border border-blue-100 bg-blue-50 p-4" aria-live="polite">
                        <h2 class="text-sm font-semibold text-gray-800">Waktu Check-in</h2>
                        <p class="mt-1 text-xs text-gray-600">Waktu ditampilkan otomatis dan akan ditetapkan oleh server saat check-in dikirim.</p>
                        <dl class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                            <div>
                                <dt class="text-xs text-gray-500">Tanggal</dt>
                                <dd data-checkin-date>{{ $checkInNow->translatedFormat('d F Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Hari</dt>
                                <dd data-checkin-day>{{ $checkInNow->translatedFormat('l') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Jam</dt>
                                <dd><time data-checkin-time data-server-time="{{ $checkInNow->timestamp }}" datetime="{{ $checkInNow->toIso8601String() }}">{{ $checkInNow->format('H:i:s') }}</time> WIB</dd>
                            </div>
                        </dl>
                    </section>

                    <div>
                        <x-input-label for="jam_selesai" value="Jam Selesai (Opsional)" />
                        <x-text-input id="jam_selesai" name="jam_selesai" type="time" class="mt-1 block w-full" :value="old('jam_selesai')" />
                        <x-input-error class="mt-2" :messages="$errors->get('jam_selesai')" />
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
                                <x-text-input id="latitude" name="latitude" type="text" inputmode="decimal" readonly class="mt-1 block w-full font-mono text-sm" required />
                                <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
                            </div>
                            <div>
                                <x-input-label for="longitude" value="Longitude" />
                                <x-text-input id="longitude" name="longitude" type="text" inputmode="decimal" readonly class="mt-1 block w-full font-mono text-sm" required />
                                <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                            </div>
                        </div>
                        <p id="geo-status" role="status" aria-live="polite" class="text-xs text-gray-600 mt-2">
                            Batas toleransi radius geofence: 150 meter dari titik pusat pesantren.
                        </p>
                    </fieldset>

                    <fieldset class="border-t pt-4">
                        <x-input-label for="foto_bukti" value="Foto Bukti Kehadiran" />
                        <p class="mt-1 text-xs text-gray-600">Ambil foto kondisi/kegiatan saat berada di lokasi pesantren (maksimum 2 MB).</p>
                        <input id="foto_bukti" name="foto_bukti" type="file" accept="image/*" capture="environment" data-checkin-photo required
                               class="mt-2 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                        <x-input-error class="mt-2" :messages="$errors->get('foto_bukti')" />
                        <div data-photo-preview-container class="mt-3 hidden">
                            <img data-photo-preview alt="Pratinjau foto bukti kehadiran" class="max-h-72 rounded-md border border-gray-200 object-contain">
                            <button type="button" data-photo-retake class="mt-2 rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                Ulangi Foto
                            </button>
                        </div>
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
