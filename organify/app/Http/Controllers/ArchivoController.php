<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use Inertia\Inertia;
use App\Models\Notificacion;
use App\Models\Equipo;
use App\Models\MiembroDeEquipo;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Archivo;

class ArchivoController extends Controller
{
    /**
     * Crea un nuevo archivo asociado a una tarea.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public static function createArchivo(Request $request)
    {   // Validación de los datos de entrada
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tarea_id' => 'required|integer',
        ]);

        // Creación de un nuevo archivo en la base de datos
        $archivo = new Archivo;
        $archivo->nombre = $request->nombre;
        $archivo->tarea_id = $request->tarea_id;
        $archivo->save();
    }

    /**
     * Elimina un archivo existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function deleteArchivo(Request $request)
    {
        // Validación de los datos de entrada
        $request->validate([
            'id' => 'required|integer',
        ]);

        // Búsqueda y eliminación del archivo
        $archivo = Archivo::find($request->id);
        if ($archivo) {
            $archivo->delete();
        }
    }
}

