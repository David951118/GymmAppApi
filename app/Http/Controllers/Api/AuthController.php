<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Afiliado;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ContactoEmergencia;

class AuthController extends Controller
{
    /**
     * Register a new afiliado (PUBLIC)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'cedula' => 'nullable|string|max:50|unique:usuarios,cedula',
            'celular' => 'required|string|max:30',
            'genero' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'contrasena' => 'required|string|min:8|confirmed',
            'centro_id' => 'required|exists:centro_deportivo,id_centro',
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Error de validación', 422);
        }

        DB::beginTransaction();
        try {
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

            ContactoEmergencia::create([
                'usuario_id' => $usuario->id_usuario,
                'nombre' => $request->contacto_emergencia['nombre'],
                'celular' => $request->contacto_emergencia['celular'],
            ]);

            $afiliado = Afiliado::create([
                'usuario_id' => $usuario->id_usuario,
                'centro_id' => $request->centro_id,
            ]);

            DB::commit();

            $token = $usuario->createToken('auth_token')->plainTextToken;

            return $this->successResponse([
                'user' => $usuario->load('contactosEmergencia'),
                'roles' => $usuario->getRoles(),
                'afiliado' => $afiliado,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 'Registro exitoso.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al crear el usuario', 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'contrasena' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Error de validación', 422);
        }

        $usuario = Usuario::where('correo', $request->correo)->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return $this->errorResponse(null, 'Credenciales inválidas', 401);
        }

        if ($usuario->estado_usuario !== 'activo') {
            return $this->errorResponse(null, 'Tu cuenta está ' . $usuario->estado_usuario, 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $usuario,
            'roles' => $usuario->getRoles(),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Inicio de sesión exitoso');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'Sesión cerrada exitosamente');
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $usuario = $request->user();
        return $this->successResponse([
            'user' => $usuario->load('contactosEmergencia'),
            'roles' => $usuario->getRoles(),
        ], 'Perfil obtenido exitosamente.');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Error de validación', 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->contrasena)) {
            return $this->errorResponse(null, 'La contraseña actual es incorrecta.', 400);
        }

        try {
            $user->contrasena = Hash::make($request->new_password);
            $user->save();
            return $this->successResponse(null, 'Contraseña actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar la contraseña', 500);
        }
    }
}
