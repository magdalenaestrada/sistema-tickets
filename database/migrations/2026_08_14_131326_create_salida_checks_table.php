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
        Schema::create('salida_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salida_id')->constrained('salidas')->onDelete('cascade');
            $table->foreignId('punto_id')->constrained('ruta_puntos')->onDelete('cascade'); 
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registrado_en')->useCurrent();
            $table->timestamps();
            $table->unique(['salida_id', 'punto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_checks');
    }
};
