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
        Schema::dropIfExists('series_sucursal');

        Schema::create('series_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('tipo_documento_factura_id')
                ->constrained('tipo_documentos_factura')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            $table->string('serie');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('series_sucursal', function (Blueprint $table) {
            //
        });
    }
};
