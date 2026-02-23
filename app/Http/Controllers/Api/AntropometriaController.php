<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antropometria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AntropometriaController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $query = Antropometria::with('afiliado.usuario')
            ->search($request->all());

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        $antropometrias = $query->orderBy('fecha_medicion', 'desc')->paginate($limit);
        return $this->successResponse($antropometrias, 'Antropometrías obtenidas exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'afiliado_id' => 'required|exists:afiliados,id_afiliado',
            'peso' => 'nullable|numeric|min:0',
            'altura_cm' => 'nullable|numeric|min:0',
            'imc' => 'nullable|numeric|min:0',
            'grasa_corporal' => 'nullable|numeric|min:0|max:100',
            'fecha_medicion' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $antropometria = Antropometria::create($request->all());
            return $this->successResponse($antropometria->load('afiliado.usuario'), 'Medición antropométrica creada exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear la medición antropométrica.', 500);
        }
    }

    public function show($id)
    {
        try {
            $antropometria = Antropometria::with('afiliado.usuario')->findOrFail($id);
            return $this->successResponse($antropometria, 'Antropometría obtenida exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Antropometría no encontrada.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'peso' => 'sometimes|numeric|min:0',
            'altura_cm' => 'sometimes|numeric|min:0',
            'imc' => 'sometimes|numeric|min:0',
            'grasa_corporal' => 'sometimes|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $antropometria = Antropometria::findOrFail($id);
            $antropometria->update($request->all());
            return $this->successResponse($antropometria->load('afiliado.usuario'), 'Medición antropométrica actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar la medición antropométrica.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $antropometria = Antropometria::findOrFail($id);
            $antropometria->delete();
            return $this->successResponse(null, 'Medición eliminada exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar la medición antropométrica.', 500);
        }
    }
}
