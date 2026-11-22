<x-app-layout title="Edit Data Santri">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Santri: ') . $santri->nama }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('admin.santri.update', $santri->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="nama" value="Nama Lengkap" />
                        <x-text-input id="nama" name="nama" type="text" autocomplete="name" class="mt-1 block w-full" :value="old('nama', $santri->nama)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('nama')" />
                    </div>

                    <div>
                        <x-input-label for="nis" value="Nomor Induk Santri (NIS)" />
                        <x-text-input id="nis" name="nis" type="text" inputmode="numeric" autocorrect="off" spellcheck="false" class="mt-1 block w-full" :value="old('nis', $santri->nis)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nis')" />
                    </div>

                    <div>
                        <x-input-label for="tingkatan" value="Tingkatan" />
                        <select id="tingkatan" name="tingkatan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm" required>
                            <option value="tsanawiyah" {{ old('tingkatan', $santri->tingkatan) == 'tsanawiyah' ? 'selected' : '' }}>Tsanawiyah</option>
                            <option value="aliyah" {{ old('tingkatan', $santri->tingkatan) == 'aliyah' ? 'selected' : '' }}>Aliyah</option>
                            <option value="takhassus" {{ old('tingkatan', $santri->tingkatan) == 'takhassus' ? 'selected' : '' }}>Takhassus</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('tingkatan')" />
                    </div>

                    <div>
                        <x-input-label for="kelas" value="Kelas" />
                        <x-text-input id="kelas" name="kelas" type="text" class="mt-1 block w-full" :value="old('kelas', $santri->kelas)" />
                        <x-input-error class="mt-2" :messages="$errors->get('kelas')" />
                    </div>

                    <div>
                        <x-input-label for="wali_murid_id" value="Wali Murid" />
                        <select id="wali_murid_id" name="wali_murid_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm">
                            <option value="">-- Tanpa Wali / Belum Ditentukan --</option>
                            @foreach($waliMuridList as $wali)
                                <option value="{{ $wali->id }}" {{ old('wali_murid_id', $santri->wali_murid_id) == $wali->id ? 'selected' : '' }}>
                                    {{ $wali->name }} ({{ $wali->email }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('wali_murid_id')" />
                    </div>

                    <div>
                        <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                        <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" autocomplete="bday" class="mt-1 block w-full" :value="old('tanggal_lahir', optional($santri->tanggal_lahir)->format('Y-m-d'))" />
                        <x-input-error class="mt-2" :messages="$errors->get('tanggal_lahir')" />
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Perbarui Data</x-primary-button>
                        <a href="{{ route('admin.santri.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
