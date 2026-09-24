<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correlativo_bajas', function (Blueprint $table) {
            $table->id();
            $table->string('serie', 10); // 'RA' o 'RC'
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->timestamps();

            $table->unique('serie'); // una fila por serie, evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correlativo_bajas');
    }
};
