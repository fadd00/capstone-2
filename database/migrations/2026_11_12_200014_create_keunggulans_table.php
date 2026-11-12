<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keunggulans', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->string('deskripsi_singkat', 255);
            $table->text('deskripsi_detail');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keunggulans');
    }
};
