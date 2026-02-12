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
        $query = Pago::with(['plan.afiliado.usuario']);

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $pagos = $query->orderBy('fecha_cobro', 'desc')->paginate(15);
        return response()->json($pagos);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pago = Pago::create($request->all());
        return response()->json($pago->load('plan.afiliado.usuario'), 201);
    }

    public function show($id)
    {
        $pago = Pago::with(['plan.afiliado.usuario'])->findOrFail($id);
        return response()->json($pago);
    }

    public function update(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'estado' => 'sometimes|string|max:50',
            'metodo_pago' => 'sometimes|string|max:50',
            'referencia' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pago->update($request->all());
        return response()->json($pago->load('plan.afiliado.usuario'));
    }

    public function destroy($id)
    {
        Pago::findOrFail($id)->delete();
        return response()->json(['message' => 'Pago eliminado'], 200);
    }
}
