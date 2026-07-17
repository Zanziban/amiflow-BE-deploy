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
        Schema::create('penjadwalan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('node_id')                 // (1) tiap jadwal milik satu node
                ->constrained('nodes')
                ->cascadeOnDelete();

            $table->string('hari');                       // (2) 'Senin', 'Selasa', dst
            $table->time('jam_buka')->nullable();         // (3) startTime, mis. 06:00
            $table->time('jam_tutup')->nullable();        // endTime, maks 23:59
            $table->boolean('aktif')->default(false);     // (4) enabled di Flutter

            $table->timestamps();

            $table->unique(['node_id', 'hari']);          // (5) satu jadwal per hari per node
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjadwalan');
    }
};
