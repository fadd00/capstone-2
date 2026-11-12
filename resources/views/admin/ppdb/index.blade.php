<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Pendaftaran Santri Baru (PPDB)') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.export.excel', ['status' => request('status')]) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md shadow-sm">
                    Ekspor Excel
                </a>
                <a href="{{ route('admin.export.pdf', ['status' => request('status')]) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm">
                    Ekspor PDF
                </a>
                <form action="{{ route('admin.destroyAll') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA data pendaftar?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-md shadow-sm">
                        Purge Semua Pendaftar
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Analytics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="text-xs font-medium text-gray-500 uppercase">Total Pendaftar</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $total ?? 0 }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-yellow-200">
                    <div class="text-xs font-medium text-yellow-600 uppercase">Menunggu</div>
                    <div class="text-2xl font-bold text-yellow-700 mt-1">{{ $menunggu ?? 0 }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-green-200">
                    <div class="text-xs font-medium text-green-600 uppercase">Lulus</div>
                    <div class="text-2xl font-bold text-green-700 mt-1">{{ $lulus ?? 0 }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-red-200">
                    <div class="text-xs font-medium text-red-600 uppercase">Tidak Lulus</div>
                    <div class="text-2xl font-bold text-red-700 mt-1">{{ $tidakLulus ?? 0 }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-200">
                    <div class="text-xs font-medium text-blue-600 uppercase">Laki-Laki</div>
                    <div class="text-2xl font-bold text-blue-700 mt-1">{{ $lakiLaki ?? 0 }}</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border border-pink-200">
                    <div class="text-xs font-medium text-pink-600 uppercase">Perempuan</div>
                    <div class="text-2xl font-bold text-pink-700 mt-1">{{ $perempuan ?? 0 }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <form method="GET" action="{{ route('admin.ppdb.index') }}" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, no HP..." class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <select name="status" class="text-sm border-gray-300 rounded-md shadow-sm">
                            <option value="">Semua Status</option>
                            <option value="Menunggu" {{ request('status') === 'Menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="Lulus" {{ request('status') === 'Lulus' ? 'selected' : '' }}>Lulus</option>
                            <option value="Tidak Lulus" {{ request('status') === 'Tidak Lulus' ? 'selected' : '' }}>Tidak Lulus</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.ppdb.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md shadow-sm">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Nama Lengkap</th>
                                <th class="px-4 py-3 text-left font-medium">JK</th>
                                <th class="px-4 py-3 text-left font-medium">Kontak</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-center font-medium">Promosi</th>
                                <th class="px-4 py-3 text-right font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($pendaftarans as $pendaftar)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $pendaftar->nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">NISN: {{ $pendaftar->nisn ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $pendaftar->jenis_kelamin }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">{{ $pendaftar->email }}</div>
                                        <div class="text-xs text-gray-500">{{ $pendaftar->no_hp }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $badgeClasses = match($pendaftar->status) {
                                                'Lulus' => 'bg-green-100 text-green-800',
                                                'Tidak Lulus' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800',
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeClasses }}">
                                            {{ $pendaftar->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($pendaftar->santri_id)
                                            <span class="text-xs font-semibold text-emerald-700">Sudah Santri</span>
                                        @elseif ($pendaftar->status === 'Lulus')
                                            <form action="{{ route('admin.pendaftar.promosikan', $pendaftar->id) }}" method="POST" class="inline-flex items-center space-x-1">
                                                @csrf
                                                <input type="text" name="nis" placeholder="NIS" required class="text-xs border-gray-300 rounded px-1.5 py-1 w-24">
                                                <input type="hidden" name="tingkatan" value="tsanawiyah">
                                                <input type="hidden" name="kelas" value="7A">
                                                <button type="submit" class="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded">Promosikan</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <a href="{{ route('admin.download-zip', $pendaftar->id) }}" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">
                                            Unduh ZIP
                                        </a>
                                        <form action="{{ route('admin.destroy', $pendaftar->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data pendaftar ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-xs font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data pendaftar yang sesuai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pendaftarans instanceof \Illuminate\Pagination\LengthAwarePaginator && $pendaftarans->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $pendaftarans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
