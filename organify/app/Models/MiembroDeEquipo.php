<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MiembroDeEquipo extends Model
{
    use HasFactory;

    protected $table = 'miembros_de_equipo';

    protected $fillable = [
        'user_id',
        'equipo_id',
        'rol',
        'aceptado'
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
