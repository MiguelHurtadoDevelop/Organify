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
    public function tareasTablon()
    {
        $tareasTablon = Tarea::where('user_id', auth()->user()->id)
                        ->where('tipo', 'personal')
                        ->get();

        foreach ($tareasTablon as $tarea) {
            

            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();

            $tarea->archivos = $archivos;

        }
        
        return Inertia::render('Tablon/Tablon', [
            'tareasTablon' => $tareasTablon,
        ]);
    }

    public function tareasCalendario()
    {   
        $tareasCalendario = Tarea::where('user_id', auth()->user()->id)
                                ->whereNotNull('fecha_ini')
                                ->whereNotNull('fecha_fin')
                                ->get();
        
        \Log::info($tareasCalendario);

        foreach ($tareasCalendario as $tarea) {
            

            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();

            $tarea->archivos = $archivos;

        }
        
        return Inertia::render('Calendario/Calendario', [
            'tareasCalendario' => $tareasCalendario,
        ]);
    }

    public function createPersonal(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'color' => 'nullable|string', // Validación para el campo color
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Validación para múltiples archivos
        ]);

        $portadaName = null;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);
        }

        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

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
            'color' => $request->color, // Añadir este campo
        ]);

        $this->subirArchivos($archivoNames, $tarea->id);
        
        
        return redirect()->route('tablon');

    }

    public function delete(Tarea $tarea)
    {
        $tipo = $tarea->tipo;
        $equipo_id = $tarea->equipo_id;
        
        $archivos = Archivo::where('tarea_id', $tarea->id)
                    ->get();
                    
        if($tarea->imagen){
            $portadaPath = public_path('archivos') . '/' . $tarea->imagen;
            if (file_exists($portadaPath)) {
                unlink($portadaPath);
            }
        }

        foreach ($archivos as $archivo) {
            $archivoPath = public_path('archivos') . '/' . $archivo->nombre;
            if (file_exists($archivoPath)) {
                unlink($archivoPath);
            }
            $archivo->delete();
        }   
        
        $tarea->delete();

        if ($tipo == 'personal') {
            return redirect()->route('tablon');
        } else {
            $this->tareasTablonEquipo($equipo_id);
        }
        
    }

    public function updatePersonal(Request $request, Tarea $tarea)
    { 
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'color' => 'nullable|string', // Validación para el campo color
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Validación para múltiples archivos
        ]);
        

        $portadaName = $tarea->imagen;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);

            // Delete the previous portada file
            if ($tarea->imagen) {
                $previousPortada = public_path('archivos') . '/' . $tarea->imagen;
                if (file_exists($previousPortada)) {
                    unlink($previousPortada);
                }
            }
        }

        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        if($request->has('archivosParaEliminar')){
            foreach ($request->archivosParaEliminar as $archivo) {
                Archivo::find($archivo)->delete();
                $archivoPath = public_path('archivos') . '/' . $archivo;
                if (file_exists($archivoPath)) {
                    unlink($archivoPath);
                }
            }
        }

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
            'color' => $request->color, // Añadir este campo
        ]);

        $this->subirArchivos($archivoNames, $tarea->id);

        return redirect()->route('tablon');
    }

    public function createEquipo(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'equipo_id' => 'required',
            'color' => 'nullable|string', 
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimetypes:image/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,audio/mpeg,video/mp4|size:max:2048', 
        ]);

        $portadaName = null;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);
        }

        $archivoNames = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        $miembro = MiembroDeEquipo::where('user_id', auth()->user()->id)
                ->where('equipo_id', $request->equipo_id)
                ->first();


        if ($miembro->rol != 'manager') {
            $aceptada = 0;
        } else {
            $aceptada = 1;
        }

        $equipo = Equipo::find($request->equipo_id);
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
            'color' => $equipo->color, // Añadir este campo
        ]);

        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                    ->where('rol', 'manager')
                    ->get();

        if ($miembro->rol != 'manager') {
            foreach ($managers as $manager) {
                NotificacionController::createNotificacion(new Request([
                    'titulo' => auth()->user()->nombre . ' ' . auth()->user()->apellido . ' ha creado una tarea en el equipo: ' . $miembro->equipo->nombre,
                    'descripcion' => 'Te has unido al equipo: ' . $miembro->equipo->nombre,
                    'tipo' => 'solicitudTarea',
                    'user_id' => $manager->user_id,
                    'data' => json_encode(['tarea' => $tarea->id , 'equipo_id' => $request->equipo_id])
                ]));
            }   
        }

        $this->subirArchivos($archivoNames, $tarea->id);

        $this->tareasTablonEquipo($equipo->id);
    }

    public function updateEquipo(Request $request, Tarea $tarea)
    {
        $request->validate([
            'titulo' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
            'fecha_ini' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_ini',
            'prioridad' => 'required|string|max:255',
            'equipo_id' => 'required',
            'color' => 'nullable|string', // Validación para el campo color
            'portada' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
            'archivos.*' => 'nullable|file|mimetypes:image/*,application/pdf,text/plain,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed,audio/mpeg,video/mp4|size:max:2048',

        ]);

        $portadaName = $tarea->imagen;
        if ($request->hasFile('portada')) {
            $portada = $request->file('portada');
            $portadaName = time() . '.' . $portada->extension();
            $portada->move(public_path('archivos'), $portadaName);

            // Delete the previous portada file
            if ($tarea->imagen) {
                $previousPortada = public_path('archivos') . '/' . $tarea->imagen;
                if (file_exists($previousPortada)) {
                    unlink($previousPortada);
                }
            }
        }

        $archivoNames = json_decode($tarea->archivos, true) ?? [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $archivo) {
                $archivoName = time() . '_' . $archivo->getClientOriginalName();
                $archivo->move(public_path('archivos'), $archivoName);
                $archivoNames[] = $archivoName;
            }
        }

        if($request->has('archivosParaEliminar')){
            foreach ($request->archivosParaEliminar as $archivo) {
                Archivo::find($archivo)->delete();
                $archivoPath = public_path('archivos') . '/' . $archivo;
                if (file_exists($archivoPath)) {
                    unlink($archivoPath);
                }
            }
        }

        $rol = MiembroDeEquipo::where('user_id', auth()->user()->id)
                ->where('equipo_id', $request->equipo_id)
                ->first()->rol;

        if ($rol != 'manager') {
            $aceptada = 0;
        } else {
            $aceptada = 1;
        }

        $equipo = Equipo::find($request->equipo_id);

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
            'color' => $equipo->color, // Añadir este campo
        ]);

        
        $this->subirArchivos($archivoNames, $tarea->id);

        return redirect()->route('equipo.tablon', ['equipo' => $equipo]);
    }

    public function tareasTablonEquipo($equipo_id)
    {
        $equipo = Equipo::find($equipo_id);

        if(!$equipo){
            return redirect()->route('equipos');
        }

        $usuarioMiembro = MiembroDeEquipo::where('equipo_id', $equipo_id)
                                ->where('user_id', auth()->user()->id)
                                ->first();

        if(!$usuarioMiembro){
            return redirect()->route('equipos');
        }   

        $tareasTablonEquipo = Tarea::where('equipo_id', $equipo_id)
                        ->where('tipo', 'equipo')
                        ->where('aceptada', 1)
                        ->get();
        
        foreach ($tareasTablonEquipo as $tarea) {

            $archivos = Archivo::where('tarea_id', $tarea->id)
                        ->get()
                        ->toArray();

            $tarea->archivos = $archivos;

        }
        

        $miembros = MiembroDeEquipo::where('equipo_id', $equipo->id)->get();


        

        if($usuarioMiembro){
            $rol = $usuarioMiembro->rol;
        }else{
            $rol = null;
        }

        $usersMiembros = [];

        foreach($miembros as $miembro){
            
            $usersMiembros[] = [
                'miembro' => $miembro,
                'user' => $miembro->user, 
            ];
        }


        $rolUsuario = MiembroDeEquipo::where('user_id', auth()->user()->id)
                        ->where('equipo_id', $equipo_id)
                        ->first()->rol;


        return Inertia::render('Equipo/TablonEquipo', [
            'tareasTablonEquipo' => $tareasTablonEquipo,
            'equipo' => $equipo,
            'miembros' => $usersMiembros,
            'rol' => $rolUsuario,
            'authUser' => auth()->user(),
        ]);
    }

    public function asignarTarea(Request $request){
        $request->validate([
            'tarea_id' => 'required',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'user_id' => 'required',
        ]);
        $tarea = Tarea::find($request->tarea_id);
        $tarea->asignada = true;
        $tarea->fecha_ini = $request->fecha_ini;
        $tarea->fecha_fin = $request->fecha_fin;
        $tarea->user_id = $request->user_id;
        $tarea->save();

        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Se te ha asignado una tarea',
            'descripcion' => 'Se te ha asignado la tarea: <strong>' . $tarea->titulo . '</strong> en el equipo: <strong>' . $tarea->equipo->nombre . '</strong>',
            'tipo' => 'solicitudTarea',
            'user_id' => $request->user_id,
        ]));

        $this->tareasTablonEquipo($tarea->equipo_id);
    }

    public function aceptarTarea($tarea_id)
    {   $tarea = Tarea::find($tarea_id);
        
        $tarea->aceptada = 1;
        $tarea->save();

        $this->tareasTablonEquipo($tarea->equipo_id);
    }

    public function rechazarTarea($tarea_id)
    {
        $tarea = Tarea::find($tarea_id);

        if($tarea->imagen){
            $portadaPath = public_path('archivos') . '/' . $tarea->imagen;
            if (file_exists($portadaPath)) {
                unlink($portadaPath);
            }
        }

        $archivos = Archivo::where('tarea_id', $tarea->id)
                    ->get();
        foreach ($archivos as $archivo) {
            $archivoPath = public_path('archivos') . '/' . $archivo->nombre;
            if (file_exists($archivoPath)) {
                unlink($archivoPath);
            }
            $archivo->delete();
        }   

        $tarea->delete();

        $this->tareasTablonEquipo($tarea->equipo_id);
    }

    public function cambiarEstado(Request $request, $id)
    {   \Log::info($request);
        $request->validate([
            'estado' => 'required',
        ]);

        $tarea = Tarea::findOrFail($id);
        $tarea->estado = $request->estado;
        $tarea->save();

    }

    public function subirArchivos(Array $archivosName, $tarea_id)
    {
        foreach ($archivosName as $archivoName) {
            ArchivoController::createArchivo(new Request([
                'nombre' => $archivoName,
                'tarea_id' => $tarea_id,
            ]));
        }
    }

    public function cambiarFecha(Request $request, Tarea $tarea)
    {
        $request->validate([
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
        ]);

        
        $tarea->fecha_ini = $request->fecha_ini;
        $tarea->fecha_fin = $request->fecha_fin;
        $tarea->save();
    }
}
