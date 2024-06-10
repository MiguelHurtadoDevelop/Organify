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



class NotificacionController extends Controller
{


    public static function createNotificacion(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'user_id' => 'required|integer',
        ]);

        $notificacion = new Notificacion;
        $notificacion->titulo = $request->titulo;
        $notificacion->descripcion = $request->descripcion;
        $notificacion->tipo = $request->tipo;
        $notificacion->user_id = $request->user_id;
        $notificacion->data = $request->data;
        $notificacion->save();

    }

    public function deleteNotificacion(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $notificacion = Notificacion::find($request->id);
        
        $notificacion->delete();

    }

}
