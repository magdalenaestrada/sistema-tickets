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
        Schema::create('asignar_horario_conductor_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId("horario_id")->constrained("horarios");
            $table->foreignId("primer_conductor_id")->constrained("empleados");
            $table->foreignId("segundo_conductor_id")->nullable()->constrained("empleados");
            $table->foreignId("vehiculo_id")->nullable()->constrained("vehiculos");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignar_horario_conductor_vehiculo');
    }
};
