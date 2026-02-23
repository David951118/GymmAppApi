<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\CentroDeportivo;
use App\Models\ActividadDeportiva;
use App\Models\Rutina;
use App\Models\Ejercicio;

class GlobalSearchController extends Controller
{
    /**
     * Search across multiple entities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return $this->errorResponse('Search query "q" is required.', 'Bad Request', 400);
        }

        $results = [
            'usuarios' => Usuario::where('nombre', 'ILIKE', "%{$query}%")
                ->orWhere('apellidos', 'ILIKE', "%{$query}%")
                ->orWhere('correo', 'ILIKE', "%{$query}%")
                ->orWhere('cedula', 'ILIKE', "%{$query}%")
                ->limit(5)->get(),

            'centros' => CentroDeportivo::where('nombre', 'ILIKE', "%{$query}%")
                ->orWhere('ubicacion', 'ILIKE', "%{$query}%")
                ->limit(5)->get(),

            'actividades' => ActividadDeportiva::where('tipo', 'ILIKE', "%{$query}%")
                ->limit(5)->get(),

            'rutinas' => Rutina::where('nombre', 'ILIKE', "%{$query}%")
                ->limit(5)->get(),

            'ejercicios' => Ejercicio::where('nombre', 'ILIKE', "%{$query}%")
                ->orWhere('tipo', 'ILIKE', "%{$query}%")
                ->limit(5)->get(),
        ];

        return $this->successResponse($results, 'Búsqueda completada.');
    }
}
