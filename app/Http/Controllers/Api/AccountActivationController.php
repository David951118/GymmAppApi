<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\Usuario;
use Illuminate\Support\Str;

class AccountActivationController extends Controller
{
    /**
     * Activate account and set initial password
     */
    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Usamos el Password Broker de Laravel para validar el token y resetear la contraseña
        // Esto maneja la seguridad del token (expiración, validación) automáticamente
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // 1. Establecer la contraseña
                $user->forceFill([
                    'contrasena' => Hash::make($password)
                ])->save();

                $user->setRememberToken(Str::random(60));

                // 2. Marcar email como verificado (Activación implícita)
                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                // 3. Activar estado del usuario si estaba inactivo
                if ($user->estado_usuario === 'inactivo') {
                    $user->estado_usuario = 'activo';
                    $user->save();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            // Login automático para devolver token
            $user = Usuario::where('correo', $request->email)->first();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Cuenta activada exitosamente.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('administrador', 'profesional', 'trabajador'), // Cargar rol relevante
            ]);
        }

        return response()->json([
            'message' => 'No se pudo activar la cuenta. El token puede ser inválido o haber expirado.',
            'error' => __($status)
        ], 400);
    }
}
