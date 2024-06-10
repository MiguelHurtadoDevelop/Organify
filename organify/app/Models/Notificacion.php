<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificacion';

    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'user_id',
        'data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}