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
        Schema::create("productos", function (Blueprint $table) {
            $table->id();
            $table->foreignId("unidad_medida_id")->constrained("unidad_medida")->nullable();
            $table->foreignId("impuesto_id")->constrained("impuestos")->nullable();
            $table->string("codigo", 13)->unique()->nullable();
            $table->string("descripcion", 255);
            $table->decimal("total", 10, 2)->default(0);
            $table
                ->enum("estado", ["A", "I"])
                ->comment("Estado de la caja: A => Activa, I => Inactivo")
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
        Schema::dropIfExists("productos");
    }
};
