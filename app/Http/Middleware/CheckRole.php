<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Super Admin tiene acceso a TODO
        if ($user->esSuperAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            switch ($role) {
                case 'afiliado':
                    if ($user->esAfiliado()) {
                        return $next($request);
                    }
                    break;
                case 'profesional':
                    if ($user->esProfesional()) {
                        return $next($request);
                    }
                    break;
                case 'trabajador':
                    if ($user->esTrabajador()) {
                        return $next($request);
                    }
                    break;
                case 'administrador':
                    if ($user->esAdministrador()) {
                        return $next($request);
                    }
                    break;
            }
        }

        return response()->json([
            'message' => 'No tienes permisos para acceder a este recurso',
            'required_roles' => $roles,
            'your_roles' => $user->getRoles()
        ], 403);
    }
}
