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
    Schema::dropIfExists('penggunaan');   // (1) buang versi lama (hardware_id)

    Schema::create('penggunaan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('node_id')      // (2) sekarang menunjuk ke node
              ->constrained('nodes')
              ->cascadeOnDelete();

        $table->decimal('volume', 12, 4);
        $table->decimal('flowrate', 10, 3);
        $table->timestamp('recorded_at');

        $table->timestamps();
        $table->index(['node_id', 'recorded_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('penggunaan');
}
};
