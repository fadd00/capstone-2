<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Calon Santri') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Status Pendaftaran</h3>
                <div class="flex items-center space-x-3">
                    @php
                        $status = $pendaftaran->status ?? 'Menunggu';
                        $badgeClasses = match($status) {
                            'Lulus' => 'bg-green-100 text-green-800 border-green-300',
                            'Tidak Lulus' => 'bg-red-100 text-red-800 border-red-300',
                            default => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                        };
                    @endphp
                    <span class="px-4 py-2 text-sm font-semibold rounded-full border {{ $badgeClasses }}">
                        {{ $status }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    @if ($status === 'Menunggu')
                        Berkas pendaftaran Anda sedang dalam proses verifikasi oleh panitia PPDB.
                    @elseif ($status === 'Lulus')
                        Selamat! Anda dinyatakan Lulus seleksi penerimaan santri baru.
                    @else
                        Mohon maaf, Anda belum dinyatakan lulus pada seleksi periode ini.
                    @endif
                </p>
            </div>

            <!-- Biodata Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Biodata Calon Santri</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pendaftaran->nama_lengkap ?? $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email Akun</dt>
                        <dd class="text-sm font-semibold text-gray-900">{{ $pendaftaran->email ?? $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor WhatsApp / HP</dt>
                        <dd class="text-sm text-gray-900">{{ $pendaftaran->no_hp ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Kelamin</dt>
                        <dd class="text-sm text-gray-900">{{ $pendaftaran->jenis_kelamin ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Asal Sekolah</dt>
                        <dd class="text-sm text-gray-900">{{ $pendaftaran->asal_sekolah ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Alamat Lengkap</dt>
                        <dd class="text-sm text-gray-900">{{ $pendaftaran->alamat ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Documents Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Berkas Dokumen Persyaratan</h3>
                @if ($pendaftaran)
                    <div class="divide-y divide-gray-200">
                        @foreach (\App\Models\Pendaftaran::ALLOWED_FILES as $fileField)
                            <div class="py-3 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $fileField) }}
                                </span>
                                @if (!empty($pendaftaran->{$fileField}))
                                    <a href="{{ route('user.file', ['id' => $pendaftaran->id, 'field' => $fileField]) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition">
                                        Unduh Dokumen
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum diunggah</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada data pendaftaran yang terhubung dengan akun ini.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
