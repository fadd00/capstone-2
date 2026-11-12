<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_lengkap', 255);
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('email', 255);
            $table->string('no_hp', 20);
            $table->text('alamat');
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nisn', 10)->nullable();
            $table->string('nik_santri', 255)->nullable();
            $table->string('no_kk', 255)->nullable();
            $table->string('nama_ayah', 255)->nullable();
            $table->string('nik_ayah', 255)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->string('penghasilan_ayah', 255)->nullable();
            $table->string('no_hp_ayah', 255)->nullable();
            $table->string('nama_ibu', 255)->nullable();
            $table->string('nik_ibu', 255)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->string('penghasilan_ibu', 255)->nullable();
            $table->string('no_hp_ibu', 255)->nullable();
            $table->string('asal_sekolah', 255)->nullable();
            $table->string('alamat_asal_sekolah', 255)->nullable();
            $table->string('jumlah_hafalan', 100)->nullable();
            $table->string('prestasi', 255)->nullable();

            // 10 Berkas Dokumen Pendaftaran + 1 Legacy Deprecated
            $table->string('pas_photo', 255)->nullable();
            $table->string('bukti_transfer', 255)->nullable();
            $table->string('formulir_pendaftaran', 255)->nullable();
            $table->string('surat_pernyataan', 255)->nullable();
            $table->string('akta_kelahiran', 255)->nullable();
            $table->string('kartu_keluarga', 255)->nullable();
            $table->string('ijazah_terakhir', 255)->nullable();
            $table->string('raport_terakhir', 255)->nullable();
            $table->string('skhun', 255)->nullable();
            $table->string('sktm', 255)->nullable();
            $table->string('berkas', 255)->nullable();

            // Status Seleksi & Waktu Pendaftaran
            $table->enum('status', ['Menunggu', 'Lulus', 'Tidak Lulus'])->default('Menunggu');
            $table->timestamp('tanggal_daftar')->useCurrent();

            // Link ke Santri Akademik (Promosi)
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('santri_id');
        });

        DB::statement('CREATE VIEW IF NOT EXISTS pendaftaran AS SELECT * FROM pendaftarans');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS pendaftaran');
        Schema::dropIfExists('pendaftarans');
    }
};
