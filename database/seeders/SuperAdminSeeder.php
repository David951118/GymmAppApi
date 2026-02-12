<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Administrador;
use App\Models\ContactoEmergencia;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un Super Admin
        $existingSuperAdmin = Administrador::where('is_super_admin', true)->first();

        if ($existingSuperAdmin) {
            $this->command->info('Super Admin ya existe. Saltando creación...');
            return;
        }

        $this->command->info('Creando Super Admin...');

        // 1. Crear usuario Super Admin
        $usuario = Usuario::create([
            'nombre' => 'Super',
            'apellidos' => 'Administrador',
            'correo' => 'superadmin@gymapp.com',
            'cedula' => 'SUPER001',
            'celular' => '+573001234567',
            'genero' => 'Otro',
            'contrasena' => Hash::make('SuperAdmin123!'),
            'estado_usuario' => 'activo',
            'email_verified_at' => now(), // Ya verificado desde el inicio
        ]);

        // 2. Crear contacto de emergencia
        ContactoEmergencia::create([
            'usuario_id' => $usuario->id_usuario,
            'nombre' => 'Contacto Sistema',
            'celular' => '+573001234568',
        ]);

        // 3. Crear rol de Administrador con flag de Super Admin
        Administrador::create([
            'usuario_id' => $usuario->id_usuario,
            'centro_id' => null, // Super Admin no está atado a un centro específico
            'nivel' => 'Super Administrador',
            'is_super_admin' => true,
        ]);

        $this->command->info('Super Admin creado exitosamente!');
        $this->command->warn('Email: superadmin@gymapp.com');
        $this->command->warn('Password: SuperAdmin123!');
    }
}
