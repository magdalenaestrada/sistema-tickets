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
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('horario_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('fecha_salida');

            $table->enum('estado', ['activo', 'cancelado'])
                ->default('activo');

            $table->timestamps();

            $table->unique(['horario_id', 'fecha_salida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
