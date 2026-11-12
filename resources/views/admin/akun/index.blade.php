<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Akun Administrator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Create Admin Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Administrator Baru</h3>
                <form action="{{ route('admin.akun.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @error('email')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password (Minimal 8 Karakter)</label>
                        <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Administrator Baru
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Admins -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Daftar Administrator Aktif</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Nama</th>
                                <th class="px-4 py-3 text-left font-medium">Email</th>
                                <th class="px-4 py-3 text-left font-medium">Terdaftar Pada</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($admins as $adminUser)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-gray-900">{{ $adminUser->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $adminUser->email }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $adminUser->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <!-- Reset Password Trigger / Form -->
                                        <form action="{{ route('admin.akun.reset.update', $adminUser->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="password" value="PasswordBaru123!">
                                            <input type="hidden" name="password_confirmation" value="PasswordBaru123!">
                                            <button type="submit" class="text-blue-600 hover:text-blue-900 text-xs font-medium" onclick="return confirm('Reset password akun ini menjadi PasswordBaru123!?');">
                                                Reset Sandi
                                            </button>
                                        </form>

                                        @if (Auth::id() !== $adminUser->id)
                                            <form action="{{ route('admin.akun.destroy', $adminUser->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun administrator ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400 italic">(Akun Anda)</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada administrator terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
