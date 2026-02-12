<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CentroDeportivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CentroDeportivoController extends Controller
{
    public function index()
    {
        $centros = CentroDeportivo::with(['profesionales', 'trabajadores', 'administradores'])->paginate(15);
        return response()->json($centros);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255',
            'ubicacion' => 'required|string',
            'horario' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $centro = CentroDeportivo::create($request->all());
        return response()->json($centro, 201);
    }

    public function show($id)
    {
        $centro = CentroDeportivo::with([
            'profesionales.usuario',
            'trabajadores.usuario',
            'administradores.usuario',
            'actividades',
            'maquinas'
        ])->findOrFail($id);

        return response()->json($centro);
    }

    public function update(Request $request, $id)
    {
        $centro = CentroDeportivo::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:255',
            'ubicacion' => 'sometimes|string',
            'horario' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $centro->update($request->all());
        return response()->json($centro);
    }

    public function destroy($id)
    {
        CentroDeportivo::findOrFail($id)->delete();
        return response()->json(['message' => 'Centro deportivo eliminado'], 200);
    }
}
