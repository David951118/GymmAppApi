<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActividadDeportiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActividadDeportivaController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);

        $query = ActividadDeportiva::with(['centro', 'profesional.usuario', 'afiliados'])
            ->search($request->all());

        if ($request->has('centro_id')) {
            $query->where('centro_id', $request->centro_id);
        }

        if ($request->has('profesional_id')) {
            $query->where('profesional_id', $request->profesional_id);
        }

        $actividades = $query->orderBy('fecha', 'desc')->paginate($limit);
        return $this->successResponse($actividades, 'Actividades deportivas obtenidas exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'centro_id' => 'required|exists:centro_deportivo,id_centro',
            'profesional_id' => 'nullable|exists:profesionales,id_profesional',
            'fecha' => 'required|date',
            'tipo' => 'nullable|string|max:100',
            'duracion' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $actividad = ActividadDeportiva::create($request->all());
            return $this->successResponse($actividad->load(['centro', 'profesional.usuario']), 'Actividad deportiva creada exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear la actividad deportiva.', 500);
        }
    }

    public function show($id)
    {
        try {
            $actividad = ActividadDeportiva::with(['centro', 'profesional.usuario', 'afiliados.usuario'])->findOrFail($id);
            return $this->successResponse($actividad, 'Actividad deportiva obtenida exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Actividad deportiva no encontrada.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|date',
            'tipo' => 'sometimes|string|max:100',
            'duracion' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $actividad = ActividadDeportiva::findOrFail($id);
            $actividad->update($request->all());
            return $this->successResponse($actividad->load(['centro', 'profesional.usuario']), 'Actividad deportiva actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar la actividad deportiva.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $actividad = ActividadDeportiva::findOrFail($id);
            $actividad->delete();
            return $this->successResponse(null, 'Actividad deportiva eliminada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar la actividad deportiva.', 500);
        }
    }

    /**
     * Register afiliado attendance to activity
     */
    public function registrarAsistencia(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'afiliado_id' => 'required|exists:afiliados,id_afiliado',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $actividad = ActividadDeportiva::findOrFail($id);

            $actividad->afiliados()->attach($request->afiliado_id, [
                'fecha_asistencia' => now(),
                'fecha_inscripcion' => now(),
            ]);

            return $this->successResponse(null, 'Asistencia registrada exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al registrar la asistencia.', 500);
        }
    }
}
