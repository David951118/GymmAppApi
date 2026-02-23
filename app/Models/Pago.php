<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'id_pago';

    protected $fillable = [
        'plan_id',
        'fecha_cobro',
        'estado',
        'monto',
        'metodo_pago',
        'referencia',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cobro' => 'datetime',
            'monto' => 'decimal:2',
        ];
    }

    // Relationships
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id_plan');
    }
}
