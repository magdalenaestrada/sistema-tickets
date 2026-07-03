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
        Schema::create('series_sucursal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')
            ->references('id')
            ->on('sucursales')
            ->restrictOnDelete();
            $table->foreignId('tipo_documento_factura_id')
                ->references('id')
                ->on('tipo_documentos_factura')
                ->restrictOnDelete();
            $table->string("serie", 4)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series_sucursal');
    }
};
