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
        Schema::table('tipo_vehiculos', function (Blueprint $table) {
            $table->integer("cantidad_pisos")->nullable();
            $table->string('licencia_requerida', 10)
                ->nullable()
                ->after('cantidad_pisos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_vehiculos', function (Blueprint $table) {
            //
        });
    }
};
