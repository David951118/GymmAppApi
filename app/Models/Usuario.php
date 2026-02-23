<?php

namespace App\Models;

use App\Traits\Searchable;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'cedula',
        'correo',
        'celular',
        'genero',
        'fecha_nacimiento',
        'contrasena',
        'estado_usuario',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'fecha_nacimiento' => 'date',
        ];
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Get the email for email verification.
     */
    public function getEmailForVerification()
    {
        return $this->correo;
    }



    // Relationships
    public function contactosEmergencia()
    {
        return $this->hasMany(ContactoEmergencia::class, 'usuario_id', 'id_usuario');
    }

    public function afiliado()
    {
        return $this->hasOne(Afiliado::class, 'usuario_id', 'id_usuario');
    }

    public function profesional()
    {
        return $this->hasOne(Profesional::class, 'usuario_id', 'id_usuario');
    }

    public function trabajador()
    {
        return $this->hasOne(Trabajador::class, 'usuario_id', 'id_usuario');
    }

    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'usuario_id', 'id_usuario');
    }

    // Helper methods to check roles
    public function esAfiliado()
    {
        return $this->afiliado()->exists();
    }

    public function esProfesional()
    {
        return $this->profesional()->exists();
    }

    public function esTrabajador()
    {
        return $this->trabajador()->exists();
    }

    /**
     * Check if user is an Administrador
     */
    public function esAdministrador(): bool
    {
        return $this->administrador !== null;
    }

    /**
     * Check if user is a Super Admin
     */
    public function esSuperAdmin(): bool
    {
        return $this->administrador && $this->administrador->is_super_admin === true;
    }

    public function getRoles()
    {
        $roles = [];
        if ($this->esAfiliado())
            $roles[] = 'afiliado';
        if ($this->esProfesional())
            $roles[] = 'profesional';
        if ($this->esTrabajador())
            $roles[] = 'trabajador';
        if ($this->esAdministrador())
            $roles[] = 'administrador';
        return $roles;
    }
}
