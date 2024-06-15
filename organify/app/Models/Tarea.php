<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarea extends Model
{
    use HasFactory;

    protected $table = 'tareas';

    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_ini',
        'fecha_fin',
        'estado',
        'prioridad',
        'imagen',
        'archivos',
        'aceptada',
        'tipo',
        'asignada',
        'user_id',
        'equipo_id',
        'color' 
    ];

    protected $casts = [
        'archivos' => 'array', 
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }
}
