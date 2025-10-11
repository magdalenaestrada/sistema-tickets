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
        Schema::create("subtipo_movimiento_caja", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("tipo_movimiento_caja_id")
                ->constrained("tipo_movimiento_caja");
            $table->string("descripcion", 100)->index();
            $table
                ->enum("estado", ["A", "I"])
                ->default("A")
                ->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("subtipo_movimiento_caja");
    }
};
