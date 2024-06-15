<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validación de datos de entrada
        $request->validate([
            'nombre' => 'required|string|max:30',
            'apellidos' => 'required|string|max:80',
            'usuario' => 'required|string|max:50|unique:'.User::class,
            'confirmado' => 'required|boolean',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'foto' => 'nullable|mimes:jpeg,png,gif,webp,svg,bmp,tiff,ico|max:2048',
        ]);

        // Procesamiento de la imagen de perfil (si se proporciona)
        if($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoName = time() . '.' . $foto->extension();
            $foto->move(public_path('archivos'), $fotoName);  
        } else {
            $fotoName = 'defaultUser.jpg';
        }

        // Creación del usuario en la base de datos
        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'usuario' => $request->usuario,
            'foto' => $fotoName,
            'confirmado' => $request->input('confirmado', false),
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Disparo del evento de registro
        event(new Registered($user));

        // Autenticación del usuario recién registrado
        Auth::login($user);

        // Redirección después del registro
        return redirect(route('tablon', absolute: false));
    }
}

