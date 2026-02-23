<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ejercicio extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'ejercicios';
    protected $primaryKey = 'id_ejercicio';

    protected $fillable = [
        'tipo',
        'guia',
    ];

    // Relationships
    public function rutinas()
    {
        return $this->belongsToMany(
            Rutina::class,
            'rutina_contiene_ejercicio',
            'ejercicio_id',
            'rutina_id'
        )->withPivot('repeticiones', 'series')
            ->withTimestamps();
    }

    public function maquinas()
    {
        return $this->hasMany(Maquina::class, 'ejercicio_id', 'id_ejercicio');
    }
}
