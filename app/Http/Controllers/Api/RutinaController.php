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
        $query = Rutina::with(['afiliado.usuario', 'profesional.usuario', 'ejercicios']);

        if ($request->has('afiliado_id')) {
            $query->where('afiliado_id', $request->afiliado_id);
        }

        if ($request->has('profesional_id')) {
            $query->where('profesional_id', $request->profesional_id);
        }

        $rutinas = $query->paginate(15);
        return response()->json($rutinas);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        return response()->json($rutina->load(['afiliado.usuario', 'profesional.usuario', 'ejercicios']), 201);
    }

    public function show($id)
    {
        $rutina = Rutina::with(['afiliado.usuario', 'profesional.usuario', 'ejercicios'])->findOrFail($id);
        return response()->json($rutina);
    }

    public function update(Request $request, $id)
    {
        $rutina = Rutina::findOrFail($id);

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
            return response()->json(['errors' => $validator->errors()], 422);
        }

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

        return response()->json($rutina->load(['afiliado.usuario', 'profesional.usuario', 'ejercicios']));
    }

    public function destroy($id)
    {
        Rutina::findOrFail($id)->delete();
        return response()->json(['message' => 'Rutina eliminada'], 200);
    }
}
