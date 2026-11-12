<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Akun Ustadz: ') . $ustadz->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form method="POST" action="{{ route('admin.ustadz.update', $ustadz->id) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama Lengkap Ustadz" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $ustadz->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Alamat Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $ustadz->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-xs text-gray-500 mb-2">Kosongkan password jika tidak ingin mengubah kata sandi.</p>
                        <div>
                            <x-input-label for="password" value="Kata Sandi Baru (Opsional)" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi Baru" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <x-primary-button>Perbarui Akun</x-primary-button>
                        <a href="{{ route('admin.ustadz.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
