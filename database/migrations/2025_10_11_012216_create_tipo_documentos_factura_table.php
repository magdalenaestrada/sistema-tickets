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
        Schema::create("tipo_documentos_factura", function (Blueprint $table) {
            $table->id();
            $table->string("codigo", 100)->unique()->index();
            $table->string("descripcion", 100)->index();
            $table->string("codigo_sunat", 1)->unique()->index()->nullable();
            $table->enum("estado", ["A", "I"])->default("A")->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("tipo_documentos_factura");
    }
};
