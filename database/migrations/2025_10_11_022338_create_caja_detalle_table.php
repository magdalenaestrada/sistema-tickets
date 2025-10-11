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
        Schema::create("caja_detalle", function (Blueprint $table) {
            $table->id();
            $table->foreignId("caja_id")->constrained("cajas");
            $table
                ->foreignId("subtipo_movimiento_caja_id")
                ->constrained("subtipo_movimiento_caja");
            $table->foreignId("metodo_pago_id")->constrained("metodo_pago");
            $table->string("table_name")->nullable();
            $table->string("table_id")->nullable();
            $table->decimal("amount", 10, 2)->default(0);
            $table->string("description")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("caja_detalle");
    }
};
