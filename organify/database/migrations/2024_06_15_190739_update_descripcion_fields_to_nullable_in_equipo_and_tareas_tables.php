<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescripcionFieldsToNullableInEquipoAndTareasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipo', function (Blueprint $table) {
            // Permitir que el campo 'descripción' sea nulo
            $table->string('descripcion')->nullable()->change();
        });

        Schema::table('tareas', function (Blueprint $table) {
            // Permitir que el campo 'descripción' sea nulo
            $table->string('descripcion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipo', function (Blueprint $table) {
            // Revertir los cambios para que el campo 'descripción' no pueda ser nulo
            $table->string('descripcion')->nullable(false)->change();
        });

        Schema::table('tareas', function (Blueprint $table) {
            // Revertir los cambios para que el campo 'descripción' no pueda ser nulo
            $table->string('descripcion')->nullable(false)->change();
        });
    }
}

