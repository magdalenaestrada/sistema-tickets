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
        Schema::table('caja_detalle', function (Blueprint $table) {

            // Eliminar columnas antiguas
            $table->dropColumn(['table_name', 'table_id']);

            // Relación con la venta
            $table->foreignId('venta_id')
                ->nullable()
                ->after('caja_id')
                ->constrained('ventas')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
