<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $query = Plan::with(['afiliado.usuario', 'pagos'])
            ->search($request->all());

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        $planes = $query->paginate($limit);
        return $this->successResponse($planes, 'Planes obtenidos exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'afiliado_id' => 'required|exists:afiliados,id_afiliado',
            'tipo' => 'required|in:Mensual,Semestral,Anual',
            'fecha_inicio' => 'required|date',
            'fecha_corte' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'valor' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $plan = Plan::create($request->all());
            return $this->successResponse($plan->load('afiliado.usuario'), 'Plan creado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear el plan.', 500);
        }
    }

    public function show($id)
    {
        try {
            $plan = Plan::with(['afiliado.usuario', 'pagos'])->findOrFail($id);
            return $this->successResponse($plan, 'Plan obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Plan no encontrado.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|in:Mensual,Semestral,Anual',
            'fecha_inicio' => 'sometimes|date',
            'fecha_corte' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'valor' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $plan = Plan::findOrFail($id);
            $plan->update($request->all());
            return $this->successResponse($plan->load('afiliado.usuario'), 'Plan actualizado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el plan.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $plan = Plan::findOrFail($id);
            $plan->delete();
            return $this->successResponse(null, 'Plan eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el plan.', 500);
        }
    }
}
