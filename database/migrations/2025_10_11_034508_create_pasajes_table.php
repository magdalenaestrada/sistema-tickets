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
        Schema::create("pasajes", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("venta_id")
                ->constrained("ventas")
                ->cascadeOnDelete();
            $table
                ->enum("estado", ["A", "I"])
                ->comment("Estado de la caja: A => Activo, I => Inactivo")
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
        Schema::dropIfExists("pasajes");
    }
};
