<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactoEmergencia extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $table = 'contacto_emergencia';
    protected $primaryKey = 'id_contacto';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'celular',
    ];

    // Relationships
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id_usuario');
    }
}
