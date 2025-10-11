<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("tipo_licencia_id")
                ->nullable()
                ->constrained("tipo_licencias");
            $table
                ->foreignId("sucursal_id")
                ->nullable()
                ->constrained("sucursales");
            $table->foreignId("persona_id")->constrained("personas");
            $table->string("documento", 20)->unique()->index();
            $table
                ->string("numero_licencia", 15)
                ->unique()
                ->nullable()
                ->index();
            $table->string("username")->nullable()->unique();
            $table->timestamp("email_verified_at")->nullable();
            $table->string("password")->nullable();
            $table->rememberToken();
            $table
                ->enum("estado", ["A", "I"])
                ->default("A")
                ->index();
            $table->dateTime("fecha_creacion");
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("password_reset_tokens", function (Blueprint $table) {
            $table->string("email")->primary();
            $table->string("token");
            $table->timestamp("created_at")->nullable();
        });

        Schema::create("sessions", function (Blueprint $table) {
            $table->string("id")->primary();
            $table->foreignId("user_id")->nullable()->index();
            $table->string("ip_address", 45)->nullable();
            $table->text("user_agent")->nullable();
            $table->longText("payload");
            $table->integer("last_activity")->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("users");
        Schema::dropIfExists("password_reset_tokens");
        Schema::dropIfExists("sessions");
    }
};
