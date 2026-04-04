<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativos_venta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('tipo_documento_factura_id');
            $table->string('serie', 10);
            $table->unsignedBigInteger('ultimo_numero')->default(0);
            $table->timestamps();
            $table->unique(
                ['sucursal_id', 'tipo_documento_factura_id', 'serie'],
                'uq_correlativos_venta'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativos_venta');
    }
};
