<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Check-in Presensi Kegiatan (GPS Haversine)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form method="POST" action="{{ route('ustadz.presensi-kegiatan.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="kegiatan_pondok_id" value="Pilih Kegiatan Pondok" />
                        <select id="kegiatan_pondok_id" name="kegiatan_pondok_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                            <option value="">-- Pilih Kegiatan yang Ditugaskan --</option>
                            @foreach($kegiatanList as $kegiatan)
                                <option value="{{ $kegiatan->id }}" {{ old('kegiatan_pondok_id') == $kegiatan->id ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }} ({{ strtoupper($kegiatan->tingkatan) }} {{ $kegiatan->kelas ? '- Kelas ' . $kegiatan->kelas : '' }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('kegiatan_pondok_id')" />
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
                            <x-text-input id="jam_mulai" name="jam_mulai" type="text" class="mt-1 block w-full" :value="old('jam_mulai', date('H:i'))" required placeholder="07:00" />
                            <x-input-error class="mt-2" :messages="$errors->get('jam_mulai')" />
                        </div>
                        <div>
                            <x-input-label for="jam_selesai" value="Jam Selesai (Opsional)" />
                            <x-text-input id="jam_selesai" name="jam_selesai" type="text" class="mt-1 block w-full" :value="old('jam_selesai')" placeholder="08:30" />
                            <x-input-error class="mt-2" :messages="$errors->get('jam_selesai')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="materi" value="Materi / Catatan Kegiatan (Opsional)" />
                        <textarea id="materi" name="materi" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Contoh: Bab Sholat Berjamaah">{{ old('materi') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('materi')" />
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold text-gray-700">Koordinat Geolocation GPS</span>
                            <button type="button" onclick="getLocation()" class="text-xs px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded border">
                                📍 Ambil Lokasi Sekarang
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="latitude" value="Latitude" />
                                <x-text-input id="latitude" name="latitude" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('latitude', '-7.797068')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
                            </div>
                            <div>
                                <x-input-label for="longitude" value="Longitude" />
                                <x-text-input id="longitude" name="longitude" type="text" class="mt-1 block w-full font-mono text-sm" :value="old('longitude', '110.370529')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Batas toleransi radius geofence: 150 meter dari titik pusat pesantren.</p>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Check-in Presensi</x-primary-button>
                        <a href="{{ route('ustadz.presensi-kegiatan.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                }, function(error) {
                    alert('Gagal mengambil koordinat: ' + error.message);
                });
            } else {
                alert('Browser tidak mendukung geolokasi.');
            }
        }
    </script>
</x-app-layout>
