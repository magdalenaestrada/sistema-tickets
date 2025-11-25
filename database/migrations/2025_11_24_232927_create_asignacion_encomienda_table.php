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
        Schema::create('asignacion_encomiendas', function (Blueprint $table) {
            $table->id();

            // Relación con asignación (horario + conductores + vehículo)
            $table->foreignId('asignacion_id')
                ->constrained('asignar_horario_conductor_vehiculo')
                ->onDelete('cascade');

            // Relación con la encomienda
            $table->foreignId('encomienda_id')
                ->constrained('encomienda')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_encomienda');
    }
};
