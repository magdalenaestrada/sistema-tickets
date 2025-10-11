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
        Schema::create("caja", function (Blueprint $table) {
            $table->id();
            $table->foreignId("usuario_id")->constrained("users");
            $table->foreignId("sucursal_id")->constrained("sucursales");
            $table->decimal("monto_apertura", 10, 2)->default(0);
            $table->decimal("monto_cierre", 10, 2)->default(0);
            $table
                ->enum("estado", ["A", "C", "P"])
                ->comment(
                    "Estado de la caja: A => Activa, C => Cerrada, P => Arqueo",
                )
                ->default("A")
                ->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_cierre")->nullable();
            $table->dateTime("fecha_arqueo")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("caja");
    }
};
