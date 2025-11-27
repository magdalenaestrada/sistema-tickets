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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas', 'id')->onDelete('cascade')->index('idx_empleados_persona');
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales', 'id')->nullOnDelete()->index('idx_empleados_sucursal');
            $table->foreignId('cargo_id')->nullable()->constrained('cargos', 'id')->nullOnDelete()->index('idx_empleados_cargo');
            $table->foreignId('tipo_licencia_id')->nullable()->constrained('tipo_licencias', 'id')->nullOnDelete()->index('idx_empleados_tipo_licencia');
            $table->string('licencia_conducir', 50)->nullable();
            $table->date('fecha_vencimiento_licencia')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado', ['A', 'I'])->default('A');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
