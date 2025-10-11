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
        Schema::create("impuestos", function (Blueprint $table) {
            $table->id();
            $table->string("codigo", 100)->index();
            $table->string("descripcion", 100)->index();
            $table->string("codigo_sunat", 1)->index();
            $table->decimal("valor")->index();
            $table->decimal("valor_2")->nullable();
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
        Schema::dropIfExists("impuestos");
    }
};
