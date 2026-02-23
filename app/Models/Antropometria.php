<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antropometria extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'antropometrias';
    protected $primaryKey = 'id_antropometria';

    protected $fillable = [
        'afiliado_id',
        'peso',
        'altura_cm',
        'imc',
        'grasa_corporal',
        'fecha_medicion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_medicion' => 'datetime',
            'peso' => 'decimal:2',
            'altura_cm' => 'decimal:2',
            'imc' => 'decimal:2',
            'grasa_corporal' => 'decimal:2',
        ];
    }

    // Relationships
    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class, 'afiliado_id', 'id_afiliado');
    }
}
