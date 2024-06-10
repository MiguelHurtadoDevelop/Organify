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


    public static function createArchivo(Request $request)
    {   
        \Log::info($request);
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tarea_id' => 'required|integer',
        ]);

        $archivo = new Archivo;
        $archivo->nombre = $request->nombre;
        $archivo->tarea_id = $request->tarea_id;
        $archivo->save();

    }

    public function deleteArchivo(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $archivo = Archivo::find($request->id);
        
        $archivo->delete();

    }

}

