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
        Schema::create("pasaje_tramos", function (Blueprint $table) {
            $table->id();

            $table->foreignId('pasaje_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tramo_id')
                ->constrained('ruta_tramos')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['pasaje_id', 'tramo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasaje_tramos');
    }
};
