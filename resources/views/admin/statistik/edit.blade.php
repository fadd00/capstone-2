<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Statistik Live Counter') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Perbarui Angka Statistik Pondok</h3>
                <form action="{{ route('admin.statistik.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="santri_aktif" class="block text-sm font-medium text-gray-700">Jumlah Santri Aktif</label>
                        <input type="number" name="santri_aktif" id="santri_aktif" required min="0" value="{{ old('santri_aktif', $statistik->santri_aktif ?? 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="asatidzah_bersanad" class="block text-sm font-medium text-gray-700">Jumlah Asatidzah Bersanad</label>
                        <input type="number" name="asatidzah_bersanad" id="asatidzah_bersanad" required min="0" value="{{ old('asatidzah_bersanad', $statistik->asatidzah_bersanad ?? 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="tahun_berkhidmat" class="block text-sm font-medium text-gray-700">Tahun Berkhidmat / Pengalaman</label>
                        <input type="number" name="tahun_berkhidmat" id="tahun_berkhidmat" required min="0" value="{{ old('tahun_berkhidmat', $statistik->tahun_berkhidmat ?? 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Perubahan Statistik
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
