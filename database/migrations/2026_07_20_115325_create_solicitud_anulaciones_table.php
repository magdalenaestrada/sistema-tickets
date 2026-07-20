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
        Schema::create('solicitud_anulaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained()->cascadeOnDelete();
            $table->foreignId('usuario_solicitante_id')
                ->constrained('users');
            $table->foreignId('usuario_aprobador_id')
                ->nullable()
                ->constrained('users');
            $table->text('motivo');
            $table->text('motivo_rechazo')->nullable();
            $table->enum('estado', [
                'Pendiente',
                'Aprobada',
                'Rechazada'
            ])->default('Pendiente');
            $table->timestamp('fecha_solicitud');
            $table->timestamp('fecha_respuesta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_anulacions');
    }
};
