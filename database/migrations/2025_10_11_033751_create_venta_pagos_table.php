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
        Schema::create("venta_pagos", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("venta_id")
                ->constrained("ventas")
                ->cascadeOnDelete();
            $table->foreignId("metodo_pago_id")->constrained("metodo_pago");
            $table->decimal("total", 10, 2);
            $table
                ->enum("estado", ["PE", "PA"])
                ->comment("Estado de la caja: PE => Pendiente, PA => Pagado")
                ->default("PA")
                ->index();
            $table->foreignId('billetera_id')->nullable()->constrained('billeteras_digitales');
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_pago")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("venta_pagos");
    }
};
