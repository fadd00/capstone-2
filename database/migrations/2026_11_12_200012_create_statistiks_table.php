<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statistiks', function (Blueprint $table) {
            $table->id();
            $table->integer('santri_aktif')->default(110);
            $table->integer('asatidzah_bersanad')->default(30);
            $table->integer('tahun_berkhidmat')->default(13);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistiks');
    }
};
