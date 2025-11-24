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
        Schema::create('horarios_tramos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->foreignId('punto_origen_id')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('punto_destino_id')->constrained('sucursales')->onDelete('cascade');
            $table->decimal('costo', 8, 2); // Costo de este tramo
            $table->integer('orden')->default(1); // Para mantener orden si es necesario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_tramos');
    }
};
