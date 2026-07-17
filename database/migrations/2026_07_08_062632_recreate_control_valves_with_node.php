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
    Schema::dropIfExists('control_valves');   // buang versi lama (hardware_id)

    Schema::create('control_valves', function (Blueprint $table) {
        $table->id();
        $table->foreignId('node_id')          // sekarang menunjuk ke node
              ->constrained('nodes')
              ->cascadeOnDelete();

        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamp('diubah_pada')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('control_valves');
}
};
