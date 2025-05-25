<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobil', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('tipe', 100);
            $table->integer('tahun');
            $table->decimal('harga', 12, 2);
            $table->string('mesin', 100);
            $table->string('transmisi', 100);
            $table->string('kapasitas_bensin', 50);
            $table->string('warna', 50);
            $table->text('fitur_lain')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('gambar', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobil');
    }
};