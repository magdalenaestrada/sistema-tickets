<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_puntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('origen_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('destino_id')->constrained('sucursales')->onDelete('cascade');
            $table->decimal('costo_acumulado', 8, 2)->default(0); // Costo total desde el origen
            $table->integer('orden'); // Orden del punto en la ruta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_puntos');
    }
};
