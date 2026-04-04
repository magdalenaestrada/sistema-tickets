<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->unique(
                ['sucursal_id', 'tipo_documento_factura_id', 'serie', 'numero'],
                'uq_ventas_comprobante'
            );
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique('uq_ventas_comprobante');
        });
    }
};
