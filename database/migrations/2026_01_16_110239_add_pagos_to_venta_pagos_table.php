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
        Schema::table('venta_pagos', function (Blueprint $table) {
            $table->char('estado', 2)
                ->default('PA')
                ->comment('PE = Pendiente, PA = Pagado, AN = Anulado')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta_pagos', function (Blueprint $table) {
            //
        });
    }
};
