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
     * Creates Usuario + ContactoEmergencia + Afiliado in transaction
     */
    public function register(Request $request)
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

            // Centro deportivo (OBLIGATORIO para afiliados)
            'centro_id' => 'required|exists:centro_deportivo,id_centro',

            // Contacto de emergencia (OBLIGATORIO)
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',
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

            // 2. Crear contacto de emergencia (OBLIGATORIO)
            ContactoEmergencia::create([
                'usuario_id' => $usuario->id_usuario,
                'nombre' => $request->contacto_emergencia['nombre'],
                'celular' => $request->contacto_emergencia['celular'],
            ]);

            // 3. Crear afiliado con centro (OBLIGATORIO)
            $afiliado = Afiliado::create([
                'usuario_id' => $usuario->id_usuario,
                'centro_id' => $request->centro_id,
            ]);

            // 4. Enviar email de verificación
            event(new Registered($usuario));

            DB::commit();

            // 5. Generar token
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registro exitoso. Por favor verifica tu correo electrónico.',
                'user' => $usuario,
                'roles' => $usuario->getRoles(),
                'afiliado' => $afiliado,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el usuario',
                'error' => $e->getMessage()
            ], 500);
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
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::where('correo', $request->correo)->first();

        // DEBUG: Imprimir hash y verificación (SOLO DESARROLLO)
        if ($usuario) {
            \Illuminate\Support\Facades\Log::info('Login Debug:', [
                'correo' => $request->correo,
                'input_password' => $request->contrasena,
                'stored_hash' => $usuario->contrasena,
                'hash_check' => Hash::check($request->contrasena, $usuario->contrasena),
                'estado' => $usuario->estado_usuario
            ]);
        }

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Check if user is active
        if ($usuario->estado_usuario !== 'activo') {
            return response()->json([
                'message' => 'Tu cuenta está ' . $usuario->estado_usuario
            ], 403);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user' => $usuario,
            'roles' => $usuario->getRoles(),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Resend email verification notification
     */
    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo ya está verificado'
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Correo de verificación enviado'
        ]);
    }

    /**
     * Verify email
     */
    public function verify(Request $request, $id, $hash)
    {
        $usuario = Usuario::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($usuario->getEmailForVerification()))) {
            return response()->json([
                'message' => 'El enlace de verificación no es válido'
            ], 400);
        }

        if ($usuario->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo ya está verificado'
            ], 400);
        }

        $usuario->markEmailAsVerified();

        return response()->json([
            'message' => 'Correo verificado exitosamente'
        ]);
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $usuario = $request->user();

        return response()->json([
            'user' => $usuario,
            'roles' => $usuario->getRoles(),
        ]);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->contrasena)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 400);
        }

        $user->contrasena = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente.'
        ]);
    }
}
