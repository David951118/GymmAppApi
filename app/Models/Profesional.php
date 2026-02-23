<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesional extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'profesionales';
    protected $primaryKey = 'id_profesional';

    protected $fillable = [
        'usuario_id',
        'centro_id',
        'especialidad',
        'fecha_ingreso',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }

    public function centro()
    {
        return $this->belongsTo(CentroDeportivo::class, 'centro_id', 'id_centro');
    }

    public function actividades()
    {
        return $this->hasMany(ActividadDeportiva::class, 'profesional_id', 'id_profesional');
    }

    public function rutinas()
    {
        return $this->hasMany(Rutina::class, 'profesional_id', 'id_profesional');
    }
}
