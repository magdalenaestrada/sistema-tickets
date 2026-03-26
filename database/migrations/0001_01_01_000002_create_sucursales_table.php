<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("sucursales", function (Blueprint $table) {
            $table->id();
            $table->string("codigo_emision", 4)->default("0000");
            $table->foreignId("empresa_id")->constrained("empresas");
            $table->foreignId("distrito_id")->constrained("distritos");
            $table->string("nombre_comercial")->nullable();
            $table->string("direccion");
            $table->string("telefono")->nullable();
            $table->unique(['empresa_id', 'distrito_id', 'nombre_comercial']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sucursales");
    }
};
