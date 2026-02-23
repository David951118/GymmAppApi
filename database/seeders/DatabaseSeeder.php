<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\CentroDeportivo;
use App\Models\Administrador;
use App\Models\Profesional;
use App\Models\Afiliado;
use App\Models\ActividadDeportiva;
use App\Models\Ejercicio;
use App\Models\ContactoEmergencia;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Super Admin
        $superAdminUser = Usuario::create([
            'nombre' => 'Super',
            'apellidos' => 'Admin',
            'correo' => 'superadmin@gym.com',
            'contrasena' => Hash::make('password123'),
            'celular' => '0000000000',
            'estado_usuario' => 'activo'
        ]);

        Administrador::create([
            'usuario_id' => $superAdminUser->id_usuario,
            'nivel' => 'Super Admin',
            'is_super_admin' => true,
        ]);

        // 2. Crear Centro Deportivo
        $centro = CentroDeportivo::create([
            'nombre' => 'Gym Central',
            'ubicacion' => 'Av. Principal 123',
            'horario' => '06:00 - 22:00',
        ]);

        // 3. Crear Admin Normal
        $adminUser = Usuario::create([
            'nombre' => 'Admin',
            'apellidos' => 'Sucursal',
            'correo' => 'admin@gym.com',
            'contrasena' => Hash::make('password123'),
            'celular' => '1111111111',
            'estado_usuario' => 'activo'
        ]);

        ContactoEmergencia::create(['usuario_id' => $adminUser->id_usuario, 'nombre' => 'Ref', 'celular' => '999']);

        Administrador::create([
            'usuario_id' => $adminUser->id_usuario,
            'centro_id' => $centro->id_centro,
            'nivel' => 'Administrador',
            'is_super_admin' => false,
        ]);

        // 4. Crear Profesional
        $profUser = Usuario::create([
            'nombre' => 'Entrenador',
            'apellidos' => 'Pro',
            'correo' => 'pro@gym.com',
            'contrasena' => Hash::make('password123'),
            'celular' => '2222222222',
            'estado_usuario' => 'activo'
        ]);

        ContactoEmergencia::create(['usuario_id' => $profUser->id_usuario, 'nombre' => 'Ref', 'celular' => '999']);

        $profesional = Profesional::create([
            'usuario_id' => $profUser->id_usuario,
            'centro_id' => $centro->id_centro,
            'especialidad' => 'Crossfit',
        ]);

        // 5. Crear Afiliado
        $afiliadoUser = Usuario::create([
            'nombre' => 'Juan',
            'apellidos' => 'Perez',
            'correo' => 'afiliado@gym.com',
            'contrasena' => Hash::make('password123'),
            'celular' => '3333333333',
            'estado_usuario' => 'activo'
        ]);

        ContactoEmergencia::create(['usuario_id' => $afiliadoUser->id_usuario, 'nombre' => 'Ref', 'celular' => '999']);

        Afiliado::create([
            'usuario_id' => $afiliadoUser->id_usuario,
            'centro_id' => $centro->id_centro,
        ]);

        $this->command->info('Base database seeded successfully!');
    }
}
