<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CentroDeportivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CentroDeportivoController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $centros = CentroDeportivo::with(['profesionales', 'trabajadores', 'administradores'])
            ->search($request->all())
            ->paginate($limit);

        return $this->successResponse($centros, 'Centros deportivos obtenidos exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'ubicacion' => 'required|string',
            'horario' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $centro = CentroDeportivo::create($request->all());
            return $this->successResponse($centro, 'Centro deportivo creado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear el centro deportivo.', 500);
        }
    }

    public function show($id)
    {
        try {
            $centro = CentroDeportivo::with([
                'profesionales.usuario',
                'trabajadores.usuario',
                'administradores.usuario',
                'actividades',
                'maquinas'
            ])->findOrFail($id);

            return $this->successResponse($centro, 'Centro deportivo obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Centro deportivo no encontrado.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'ubicacion' => 'sometimes|string',
            'horario' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $centro = CentroDeportivo::findOrFail($id);
            $centro->update($request->all());
            return $this->successResponse($centro, 'Centro deportivo actualizado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el centro deportivo.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $centro = CentroDeportivo::findOrFail($id);
            $centro->delete();
            return $this->successResponse(null, 'Centro deportivo eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el centro deportivo.', 500);
        }
    }
}
