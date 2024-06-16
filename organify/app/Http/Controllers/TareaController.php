<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tarea;
use Inertia\Inertia;
use App\Models\Equipo;
use App\Models\MiembroDeEquipo;
use App\Models\Archivo;


class TareaController extends Controller
{
    /**
     * Función para mostrar las tareas personales con sus archivos asociados en el tablón.
     *
     * @return \Inertia\Response
     */
    public function tareasTablon()
    {
        // Selecciona todas las tareas personales del usuario autenticado
        $tareasTablon = Tarea::where('user_id', auth()->user()->id)
                        ->where('tipo', 'personal')
                        ->get();

        // Itera sobre cada tarea encontrada
        foreach ($tareasTablon as $tarea) {
            // Busca los archivos asociados a cada tarea
            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();

            // Asigna los archivos encontrados a la tarea actual
            $tarea->archivos = $archivos;
        }
        
        // Renderiza la vista utilizando Inertia.js, pasando las tareas con sus archivos
        return Inertia::render('Tablon/Tablon', [
            'tareasTablon' => $tareasTablon,
        ]);
    }


    /**
     * Obtiene y muestra las tareas del calendario para el usuario autenticado.
     *
     * @return \Inertia\Response
     */
    public function tareasCalendario()
    {   
        // Obtener todas las tareas del calendario para el usuario autenticado que tienen fechas de inicio y fin definidas
        $tareasCalendario = Tarea::where('user_id', auth()->user()->id)
                                ->whereNotNull('fecha_ini')
                                ->whereNotNull('fecha_fin')
                                ->get();


        // Iterar sobre cada tarea encontrada y obtener los archivos asociados
        foreach ($tareasCalendario as $tarea) {
            // Buscar los archivos asociados a cada tarea
            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();

            // Asignar los archivos encontrados a la tarea actual
            $tarea->archivos = $archivos;
        }
        
        // Renderizar la vista utilizando Inertia.js, pasando las tareas con sus archivos asociados
        return Inertia::render('Calendario/Calendario', [
            'tareasCalendario' => $tareasCalendario,
        ]);
    }


