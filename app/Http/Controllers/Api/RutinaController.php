<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rutina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RutinaController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $query = Rutina::with(['afiliado.usuario', 'profesional.usuario', 'ejercicios'])
            ->search($request->all());

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        if ($request->has('profesional_id')) {
            $query->where('profesional_id', $request->profesional_id);
        }

        $rutinas = $query->paginate($limit);
        return $this->successResponse($rutinas, 'Rutinas obtenidas exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'afiliado_id' => 'required|exists:afiliados,id_afiliado',
            'profesional_id' => 'nullable|exists:profesionales,id_profesional',
            'nombre' => 'nullable|string|max:200',
            'sesiones_totales' => 'nullable|integer|min:1',
            'sesiones_restantes' => 'nullable|integer|min:0',
            'ejercicios' => 'sometimes|array',
            'ejercicios.*.ejercicio_id' => 'required|exists:ejercicios,id_ejercicio',
            'ejercicios.*.series' => 'nullable|integer|min:1',
            'ejercicios.*.repeticiones' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $rutina = Rutina::create($request->except('ejercicios'));

            // Attach exercises if provided
            if ($request->has('ejercicios')) {
                foreach ($request->ejercicios as $ejercicio) {
                    $rutina->ejercicios()->attach($ejercicio['ejercicio_id'], [
                        'series' => $ejercicio['series'] ?? null,
                        'repeticiones' => $ejercicio['repeticiones'] ?? null,
                    ]);
                }
            }

            return $this->successResponse($rutina->load(['afiliado.usuario', 'profesional.usuario', 'ejercicios']), 'Rutina creada exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear la rutina.', 500);
        }
    }

    public function show($id)
    {
        try {
            $rutina = Rutina::with(['afiliado.usuario', 'profesional.usuario', 'ejercicios'])->findOrFail($id);
            return $this->successResponse($rutina, 'Rutina obtenida exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Rutina no encontrada.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:200',
            'sesiones_totales' => 'sometimes|integer|min:1',
            'sesiones_restantes' => 'sometimes|integer|min:0',
            'ejercicios' => 'sometimes|array',
            'ejercicios.*.ejercicio_id' => 'required|exists:ejercicios,id_ejercicio',
            'ejercicios.*.series' => 'nullable|integer|min:1',
            'ejercicios.*.repeticiones' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $rutina = Rutina::findOrFail($id);
            $rutina->update($request->except('ejercicios'));

            // Update exercises if provided
            if ($request->has('ejercicios')) {
                $rutina->ejercicios()->detach();
                foreach ($request->ejercicios as $ejercicio) {
                    $rutina->ejercicios()->attach($ejercicio['ejercicio_id'], [
                        'series' => $ejercicio['series'] ?? null,
                        'repeticiones' => $ejercicio['repeticiones'] ?? null,
                    ]);
                }
            }

            return $this->successResponse($rutina->load(['afiliado.usuario', 'profesional.usuario', 'ejercicios']), 'Rutina actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar la rutina.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rutina = Rutina::findOrFail($id);
            $rutina->delete();
            return $this->successResponse(null, 'Rutina eliminada exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar la rutina.', 500);
        }
    }
}
