<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pueblitos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('distrito_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pueblitos', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
    }
};
