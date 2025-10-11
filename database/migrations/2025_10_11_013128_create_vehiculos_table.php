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
            $table
                ->foreignId("tipo_vehiculo_id")
                ->constrained("tipo_vehiculos");
            $table->string("numero_placa", 15)->unique()->index();
            $table->unsignedInteger("cantidad_asientos");
            $table->enum("cantidad_pisos", [1, 2])->default(1);
            $table->unsignedInteger("cantidad_filas");
            $table->enum("cantidad_columnas", [1, 2])->default(2);
            $table->decimal("peso_bodega")->default(0);
            $table->unsignedInteger("cantidad_conductores")->index();
            $table->string("url_svg");
            $table
                ->enum("estado", ["A", "I"])
                ->default("A")
                ->index();
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
