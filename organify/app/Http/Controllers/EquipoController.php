<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipo;
use App\Models\MiembroDeEquipo;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Tarea;
use App\Http\Controllers\NotificacionController;

class EquipoController extends Controller
{
    /**
     * Obtiene todos los equipos y sus miembros para mostrar en la vista.
     *
     * @return \Inertia\Response
     */
    public function getEquipos()
    {
        // Obtener todos los equipos
        $equipos = Equipo::all();
        
        // Obtener equipos en los que el usuario actual es miembro
        $equiposUsuario = MiembroDeEquipo::where('user_id', Auth::user()->id)->get();

        // Iterar sobre cada equipo para obtener sus miembros
        foreach ($equipos as $equipo) {
            $miembros = MiembroDeEquipo::where('equipo_id', $equipo->id)->get();
            $usersMiembros = [];

            // Obtener información detallada de cada miembro
            foreach ($miembros as $miembro) {
                $usersMiembros[] = [
                    'miembro' => $miembro,
                    'user' => $miembro->user, 
                ];
            }

            // Asignar la lista de miembros al equipo actual
            $equipo->miembros = $usersMiembros;
        }

        // Renderizar la vista usando Inertia con datos de equipos y miembros del usuario
        return Inertia::render('Equipo/EquiposList', [
            'Equipos' => $equipos,
            'MiembroEquipos' => $equiposUsuario
        ]);
    }

