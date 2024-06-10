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
        Schema::create('horas_de_trabajo', function (Blueprint $table) {
            $table->id();
            $table->datetime('hora_ini');
            $table->datetime('hora_fin');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('equipo_id')->constrained('equipo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horas_de_trabajo');
    }
};
