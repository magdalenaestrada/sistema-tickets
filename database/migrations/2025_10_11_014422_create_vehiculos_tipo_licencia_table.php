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
        Schema::create("vehiculos_tipo_licencia", function (Blueprint $table) {
            $table->id();
            $table->foreignId("vehiculo_id")->constrained("vehiculos");
            $table
                ->foreignId("tipo_licencia_id")
                ->constrained("tipo_licencias");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vehiculos_tipo_licencia");
    }
};
