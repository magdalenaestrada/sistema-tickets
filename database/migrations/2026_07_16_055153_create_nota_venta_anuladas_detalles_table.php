<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nota_venta_anuladas_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('anulacion_id')
                ->constrained('nota_venta_anuladas')
                ->cascadeOnDelete();

            $table->foreignId('venta_detalle_id')
                ->constrained('venta_detalles')
                ->cascadeOnDelete();

            $table->unsignedInteger('cantidad')->default(1);

            $table->decimal('precio_unitario', 12, 2);

            $table->decimal('subtotal', 12, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_venta_anuladas_detalles');
    }
};
