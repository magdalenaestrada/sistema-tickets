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
        Schema::create("encomienda", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("sucursal_id")
                ->nullable()
                ->constrained("sucursales");
            $table->foreignId("usuario_id")->constrained("users");
            $table->foreignId("emisor_persona_id")->constrained("personas");
            $table->foreignId("receptor_persona_id")->constrained("personas");
            $table->foreignId("distrito_id")->constrained("distritos");
            $table->foreignId("venta_id")->nullable()->constrained("ventas");
            $table
                ->enum("estado", ["A", "P"])
                ->comment("Estado de la caja: A => Activa, P => Procesado")
                ->default("A")
                ->index();
            $table->decimal("total", 10, 2);
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_procesado")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("encomienda");
    }
};