    /**
     * Crea una nueva tarea personal para el usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPersonal(Request $request)
    {
        // Validar los datos recibidos del formulario de creación de tarea personal
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'color' => 'nullable|string',
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf,xls,xlsx,doc,docx,ppt,pptx|max:2048',
        ]);

        // Subir y almacenar la imagen de portada si existe
        $portadaName = null;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);
        }

        // Subir y almacenar los archivos adjuntos si existen
        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        // Crear una nueva tarea personal en la base de datos
        $tarea = Tarea::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_ini' => $request->fecha_ini,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'to-do',
            'prioridad' => $request->prioridad,
            'aceptada' => null,
            'tipo' => 'personal',
            'asignada' => null,
            'imagen' => $portadaName,
            'user_id' => auth()->user()->id,
            'equipo_id' => null,
            'color' => $request->color, // Asignar el campo de color si está definido
        ]);

        // Llamar a un método para manejar la subida de archivos asociados a la tarea creada
        $this->subirArchivos($archivoNames, $tarea->id);
        
        // Redirigir a la ruta de tablón después de crear la tarea
        return redirect()->route('tablon');
    }


    /**
     * Elimina una tarea junto con sus archivos asociados.
     *
     * @param  Tarea  $tarea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Tarea $tarea)
    {
        // Almacena el tipo y el ID de equipo de la tarea antes de eliminarla
        $tipo = $tarea->tipo;
        $equipo_id = $tarea->equipo_id;
        
        // Obtiene todos los archivos asociados a la tarea
        $archivos = Archivo::where('tarea_id', $tarea->id)->get();
        
        // Elimina la imagen de portada si existe
        if ($tarea->imagen) {
            $portadaPath = public_path('archivos') . '/' . $tarea->imagen;
            if (file_exists($portadaPath)) {
                unlink($portadaPath); // Elimina el archivo físico de la imagen de portada
            }
        }

        // Itera sobre cada archivo asociado y los elimina junto con sus archivos físicos
        foreach ($archivos as $archivo) {
            $archivoPath = public_path('archivos') . '/' . $archivo->nombre;
            if (file_exists($archivoPath)) {
                unlink($archivoPath); // Elimina el archivo físico del archivo asociado
            }
            $archivo->delete(); // Elimina el registro del archivo de la base de datos
        }   
        
        // Elimina la tarea de la base de datos
        $tarea->delete();

        // Redirige a la ruta del tablón si la tarea era de tipo 'personal'
        if ($tipo == 'personal') {
            return redirect()->route('tablon');
        } else {
            // Llama al método para actualizar las tareas del equipo si la tarea era de un equipo
            $this->tareasTablonEquipo($equipo_id);
        }
    }

    /**
     * Actualiza una tarea personal existente para el usuario autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Tarea  $tarea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePersonal(Request $request, Tarea $tarea)
    { 
        // Validar los datos recibidos del formulario de actualización de tarea personal
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'color' => 'nullable|string', 
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimetypes:image/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,audio/mpeg,video/mp4|max:2048', 
        ]);
        
        // Mantener el nombre de la imagen de portada actual si no se sube una nueva
        $portadaName = $tarea->imagen;
        if ($request->hasFile('portada')) {
            // Subir y guardar la nueva imagen de portada si se sube una nueva
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);

            // Eliminar la imagen de portada anterior si existe
            if ($tarea->imagen) {
                $previousPortada = public_path('archivos') . '/' . $tarea->imagen;
                if (file_exists($previousPortada)) {
                    unlink($previousPortada); // Eliminar el archivo físico de la imagen anterior
                }
            }
        }

        // Subir y guardar los nuevos archivos adjuntos si existen
        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        // Eliminar los archivos seleccionados para eliminar si existen
        if ($request->has('archivosParaEliminar')) {
            foreach ($request->archivosParaEliminar as $archivoId) {
                $archivo = Archivo::find($archivoId);
                if ($archivo) {
                    $archivoPath = public_path('archivos') . '/' . $archivo->nombre;
                    if (file_exists($archivoPath)) {
                        unlink($archivoPath); // Eliminar el archivo físico del archivo seleccionado
                    }
                    $archivo->delete(); // Eliminar el registro del archivo de la base de datos
                }
            }
        }

        // Actualizar los datos de la tarea personal en la base de datos
        $tarea->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_ini' => $request->fecha_ini,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'to-do',
            'prioridad' => $request->prioridad,
            'aceptada' => null,
            'tipo' => 'personal',
            'asignada' => null,
            'imagen' => $portadaName,
            'user_id' => auth()->user()->id,
            'equipo_id' => null,
            'color' => $request->color, 
        ]);

        // Llamar al método para manejar la subida de archivos asociados a la tarea actualizada
        $this->subirArchivos($archivoNames, $tarea->id);

        // Redirigir a la ruta del tablón después de actualizar la tarea personal
        return redirect()->route('tablon');
    }



    /**
     * Crea una nueva tarea para un equipo específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function createEquipo(Request $request)
    {
        // Validar los datos recibidos del formulario de creación de tarea de equipo
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'equipo_id' => 'required',
            'color' => 'nullable|string', 
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimetypes:image/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,audio/mpeg,video/mp4|max:2048', 
        ]);

        // Manejar la subida de la imagen de portada si existe
        $portadaName = null;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);
        }

        // Manejar la subida de los archivos adjuntos si existen
        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        // Obtener el rol del usuario en el equipo para determinar si la tarea debe ser aceptada automáticamente
        $miembro = MiembroDeEquipo::where('user_id', auth()->user()->id)
                ->where('equipo_id', $request->equipo_id)
                ->first();

        // Determinar si la tarea debe ser aceptada automáticamente o no basado en el rol del usuario
        $aceptada = ($miembro->rol != 'manager') ? 0 : 1;

        // Obtener el equipo al que pertenece la tarea
        $equipo = Equipo::find($request->equipo_id);

        // Crear la nueva tarea de equipo en la base de datos
        $tarea = Tarea::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_ini' => $request->fecha_ini,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'to-do',
            'prioridad' => $request->prioridad,
            'aceptada' => $aceptada,
            'tipo' => 'equipo',
            'asignada' => null,
            'imagen' => $portadaName,
            'user_id' => null,
            'equipo_id' => $request->equipo_id,
            'color' => $equipo->color, // Asignar el color del equipo a la tarea
        ]);

        // Notificar a los managers del equipo sobre la creación de la tarea si el usuario no es manager
        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                    ->where('rol', 'manager')
                    ->get();

        if ($miembro->rol != 'manager') {
            foreach ($managers as $manager) {
                NotificacionController::createNotificacion(new Request([
                    'titulo' => auth()->user()->nombre . ' ' . auth()->user()->apellido . ' ha creado una tarea en el equipo: ' . $miembro->equipo->nombre,
                    'descripcion' => '<strong>Tarea</strong>: ' . $tarea->titulo,
                    'tipo' => 'solicitudTarea',
                    'user_id' => $manager->user_id,
                    'data' => json_encode(['tarea' => $tarea->id , 'equipo_id' => $request->equipo_id])
                ]));
            }   
        }

        // Llamar al método para manejar la subida de archivos asociados a la tarea creada
        $this->subirArchivos($archivoNames, $tarea->id);

        // Actualizar las tareas en el tablón del equipo después de crear la tarea
        $this->tareasTablonEquipo($equipo->id);
    }


    /**
     * Actualiza una tarea de equipo existente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Tarea  $tarea
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateEquipo(Request $request, Tarea $tarea)
    {
        // Validar los datos recibidos del formulario de actualización de tarea de equipo
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'equipo_id' => 'required',
            'color' => 'nullable|string',
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimetypes:image/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,audio/mpeg,video/mp4|max:2048',
        ]);

        // Manejar la subida de la imagen de portada si existe
        $portadaName = $tarea->imagen;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);

            // Eliminar la portada anterior si existe
            if ($tarea->imagen) {
                $previousPortada = public_path('archivos') . '/' . $tarea->imagen;
                if (file_exists($previousPortada)) {
                    unlink($previousPortada);
                }
            }
        }

        // Manejar la actualización de archivos adjuntos
        $archivoNames = json_decode($tarea->archivos, true) ?? [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        // Manejar la eliminación de archivos seleccionados
        if ($request->has('archivosParaEliminar')) {
            foreach ($request->archivosParaEliminar as $archivo) {
                $archivoModel = Archivo::find($archivo);
                if ($archivoModel) {
                    $archivoModel->delete();
                    $archivoPath = public_path('archivos') . '/' . $archivoModel->nombre;
                    if (file_exists($archivoPath)) {
                        unlink($archivoPath);
                    }
                }
            }
        }

        // Determinar si la tarea debe ser aceptada automáticamente basado en el rol del usuario en el equipo
        $rol = MiembroDeEquipo::where('user_id', auth()->user()->id)
                ->where('equipo_id', $request->equipo_id)
                ->first()->rol;

        $aceptada = ($rol != 'manager') ? 0 : 1;

        // Obtener el equipo asociado a la tarea
        $equipo = Equipo::find($request->equipo_id);

        // Actualizar la tarea en la base de datos con los nuevos datos
        $tarea->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_ini' => $request->fecha_ini,
            'fecha_fin' => $request->fecha_fin,
            'estado' => 'to-do',
            'prioridad' => $request->prioridad,
            'aceptada' => $aceptada,
            'tipo' => 'equipo',
            'asignada' => null,
            'imagen' => $portadaName,
            'user_id' => null,
            'equipo_id' => $request->equipo_id,
            'color' => $equipo->color, // Asignar el color del equipo a la tarea
        ]);

        // Llamar al método para manejar la subida de archivos asociados a la tarea actualizada
        $this->subirArchivos($archivoNames, $tarea->id);

        // Redireccionar a la ruta del tablón del equipo actualizado
        return redirect()->route('equipo.tablon', ['equipo' => $equipo]);
    }


    /**
     * Obtiene las tareas del tablón de un equipo específico y renderiza la vista correspondiente.
     *
     * @param  int  $equipo_id
     * @return \Inertia\Response
     */
    public function tareasTablonEquipo($equipo_id)
    {
        // Buscar el equipo por su ID
        $equipo = Equipo::find($equipo_id);

        // Si el equipo no existe, redirigir al listado de equipos
        if (!$equipo) {
            return redirect()->route('equipos');
        }

        // Verificar si el usuario es miembro del equipo
        $usuarioMiembro = MiembroDeEquipo::where('equipo_id', $equipo_id)
                            ->where('user_id', auth()->user()->id)
                            ->first();

        // Si el usuario no es miembro del equipo, redirigir al listado de equipos
        if (!$usuarioMiembro) {
            return redirect()->route('equipos');
        }

        // Obtener las tareas del equipo que han sido aceptadas
        $tareasTablonEquipo = Tarea::where('equipo_id', $equipo_id)
                                ->where('tipo', 'equipo')
                                ->where('aceptada', 1)
                                ->get();
        
        // Obtener y asignar los archivos asociados a cada tarea del equipo
        foreach ($tareasTablonEquipo as $tarea) {
            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();
            $tarea->archivos = $archivos;
        }

        // Obtener todos los miembros del equipo
        $miembros = MiembroDeEquipo::where('equipo_id', $equipo->id)->get();

        // Determinar el rol del usuario actual en el equipo
        $rolUsuario = $usuarioMiembro ? $usuarioMiembro->rol : null;

        // Preparar un arreglo con información de usuarios y sus roles en el equipo
        $usersMiembros = [];
        foreach ($miembros as $miembro) {
            $usersMiembros[] = [
                'miembro' => $miembro,
                'user' => $miembro->user, // Acceder al modelo de usuario asociado al miembro
            ];
        }

        // Renderizar la vista del tablón del equipo con los datos obtenidos
        return Inertia::render('Equipo/TablonEquipo', [
            'tareasTablonEquipo' => $tareasTablonEquipo,
            'equipo' => $equipo,
            'miembros' => $usersMiembros,
            'rol' => $rolUsuario,
            'authUser' => auth()->user(), // Usuario autenticado actualmente
        ]);
    }


