<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rutina extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'rutinas';
    protected $primaryKey = 'id_rutina';

    protected $fillable = [
        'afiliado_id',
        'profesional_id',
        'nombre',
        'sesiones_totales',
        'sesiones_restantes',
    ];

    // Relationships
    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class, 'afiliado_id', 'id_afiliado');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'profesional_id', 'id_profesional');
    }

    public function ejercicios()
    {
        return $this->belongsToMany(
            Ejercicio::class,
            'rutina_contiene_ejercicio',
            'rutina_id',
            'ejercicio_id'
        )->withPivot('repeticiones', 'series')
            ->withTimestamps();
    }
}
