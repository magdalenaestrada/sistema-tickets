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
        Schema::table('descuentos', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_cupon_id')->nullable();
            $table->foreign('tipo_cupon_id')->references('id')->on('tipo_cupones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descuentos', function (Blueprint $table) {
            //
        });
    }
};
