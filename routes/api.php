<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AfiliadoController;
use App\Http\Controllers\Api\ProfesionalController;
use App\Http\Controllers\Api\TrabajadorController;
use App\Http\Controllers\Api\AdministradorController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\AntropometriaController;
use App\Http\Controllers\Api\RutinaController;
use App\Http\Controllers\Api\ActividadDeportivaController;
use App\Http\Controllers\Api\EjercicioController;
use App\Http\Controllers\Api\CentroDeportivoController;
use App\Http\Controllers\Api\MaquinaController;
use App\Http\Controllers\Api\GlobalSearchController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\AplicacionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/health', HealthCheckController::class);
Route::get('/centros/public', [CentroDeportivoController::class, 'publicIndex']);
Route::get('/aplicacion', [AplicacionController::class, 'index']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/search', GlobalSearchController::class);

    // Routes requiring verified email (REMOVED verification middleware for simplicity)
    Route::middleware([])->group(function () {

        // ADMIN ROUTES - Creation of internal staff
        Route::middleware(['role:administrador'])->group(function () {
            // Gestión de Configuración de Aplicación (Solo Super Admin)
            Route::put('/admin/aplicacion', [AplicacionController::class, 'update'])->middleware('role:superadmin');

            // Gestión de Administradores (Solo Super Admin puede crear, validado en Controller/Middleware)
            Route::apiResource('admin/administradores', AdministradorController::class);

            // Gestión de Profesionales
            Route::post('/admin/profesionales', [ProfesionalController::class, 'store']);
            Route::put('/admin/profesionales/{id}', [ProfesionalController::class, 'update']);
            Route::delete('/admin/profesionales/{id}', [ProfesionalController::class, 'destroy']);

            // Gestión de Trabajadores
            Route::apiResource('admin/trabajadores', TrabajadorController::class);
        });

        // Afiliado routes - Accessible by afiliados, professionals and admins
        Route::middleware(['role:afiliado,profesional,administrador'])->group(function () {
            // Todos pueden ver y modificar su propio afiliado, pero la creación es solo para staff
            Route::apiResource('afiliados', AfiliadoController::class)->except(['store']);
        });

        // Store de afiliado para staff (crear perfil)
        Route::middleware(['role:administrador,profesional,trabajador'])->group(function () {
            Route::post('afiliados', [AfiliadoController::class, 'store']);
        });

        // Profesional routes - Read access
        Route::get('/profesionales', [ProfesionalController::class, 'index']);
        Route::get('/profesionales/{id}', [ProfesionalController::class, 'show']);

        // Plan routes - Afiliados and admins
        Route::middleware(['role:afiliado,administrador'])->group(function () {
            Route::apiResource('planes', PlanController::class);
        });

        // Pago routes - Afiliados and admins
        Route::middleware(['role:afiliado,administrador'])->group(function () {
            Route::apiResource('pagos', PagoController::class);
        });

        // Antropometria routes - Afiliados, profesionales, and admins
        Route::middleware(['role:afiliado,profesional,administrador'])->group(function () {
            Route::apiResource('antropometrias', AntropometriaController::class);
        });

        // Rutina routes - Afiliados, profesionales, and admins
        Route::middleware(['role:afiliado,profesional,administrador'])->group(function () {
            Route::apiResource('rutinas', RutinaController::class);
        });

        // Ejercicio routes - All authenticated users can read, only professionals and admins can write
        Route::prefix('ejercicios')->group(function () {
            Route::get('/', [EjercicioController::class, 'index']);
            Route::get('/{id}', [EjercicioController::class, 'show']);

            Route::middleware(['role:profesional,administrador'])->group(function () {
                Route::post('/', [EjercicioController::class, 'store']);
                Route::put('/{id}', [EjercicioController::class, 'update']);
                Route::delete('/{id}', [EjercicioController::class, 'destroy']);
            });
        });

        // Actividad Deportiva routes
        Route::prefix('actividades')->group(function () {
            Route::get('/', [ActividadDeportivaController::class, 'index']);
            Route::get('/{id}', [ActividadDeportivaController::class, 'show']);

            // All logged users can register attendance
            Route::post('/{id}/asistencia', [ActividadDeportivaController::class, 'registrarAsistencia']);

            // Only professionals and admins can create/edit/delete activities
            Route::middleware(['role:profesional,administrador'])->group(function () {
                Route::post('/', [ActividadDeportivaController::class, 'store']);
                Route::put('/{id}', [ActividadDeportivaController::class, 'update']);
                Route::delete('/{id}', [ActividadDeportivaController::class, 'destroy']);
            });
        });

        // Centro Deportivo routes - Everyone can read, only admins can write
        Route::prefix('centros')->group(function () {
            Route::get('/', [CentroDeportivoController::class, 'index']);
            Route::get('/{id}', [CentroDeportivoController::class, 'show']);

            Route::middleware(['role:administrador'])->group(function () {
                Route::post('/', [CentroDeportivoController::class, 'store']);
                Route::put('/{id}', [CentroDeportivoController::class, 'update']);
                Route::delete('/{id}', [CentroDeportivoController::class, 'destroy']);
            });
        });

        // Maquina routes - All authenticated users can read, only professionals and admins can write
        Route::prefix('maquinas')->group(function () {
            Route::get('/', [MaquinaController::class, 'index']);
            Route::get('/{id}', [MaquinaController::class, 'show']);

            Route::middleware(['role:administrador,profesional'])->group(function () {
                Route::post('/', [MaquinaController::class, 'store']);
                Route::put('/{id}', [MaquinaController::class, 'update']);
                Route::patch('/{id}', [MaquinaController::class, 'update']);
                Route::delete('/{id}', [MaquinaController::class, 'destroy']);
            });
        });
    });
});
