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
        Schema::create("almacen", function (Blueprint $table) {
            $table->id();
            $table->foreignId("sucursal_id")->constrained("sucursales");
            $table->string("codigo", 100)->index();
            $table->string("descripcion", 100)->index();
            $table
                ->enum("estado", ["A", "I"])
                ->default("A")
                ->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivo")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("almacen");
    }
};
