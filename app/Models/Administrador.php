<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrador extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'administradores';
    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Check if this admin is a Super Admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }
    protected $primaryKey = 'id_administrador';

    protected $fillable = [
        'usuario_id',
        'centro_id',
        'nivel',
        'is_super_admin',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }

    public function centro()
    {
        return $this->belongsTo(CentroDeportivo::class, 'centro_id', 'id_centro');
    }

    public function maquinas()
    {
        return $this->hasMany(Maquina::class, 'administrador_id', 'id_administrador');
    }
}
