<x-app-layout title="Tambah Kegiatan Pondok">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Kegiatan Pondok') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('admin.kegiatan.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="nama_kegiatan" value="Nama Kegiatan" />
                        <x-text-input id="nama_kegiatan" name="nama_kegiatan" type="text" class="mt-1 block w-full" :value="old('nama_kegiatan')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('nama_kegiatan')" />
                    </div>

                    <div>
                        <x-input-label for="tingkatan" value="Target Tingkatan" />
                        <select id="tingkatan" name="tingkatan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm" required>
                            <option value="semua" {{ old('tingkatan') == 'semua' ? 'selected' : '' }}>Semua Tingkatan</option>
                            <option value="tsanawiyah" {{ old('tingkatan') == 'tsanawiyah' ? 'selected' : '' }}>Tsanawiyah</option>
                            <option value="aliyah" {{ old('tingkatan') == 'aliyah' ? 'selected' : '' }}>Aliyah</option>
                            <option value="takhassus" {{ old('tingkatan') == 'takhassus' ? 'selected' : '' }}>Takhassus</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tingkatan')" />
                    </div>

                    <div>
                        <x-input-label for="kelas" value="Target Kelas (Opsional)" />
                        <x-text-input id="kelas" name="kelas" type="text" class="mt-1 block w-full" :value="old('kelas')" placeholder="Contoh: 7, 10 IPA, atau kosongkan untuk semua kelas" />
                        <x-input-error class="mt-2" :messages="$errors->get('kelas')" />
                    </div>

                    <div>
                        <x-input-label for="deskripsi" value="Deskripsi Kegiatan (Opsional)" />
                        <textarea id="deskripsi" name="deskripsi" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm">{{ old('deskripsi') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('deskripsi')" />
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Simpan Kegiatan</x-primary-button>
                        <a href="{{ route('admin.kegiatan.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
