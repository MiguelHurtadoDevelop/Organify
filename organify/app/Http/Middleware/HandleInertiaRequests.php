<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Equipo;
use App\Models\MiembroDeEquipo;
use Illuminate\Support\Facades\Auth;
use App\Models\Notificacion;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'EquiposUsuario' => function () {
                if (Auth::check()) {
                    $equipos = MiembroDeEquipo::where('user_id', Auth::id())
                                                ->where('aceptado', 1)
                                                ->pluck('equipo_id');
                    return Equipo::whereIn('id', $equipos)->get();
                }
                return [];
            },
            'NotificacionesUsuario' => function () {
                if (Auth::check()) {
                    return Notificacion::where('user_id', Auth::id())->get();
                }
                return [];
            },
        ]);
    }
}
