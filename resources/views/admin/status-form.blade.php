<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kontrol Jadwal & Status Formulir PPDB') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Pengaturan Buka / Tutup Pendaftaran</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Formulir pendaftaran online akan otomatis aktif atau non-aktif berdasarkan pengaturan status dan rentang tanggal di bawah ini.
                </p>

                <form action="{{ route('admin.updateStatusForm') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status Formulir</label>
                        <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Buka" {{ old('status', $statusForm->status ?? '') === 'Buka' ? 'selected' : '' }}>Buka (Pendaftaran Aktif)</option>
                            <option value="Tutup" {{ old('status', $statusForm->status ?? '') === 'Tutup' ? 'selected' : '' }}>Tutup (Pendaftaran Ditutup)</option>
                        </select>
                        @error('status')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_buka" class="block text-sm font-medium text-gray-700">Tanggal Buka (Opsional)</label>
                            <input type="date" name="tanggal_buka" id="tanggal_buka" value="{{ old('tanggal_buka', optional($statusForm->tanggal_buka)->format('Y-m-d') ?? $statusForm->tanggal_buka ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('tanggal_buka')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_tutup" class="block text-sm font-medium text-gray-700">Tanggal Tutup (Opsional)</label>
                            <input type="date" name="tanggal_tutup" id="tanggal_tutup" value="{{ old('tanggal_tutup', optional($statusForm->tanggal_tutup)->format('Y-m-d') ?? $statusForm->tanggal_tutup ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('tanggal_tutup')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Pengaturan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
