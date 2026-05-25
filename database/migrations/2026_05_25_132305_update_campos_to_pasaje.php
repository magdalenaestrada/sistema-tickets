<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasajes', function (Blueprint $table) {

            $table->foreignId('origen_pueblito_id')
                ->nullable()
                ->after('salida_id')
                ->constrained('pueblitos');

            $table->foreignId('destino_pueblito_id')
                ->nullable()
                ->after('origen_pueblito_id')
                ->constrained('pueblitos');

            $table->dropForeign(['origen_sucursal_id']);
            $table->dropForeign(['destino_sucursal_id']);

            $table->dropColumn([
                'origen_sucursal_id',
                'destino_sucursal_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pasajes', function (Blueprint $table) {

            $table->foreignId('origen_sucursal_id')
                ->nullable()
                ->constrained('sucursales');

            $table->foreignId('destino_sucursal_id')
                ->nullable()
                ->constrained('sucursales');

            $table->dropForeign(['origen_pueblito_id']);
            $table->dropForeign(['destino_pueblito_id']);

            $table->dropColumn([
                'origen_pueblito_id',
                'destino_pueblito_id'
            ]);
        });
    }
};
