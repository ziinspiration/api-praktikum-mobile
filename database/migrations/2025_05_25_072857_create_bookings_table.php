<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mobil_id')->constrained('mobil')->onDelete('cascade');
            $table->foreignId('dealer_id')->constrained('dealer')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('waktu');
            $table->enum('status', ['menunggu', 'selesai', 'batal'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};