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
        Schema::table('encomienda', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_pueblito_id')->nullable();
            $table->unsignedBigInteger('destino_pueblito_id')->nullable();

            $table->foreign('origen_pueblito_id')
                ->references('id')
                ->on('pueblitos');

            $table->foreign('destino_pueblito_id')
                ->references('id')
                ->on('pueblitos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encomienda', function (Blueprint $table) {
            //
        });
    }
};
