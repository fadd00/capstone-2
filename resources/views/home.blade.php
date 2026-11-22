<x-guest-layout title="Masuk">
    @auth
        <div class="space-y-4">
            <h1 class="text-lg font-semibold text-gray-900">Sesi Aktif</h1>
            <p class="text-sm text-gray-700">
                Masuk sebagai <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span>.
            </p>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex-1 text-center px-4 py-2 text-sm font-medium text-white bg-emerald-700 rounded-md hover:bg-emerald-800">
                    Buka Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    @else
        <h1 class="text-lg font-semibold text-gray-900 mb-4">Masuk ke Portal</h1>
        @include('auth._login-form')
    @endauth
</x-guest-layout>
