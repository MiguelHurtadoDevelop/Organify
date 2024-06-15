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
    /**
     * Crea una nueva notificación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public static function createNotificacion(Request $request)
    {
        // Validar los datos del request
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'user_id' => 'required|integer',
        ]);

        // Crear una nueva instancia de Notificacion y guardarla en la base de datos
        $notificacion = new Notificacion;
        $notificacion->titulo = $request->titulo;
        $notificacion->descripcion = $request->descripcion;
        $notificacion->tipo = $request->tipo;
        $notificacion->user_id = $request->user_id;
        $notificacion->data = $request->data; // Asumiendo que 'data' está presente en el request
        $notificacion->save();
    }

    /**
     * Elimina una notificación específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function deleteNotificacion(Request $request)
    {
        // Validar los datos del request
        $request->validate([
            'id' => 'required|integer',
        ]);

        // Encontrar la notificación por su ID y eliminarla
        $notificacion = Notificacion::find($request->id);
        $notificacion->delete();
    }
}
