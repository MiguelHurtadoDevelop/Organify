<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\NotificacionController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});



Route::delete('tarea/{tarea}', [TareaController::class, 'delete'])->middleware((['auth', 'verified']))->name('tarea.delete');

Route::post('tareaPersonal', [TareaController::class, 'createPersonal'])->middleware((['auth', 'verified']))->name('tareaPersonal.create');

Route::post('tareaEquipo/{tarea}', [TareaController::class, 'updateEquipo'])->middleware((['auth', 'verified']))->name('tareaEquipo.update');

Route::post('tareaEquipo', [TareaController::class, 'createEquipo'])->middleware((['auth', 'verified']))->name('tareaEquipo.create');

Route::post('tareaPersonal/{tarea}', [TareaController::class, 'updatePersonal'])->middleware((['auth', 'verified']))->name('tareaPersonal.update');

Route::post('tarea/cambiarFecha/{tarea}', [TareaController::class, 'cambiarFecha'])->middleware((['auth', 'verified']))->name('tarea.cambiarFecha');

Route::post('tarea/asignar', [TareaController::class, 'asignarTarea'])->middleware((['auth', 'verified']))->name('tarea.asignar');

Route::post('equipo', [EquipoController::class, 'createEquipo'])->middleware((['auth', 'verified']))->name('equipo.create');

Route::get('equipos', [EquipoController::class, 'getEquipos'])->middleware((['auth', 'verified']))->name('equipos');

Route::get('equipo/{equipo}', [EquipoController::class, 'equipoById'])->middleware((['auth', 'verified']))->name('equipo.show');

Route::get('equipo/{equipo}/tablon', [TareaController::class, 'tareasTablonEquipo'])->middleware((['auth', 'verified']))->name('equipo.tablon');

Route::post('equipo/solicitarUnirse', [EquipoController::class, 'solicitarUnirse'])->middleware((['auth', 'verified']))->name('equipo.solicitarUnirse');

Route::get('equipo/aceptarSolicitud/{user}/{equipo_id}/{notificacion_id}', [EquipoController::class, 'aceptarSolicitud'])->middleware((['auth', 'verified']))->name('equipo.aceptarSolicitud');

Route::get('equipo/rechazarSolicitud/{user}/{equipo_id}/{notificacion_id}', [EquipoController::class, 'rechazarSolicitud'])->middleware((['auth', 'verified']))->name('equipo.rechazarSolicitud');

Route::post('equipo/join', [EquipoController::class, 'joinEquipo'])->middleware((['auth', 'verified']))->name('equipo.join');

Route::post('equipo/leave', [EquipoController::class, 'leaveEquipo'])->middleware((['auth', 'verified']))->name('equipo.leave');

Route::post('equipo/expulsar/{equipo_id}', [EquipoController::class, 'expulsarMiembro'])->middleware((['auth', 'verified']))->name('equipo.expulsar');

Route::post('/equipo/update/{equipo_id}', [EquipoController::class, 'updateEquipo'])->middleware((['auth', 'verified']))->name('equipo.update');

Route::delete('/equipo/delete/{equipo_id}', [EquipoController::class, 'deleteEquipo'])->middleware((['auth', 'verified']))->name('equipo.delete');

Route::get('/calendario', [TareaController::class, 'tareasCalendario'])->middleware(['auth', 'verified'])->name('calendario');

Route::get('/tablon',[TareaController::class, 'tareasTablon'])->middleware((['auth', 'verified']))->name('tablon');

Route::get('/tarea/aceptar/{tarea}/{notificacion_id}', [TareaController::class, 'aceptarTarea'])->middleware((['auth', 'verified']))->name('tarea.aceptar');

Route::get('/tarea/rechazar/{tarea}/{notificacion_id}', [TareaController::class, 'rechazarTarea'])->middleware((['auth', 'verified']))->name('tarea.rechazar');

Route::post('/tarea/estado/{id}', [TareaController::class, 'cambiarEstado'])->middleware(['auth', 'verified'])->name('tarea.estado');


Route::post('notificacion/eliminar', [NotificacionController::class, 'deleteNotificacion'])->middleware((['auth', 'verified']))->name('notificacion.eliminar');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::fallback(function () {
    return Inertia::render('Errors/404'); // Asegúrate de tener el componente Vue configurado en 'Errors/404'
});

require __DIR__.'/auth.php';
