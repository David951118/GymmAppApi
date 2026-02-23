<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaquinaController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $query = Maquina::with(['centro', 'administrador.usuario', 'ejercicio'])
            ->search($request->all());

        if ($request->has('centro_id')) {
            $query->where('centro_id', $request->centro_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $maquinas = $query->paginate($limit);
        return $this->successResponse($maquinas, 'Máquinas obtenidas exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'administrador_id' => 'nullable|exists:administradores,id_administrador',
            'ejercicio_id' => 'nullable|exists:ejercicios,id_ejercicio',
            'nombre' => 'nullable|string|max:150',
            'estado' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $maquina = Maquina::create($request->all());
            return $this->successResponse($maquina->load(['centro', 'administrador.usuario', 'ejercicio']), 'Máquina creada exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear la máquina.', 500);
        }
    }

    public function show($id)
    {
        try {
            $maquina = Maquina::with(['centro', 'administrador.usuario', 'ejercicio'])->findOrFail($id);
            return $this->successResponse($maquina, 'Máquina obtenida exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Máquina no encontrada.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:150',
            'estado' => 'sometimes|string|max:50',
            'ubicacion' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $maquina = Maquina::findOrFail($id);
            $maquina->update($request->all());
            return $this->successResponse($maquina->load(['centro', 'administrador.usuario', 'ejercicio']), 'Máquina actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar la máquina.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $maquina = Maquina::findOrFail($id);
            $maquina->delete();
            return $this->successResponse(null, 'Máquina eliminada exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar la máquina.', 500);
        }
    }
}
