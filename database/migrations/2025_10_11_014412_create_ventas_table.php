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
        Schema::create("ventas", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_servicio_id');
            $table
                ->foreignId("sucursal_id")
                ->nullable()
                ->constrained("sucursales");
            $table
                ->foreignId("tipo_documento_factura_id")
                ->constrained("tipo_documentos_factura");
            $table->foreignId("usuario_id")->constrained("users");
            $table->foreignId("persona_id")->constrained("personas");
            $table->string("serie");
            $table->unsignedBigInteger("numero");
            $table
                ->string("documento_referencia")
                ->nullable()
                ->comment(
                    "En caso de que la factura se asocie a una guia de remisión",
                );
            $table
                ->string("direccion_alternativa")
                ->nullable()
                ->comment(
                    "En caso de que el cliente tenga una dirección alternativa",
                );
            $table->decimal("subtotal_sin_igv", 10, 2)->default(0);
            $table->decimal("subtotal", 10, 2)->default(0);
            $table->decimal("impuesto", 10, 2)->default(0);
            $table->decimal("sin_igv", 10, 2)->default(0);
            $table->decimal("total", 10, 2)->default(0);
            $table->decimal("total_sin_igv", 10, 2)->default(0);
            $table->decimal("monto_pagado", 10, 2)->default(0);
            $table->string("observacion")->nullable();
            $table->string("ruta_xml")->nullable();
            $table->string("ruta_pdf")->nullable();
            $table->string("ruta_cdr")->nullable();
            $table->string("hash")->nullable();
            $table
                ->enum("estado", ["E", "A"])
                ->comment("Estado de la caja: E => Emitido, A => Anulado")
                ->default("E")
                ->index();
            $table->dateTime("fecha_emision");
            $table->dateTime("fecha_anulacion")->nullable();
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("ventas");
    }
};
