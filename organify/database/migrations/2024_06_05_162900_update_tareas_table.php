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
        Schema::table('tareas', function (Blueprint $table) {
            // Eliminar el campo 'imagen' de la tabla 'tareas'
            $table->dropColumn('archivos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tareas', function (Blueprint $table) {
            // Revertir la eliminación del campo 'imagen' en caso de un rollback
            $table->string('archivos')->nullable();
        });
    }
};