    /**
     * Asigna una tarea a un usuario específico dentro de un equipo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function asignarTarea(Request $request)
    {
        // Validar los datos recibidos para la asignación de la tarea
        $request->validate([
            'tarea_id' => 'required',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'user_id' => 'required',
        ]);

        // Buscar la tarea por su ID
        $tarea = Tarea::find($request->tarea_id);

        // Actualizar los datos de la tarea con la asignación
        $tarea->asignada = true;
        $tarea->fecha_ini = $request->fecha_ini;
        $tarea->fecha_fin = $request->fecha_fin;
        $tarea->user_id = $request->user_id;
        $tarea->save();

        // Crear una notificación para informar al usuario asignado sobre la tarea
        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Se te ha asignado una tarea',
            'descripcion' => 'Se te ha asignado la tarea <strong>' . $tarea->titulo . '</strong> en el equipo <strong>' . $tarea->equipo->nombre . '</strong>',
            'tipo' => 'solicitudTarea',
            'user_id' => $request->user_id,
        ]));

        // Redireccionar de vuelta al tablón del equipo después de realizar la asignación
        $this->tareasTablonEquipo($tarea->equipo_id);
    }


    /**
     * Marca una tarea como aceptada dentro del tablón del equipo.
     *
     * @param  int  $tarea_id
     * @return void
     */
    public function aceptarTarea($tarea_id, $notificacion_id)
    {
        NotificacionController::deleteNotificacion(new Request([
            'id' => $notificacion_id
        ]));
        // Buscar la tarea por su ID
        $tarea = Tarea::find($tarea_id);
        
        // Marcar la tarea como aceptada
        $tarea->aceptada = 1;
        $tarea->save();

        // Redireccionar de vuelta al tablón del equipo después de marcar la tarea como aceptada
        return redirect()->route('equipo.tablon', $tarea->equipo_id);
    }


