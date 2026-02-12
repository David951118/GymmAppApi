<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaquinaController extends Controller
{
    public function index(Request $request)
    {
        $query = Maquina::with(['centro', 'administrador.usuario', 'ejercicio']);

        if ($request->has('centro_id')) {
            $query->where('centro_id', $request->centro_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $maquinas = $query->paginate(15);
        return response()->json($maquinas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'administrador_id' => 'nullable|exists:administradores,id_administrador',
            'ejercicio_id' => 'nullable|exists:ejercicios,id_ejercicio',
            'nombre' => 'nullable|string|max:150',
            'estado' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maquina = Maquina::create($request->all());
        return response()->json($maquina->load(['centro', 'administrador.usuario', 'ejercicio']), 201);
    }

    public function show($id)
    {
        $maquina = Maquina::with(['centro', 'administrador.usuario', 'ejercicio'])->findOrFail($id);
        return response()->json($maquina);
    }

    public function update(Request $request, $id)
    {
        $maquina = Maquina::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:150',
            'estado' => 'sometimes|string|max:50',
            'ubicacion' => 'sometimes|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $maquina->update($request->all());
        return response()->json($maquina->load(['centro', 'administrador.usuario', 'ejercicio']));
    }

    public function destroy($id)
    {
        Maquina::findOrFail($id)->delete();
        return response()->json(['message' => 'Máquina eliminada'], 200);
    }
}
