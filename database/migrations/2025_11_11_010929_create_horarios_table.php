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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_viaje_id')->constrained('tipos_viajes');
            $table->foreignId('tipo_vehiculo_id')->constrained('tipo_vehiculos');
            $table->foreignId('punto_origen_id')->constrained('sucursales');
            $table->foreignId('punto_destino_id')->nullable()->constrained('sucursales');
            $table->time('hora_salida');
            $table->decimal('costo_base', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
