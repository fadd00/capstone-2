<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Kontak Resmi Institusi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Perbarui Informasi Kontak Resmi</h3>
                <form action="{{ route('admin.kontak.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat Lengkap Pesantren</label>
                        <textarea name="alamat" id="alamat" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('alamat', $kontak->alamat ?? '') }}</textarea>
                    </div>
                    <div>
                        <label for="telepon" class="block text-sm font-medium text-gray-700">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="telepon" id="telepon" required value="{{ old('telepon', $kontak->telepon ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email Resmi</label>
                        <input type="email" name="email" id="email" required value="{{ old('email', $kontak->email ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Kontak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
