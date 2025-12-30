<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->date("fecha_inicio");
            $table->time("hora_inicio");
            $table->date("fecha_fin")->nullable();
            $table->time("hora_fin")->nullable();
            $table->foreignId("vehiculo_id")->constrained("vehiculos")->references("id");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos_mantenimiento');
    }
};
