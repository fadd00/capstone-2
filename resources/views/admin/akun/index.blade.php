<x-app-layout title="Manajemen Akun Administrator">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Akun Administrator') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div role="status" aria-live="polite" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div role="alert" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <section class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <h2 id="create-admin-heading" class="text-lg font-medium text-gray-900 mb-4">Tambah Administrator Baru</h2>
                <form action="{{ route('admin.akun.store') }}" method="POST" aria-labelledby="create-admin-heading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" id="name" required autocomplete="name" value="{{ old('name') }}"
                               @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <x-input-error id="name-error" :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                        <input type="email" name="email" id="email" required autocomplete="email" inputmode="email" autocorrect="off" autocapitalize="none" spellcheck="false" value="{{ old('email') }}"
                               @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password (Minimal 8 Karakter)</label>
                        <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password"
                               @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-medium rounded-md shadow-sm text-sm">
                            Simpan Administrator Baru
                        </button>
                    </div>
                </form>
            </section>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h2 id="admin-list-heading" class="text-lg font-medium text-gray-900">Daftar Administrator Aktif</h2>
                </div>
                <div class="responsive-table-wrapper" tabindex="0" role="region" aria-labelledby="admin-list-heading">
                    <table class="responsive-table min-w-full divide-y divide-gray-200 text-sm">
                        <caption class="sr-only">Daftar akun administrator aktif</caption>
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-medium">Nama</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium">Email</th>
                                <th scope="col" class="px-4 py-3 text-left font-medium">Terdaftar Pada</th>
                                <th scope="col" class="px-4 py-3 text-center font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($admins as $adminUser)
                                <tr>
                                    <td data-label="Nama" class="px-4 py-3 font-semibold text-gray-900">{{ $adminUser->name }}</td>
                                    <td data-label="Email" class="px-4 py-3 text-gray-700">{{ $adminUser->email }}</td>
                                    <td data-label="Terdaftar Pada" class="px-4 py-3 text-gray-600">{{ $adminUser->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td data-label="Aksi" class="px-4 py-3 text-center justify-center space-x-2">
                                        <form action="{{ route('admin.akun.reset.update', $adminUser->id) }}"
                                              method="POST"
                                              class="inline"
                                              data-confirm="Reset password akun {{ $adminUser->name }} menjadi PasswordBaru123!?"
                                              data-confirm-title="Reset Kata Sandi">
                                            @csrf
                                            <input type="hidden" name="password" value="PasswordBaru123!">
                                            <input type="hidden" name="password_confirmation" value="PasswordBaru123!">
                                            <button type="submit" class="text-blue-700 hover:text-blue-900 text-xs font-medium">
                                                Reset Sandi <span class="sr-only">{{ $adminUser->name }}</span>
                                            </button>
                                        </form>

                                        @if (Auth::id() !== $adminUser->id)
                                            <form action="{{ route('admin.akun.destroy', $adminUser->id) }}"
                                                  method="POST"
                                                  class="inline"
                                                  data-confirm="Hapus akun administrator {{ $adminUser->name }}?"
                                                  data-confirm-title="Hapus Administrator">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-700 hover:text-red-900 text-xs font-medium">
                                                    Hapus <span class="sr-only">{{ $adminUser->name }}</span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-500 italic">(Akun Anda)</span>
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
            </section>
        </div>
    </div>
</x-app-layout>
