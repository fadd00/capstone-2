<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_pondok_id')->constrained('kegiatan_pondok')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('hari', 20);
            $table->time('jam_mulai');
            $table->time('jam_selesai')->nullable();
            $table->text('materi')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('status', ['valid', 'invalid', 'pending'])->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['kegiatan_pondok_id', 'ustadz_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_kegiatan');
    }
};
