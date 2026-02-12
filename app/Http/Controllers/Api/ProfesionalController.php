<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Profesional;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Validator};

class ProfesionalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profesionales = Profesional::with(['usuario', 'centro'])->paginate(15);
        return response()->json($profesionales);
    }

    /**
     * Store a newly created resource in storage.
     * Creates Usuario + ContactoEmergencia + Profesional in transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos de usuario
            'nombre' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'cedula' => 'nullable|string|max:50|unique:usuarios,cedula',
            'celular' => 'required|string|max:30',
            'genero' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'contrasena' => 'required|string|min:8|confirmed',

            // Contacto de emergencia (OBLIGATORIO)
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',

            // Datos de profesional
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'especialidad' => 'nullable|string|max:150',
            'fecha_ingreso' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Crear usuario
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'correo' => $request->correo,
                'cedula' => $request->cedula,
                'celular' => $request->celular,
                'genero' => $request->genero,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'contrasena' => Hash::make($request->contrasena),
                'estado_usuario' => 'activo',
            ]);

            // 2. Crear contacto de emergencia
            ContactoEmergencia::create([
                'usuario_id' => $usuario->id_usuario,
                'nombre' => $request->contacto_emergencia['nombre'],
                'celular' => $request->contacto_emergencia['celular'],
            ]);

            // 3. Crear profesional
            $profesional = Profesional::create([
                'usuario_id' => $usuario->id_usuario,
                'centro_id' => $request->centro_id,
                'especialidad' => $request->especialidad,
                'fecha_ingreso' => $request->fecha_ingreso,
            ]);



            DB::commit();

            return response()->json([
                'message' => 'Profesional creado exitosamente.',
                'usuario' => $usuario->load('contactosEmergencia'),
                'profesional' => $profesional->load('centro'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear profesional',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $profesional = Profesional::with(['usuario.contactosEmergencia', 'centro', 'actividades', 'rutinas'])
            ->findOrFail($id);

        return response()->json($profesional);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $profesional = Profesional::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'centro_id' => 'sometimes|exists:centro_deportivo,id_centro',
            'especialidad' => 'sometimes|string|max:150',
            'fecha_ingreso' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profesional->update($request->all());
        return response()->json($profesional->load(['usuario', 'centro']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Profesional::findOrFail($id)->delete();
        return response()->json(['message' => 'Profesional eliminado'], 200);
    }
}
