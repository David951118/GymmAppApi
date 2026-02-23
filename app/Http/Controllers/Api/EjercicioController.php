<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EjercicioController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $ejercicios = Ejercicio::search($request->all())->paginate($limit);
        return $this->successResponse($ejercicios, 'Ejercicios obtenidos exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string|max:100',
            'guia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $ejercicio = Ejercicio::create($request->all());
            return $this->successResponse($ejercicio, 'Ejercicio creado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear el ejercicio.', 500);
        }
    }

    public function show($id)
    {
        try {
            $ejercicio = Ejercicio::with(['rutinas', 'maquinas'])->findOrFail($id);
            return $this->successResponse($ejercicio, 'Ejercicio obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Ejercicio no encontrado.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|string|max:100',
            'guia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $ejercicio = Ejercicio::findOrFail($id);
            $ejercicio->update($request->all());
            return $this->successResponse($ejercicio, 'Ejercicio actualizado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el ejercicio.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ejercicio = Ejercicio::findOrFail($id);
            $ejercicio->delete();
            return $this->successResponse(null, 'Ejercicio eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el ejercicio.', 500);
        }
    }
}
