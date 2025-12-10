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
            $table->unsignedBigInteger('tipo_servicio_id')->index();
            $table
                ->foreignId("sucursal_id")
                ->nullable()
                ->constrained("sucursales")->index();
            $table
                ->foreignId("tipo_documento_factura_id")
                ->constrained("tipo_documentos_factura")->index();
            $table->foreignId("usuario_id")->constrained("users")->index();
            $table->foreignId("persona_id")->constrained("personas")->index();
            $table->string("serie")->index();
            $table->unsignedBigInteger("numero")->index();
            $table
                ->string("documento_referencia")
                ->nullable()
                ->comment(
                    "En caso de que la factura se asocie a una guia de remisión",
                )->index();
            $table
                ->string("direccion_alternativa")
                ->nullable()
                ->comment(
                    "En caso de que el cliente tenga una dirección alternativa",
                )->index();
            $table->decimal("subtotal_sin_igv", 10, 2)->default(0)->index();
            $table->decimal("subtotal", 10, places: 2)->default(0)->index();
            $table->decimal("impuesto", 10, 2)->default(0)->index();
            $table->decimal("sin_igv", 10, 2)->default(0)->index();
            $table->decimal("total", 10, 2)->default(0)->index();
            $table->decimal("total_sin_igv", 10, 2)->default(0)->index();
            $table->decimal("monto_pagado", 10, 2)->default(0)->index();
            $table->string("observacion")->nullable()->index();
            $table->string("ruta_xml")->nullable()->index();
            $table->string("ruta_pdf")->nullable()->index();
            $table->string("ruta_cdr")->nullable()->index();
            $table->string("hash")->nullable()->index();
            $table
                ->enum("estado", ["E", "A"])
                ->comment("Estado de la caja: E => Emitido, A => Anulado")
                ->default("E")->index();
            $table->dateTime("fecha_emision")->index();
            $table->dateTime("fecha_anulacion")->nullable()->index();
            $table->foreign('tipo_servicio_id')->references('id')->on('tipo_servicio')->onDelete('cascade')->index();
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
