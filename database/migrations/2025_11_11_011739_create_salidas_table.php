<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->date('fecha_salida');
            $table->enum('estado', ['programado', 'en_ruta', 'finalizado', 'cancelado'])
                ->default('programado');
            $table->timestamps();

            $table->unique(['horario_id', 'fecha_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
