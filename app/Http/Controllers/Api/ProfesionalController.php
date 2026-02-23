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
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $profesionales = Profesional::with(['usuario', 'centro'])
            ->search($request->all())
            ->paginate($limit);

        return $this->successResponse($profesionales, 'Profesionales obtenidos exitosamente.');
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
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
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

            return $this->successResponse([
                'usuario' => $usuario->load('contactosEmergencia'),
                'profesional' => $profesional->load('centro'),
            ], 'Profesional creado exitosamente.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al crear profesional', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $profesional = Profesional::with(['usuario.contactosEmergencia', 'centro', 'actividades', 'rutinas'])
                ->findOrFail($id);

            return $this->successResponse($profesional, 'Profesional obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Profesional no encontrado.', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'centro_id' => 'sometimes|exists:centro_deportivo,id_centro',
            'especialidad' => 'sometimes|string|max:150',
            'fecha_ingreso' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $profesional = Profesional::findOrFail($id);
            $profesional->update($request->all());
            return $this->successResponse($profesional->load(['usuario', 'centro']), 'Profesional actualizado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el profesional', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $profesional = Profesional::findOrFail($id);
            $profesional->delete();
            return $this->successResponse(null, 'Profesional eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el profesional.', 500);
        }
    }
}
