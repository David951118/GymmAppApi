<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Administrador;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Support\Str;

class AdministradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $administradores = Administrador::with(['usuario', 'centro'])
            ->where('is_super_admin', false) // No mostrar Super Admin en listado
            ->search($request->all())
            ->paginate($limit);

        return $this->successResponse($administradores, 'Administradores obtenidos exitosamente.');
    }

    /**
     * Store a newly created resource in storage.
     * Creates Usuario + ContactoEmergencia + Administrador in transaction
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
            'estado_usuario' => 'nullable|string|in:activo,inactivo,pendiente',

            // Contacto de emergencia (OBLIGATORIO)
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',

            // Datos de administrador
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'nivel' => 'nullable|string|max:50',
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
                'estado_usuario' => $request->estado_usuario ?? 'activo',
            ]);

            // 2. Crear contacto de emergencia (OBLIGATORIO)
            ContactoEmergencia::create([
                'usuario_id' => $usuario->id_usuario,
                'nombre' => $request->contacto_emergencia['nombre'],
                'celular' => $request->contacto_emergencia['celular'],
            ]);

            // 3. Crear administrador
            $admin = Administrador::create([
                'usuario_id' => $usuario->id_usuario,
                'centro_id' => $request->centro_id,
                'nivel' => $request->nivel ?? 'Administrador',
                'is_super_admin' => false, // Nunca true desde API
            ]);

            DB::commit();

            return $this->successResponse([
                'usuario' => $usuario,
                'administrador' => $admin->load('centro'),
            ], 'Administrador creado exitosamente.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al crear administrador', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $admin = Administrador::with(['usuario.contactosEmergencia', 'centro'])
                ->findOrFail($id);

            return $this->successResponse($admin, 'Administrador obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Administrador no encontrado.', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $admin = Administrador::findOrFail($id);

            // No permitir modificar is_super_admin desde API
            if ($admin->is_super_admin) {
                return $this->errorResponse(null, 'No se puede modificar el Super Admin', 403);
            }

            $validator = Validator::make($request->all(), [
                'centro_id' => 'sometimes|exists:centro_deportivo,id_centro',
                'nivel' => 'sometimes|string|max:50',

                // Actualizar datos de usuario
                'usuario.nombre' => 'sometimes|string|max:150',
                'usuario.apellidos' => 'sometimes|string|max:150',
                'usuario.celular' => 'sometimes|string|max:30',
                'usuario.genero' => 'sometimes|string|max:20',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
            }

            DB::beginTransaction();

            // Actualizar administrador
            $admin->update($request->only(['centro_id', 'nivel']));

            // Actualizar usuario si se envió data
            if ($request->has('usuario')) {
                $admin->usuario->update($request->usuario);
            }

            DB::commit();

            return $this->successResponse($admin->load(['usuario', 'centro']), 'Administrador actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al actualizar administrador', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $admin = Administrador::findOrFail($id);

            // No permitir eliminar Super Admin
            if ($admin->is_super_admin) {
                return $this->errorResponse(null, 'No se puede eliminar el Super Admin', 403);
            }

            $admin->delete(); // Soft delete. El usuario original requiere manejo especial si se desea borrar completo, pero softdelete cubre la entidad admin.

            return $this->successResponse(null, 'Administrador eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar administrador', 500);
        }
    }
}
