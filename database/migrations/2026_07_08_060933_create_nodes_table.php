<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gateway_id')                 // (1)
                ->constrained('gateways')
                ->cascadeOnDelete();

            $table->string('kode_node')->unique();          // (2) ID LoRa/device
            $table->string('nama_pemilik');                 // owner di Flutter
            $table->integer('jumlah_penghuni')->default(1); // totalUsers di Flutter
            $table->string('password');                     // auth alat (disimpan hash)
            $table->boolean('aktif')->default(true);        // node terdaftar/aktif
            $table->boolean('online')->default(false);      // (3) status koneksi

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
