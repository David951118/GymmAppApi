<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $query = Pago::with(['plan.afiliado.usuario'])
            ->search($request->all());

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos = $query->orderBy('fecha_cobro', 'desc')->paginate($limit);
        return $this->successResponse($pagos, 'Pagos obtenidos exitosamente.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:planes,id_plan',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|string|max:50',
            'metodo_pago' => 'nullable|string|max:50',
            'referencia' => 'nullable|string|max:200',
            'fecha_cobro' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $pago = Pago::create($request->all());
            return $this->successResponse($pago->load('plan.afiliado.usuario'), 'Pago creado exitosamente.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al crear el pago.', 500);
        }
    }

    public function show($id)
    {
        try {
            $pago = Pago::with(['plan.afiliado.usuario'])->findOrFail($id);
            return $this->successResponse($pago, 'Pago obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Pago no encontrado.', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'sometimes|string|max:50',
            'metodo_pago' => 'sometimes|string|max:50',
            'referencia' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $pago = Pago::findOrFail($id);
            $pago->update($request->all());
            return $this->successResponse($pago->load('plan.afiliado.usuario'), 'Pago actualizado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el pago.', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pago = Pago::findOrFail($id);
            $pago->delete();
            return $this->successResponse(null, 'Pago eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el pago.', 500);
        }
    }
}
