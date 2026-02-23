<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroDeportivo extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'centro_deportivo';
    protected $primaryKey = 'id_centro';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'horario',
    ];

    // Relationships
    public function profesionales()
    {
        return $this->hasMany(Profesional::class, 'centro_id', 'id_centro');
    }

    public function trabajadores()
    {
        return $this->hasMany(Trabajador::class, 'centro_id', 'id_centro');
    }

    public function administradores()
    {
        return $this->hasMany(Administrador::class, 'centro_id', 'id_centro');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadDeportiva::class, 'centro_id', 'id_centro');
    }

    public function maquinas()
    {
        return $this->hasMany(Maquina::class, 'centro_id', 'id_centro');
    }

    public function afiliados()
    {
        return $this->belongsToMany(
            Afiliado::class,
            'ingresa_afiliado_centro',
            'centro_id',
            'afiliado_id'
        )->withPivot('fecha_ingreso')
            ->withTimestamps();
    }
}
