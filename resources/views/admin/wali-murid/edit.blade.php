<x-app-layout title="Edit Akun Wali Murid">
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Akun Wali Murid: ') . $wali->name }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm border border-gray-200 sm:rounded-lg">
                <form method="POST" action="{{ route('admin.wali-murid.update', $wali->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama Lengkap Wali Murid" />
                        <x-text-input id="name" name="name" type="text" autocomplete="name" class="mt-1 block w-full" :value="old('name', $wali->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Alamat Email" />
                        <x-text-input id="email" name="email" type="email" autocomplete="email" inputmode="email" autocorrect="off" autocapitalize="none" spellcheck="false" class="mt-1 block w-full" :value="old('email', $wali->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <fieldset class="border-t pt-4">
                        <legend class="text-xs text-gray-600 mb-2">Kosongkan password jika tidak ingin mengubah kata sandi.</legend>
                        <div>
                            <x-input-label for="password" value="Kata Sandi Baru (Opsional)" />
                            <x-text-input id="password" name="password" type="password" autocomplete="new-password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>
                    </fieldset>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Perbarui Akun</x-primary-button>
                        <a href="{{ route('admin.wali-murid.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
