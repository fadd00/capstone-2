<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('murojaah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->integer('juz');
            $table->string('surat', 100);
            $table->string('ayat', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->boolean('status_selesai')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index(['santri_id', 'tanggal']);
            $table->index(['ustadz_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('murojaah');
    }
};
