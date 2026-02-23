<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';
    protected $primaryKey = 'id_plan';

    protected $fillable = [
        'afiliado_id',
        'tipo',
        'fecha_inicio',
        'fecha_corte',
        'fecha_fin',
        'valor',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_corte' => 'date',
            'fecha_fin' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    // Relationships
    public function afiliado()
    {
        return $this->belongsTo(Afiliado::class, 'afiliado_id', 'id_afiliado');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'plan_id', 'id_plan');
    }
}
