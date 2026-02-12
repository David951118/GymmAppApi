<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EjercicioController extends Controller
{
    public function index()
    {
        $ejercicios = Ejercicio::paginate(15);
        return response()->json($ejercicios);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string|max:100',
            'guia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ejercicio = Ejercicio::create($request->all());
        return response()->json($ejercicio, 201);
    }

    public function show($id)
    {
        $ejercicio = Ejercicio::with(['rutinas', 'maquinas'])->findOrFail($id);
        return response()->json($ejercicio);
    }

    public function update(Request $request, $id)
    {
        $ejercicio = Ejercicio::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|string|max:100',
            'guia' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ejercicio->update($request->all());
        return response()->json($ejercicio);
    }

    public function destroy($id)
    {
        Ejercicio::findOrFail($id)->delete();
        return response()->json(['message' => 'Ejercicio eliminado'], 200);
    }
}