    /**
     * Elimina una tarea rechazada junto con sus archivos asociados del tablón del equipo.
     *
     * @param  int  $tarea_id
     * @return void
     */
    public function rechazarTarea($tarea_id, $notificacion_id)
    {
        NotificacionController::deleteNotificacion(new Request([
            'id' => $notificacion_id
        ]));
        // Buscar la tarea por su ID
        $tarea = Tarea::find($tarea_id);

        // Eliminar la imagen de portada asociada a la tarea si existe
        if ($tarea->imagen) {
            $portadaPath = public_path('archivos') . '/' . $tarea->imagen;
            if (file_exists($portadaPath)) {
                unlink($portadaPath);
            }
        }

        // Obtener y eliminar todos los archivos asociados a la tarea
        $archivos = Archivo::where('tarea_id', $tarea->id)->get();
        foreach ($archivos as $archivo) {
            $archivoPath = public_path('archivos') . '/' . $archivo->nombre;
            if (file_exists($archivoPath)) {
                unlink($archivoPath);
            }
            $archivo->delete();
        }   

        // Eliminar la tarea
        $tarea->delete();

        // Redireccionar de vuelta al tablón del equipo después de eliminar la tarea
        return redirect()->route('equipo.tablon', $tarea->equipo_id);
    }


    /**
     * Cambia el estado de una tarea específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return void
     */
    public function cambiarEstado(Request $request, $id)
    {
        // Validar los datos recibidos para el cambio de estado de la tarea
        $request->validate([
            'estado' => 'required',
        ]);

        // Buscar la tarea por su ID
        $tarea = Tarea::findOrFail($id);

        // Actualizar el estado de la tarea con el nuevo estado proporcionado
        $tarea->estado = $request->estado;
        $tarea->save();
    }


    /**
     * Sube archivos asociados a una tarea especificada.
     *
     * @param  array  $archivosName
     * @param  int  $tarea_id
     * @return void
     */
    public function subirArchivos(Array $archivosName, $tarea_id)
    {
        // Iterar a través de los nombres de los archivos y crear registros de archivo asociados a la tarea
        foreach ($archivosName as $archivoName) {
            ArchivoController::createArchivo(new Request([
                'nombre' => $archivoName,
                'tarea_id' => $tarea_id,
            ]));
        }
    }


    /**
     * Cambia la fecha de inicio y fin de una tarea específica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Tarea  $tarea
     * @return void
     */
    public function cambiarFecha(Request $request, Tarea $tarea)
    {
        // Validar los datos recibidos para el cambio de fecha de la tarea
        $request->validate([
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
        ]);

        // Actualizar las fechas de inicio y fin de la tarea con las nuevas fechas proporcionadas
        $tarea->fecha_ini = $request->fecha_ini;
        $tarea->fecha_fin = $request->fecha_fin;
        $tarea->save();
    }

}
