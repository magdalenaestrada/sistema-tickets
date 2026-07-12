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
        Schema::disableForeignKeyConstraints();

        Schema::table('salidas', function (Blueprint $table) {
            $table->dropForeign(['conductor_principal_id']);
            $table->dropForeign(['conductor_secundario_id']);
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->foreign('conductor_principal_id')
                ->references('id')
                ->on('empleados')
                ->nullOnDelete();

            $table->foreign('conductor_secundario_id')
                ->references('id')
                ->on('empleados')
                ->nullOnDelete();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
