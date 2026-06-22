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
        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->string('codigo')->nullable();

            $table->string('unidad')->default('NIU');

            $table->decimal('valor_unitario', 12, 2)->default(0);

            $table->decimal('precio_unitario', 12, 2)->default(0);

            $table->decimal('base_igv', 12, 2)->default(0);

            $table->decimal('porcentaje_igv', 5, 2)->default(18);

            $table->decimal('igv', 12, 2)->default(0);

            $table->decimal('valor_venta', 12, 2)->default(0);

            $table->string('tipo_afectacion_igv')
                ->default('10');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta_detalles', function (Blueprint $table) {
            //
        });
    }
};
