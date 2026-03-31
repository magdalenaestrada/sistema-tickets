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
            $table->foreignId('vehiculo_id')->nullable()->constrained('vehiculos');
            $table->foreignId('conductor_principal_id')->nullable()->constrained('personas');
            $table->foreignId('conductor_secundario_id')->nullable()->constrained('personas');
            $table->unique(['horario_id', 'fecha_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
