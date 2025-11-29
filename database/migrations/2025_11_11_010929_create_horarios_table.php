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
            $table->foreignId("tipo_vehiculo_id")->constrained("tipo_vehiculos")->onDelete("cascade");
            $table->foreignId("tipo_viaje_id")->constrained("tipos_viajes")->onDelete("cascade");
            $table->foreignId("punto_origen_id")->constrained("sucursales")->onDelete("cascade");
            $table->foreignId("punto_destino_id")->constrained("sucursales")->onDelete("cascade");
            $table->decimal('costo_pasaje', 8, 2);
            $table->time("hora_embarque");
            $table->date('fecha_salida');
            $table->date('repetir_hasta')->nullable();
            $table->boolean('lunes')->default(false);
            $table->boolean('martes')->default(false);
            $table->boolean('miercoles')->default(false);
            $table->boolean('jueves')->default(false);
            $table->boolean('viernes')->default(false);
            $table->boolean('sabado')->default(false);
            $table->boolean('domingo')->default(false);
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
