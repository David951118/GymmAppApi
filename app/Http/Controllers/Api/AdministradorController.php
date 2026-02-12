<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Administrador;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Notifications\SetPasswordNotification;

class AdministradorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $administradores = Administrador::with(['usuario', 'centro'])
            ->where('is_super_admin', false) // No mostrar Super Admin en listado
            ->paginate(15);

        return response()->json($administradores);
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

            // Contacto de emergencia (OBLIGATORIO)
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',

            // Datos de administrador
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'nivel' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 1. Crear usuario (SIN contraseña aún, se establece por email)
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'apellidos' => $request->apellidos,
                'correo' => $request->correo,
                'cedula' => $request->cedula,
                'celular' => $request->celular,
                'genero' => $request->genero,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'contrasena' => Hash::make(Str::random(32)), // Temporal
                'estado_usuario' => 'inactivo', // Inactivo hasta verificar email
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

            // 4. Enviar notificación de activación con token
            $token = Password::broker()->createToken($usuario);
            $usuario->notify(new SetPasswordNotification($token));

            DB::commit();

            return response()->json([
                'message' => 'Administrador creado exitosamente. Se envió correo de activación para establecer contraseña.',
                'usuario' => $usuario->load('contactosEmergencia'),
                'administrador' => $admin->load('centro'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear administrador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $admin = Administrador::with(['usuario.contactosEmergencia', 'centro'])
            ->findOrFail($id);

        return response()->json($admin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $admin = Administrador::findOrFail($id);

        // No permitir modificar is_super_admin desde API
        if ($admin->is_super_admin) {
            return response()->json([
                'message' => 'No se puede modificar el Super Admin'
            ], 403);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Actualizar administrador
            $admin->update($request->only(['centro_id', 'nivel']));

            // Actualizar usuario si se envió data
            if ($request->has('usuario')) {
                $admin->usuario->update($request->usuario);
            }

            DB::commit();

            return response()->json($admin->load(['usuario', 'centro']));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $admin = Administrador::findOrFail($id);

        // No permitir eliminar Super Admin
        if ($admin->is_super_admin) {
            return response()->json([
                'message' => 'No se puede eliminar el Super Admin'
            ], 403);
        }

        $admin->delete(); // Cascade eliminará el usuario también

        return response()->json([
            'message' => 'Administrador eliminado'
        ], 200);
    }
}
