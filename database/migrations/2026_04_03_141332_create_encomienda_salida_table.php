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
        Schema::create('encomienda_salida', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomienda_id')->constrained('encomienda');
            $table->foreignId('salida_id')->constrained('salidas');
            $table->foreignId('usuario_id')->nullable()->constrained('users');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_llegada')->nullable();
            $table->string('estado', 1)->default('A'); // A=Asignada, L=Llegó
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encomienda_salida');
    }
};
