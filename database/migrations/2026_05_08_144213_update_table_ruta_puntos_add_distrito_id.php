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
        Schema::table('ruta_puntos', function (Blueprint $table) {

            $table->foreignId('distrito_id')
                ->after('ruta_id')
                ->nullable()
                ->constrained('distritos');

            $table->foreignId('sucursal_id')
                ->nullable()
                ->change();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_puntos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('distrito_id');
            $table->foreignId('sucursal_id')
                ->nullable(false)
                ->change();
        });
    }
};
