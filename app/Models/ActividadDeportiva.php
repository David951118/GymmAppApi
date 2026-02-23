<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadDeportiva extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'actividad_deportiva';
    protected $primaryKey = 'id_actividad';

    protected $fillable = [
        'centro_id',
        'profesional_id',
        'fecha',
        'tipo',
        'duracion',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
        ];
    }

    // Relationships
    public function centro()
    {
        return $this->belongsTo(CentroDeportivo::class, 'centro_id', 'id_centro');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'profesional_id', 'id_profesional');
    }

    public function afiliados()
    {
        return $this->belongsToMany(
            Afiliado::class,
            'asiste_afiliado_actividad',
            'actividad_id',
            'afiliado_id'
        )->withPivot('fecha_asistencia', 'fecha_inscripcion')
            ->withTimestamps();
    }
}
