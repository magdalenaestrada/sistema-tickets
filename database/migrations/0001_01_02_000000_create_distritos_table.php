<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubigeo');
            $table->foreignId('provincia_id')->constrained('provincias');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distritos');
    }
};
