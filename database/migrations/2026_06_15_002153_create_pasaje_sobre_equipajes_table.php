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
        Schema::create('pasaje_sobre_equipajes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pasaje_id')
                ->constrained('pasajes')
                ->cascadeOnDelete();

            $table->foreignId('tipo_encomienda_id')
                ->nullable()
                ->constrained('tipo_encomienda');

            $table->string('descripcion')->nullable();

            $table->decimal('peso', 10, 2)->default(0);

            $table->decimal('costo', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasaje_sobre_equipajes');
    }
};
