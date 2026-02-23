<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create("pasajes", function (Blueprint $table) {
            $table->id();
            $table->foreignId("venta_id")->nullable()->constrained("ventas")->cascadeOnDelete();

            $table->foreignId("usuario_id")->constrained("users");
            $table->foreignId('persona_id')
                ->nullable()
                ->constrained('personas')
                ->nullOnDelete();
            $table->boolean("pasajero_menor")->nullable();
            $table->string("autorizacion_pdf")->nullable();
            $table->integer('asiento_numero');
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->enum("estado", ["R", "V", "F", "X"])
                ->comment("R = Reservado, V = Vendido, F = Finalizado, X = Cancelado")
                ->default("R")
                ->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivacion")->nullable();
            $table->foreignId('tramo_id')
                ->nullable()
                ->constrained('horario_tramos')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("pasajes");
    }
};
