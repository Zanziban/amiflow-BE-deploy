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
        Schema::create('control_valves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hardware_id')            // (1)
                ->constrained('hardware')
                ->cascadeOnDelete();

            $table->enum('status', ['active', 'inactive'])  // (2)
                ->default('active');                       // (3)

            $table->timestamp('diubah_pada')->nullable();   // (4)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('control_valves');
    }
};
