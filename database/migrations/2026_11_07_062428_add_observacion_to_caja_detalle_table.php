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
        Schema::table('caja_detalle', function (Blueprint $table) {
            $table->text('observacion')->nullable()->after('description');
            $table->foreignId('observacion_usuario_id')
                ->nullable()
                ->after('observacion')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('observacion_at')->nullable()->after('observacion_usuario_id');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caja_detalle', function (Blueprint $table) {
            //
        });
    }
};
