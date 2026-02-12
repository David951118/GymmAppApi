<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Afiliado extends Model
{
    use HasFactory;

    protected $table = 'afiliados';
    protected $primaryKey = 'id_afiliado';

    protected $fillable = [
        'usuario_id',
        'centro_id', // Centro deportivo inicial (obligatorio)
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
        ];
    }

    // Relationships
    /**
     * Get the Usuario that owns the Afiliado
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }

    /**
     * Get the Centro Deportivo inicial del Afiliado
     */
    public function centroInicial()
    {
        return $this->belongsTo(CentroDeportivo::class, 'centro_id', 'id_centro');
    }

    public function antropometrias()
    {
        return $this->hasMany(Antropometria::class, 'afiliado_id', 'id_afiliado');
    }

    public function planes()
    {
        return $this->hasMany(Plan::class, 'afiliado_id', 'id_afiliado');
    }

    public function rutinas()
    {
        return $this->hasMany(Rutina::class, 'afiliado_id', 'id_afiliado');
    }

    public function actividades()
    {
        return $this->belongsToMany(
            ActividadDeportiva::class,
            'asiste_afiliado_actividad',
            'afiliado_id',
            'actividad_id'
        )->withPivot('fecha_asistencia', 'fecha_inscripcion')
            ->withTimestamps();
    }

    public function centros()
    {
        return $this->belongsToMany(
            CentroDeportivo::class,
            'ingresa_afiliado_centro',
            'afiliado_id',
            'centro_id'
        )->withPivot('fecha_ingreso')
            ->withTimestamps();
    }
}
