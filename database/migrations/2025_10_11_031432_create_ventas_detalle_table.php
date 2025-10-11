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
        Schema::create("venta_detalles", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("venta_id")
                ->constrained("ventas")
                ->cascadeOnDelete();
            $table->foreignId("almacen_id")->default(1)->constrained("almacen");
            $table->foreignId("unidad_medida_id")->constrained("unidad_medida");
            $table->foreignId("producto_id")->constrained("productos");
            $table->decimal("descuento", 10, 2)->default(0);
            $table->decimal("cantidad", 10, 2);
            $table->decimal("precio_venta", 10, 2);
            $table->decimal("precio_compra", 10, 2)->default(0);
            $table->decimal("total", 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("venta_detalles");
    }
};
