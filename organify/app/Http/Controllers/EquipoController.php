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
    public function getEquipos()
    {
        $equipos = Equipo::all();
        
        $equiposUsuario = MiembroDeEquipo::where('user_id', Auth::user()->id)
                                        ->get();

        foreach($equipos as $equipo){
            $miembros = MiembroDeEquipo::where('equipo_id', $equipo->id)
                                        ->get();
            $usersMiembros = [];

            foreach($miembros as $miembro){
                
                $usersMiembros[] = [
                    'miembro' => $miembro,
                    'user' => $miembro->user, 
                ];
            }

            $equipo->miembros = $usersMiembros;
        }


        return Inertia::render('Equipo/EquiposList', [
            'Equipos' => $equipos,
            'MiembroEquipos' => $equiposUsuario
        ]);
    }

    public function createEquipo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'color' => 'required|string|max:7',
        ]);

        if($request->hasFile('foto')){
            $request->validate([
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time().'.'.$request->foto->extension();  
            $request->foto->move(public_path('archivos'), $imageName);
        }else{
            $imageName = 'default.jpg';
        }

        $equipo = new Equipo;
        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->foto = $imageName;
        $equipo->tipo = $request->tipo;
        $equipo->color = $request->color;
        $equipo->save();

        $miembro = new MiembroDeEquipo;
        $miembro->user_id = Auth::user()->id;
        $miembro->equipo_id = $equipo->id;
        $miembro->rol = 'manager';
        $miembro->aceptado = 1;
        $miembro->save();

        return redirect()->route('equipo.tablon', $equipo->id);
    }

    public function solicitarUnirse(Request $request)
    {
        $miembrosEquipo= MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                        ->where('user_id', Auth::user()->id)
                                        ->get();

        if($miembrosEquipo->count() > 0){
            return redirect()->route('equipoById', $request->equipo_id)->withErrors(['message' => 'Ya eres miembro de este equipo.']);
        }else{
            $miembro = new MiembroDeEquipo;
            $miembro->user_id = Auth::user()->id;
            $miembro->equipo_id = $request->equipo_id;
            $miembro->rol = 'member';
            $miembro->aceptado = 0;
            $miembro->save();
        }

        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();

        foreach($managers as $manager){
            $manager->user->notify(new \App\Notifications\SolicitudUnirseEquipo(Auth::user(), $request->equipo_id));
            NotificacionController::createNotificacion(new Request([
                'titulo' => 'Solicitud de unión a equipo',
                'descripcion' => Auth::user()->nombre  . ' ha solicitado unirse a tu equipo.',
                'tipo' => 'solicitud',
                'user_id' => $manager->user->id,
                'data' => json_encode(['user' => Auth::user(), 'equipo_id' => $request->equipo_id])
            ]));
        }

        $this->equipoById($request->equipo_id);
    }

    public function aceptarSolicitud($user_id, $equipo_id)
    {
        $miembro = MiembroDeEquipo::where('user_id', $user_id)
                                    ->where('equipo_id', $equipo_id)
                                    ->first();

        if(!$miembro){
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No se ha encontrado la solicitud.']);
        }
        $miembro->aceptado = 1;

        $miembro->save();

        $miembro->user->notify(new \App\Notifications\SolicitudAceptada($user_id, $equipo_id));

        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has sido aceptado en un equipo',
            'descripcion' => 'Tu solicitud para unirte al equipo ha sido aceptada.',
            'tipo' => 'aceptado' ,
            'user_id' => $user_id
        ]));

        return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No se ha encontrado la solicitud.']);
    }

    public function rechazarSolicitud($user_id, $equipo_id){
        $miembro = MiembroDeEquipo::where('user_id', $user_id)
                                    ->where('equipo_id', $equipo_id)
                                    ->first();

        if(!$miembro){
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No se ha encontrado la solicitud.']);
        }else{
            $miembro->delete();
        }

        $miembro->user->notify(new \App\Notifications\SolicitudRechazada($user_id, $equipo_id));

        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Solicitud de unión a equipo rechazada',
            'descripcion' => 'Tu solicitud para unirte al equipo ha sido rechazada.',
            'tipo' => 'rechazado' ,
            'user_id' => $user_id
        ]));

    }

    public function joinEquipo(Request $request)
    {
        $miembro = MiembroDeEquipo::where('user_id', Auth::user()->id)
                                    ->where('equipo_id', $request->equipo_id)
                                    ->first();
        

        if(!$miembro){
            $miembro = new MiembroDeEquipo;
            $miembro->user_id = Auth::user()->id;
            $miembro->equipo_id = $request->equipo_id;
            $miembro->rol = 'member';
            $miembro->aceptado = 1;
            $miembro->save();
        }


        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Te has unido a un equipo',
            'descripcion' => 'Te has unido al equipo: ' . $miembro->equipo->nombre,
            'tipo' => 'unido',
            'user_id' => Auth::user()->id,
        ]));

        $manager = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();

        foreach($manager as $manager){
            NotificacionController::createNotificacion(new Request([
                'titulo' => 'Nuevo miembro en tu equipo',
                'descripcion' => Auth::user()->nombre .' se ha unido al equipo:' . $miembro->equipo->nombre,
                'tipo' => 'unido',
                'user_id' => $manager->user_id,
            ]));
        }
        return redirect()->route('equipo.tablon', $request->equipo_id);
    }

    public function equipoById($equipo_id)
    {
        
        $equipo = Equipo::find($equipo_id);

        $miembros = MiembroDeEquipo::where('equipo_id', $equipo->id)->get();


        $usuarioMiembro = MiembroDeEquipo::where('equipo_id', $equipo->id)
                                ->where('user_id', Auth::user()->id)
                                ->first();

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

        return Inertia::render('Equipo/Equipo', [
            'equipo' => $equipo,
            'miembros' => $usersMiembros,
            'rol' => $rol
        ]);
    }

    public function leaveEquipo(Request $request)
    {
        $miembro = MiembroDeEquipo::where('user_id', Auth::user()->id)
                                    ->where('equipo_id', $request->equipo_id)
                                    ->first();

        $managers = MiembroDeEquipo::where('equipo_id', $request->equipo_id)
                                    ->where('rol', 'manager')
                                    ->get();
        
        if($managers->count() == 0 ){
            $equipo = Equipo::find($request->equipo_id);
            $this->deleteEquipo($equipo->id);
        }
        $miembro->delete();

        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has abandonado un equipo',
            'descripcion' => 'Has abandonado el equipo: ' . $miembro->equipo->nombre,
            'tipo' => 'abandono',
            'user_id' => Auth::user()->id,
        ]));

        return redirect()->route('equipos');
    }

    public function updateEquipo(Request $request, $equipo_id)
    {
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|string|in:publico,privado',
            'color' => 'required|string|max:7',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $equipo = Equipo::findOrFail($equipo_id); 
        $equipo->nombre = $request->nombre;
        $equipo->descripcion = $request->descripcion;
        $equipo->tipo = $request->tipo;
        $equipo->color = $request->color;

        if ($request->hasFile('foto')) {
            $imageName = time().'.'.$request->foto->extension();  
            $request->foto->move(public_path('archivos'), $imageName);
            $equipo->foto = $imageName;
        }
        if($request->color){
            $notas = Tarea::where('equipo_id', $equipo_id)->get();
            foreach($notas as $nota){
                $nota->color = $request->color;
                $nota->save();
            }
        }

        $equipo->save();
        

        $this->equipoById($equipo_id);
        
    }

    public function expulsarMiembro(Request $request, $miembro_id)
    {
        $miembro = MiembroDeEquipo::findOrFail($miembro_id);
        $equipo_id = $miembro->equipo_id;

        // Asegurarse de que el usuario que intenta expulsar es un manager del equipo
        $manager = MiembroDeEquipo::where('equipo_id', $equipo_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->where('rol', 'manager')
                                    ->first();

        if (!$manager) {
            return redirect()->route('equipoById', $equipo_id)->withErrors(['message' => 'No tienes permiso para expulsar miembros.']);
        }

        NotificacionController::createNotificacion(new Request([
            'titulo' => 'Has sido expulsado de un equipo',
            'descripcion' => 'Has sido expulsado del equipo:: ' . $miembro->equipo->nombre,
            'tipo' => 'abandono',
            'user_id' => $miembro->user_id,
        ]));

        $miembro->delete();

        $this->equipoById($equipo_id);
    }

    
    public function deleteEquipo($equipo_id)
    {
        $equipo = Equipo::findOrFail($equipo_id);

        // Asegurarse de que el usuario que intenta eliminar es un manager del equipo
        $manager = MiembroDeEquipo::where('equipo_id', $equipo_id)
                                    ->where('user_id', Auth::user()->id)
                                    ->where('rol', 'manager')
                                    ->first();

        if (!$manager) {
            return redirect()->route('equipo.tablon', $equipo_id)->withErrors(['message' => 'No tienes permiso para eliminar el equipo.']);
        }

        // Eliminar todos los MiembroDeEquipo asociados
        MiembroDeEquipo::where('equipo_id', $equipo_id)->delete();

        Tarea::where('equipo_id', $equipo_id)->delete();

        $equipo->delete();

        return redirect()->route('equipos');
    }

}
