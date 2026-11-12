@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="border-b border-gray-200 pb-6 mb-6">
            <h1 class="text-3xl font-extrabold text-gray-900">Formulir Pendaftaran Santri Baru (PPDB)</h1>
            <p class="mt-2 text-sm text-gray-500">Lengkapi seluruh data dan unggah dokumen persyaratan yang diminta.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded text-red-700 text-sm">
                <p class="font-bold mb-2">Terdapat beberapa kesalahan pengisian formulir:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pendaftaran.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Section 1: Data Pribadi Santri -->
            <div>
                <h2 class="text-lg font-bold text-emerald-800 border-b border-gray-100 pb-2 mb-4">1. Data Calon Santri</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Kelamin *</label>
                        <select name="jenis_kelamin" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Aktif *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp / HP *</label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Domisili Lengkap *</label>
                        <textarea name="alamat" rows="2" required class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('alamat') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NISN (10 Digit)</label>
                        <input type="text" name="nisn" maxlength="10" value="{{ old('nisn') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIK Santri</label>
                        <input type="text" name="nik_santri" value="{{ old('nik_santri') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Kartu Keluarga (KK)</label>
                        <input type="text" name="no_kk" value="{{ old('no_kk') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah" value="{{ old('asal_sekolah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Asal Sekolah</label>
                        <input type="text" name="alamat_asal_sekolah" value="{{ old('alamat_asal_sekolah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jumlah Hafalan Al-Qur'an Sebelumnya</label>
                        <input type="text" name="jumlah_hafalan" value="{{ old('jumlah_hafalan') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Prestasi yang Pernah Diraih</label>
                        <input type="text" name="prestasi" value="{{ old('prestasi') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Orang Tua / Wali -->
            <div>
                <h2 class="text-lg font-bold text-emerald-800 border-b border-gray-100 pb-2 mb-4">2. Data Orang Tua / Wali</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIK Ayah</label>
                        <input type="text" name="nik_ayah" value="{{ old('nik_ayah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Penghasilan Ayah</label>
                        <input type="text" name="penghasilan_ayah" value="{{ old('penghasilan_ayah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor HP / WA Ayah</label>
                        <input type="text" name="no_hp_ayah" value="{{ old('no_hp_ayah') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">NIK Ibu</label>
                        <input type="text" name="nik_ibu" value="{{ old('nik_ibu') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Penghasilan Ibu</label>
                        <input type="text" name="penghasilan_ibu" value="{{ old('penghasilan_ibu') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor HP / WA Ibu</label>
                        <input type="text" name="no_hp_ibu" value="{{ old('no_hp_ibu') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Section 3: Unggah 7 Berkas Wajib -->
            <div>
                <h2 class="text-lg font-bold text-emerald-800 border-b border-gray-100 pb-2 mb-2">3. Berkas Dokumen Persyaratan (Wajib)</h2>
                <p class="text-xs text-gray-500 mb-4">Format berkas: PDF, JPG, JPEG, PNG. Ukuran maksimal 2048 KB (2 MB) per dokumen.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pas Foto 3x4 *</label>
                        <input type="file" name="pas_photo" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Bukti Transfer Pendaftaran *</label>
                        <input type="file" name="bukti_transfer" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Formulir Pendaftaran Bermaterai *</label>
                        <input type="file" name="formulir_pendaftaran" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Surat Pernyataan Orang Tua / Santri *</label>
                        <input type="file" name="surat_pernyataan" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Akta Kelahiran *</label>
                        <input type="file" name="akta_kelahiran" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kartu Keluarga (KK) *</label>
                        <input type="file" name="kartu_keluarga" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Raport Terakhir *</label>
                        <input type="file" name="raport_terakhir" required class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>
            </div>

            <!-- Section 4: Unggah 3 Berkas Opsional -->
            <div>
                <h2 class="text-lg font-bold text-emerald-800 border-b border-gray-100 pb-2 mb-2">4. Dokumen Tambahan (Opsional)</h2>
                <p class="text-xs text-gray-500 mb-4">Unggah jika sudah tersedia. Format: PDF, JPG, JPEG, PNG (Maksimal 2048 KB).</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Ijazah Terakhir</label>
                        <input type="file" name="ijazah_terakhir" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">SKHUN</label>
                        <input type="file" name="skhun" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">SKTM (Keterangan Tidak Mampu)</label>
                        <input type="file" name="sktm" class="w-full text-xs text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-200 flex justify-end">
                <button type="submit" class="px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-md transition">
                    Kirim Formulir Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
