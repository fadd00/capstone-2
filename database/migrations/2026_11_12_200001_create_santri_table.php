<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nis', 50)->unique();
            $table->enum('tingkatan', ['tsanawiyah', 'aliyah', 'takhassus'])->default('tsanawiyah');
            $table->string('kelas', 50)->nullable();
            $table->foreignId('wali_murid_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal_lahir')->nullable();
            $table->timestamps();

            $table->index(['tingkatan', 'kelas']);
            $table->index('wali_murid_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri');
    }
};
