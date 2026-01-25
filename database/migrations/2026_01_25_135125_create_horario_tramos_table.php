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
        Schema::create('horario_tramos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->foreignId('punto_origen_id')->constrained('horario_puntos');
            $table->foreignId('punto_destino_id')->constrained('horario_puntos');
            $table->integer('duracion_minutos');
            $table->time('hora_llegada')->nullable(); // <-- nueva columna
            $table->decimal('costo_tramo', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
