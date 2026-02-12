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
        $query = Antropometria::with('afiliado.usuario');

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        $antropometrias = $query->orderBy('fecha_medicion', 'desc')->paginate(15);
        return response()->json($antropometrias);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $antropometria = Antropometria::create($request->all());
        return response()->json($antropometria->load('afiliado.usuario'), 201);
    }

    public function show($id)
    {
        $antropometria = Antropometria::with('afiliado.usuario')->findOrFail($id);
        return response()->json($antropometria);
    }

    public function update(Request $request, $id)
    {
        $antropometria = Antropometria::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'peso' => 'sometimes|numeric|min:0',
            'altura_cm' => 'sometimes|numeric|min:0',
            'imc' => 'sometimes|numeric|min:0',
            'grasa_corporal' => 'sometimes|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $antropometria->update($request->all());
        return response()->json($antropometria->load('afiliado.usuario'));
    }

    public function destroy($id)
    {
        Antropometria::findOrFail($id)->delete();
        return response()->json(['message' => 'Medición eliminada'], 200);
    }
}
