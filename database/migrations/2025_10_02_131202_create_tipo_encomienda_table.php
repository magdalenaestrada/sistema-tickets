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
        Schema::create('tipo_encomienda', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion')->unique();
            $table->decimal('precio_base', 10, 2);
            $table->decimal('peso_limite', 8, 2)->nullable();
            $table->decimal('costo_kilo_extra', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_encomienda');
    }
};
