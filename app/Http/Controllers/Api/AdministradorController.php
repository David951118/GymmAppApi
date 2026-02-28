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
        $admin = Administrador::findOrFail($id);

        // No permitir modificar is_super_admin desde API
        if ($admin->is_super_admin) {
            return $this->errorResponse(null, 'No se puede modificar el Super Admin', 403);
        }

        $usuario = $admin->usuario;

        $validator = Validator::make($request->all(), [
            // Datos de usuario
            'nombre' => 'sometimes|string|max:150',
            'apellidos' => 'sometimes|string|max:150',
            'correo' => [
                'sometimes',
                'email',
                \Illuminate\Validation\Rule::unique('usuarios', 'correo')->ignore($usuario->id_usuario, 'id_usuario')
            ],
            'cedula' => [
                'sometimes',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('usuarios', 'cedula')->ignore($usuario->id_usuario, 'id_usuario')
            ],
            'celular' => 'sometimes|string|max:30',
            'genero' => 'sometimes|nullable|string|max:20',
            'fecha_nacimiento' => 'sometimes|date',
            'contrasena' => 'sometimes|string|min:8',

            // Contacto de emergencia
            'contacto_emergencia' => 'sometimes|array',
            'contacto_emergencia.nombre' => 'sometimes|string|max:150',
            'contacto_emergencia.celular' => 'sometimes|string|max:30',

            // Datos de administrador
            'centro_id' => 'sometimes|exists:centro_deportivo,id_centro',
            'nivel' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        DB::beginTransaction();
        try {
            // Datos para actualizar usuario
            $datosUsuario = [
                'nombre' => $request->get('nombre', $usuario->nombre),
                'apellidos' => $request->get('apellidos', $usuario->apellidos),
                'correo' => $request->get('correo', $usuario->correo),
                'cedula' => $request->get('cedula', $usuario->cedula),
                'celular' => $request->get('celular', $usuario->celular),
                'genero' => $request->get('genero', $usuario->genero),
                'fecha_nacimiento' => $request->get('fecha_nacimiento', $usuario->fecha_nacimiento),
            ];

            // Si se envía contraseña, hashearla
            if ($request->has('contrasena') && !empty($request->get('contrasena'))) {
                $datosUsuario['contrasena'] = Hash::make($request->get('contrasena'));
            }

            // Actualizar usuario
            $usuario->update($datosUsuario);

            // Actualizar o crear contactos de emergencia
            if ($request->has('contacto_emergencia')) {
                $contactoData = $request->get('contacto_emergencia');
                $contactoEmergencia = ContactoEmergencia::where('usuario_id', $usuario->id_usuario)->first();
                
                if ($contactoEmergencia) {
                    $contactoEmergencia->update([
                        'nombre' => $contactoData['nombre'] ?? $contactoEmergencia->nombre,
                        'celular' => $contactoData['celular'] ?? $contactoEmergencia->celular,
                    ]);
                } else {
                    ContactoEmergencia::create([
                        'usuario_id' => $usuario->id_usuario,
                        'nombre' => $contactoData['nombre'],
                        'celular' => $contactoData['celular'],
                    ]);
                }
            }

            // Actualizar datos del administrador
            $datosAdmin = [];
            if ($request->has('centro_id')) {
                $datosAdmin['centro_id'] = $request->get('centro_id');
            }
            if ($request->has('nivel')) {
                $datosAdmin['nivel'] = $request->get('nivel');
            }
            
            if (!empty($datosAdmin)) {
                $admin->update($datosAdmin);
            }

            DB::commit();

            return $this->successResponse($admin->load(['usuario.contactosEmergencia', 'centro']), 'Administrador actualizado exitosamente.');
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
