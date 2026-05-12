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
        Schema::table('ruta_puntos', function (Blueprint $table) {

            $table->foreignId('pueblito_id')
                ->nullable()
                ->constrained('pueblitos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruta_puntos', function (Blueprint $table) {
            //
        });
    }
};
