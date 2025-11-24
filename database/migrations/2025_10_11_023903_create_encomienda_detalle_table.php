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
            $table->unsignedBigInteger("encomienda_id");
            $table->unsignedBigInteger("tipo_encomienda_id");
            $table->string("descripcion", 100)->nullable();
            $table->decimal("peso", 10, 2)->default(0);
            $table->decimal("costo", 10, 2)->default(0);
            $table->timestamps();
            $table->foreign("encomienda_id")->references("id")->on("encomienda");
            $table->foreign("tipo_encomienda_id")->references("id")->on("tipo_encomienda");
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
