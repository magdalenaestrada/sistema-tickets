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
        Schema::create('grupos_series', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique(); //001,002,BB,FF...
            $table->string('descripcion')->nullable();
            $table->string('estado', 1)->default('A');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo_series');
    }
};
