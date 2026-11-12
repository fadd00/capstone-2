<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Setoran Hafalan Baru (Ziyadah)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('ustadz.setoran-hafalan.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="santri_id" class="block text-sm font-medium text-gray-700">Santri Binaan <span class="text-red-500">*</span></label>
                        <select name="santri_id" id="santri_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih Santri Binaan --</option>
                            @foreach($santriBinaan as $santri)
                                <option value="{{ $santri->id }}" {{ old('santri_id') == $santri->id ? 'selected' : '' }}>
                                    {{ $santri->nama }} (NIS: {{ $santri->nis }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal Setoran <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="juz" class="block text-sm font-medium text-gray-700">Juz (1 - 30) <span class="text-red-500">*</span></label>
                            <input type="number" name="juz" id="juz" min="1" max="30" value="{{ old('juz') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="surat" class="block text-sm font-medium text-gray-700">Nama Surat <span class="text-red-500">*</span></label>
                            <input type="text" name="surat" id="surat" value="{{ old('surat') }}" required placeholder="Contoh: An-Naba" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div>
                            <label for="ayat" class="block text-sm font-medium text-gray-700">Ayat <span class="text-red-500">*</span></label>
                            <input type="text" name="ayat" id="ayat" value="{{ old('ayat') }}" required placeholder="Contoh: 1-20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nilai" class="block text-sm font-medium text-gray-700">Nilai (0.00 - 100.00)</label>
                            <input type="number" step="0.01" min="0" max="100" name="nilai" id="nilai" value="{{ old('nilai') }}" placeholder="Contoh: 92.50" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="status_selesai" value="1" {{ old('status_selesai') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-700 font-medium">Tuntas / Selesai Target</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan Evaluasi / Makhraj / Tajwid</label>
                        <textarea name="catatan" id="catatan" rows="3" placeholder="Catatan evaluasi ustadz..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('ustadz.setoran-hafalan.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">Simpan Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
