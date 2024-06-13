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

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $equipos = MiembroDeEquipo::where('user_id', auth()->user()->id)->get();

        $equipos = $equipos->map(function ($equipo) {
            return $equipo->equipo;
        });

        \Log::info($equipos);
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'equipos' => $equipos,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    
    public function update(Request $request): RedirectResponse
    {
        \Log::info($request->all());
        $user = $request->user();
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
        ]);

        if($request->hasFile('foto')) {
            $request->validate([
                'foto' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $foto = $request->file('foto');
            $fotoName = time() . '.' . $foto->extension();
            $foto->move(public_path('archivos'), $fotoName);  

            if($user->foto != 'default.jpg') {
                unlink(public_path('archivos/' . $user->foto)); // Elimina la foto anterior si no es la foto por defecto
            }
        } else {
            $fotoName = $user->foto; // Conserva la foto existente si no se proporciona una nueva
        }

        $user->update([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'foto' => $fotoName,
        ]);

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