    /**
     * Crea un nuevo equipo con la información proporcionada por el usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createEquipo(Request $request)
    {
        // Validar los datos de entrada del formulario
        $request->validate([
            'nombre' => 'required|string|max:30',
            'descripcion' => 'nullable|string|max:255',
            'tipo' => 'required|string|max:7',
            'foto' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'color' => 'required|string|max:7',
        ]);

        // Manejar la carga de la foto del equipo si está presente
        if ($request->hasFile('foto')) {
            $imageName = time().'.'.$request->foto->extension();  
            $request->foto->move(public_path('archivos'), $imageName);
        } else {
            $imageName = 'defaultTeam.jpg';
        }

        // Crear un nuevo equipo en la base de datos
        $equipo = new Equipo;
        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->foto = $imageName;
        $equipo->tipo = $request->tipo;
        $equipo->color = $request->color;
        $equipo->save();

        // Asignar al usuario actual como manager del nuevo equipo creado
        $miembro = new MiembroDeEquipo;
        $miembro->user_id = Auth::user()->id;
        $miembro->equipo_id = $equipo->id;
        $miembro->rol = 'manager';
        $miembro->aceptado = 1;
        $miembro->save();

        // Redirigir al tablón del equipo recién creado
        return redirect()->route('equipo.tablon', $equipo->id);
    }

    /**
     * Maneja la solicitud de un usuario para unirse a un equipo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function solicitarUnirse(Request $request)
    {
        // Verificar si el usuario ya es miembro del equipo
        $miembrosEquipo = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                        ->where('user_id', Auth::user()->id)
                                        ->get();

        // Si el usuario ya es miembro, redirigir con un mensaje de error
        if ($miembrosEquipo->count() > 0) {
            return redirect()->route('equipos', $request->equipo_id)->withErrors(['message' => 'Ya eres miembro de este equipo.']);
        } else {
            // Crear una nueva solicitud de unión al equipo para el usuario
            $miembro = new MiembroDeEquipo;
            $miembro->user_id = Auth::user()->id;
            $miembro->equipo_id = $request->equipo_id;
            $miembro->rol = 'member';
            $miembro->aceptado = 0;
            $miembro->save();
        }

        // Notificar a los managers del equipo sobre la solicitud de unión
        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();


        foreach ($managers as $manager) {            
            $notificacion = NotificacionController::createNotificacion(new Request([
                'titulo' => 'Solicitud de unión a equipo',
                'descripcion' => '<strong>' . Auth::user()->nombre . ' ' . Auth::user()->apellido . '</strong> ha solicitado unirse a tu equipo.',
                'tipo' => 'solicitud',
                'user_id' => $manager->user->id,
                'data' => json_encode(['user' => Auth::user(), 'equipo_id' => $request->equipo_id, 'equipo_nombre' => $manager->equipo->nombre])
            ]));


            $manager->user->notify(new \App\Notifications\SolicitudUnirseEquipo(Auth::user(), $request->equipo_id, $notificacion->id));
        }

    }

    /**
     * Acepta la solicitud de un usuario para unirse a un equipo.
     *
     * @param  int  $user_id
     * @param  int  $equipo_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function aceptarSolicitud($user_id, $equipo_id, $notificacion_id)
    {
        NotificacionController::deleteNotificacion(new Request([
            'id' => $notificacion_id
        ]));

        // Buscar el miembro del equipo con el usuario y equipo específicos
        $miembro = MiembroDeEquipo::where('user_id', $user_id)
                                    ->where('equipo_id', $equipo_id)
                                    ->first();

        // Si no se encuentra el miembro, redirigir con un mensaje de error
        if (!$miembro) {
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No se ha encontrado la solicitud.']);
        }

        // Aceptar la solicitud de unión actualizando el campo aceptado a 1
        $miembro->aceptado = 1;
        $miembro->save();

        // Obtener el equipo asociado a la solicitud
        $equipo = Equipo::find($equipo_id);

        // Notificar al usuario que su solicitud ha sido aceptada
        $miembro->user->notify(new \App\Notifications\SolicitudAceptada($user_id, $equipo_id));

        // Crear una notificación sobre la aceptación de la solicitud
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has sido aceptado en un equipo',
            'descripcion' => 'Tu solicitud para unirte al equipo "<strong>' . $miembro->equipo->nombre . '</strong>" ha sido aceptada.',
            'tipo' => 'aceptado' ,
            'user_id' => $user_id
        ]));

        return redirect()->route('equipo.tablon', $equipo_id);
        
    }

    /**
     * Rechaza la solicitud de un usuario para unirse a un equipo.
     *
     * @param  int  $user_id
     * @param  int  $equipo_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rechazarSolicitud($user_id, $equipo_id, $notificacion_id)
    {
        NotificacionController::deleteNotificacion(new Request([
            'id' => $notificacion_id
        ]));
        // Buscar el miembro del equipo con el usuario y equipo específicos
        $miembro = MiembroDeEquipo::where('user_id', $user_id)
                                    ->where('equipo_id', $equipo_id)
                                    ->first();
        
        // Si no se encuentra el miembro, redirigir con un mensaje de error
        if (!$miembro) {
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No se ha encontrado la solicitud.']);
        } else {
            // Eliminar el miembro del equipo y registrar la acción
            $miembro->delete();
        }

        // Notificar al usuario que su solicitud ha sido rechazada
        $miembro->user        ->notify(new \App\Notifications\SolicitudRechazada($user_id, $equipo_id));

        // Crear una notificación sobre el rechazo de la solicitud
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Solicitud de unión a equipo rechazada',
            'descripcion' => 'Tu solicitud para unirte al equipo "<strong>' . $miembro->equipo->nombre . '</strong>" ha sido rechazada.',
            'tipo' => 'rechazado',
            'user_id' => $user_id
        ]));

        return redirect()->route('equipo.tablon', $equipo_id);
        
    }

    /**
     * Permite a un usuario unirse a un equipo automáticamente sin solicitud.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function joinEquipo(Request $request)
    {
        // Verificar si el usuario ya es miembro del equipo
        $miembro = MiembroDeEquipo::where('user_id', Auth::user()->id)
                                    ->where('equipo_id', $request->equipo_id)
                                    ->first();

        // Si no es miembro, agregarlo como miembro del equipo
        if (!$miembro) {
            $miembro = new MiembroDeEquipo;
            $miembro->user_id = Auth::user()->id;
            $miembro->equipo_id = $request->equipo_id;
            $miembro->rol = 'member';
            $miembro->aceptado = 1;
            $miembro->save();
        }

        // Crear una notificación informando al usuario que se ha unido al equipo
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Te has unido a un equipo',
            'descripcion' => 'Te has unido al equipo: <strong>' . $miembro->equipo->nombre . '</strong>',
            'tipo' => 'unido',
            'user_id' => Auth::user()->id,
        ]));

        // Notificar a los managers del equipo sobre el nuevo miembro
        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();

        foreach ($managers as $manager) {
            NotificacionController::createNotificacion(new Request([
                'titulo' => 'Nuevo miembro en tu equipo',
                'descripcion' => '<strong>' . Auth::user()->nombre . ' ' . Auth::user()->apellido . '</strong> se ha unido al equipo: <strong>' . $miembro->equipo->nombre . '</strong>',
                'tipo' => 'unido',
                'user_id' => $manager->user_id,
            ]));
        }

        // Redirigir al tablón del equipo después de que el usuario se haya unido
        return redirect()->route('equipo.tablon', $request->equipo_id);
    }

    /**
     * Permite a un usuario dejar un equipo del cual es miembro.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leaveEquipo(Request $request)
    {
        // Buscar el miembro del equipo que quiere dejar el usuario actual
        $miembro = MiembroDeEquipo::where('user_id', Auth::user()->id)
                                    ->where('equipo_id', $request->equipo_id)
                                    ->first();

        // Obtener todos los managers del equipo
        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();
        
        // Si no hay managers en el equipo, eliminar el equipo completamente
        if ($managers->count() == 0) {
            $equipo = Equipo::find($request->equipo_id);
            $this->deleteEquipo($equipo->id);
        }

        // Eliminar al miembro del equipo
        $miembro->delete();

        // Crear una notificación informando al usuario que ha dejado el equipo
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has abandonado un equipo',
            'descripcion' => 'Has abandonado el equipo: <strong>' . $miembro->equipo->nombre . '</strong>',
            'tipo' => 'abandono',
            'user_id' => Auth::user()->id,
        ]));

        // Redirigir a la lista de equipos después de que el usuario haya dejado el equipo
        return redirect()->route('equipos');
    }

    /**
     * Actualiza la información de un equipo específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $equipo_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEquipo(Request $request, $equipo_id)
    {
        // Validar los datos de entrada del formulario de actualización
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:publico,privado',
            'color' => 'required|string|max:7',
            'foto' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
        ]);

        // Buscar el equipo por su ID
        $equipo = Equipo::findOrFail($equipo_id);

        // Actualizar los campos del equipo con la información proporcionada
        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->tipo = $request->tipo;
        $equipo->color = $request->color;

        // Manejar la carga de una nueva foto para el equipo si está presente
        if ($request->hasFile('foto')) {
            $imageName = time().'.'.$request->foto->extension();  
            $request->foto->move(public_path('archivos'), $imageName);

            // Eliminar la foto anterior del equipo si existe y no es la imagen por defecto
            if ($equipo->foto) {
                $previousFoto = public_path('archivos') . '/' . $equipo->foto;
                if (file_exists($previousFoto) && $equipo->foto != 'defaultTeam.jpg') {
                    unlink($previousFoto);
                }
            }

            $equipo->foto = $imageName;
        }

        // Actualizar el color de las notas asociadas al equipo si se proporciona un nuevo color
        if ($request->color) {
            $notas = Tarea::where('equipo_id', $equipo_id)->get();
            foreach ($notas as $nota) {
                $nota->color = $request->color;
                $nota->save();
            }
        }

        // Guardar los cambios realizados en el equipo
        $equipo->save();

        // Redirigir a la página de detalles del equipo actualizado
        
    }

    /**
     * Expulsa a un miembro específico del equipo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $miembro_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function expulsarMiembro(Request $request, $miembro_id)
    {
        // Buscar el miembro del equipo por su ID
        $miembro = MiembroDeEquipo::findOrFail($miembro_id);
        $equipo_id = $miembro->equipo_id;

        // Asegurarse de que el usuario que intenta expulsar es un manager del equipo
        $manager = MiembroDeEquipo::where('equipo_id', $equipo_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->where('rol', 'manager')
                                    ->first();

        // Si el usuario no es manager, mostrar un mensaje de error
        if (!$manager) {
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No tienes permiso para expulsar a este miembro.']);
        }

        // Crear una notificación informando al miembro expulsado sobre la acción
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has sido expulsado de un equipo',
            'descripcion' => 'Has sido expulsado del equipo: <strong>' . $miembro->equipo->nombre . '</strong>',
            'tipo' => 'expulsion',
            'user_id' => $miembro->user_id,
        ]));

        // Eliminar al miembro del equipo
        $miembro->delete();

        // Redirigir a la página de detalles del equipo después de la expulsión
       
    }

    /**
     * Elimina completamente un equipo y sus miembros.
     *
     * @param  int  $equipo_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteEquipo($equipo_id)
    {
        // Buscar el equipo por su ID
        $equipo = Equipo::findOrFail($equipo_id);

        // Asegurarse de que el usuario que intenta eliminar es un manager del equipo
        $manager = MiembroDeEquipo::where('equipo_id', $equipo_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->where('rol', 'manager')
                                    ->first();

        // Si el usuario no es manager, mostrar un mensaje de error
        if (!$manager) {
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No tienes permiso para eliminar el equipo.']);
        }

        // Eliminar todos los miembros del equipo
        MiembroDeEquipo::where('equipo_id', $equipo_id)->delete();

        // Eliminar todas las tareas asociadas al equipo
        Tarea::where('equipo_id', $equipo_id)->delete();

        // Eliminar la foto del equipo si existe
        if ($equipo->foto) {
            $foto = public_path('archivos') . '/' . $equipo->foto;
            if (file_exists($foto) && $equipo->foto != 'defaultTeam.jpg') {
                unlink($foto);
            }
        }

        // Eliminar el equipo de la base de datos
        $equipo->delete();

        // Redirigir a la lista de equipos después de eliminar el equipo
        return redirect()->route('equipos');
    }

}


