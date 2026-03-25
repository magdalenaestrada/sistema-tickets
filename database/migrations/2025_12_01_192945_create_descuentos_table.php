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
        Schema::create('descuentos', function (Blueprint $table) {
            $table->id();
            $table->string("codigo")->index();
            $table->integer("cantidad_usos")->nullable();
            $table->date("fecha_maxima")->nullable()->index();
            $table->decimal('monto_efectivo', 10, 2)->nullable();
            $table->decimal('porcentaje', 5, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->string("tipo_asignacion_id");
            $table->string("tipo_descuento_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descuentos');
    }
};
