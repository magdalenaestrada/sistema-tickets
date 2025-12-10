<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // FK: tipo de servicio (manual)
            $table->unsignedBigInteger('tipo_servicio_id');
            $table->foreign('tipo_servicio_id', 'ventas_tipo_servicio_id_foreign')
                ->references('id')
                ->on('tipo_servicio')
                ->onDelete('cascade');

            // FK: sucursal
            $table->foreignId('sucursal_id')
                ->nullable()
                ->constrained('sucursales')
                ->cascadeOnDelete()
                ->name('ventas_sucursal_id_foreign');

            // FK: tipo documento factura
            $table->foreignId('tipo_documento_factura_id')
                ->constrained('tipo_documentos_factura')
                ->cascadeOnDelete()
                ->name('ventas_tipo_documento_factura_id_foreign');

            // FK: usuario (users)
            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->name('ventas_usuario_id_foreign');

            // FK: persona
            $table->foreignId('persona_id')
                ->constrained('personas')
                ->cascadeOnDelete()
                ->name('ventas_persona_id_foreign');

            // Datos generales
            $table->string('serie')->index();
            $table->unsignedBigInteger('numero')->index();

            $table->string('documento_referencia')->nullable()->index();
            $table->string('direccion_alternativa')->nullable()->index();

            // Totales
            $table->decimal('subtotal_sin_igv', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('impuesto', 10, 2)->default(0);
            $table->decimal('sin_igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('total_sin_igv', 10, 2)->default(0);
            $table->decimal('monto_pagado', 10, 2)->default(0);

            // Archivos XML, PDF, CDR
            $table->string('ruta_xml')->nullable();
            $table->string('ruta_pdf')->nullable();
            $table->string('ruta_cdr')->nullable();
            $table->string('hash')->nullable();

            // Estado
            $table->enum('estado', ['E', 'A'])
                ->default('E')
                ->comment('E => Emitido, A => Anulado');

            // Fechas
            $table->dateTime('fecha_emision')->index();
            $table->dateTime('fecha_anulacion')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
