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
        Schema::table('pasaje_sobre_equipajes', function (Blueprint $table) {
            $table->dropForeign(['tipo_encomienda_id']);

            $table->dropColumn([
                'tipo_encomienda_id',
                'descripcion',
                'peso',
                'costo',
            ]);

            $table->foreignId('encomienda_id')
                ->after('pasaje_id')
                ->nullable()
                ->constrained('encomienda')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasaje_sobre_equipajes', function (Blueprint $table) {
            //
        });
    }
};
