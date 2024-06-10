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
        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('descripcion');
            $table->datetime('fecha_ini')->nullable();
            $table->datetime('fecha_fin')->nullable();
            $table->string('estado');   
            $table->string('prioridad');
            $table->string('imagen')->nullable();
            $table->string('archivos')->nullable();
            $table->boolean('aceptada')->nullable();
            $table->string('tipo');
            $table->boolean('asignada')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('equipo_id')->nullable()->constrained('equipo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
