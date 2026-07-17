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
    Schema::table('hardware', function (Blueprint $table) {
        $table->integer('jumlah_penghuni')->default(1)->after('nama_pemilik'); // (1)
    });
}

public function down(): void
{
    Schema::table('hardware', function (Blueprint $table) {
        $table->dropColumn('jumlah_penghuni'); // (2)
    });
}
};
