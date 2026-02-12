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
        $query = Plan::with(['afiliado.usuario', 'pagos']);

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        $planes = $query->paginate(15);
        return response()->json($planes);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan = Plan::create($request->all());
        return response()->json($plan->load('afiliado.usuario'), 201);
    }

    public function show($id)
    {
        $plan = Plan::with(['afiliado.usuario', 'pagos'])->findOrFail($id);
        return response()->json($plan);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|in:Mensual,Semestral,Anual',
            'fecha_inicio' => 'sometimes|date',
            'fecha_corte' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'valor' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan->update($request->all());
        return response()->json($plan->load('afiliado.usuario'));
    }

    public function destroy($id)
    {
        Plan::findOrFail($id)->delete();
        return response()->json(['message' => 'Plan eliminado'], 200);
    }
}
