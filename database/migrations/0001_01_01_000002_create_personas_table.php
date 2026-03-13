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
        Schema::create("personas", function (Blueprint $table) {
            $table->id();
            $table->foreignId("tipo_documento_id")->constrained("tipo_documento_personas");
            $table->foreignId("distrito_id")->nullable()->constrained("distritos");
            $table->string("documento", 20)->nullable()->index();
            $table->string("nombres", 200)->nullable();
            $table->string("apellidos", 200)->nullable()->index();
            $table->string('razon_social', 200)->nullable()->index();
            $table->string("telefono", 20)->nullable()->index();
            $table->string("celular", 20)->nullable()->index();
            $table->string("correo", 150)->nullable()->index();
            $table->string("direccion", 150)->nullable()->index();
            $table->date("fecha_nacimiento")->nullable();
            $table->enum("estado", ["A", "I"])->default("A")->index();
            $table->dateTime("fecha_creacion");
            $table->dateTime("fecha_inactivacion")->nullable();
            $table->unique(['tipo_documento_id', 'documento']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("personas");
    }
};
