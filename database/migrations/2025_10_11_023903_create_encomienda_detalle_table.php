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
        Schema::create("encomienda_detalle", function (Blueprint $table) {
            $table->id();
            $table->foreignId("encomienda_id")->constrained("encomiendas");
            $table->string("tipo_equipaje", 100);
            $table->string("descripcion", 100);
            $table->decimal("peso", 10, 2)->default(0);
            $table->decimal("costo", 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("encomienda_detalle");
    }
};
