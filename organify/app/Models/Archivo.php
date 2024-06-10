<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use HasFactory;

    protected $table = 'archivo';

    protected $fillable = [
        'nombre',
        'tarea_id'
    ];

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }
}