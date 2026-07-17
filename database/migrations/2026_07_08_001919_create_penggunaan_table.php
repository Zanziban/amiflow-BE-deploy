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
        Schema::create('penggunaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hardware_id')            // (1)
                ->constrained('hardware')             // (2)
                ->cascadeOnDelete();                  // (3)

            $table->decimal('volume', 12, 4);           // (4)
            $table->decimal('flowrate', 10, 3);         // (5)
            $table->timestamp('recorded_at');           // (6)
            $table->timestamps();
            $table->index(['hardware_id', 'recorded_at']); // (7)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggunaan');
    }
};
