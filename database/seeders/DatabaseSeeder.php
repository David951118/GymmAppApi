<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\CentroDeportivo;
use App\Models\Administrador;
use App\Models\Profesional;
use App\Models\Afiliado;
use App\Models\Trabajador;
use App\Models\ActividadDeportiva;
use App\Models\Ejercicio;
use App\Models\ContactoEmergencia;
use App\Models\Aplicacion;
use App\Models\Maquina;
use App\Models\Plan;
use App\Models\Pago;
use App\Models\Rutina;
use App\Models\Antropometria;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 0. App Config ──────────────────────────────────────────────
        Aplicacion::create([
            'nombre_aplicacion' => 'GymmApp Pro',
            'link_icono' => 'https://cdn-icons-png.flaticon.com/512/2936/2936886.png',
            'gama_colores' => '#1a1a2e,#16213e,#0f3460,#e94560',
            'logo' => 'https://example.com/logo.png',
            'descripcion' => 'Sistema integral de gestión de gimnasios.',
        ]);

        // ─── 1. Centros ──────────────────────────────────────────────────
        $c1 = CentroDeportivo::create(['nombre' => 'Gym Central Norte', 'ubicacion' => 'Av. 7 de Agosto #15-30, Bogotá', 'horario' => 'Lun-Sab 06:00-22:00']);
        $c2 = CentroDeportivo::create(['nombre' => 'Gym Central Sur', 'ubicacion' => 'Calle 72 Sur #89-10, Bogotá', 'horario' => 'Lun-Vie 05:30-23:00']);

        // Helper to create a user + emergency contact
        $mkUser = function ($data) {
            $u = Usuario::create(array_merge($data, ['contrasena' => Hash::make('password123'), 'estado_usuario' => 'activo']));
            ContactoEmergencia::create(['usuario_id' => $u->id_usuario, 'nombre' => 'Contacto Ref', 'celular' => '3000000000']);
            return $u;
        };

        // ─── 2. Super Admin ───────────────────────────────────────────────
        $sa = $mkUser(['nombre' => 'Carlos', 'apellidos' => 'Ramirez', 'cedula' => '1000000001', 'correo' => 'superadmin@gym.com', 'celular' => '3001110001', 'genero' => 'M']);
        Administrador::create(['usuario_id' => $sa->id_usuario, 'nivel' => 'Super Admin', 'is_super_admin' => true]);

        // ─── 3. Admins ────────────────────────────────────────────────────
        $a1 = $mkUser(['nombre' => 'Laura', 'apellidos' => 'Gomez', 'cedula' => '1000000002', 'correo' => 'admin1@gym.com', 'celular' => '3002220002', 'genero' => 'F']);
        Administrador::create(['usuario_id' => $a1->id_usuario, 'centro_id' => $c1->id_centro, 'nivel' => 'Administrador', 'is_super_admin' => false]);

        $a2 = $mkUser(['nombre' => 'Miguel', 'apellidos' => 'Torres', 'cedula' => '1000000003', 'correo' => 'admin2@gym.com', 'celular' => '3003330003', 'genero' => 'M']);
        Administrador::create(['usuario_id' => $a2->id_usuario, 'centro_id' => $c2->id_centro, 'nivel' => 'Administrador', 'is_super_admin' => false]);

        // ─── 4. Profesionales ─────────────────────────────────────────────
        $p1u = $mkUser(['nombre' => 'Diego', 'apellidos' => 'Vargas', 'cedula' => '1000000004', 'correo' => 'pro1@gym.com', 'celular' => '3004440004', 'genero' => 'M']);
        $pro1 = Profesional::create(['usuario_id' => $p1u->id_usuario, 'centro_id' => $c1->id_centro, 'especialidad' => 'Crossfit y Funcional']);

        $p2u = $mkUser(['nombre' => 'Camila', 'apellidos' => 'Rios', 'cedula' => '1000000005', 'correo' => 'pro2@gym.com', 'celular' => '3005550005', 'genero' => 'F']);
        $pro2 = Profesional::create(['usuario_id' => $p2u->id_usuario, 'centro_id' => $c2->id_centro, 'especialidad' => 'Pilates y Yoga']);

        // ─── 5. Trabajadores ──────────────────────────────────────────────
        $t1u = $mkUser(['nombre' => 'Andres', 'apellidos' => 'Mora', 'cedula' => '1000000006', 'correo' => 'trab1@gym.com', 'celular' => '3006660006', 'genero' => 'M']);
        Trabajador::create(['usuario_id' => $t1u->id_usuario, 'centro_id' => $c1->id_centro, 'puesto' => 'Recepcionista']);

        $t2u = $mkUser(['nombre' => 'Valentina', 'apellidos' => 'Cruz', 'cedula' => '1000000007', 'correo' => 'trab2@gym.com', 'celular' => '3007770007', 'genero' => 'F']);
        Trabajador::create(['usuario_id' => $t2u->id_usuario, 'centro_id' => $c2->id_centro, 'puesto' => 'Limpieza']);

        // ─── 6. Afiliados ─────────────────────────────────────────────────
        $af1u = $mkUser(['nombre' => 'Juan', 'apellidos' => 'Perez', 'cedula' => '1000000008', 'correo' => 'afiliado1@gym.com', 'celular' => '3008880008', 'genero' => 'M']);
        $afil1 = Afiliado::create(['usuario_id' => $af1u->id_usuario, 'centro_id' => $c1->id_centro]);

        $af2u = $mkUser(['nombre' => 'Sara', 'apellidos' => 'Lopez', 'cedula' => '1000000009', 'correo' => 'afiliado2@gym.com', 'celular' => '3009990009', 'genero' => 'F']);
        $afil2 = Afiliado::create(['usuario_id' => $af2u->id_usuario, 'centro_id' => $c2->id_centro]);

        // ─── 7. Ejercicios ────────────────────────────────────────────────
        $ej1 = Ejercicio::create(['tipo' => 'Cardio', 'guia' => 'Correr a 8km/h durante 30 minutos en cinta.']);
        $ej2 = Ejercicio::create(['tipo' => 'Fuerza', 'guia' => 'Press de banca: 4 series de 10 reps con 50kg.']);
        $ej3 = Ejercicio::create(['tipo' => 'Flexibilidad', 'guia' => 'Estiramiento de isquiotibiales 30 seg cada lado.']);
        $ej4 = Ejercicio::create(['tipo' => 'Funcional', 'guia' => 'Sentadillas con mancuernas: 3 series de 15 reps.']);

        // --- 8. Maquinas (schema: nombre, estado, ubicacion, ejercicio_id, centro_id) ---
        Maquina::create(['centro_id' => $c1->id_centro, 'nombre' => 'Cinta Life Fitness 95Ti',  'estado' => 'activo',       'ubicacion' => 'Sala Cardio A',  'ejercicio_id' => $ej1->id_ejercicio]);
        Maquina::create(['centro_id' => $c1->id_centro, 'nombre' => 'Maquina Smith Technogym',  'estado' => 'activo',       'ubicacion' => 'Sala Fuerza 1',  'ejercicio_id' => $ej2->id_ejercicio]);
        Maquina::create(['centro_id' => $c2->id_centro, 'nombre' => 'Eliptica Precor EFX 885',  'estado' => 'activo',       'ubicacion' => 'Sala Cardio B',  'ejercicio_id' => $ej1->id_ejercicio]);
        Maquina::create(['centro_id' => $c2->id_centro, 'nombre' => 'Remo Matrix G1',           'estado' => 'mantenimiento','ubicacion' => 'Sala Cardio B',  'ejercicio_id' => $ej1->id_ejercicio]);

        // ─── 9. Actividades ───────────────────────────────────────────────
        ActividadDeportiva::create(['centro_id' => $c1->id_centro, 'profesional_id' => $pro1->id_profesional, 'fecha' => now()->addDays(2)->setTime(7, 0), 'tipo' => 'Crossfit', 'duracion' => 60]);
        ActividadDeportiva::create(['centro_id' => $c1->id_centro, 'profesional_id' => $pro1->id_profesional, 'fecha' => now()->addDays(4)->setTime(9, 0), 'tipo' => 'Funcional', 'duracion' => 45]);
        ActividadDeportiva::create(['centro_id' => $c2->id_centro, 'profesional_id' => $pro2->id_profesional, 'fecha' => now()->addDays(1)->setTime(8, 0), 'tipo' => 'Yoga', 'duracion' => 75]);
        ActividadDeportiva::create(['centro_id' => $c2->id_centro, 'profesional_id' => $pro2->id_profesional, 'fecha' => now()->addDays(3)->setTime(18, 0), 'tipo' => 'Pilates', 'duracion' => 60]);

        // ─── 10. Rutinas ───────────────────────────────────────────────────
        $rut1 = Rutina::create(['afiliado_id' => $afil1->id_afiliado, 'profesional_id' => $pro1->id_profesional, 'nombre' => 'Rutina Fuerza Avanzada', 'sesiones_totales' => 24, 'sesiones_restantes' => 20]);
        $rut1->ejercicios()->attach($ej2->id_ejercicio, ['repeticiones' => 10, 'series' => 4]);
        $rut1->ejercicios()->attach($ej4->id_ejercicio, ['repeticiones' => 15, 'series' => 3]);

        $rut2 = Rutina::create(['afiliado_id' => $afil2->id_afiliado, 'profesional_id' => $pro2->id_profesional, 'nombre' => 'Rutina Flexibilidad Yoga', 'sesiones_totales' => 12, 'sesiones_restantes' => 12]);
        $rut2->ejercicios()->attach($ej3->id_ejercicio, ['repeticiones' => 5, 'series' => 3]);

        // ─── 11. Planes (schema: tipo ENUM, valor, fecha_inicio, fecha_corte, fecha_fin) ─
        $plan1 = Plan::create(['afiliado_id' => $afil1->id_afiliado, 'tipo' => 'Mensual', 'valor' => 80000, 'fecha_inicio' => now()->toDateString(), 'fecha_fin' => now()->addDays(30)->toDateString()]);
        $plan2 = Plan::create(['afiliado_id' => $afil2->id_afiliado, 'tipo' => 'Semestral', 'valor' => 420000, 'fecha_inicio' => now()->toDateString(), 'fecha_fin' => now()->addDays(180)->toDateString()]);

        // ─── 12. Pagos (schema: fecha_cobro, estado, plan_id, monto, metodo_pago) ─
        Pago::create(['plan_id' => $plan1->id_plan, 'monto' => 80000, 'fecha_cobro' => now(), 'estado' => 'completado', 'metodo_pago' => 'Tarjeta Crédito']);
        Pago::create(['plan_id' => $plan2->id_plan, 'monto' => 420000, 'fecha_cobro' => now(), 'estado' => 'completado', 'metodo_pago' => 'Transferencia']);

        // ─── 13. Antropometrías (schema: altura_cm, peso, imc, grasa_corporal) ────────
        Antropometria::create(['afiliado_id' => $afil1->id_afiliado, 'peso' => 78.5, 'altura_cm' => 175, 'imc' => 25.6, 'grasa_corporal' => 18.2]);
        Antropometria::create(['afiliado_id' => $afil2->id_afiliado, 'peso' => 62.0, 'altura_cm' => 165, 'imc' => 22.8, 'grasa_corporal' => 21.4]);

        $this->command->info('✅ Base de datos poblada con datos completos de prueba.');
    }
}
