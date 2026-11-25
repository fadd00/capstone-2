{{-- Alur: pilih surat -> isi ayat (dibatasi jumlah ayat surat) -> juz terisi otomatis. Juz final dihitung server. --}}
<fieldset data-tahfidz-range data-juz-starts="{{ json_encode(config('quran.juz')) }}" class="space-y-4">
    <legend class="sr-only">Surat, ayat, dan juz</legend>

    <div>
        <label for="surat" class="block text-sm font-medium text-gray-700">1. Nama Surat <span aria-hidden="true" class="text-red-600">*</span></label>
        <select name="surat" id="surat" required data-surah-select aria-describedby="surat-info" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm">
            <option value="">-- Pilih Nama Surat --</option>
            @foreach ($surahList as $index => $surah)
                <option value="{{ $surah }}" data-number="{{ $index + 1 }}" data-ayat="{{ config('quran.ayat')[$index] }}" @selected(old('surat') === $surah)>
                    {{ $index + 1 }}. {{ $surah }}
                </option>
            @endforeach
        </select>
        <p id="surat-info" data-surah-info class="mt-1 text-xs text-gray-500">Pilih surat terlebih dahulu untuk mengisi ayat.</p>
        <x-input-error class="mt-2" :messages="$errors->get('surat')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="ayat_mulai" class="block text-sm font-medium text-gray-700">2. Ayat Mulai <span aria-hidden="true" class="text-red-600">*</span></label>
            <input type="number" inputmode="numeric" name="ayat_mulai" id="ayat_mulai" min="1" value="{{ old('ayat_mulai') }}" required data-ayat-start class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm disabled:bg-gray-100">
            <x-input-error class="mt-2" :messages="$errors->get('ayat_mulai')" />
        </div>

        <div>
            <label for="ayat_selesai" class="block text-sm font-medium text-gray-700">Ayat Selesai</label>
            <input type="number" inputmode="numeric" name="ayat_selesai" id="ayat_selesai" min="1" value="{{ old('ayat_selesai') }}" data-ayat-end placeholder="Sama dengan ayat mulai" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm disabled:bg-gray-100">
            <x-input-error class="mt-2" :messages="$errors->get('ayat_selesai')" />
        </div>

        <div>
            <label for="juz_otomatis" class="block text-sm font-medium text-gray-700">3. Juz (otomatis)</label>
            <output id="juz_otomatis" data-juz-output for="surat ayat_mulai ayat_selesai" aria-live="polite" class="mt-1 block w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">-</output>
        </div>
    </div>
</fieldset>
