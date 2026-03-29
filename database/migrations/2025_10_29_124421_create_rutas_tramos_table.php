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
        Schema::create('ruta_tramos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained()->cascadeOnDelete();

            $table->foreignId('punto_origen_id')->constrained('ruta_puntos');
            $table->foreignId('punto_destino_id')->constrained('ruta_puntos');

            $table->integer('duracion_minutos');
            $table->decimal('costo_tramo', 8, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas_tramos');
    }
};
