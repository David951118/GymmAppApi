<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActividadDeportiva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActividadDeportivaController extends Controller
{
    public function index(Request $request)
    {
        $query = ActividadDeportiva::with(['centro', 'profesional.usuario', 'afiliados']);

        if ($request->has('centro_id')) {
            $query->where('centro_id', $request->centro_id);
        }

        if ($request->has('profesional_id')) {
            $query->where('profesional_id', $request->profesional_id);
        }

        $actividades = $query->orderBy('fecha', 'desc')->paginate(15);
        return response()->json($actividades);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'centro_id' => 'required|exists:centro_deportivo,id_centro',
            'profesional_id' => 'nullable|exists:profesionales,id_profesional',
            'fecha' => 'required|date',
            'tipo' => 'nullable|string|max:100',
            'duracion' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $actividad = ActividadDeportiva::create($request->all());
        return response()->json($actividad->load(['centro', 'profesional.usuario']), 201);
    }

    public function show($id)
    {
        $actividad = ActividadDeportiva::with(['centro', 'profesional.usuario', 'afiliados.usuario'])->findOrFail($id);
        return response()->json($actividad);
    }

    public function update(Request $request, $id)
    {
        $actividad = ActividadDeportiva::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'fecha' => 'sometimes|date',
            'tipo' => 'sometimes|string|max:100',
            'duracion' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $actividad->update($request->all());
        return response()->json($actividad->load(['centro', 'profesional.usuario']));
    }

    public function destroy($id)
    {
        ActividadDeportiva::findOrFail($id)->delete();
        return response()->json(['message' => 'Actividad eliminada'], 200);
    }

    /**
     * Register afiliado attendance to activity
     */
    public function registrarAsistencia(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'afiliado_id' => 'required|exists:afiliados,id_afiliado',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $actividad = ActividadDeportiva::findOrFail($id);

        $actividad->afiliados()->attach($request->afiliado_id, [
            'fecha_asistencia' => now(),
            'fecha_inscripcion' => now(),
        ]);

        return response()->json(['message' => 'Asistencia registrada'], 201);
    }
}
