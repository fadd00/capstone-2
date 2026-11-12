@extends('layouts.public')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center space-y-6">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Pendaftaran Berhasil!</h1>
            <p class="text-gray-600 text-sm">
                Alhamdulillah, berkas pendaftaran santri baru Anda sukses tersimpan di sistem PPDB Ibnu Juraimi.
            </p>
        </div>

        @if ($email || session('email'))
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-left space-y-3">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kredensial Akun Calon Santri Anda:</h3>
                <p class="text-xs text-amber-700 font-medium">
                    ⚠️ Simpan informasi kredensial ini. Anda dapat menggunakannya untuk memantau status seleksi di Dashboard Calon Santri.
                </p>
                <div class="space-y-2 pt-2 text-sm font-mono bg-white p-4 rounded-lg border border-gray-200">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Email Login:</span>
                        <span class="font-bold text-gray-900">{{ $email ?? session('email') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Password Sementara:</span>
                        <span class="font-bold text-emerald-700">{{ $password ?? session('password') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center text-sm text-gray-600">
                Informasi login dan kredensial akun telah dikirimkan ke email yang Anda daftarkan saat pengisian formulir.
            </div>
        @endif

        <div class="pt-4 flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('login') }}" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition">
                Masuk ke Portal Calon Santri
            </a>
            <a href="{{ route('home') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
