<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maquina extends Model
{
    use HasFactory;

    protected $table = 'maquinas';
    protected $primaryKey = 'id_maquina';

    protected $fillable = [
        'centro_id',
        'administrador_id',
        'ejercicio_id',
        'nombre',
        'estado',
        'ubicacion',
    ];

    // Relationships
    public function centro()
    {
        return $this->belongsTo(CentroDeportivo::class, 'centro_id', 'id_centro');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'administrador_id', 'id_administrador');
    }

    public function ejercicio()
    {
        return $this->belongsTo(Ejercicio::class, 'ejercicio_id', 'id_ejercicio');
    }
}
