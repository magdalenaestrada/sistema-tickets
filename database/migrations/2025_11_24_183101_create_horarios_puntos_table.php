<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->unsignedInteger('orden');
            $table->timestamps();
            $table->unique(['horario_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_puntos');
    }
};
