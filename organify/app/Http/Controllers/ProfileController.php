<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\MiembroDeEquipo;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Models\Tarea;
use App\Models\Notificacion;
use App\Models\Equipo;
use App\Http\Controllers\TareaController;   
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\EquipoController;

class ProfileController extends Controller
{
    /**
     * Muestra el formulario de edición del perfil del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function edit(Request $request): Response
    {
        // Obtener los equipos en los que el usuario es miembro
        $equipos = MiembroDeEquipo::where('user_id', auth()->user()->id)
                                    ->where('aceptado', 1)
                                    ->get();

        // Mapear los resultados para obtener solo los modelos de Equipo
        $equipos = $equipos->map(function ($equipo) {
            return $equipo->equipo;
        });

        // Renderizar la vista de edición de perfil con los datos necesarios
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'equipos' => $equipos,
            'status' => session('status'),
        ]);
    }

    /**
     * Actualiza la información del perfil del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        // Obtener el usuario actual
        $user = $request->user();

        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'foto' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
        ]);

        // Procesar la foto si se proporciona una nueva
        if($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoName = time() . '.' . $foto->extension();
            $foto->move(public_path('archivos'), $fotoName);  

            // Eliminar la foto anterior si no es la foto por defecto
            if($user->foto != 'defaultUser.jpg') {
                unlink(public_path('archivos/' . $user->foto)); 
            }
        } else {
            $fotoName = $user->foto; // Conservar la foto existente
        }

        // Actualizar los datos del usuario
        $user->update([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'foto' => $fotoName,
        ]);

        // Redireccionar de vuelta al formulario de edición con un mensaje de éxito
        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Elimina la cuenta del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Validar la contraseña actual antes de proceder con la eliminación
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        // Obtener el usuario actual
        $user = $request->user();

        // Obtener todas las tareas asociadas al usuario y eliminarlas
        $tareas = Tarea::where('user_id', $user->id)->get();
        foreach($tareas as $tarea) {
            $tareaController = new TareaController;
            $tareaController->delete($tarea);
        }

        // Obtener todos los miembros de equipos asociados al usuario y eliminarlos
        $miembros = MiembroDeEquipo::where('user_id', $user->id)->get();
        foreach($miembros as $miembro) {
            if($miembro->rol == 'manager'){
                $equipo = Equipo::find($miembro->equipo_id); 
                $managers = MiembroDeEquipo::where('equipo_id', $equipo->id)->where('rol', 'manager')->get();
                if($managers->count() == 1) {
                    $equipoController = new EquipoController;
                    $equipoController->deleteEquipo($equipo->id);
                }
            }
            $miembro->delete();
        }

        // Obtener todas las notificaciones asociadas al usuario y eliminarlas
        $notificaciones = Notificacion::where('user_id', $user->id)->get();
        foreach($notificaciones as $notificacion) {
            $notificacion->delete();
        }

        // Eliminar la foto del usuario si no es la foto por defecto
        if($user->foto != 'defaultUser.jpg') {
            unlink(public_path('archivos/' . $user->foto)); 
        }

        // Cerrar sesión del usuario y eliminar la cuenta
        Auth::logout();
        $user->delete();

        // Invalidar la sesión actual y regenerar el token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redireccionar al inicio después de eliminar la cuenta
        return Redirect::to('/');
    }
}
