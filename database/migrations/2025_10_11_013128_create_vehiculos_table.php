<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("vehiculos", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tipo_vehiculo_id")->constrained("tipo_vehiculos");
            $table->string("numero_placa", 15)->unique()->index();
            $table->unsignedInteger("cantidad_conductores")->nullable();
            $table->enum("estado", ["A", "I"])->default("A")->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivacion")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vehiculos");
    }
};
