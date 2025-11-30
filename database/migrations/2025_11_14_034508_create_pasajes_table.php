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
                ->nullable()                  // permite que sea null
                ->constrained('personas')     // referencia a la tabla personas
                ->nullOnDelete();             // si la persona se elimina, pone null aquí
            $table->boolean("pasajero_menor")->nullable();
            $table->string("autorizacion_pdf")->nullable();
            $table->integer('asiento_numero'); // 1 a 15
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->unique(['horario_id', 'asiento_numero'], 'asiento_unico_por_horario');
            $table->enum("estado", ["R", "V"])
                ->comment("R = Reservado, V = Vendido")
                ->default("R")
                ->index();

            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivacion")->nullable();
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
