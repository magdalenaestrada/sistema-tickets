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
        Schema::create('ruta_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained("rutas")->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->unsignedInteger('orden');
            $table->timestamps();

            $table->unique(['ruta_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas_puntos');
    }
};
