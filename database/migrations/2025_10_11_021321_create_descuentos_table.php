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
        Schema::create("descuentos", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("tipo_documento_id")
                ->constrained("tipo_documento_personas");
            $table
                ->foreignId("tipo_descuento_id")
                ->constrained("tipo_descuentos");
            $table->foreignId("persona_id")->constrained("personas");
            $table->string("cupon", 6)->index();
            $table->unsignedInteger("porcentaje_descuento")->default(0);
            $table
                ->enum("estado", ["A", "I"])
                ->default("A")
                ->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivacion")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("descuentos");
    }
};
