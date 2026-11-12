<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Broadcast WhatsApp Massal (Fonnte)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Kirim Pesan Siaran WhatsApp</h3>
                <p class="text-sm text-gray-600 mb-6">
                    Pesan akan dikirimkan secara otomatis ke nomor WhatsApp pendaftar yang terdaftar melalui Fonnte API Gateway.
                </p>

                <form action="{{ route('admin.kirimNotifikasi') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="target_status" class="block text-sm font-medium text-gray-700">Target Status Pendaftar</label>
                        <select name="target_status" id="target_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Pilih Target Pendaftar --</option>
                            <option value="semua" {{ old('target_status') === 'semua' ? 'selected' : '' }}>Semua Pendaftar</option>
                            <option value="Menunggu" {{ old('target_status') === 'Menunggu' ? 'selected' : '' }}>Hanya Menunggu Verifikasi</option>
                            <option value="Lulus" {{ old('target_status') === 'Lulus' ? 'selected' : '' }}>Hanya Dinyatakan Lulus</option>
                            <option value="Tidak Lulus" {{ old('target_status') === 'Tidak Lulus' ? 'selected' : '' }}>Hanya Dinyatakan Tidak Lulus</option>
                        </select>
                        @error('target_status')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pesan" class="block text-sm font-medium text-gray-700">Isi Pesan Siaran</label>
                        <textarea name="pesan" id="pesan" rows="6" required placeholder="Tuliskan isi pesan pengumuman..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('pesan') }}</textarea>
                        @error('pesan')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Kirim Broadcast WhatsApp
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
