<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aplicacion extends Model
{
    use HasFactory;

    protected $table = 'aplicacion';

    protected $fillable = [
        'nombre_aplicacion',
        'link_icono',
        'gama_colores',
        'logo',
        'descripcion'
    ];
}
