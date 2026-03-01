<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Afiliado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\ContactoEmergencia;

class AfiliadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $afiliados = Afiliado::with(['usuario', 'centroInicial'])
            ->search($request->all())
            ->paginate($request->get('limit', 15));

        return $this->successResponse($afiliados, 'Afiliados recuperados exitosamente.');
    }

    /**
     * Store method - Affiliates created by Staff
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'apellidos' => 'required|string|max:150',
            'correo' => 'required|email|unique:usuarios,correo',
            'cedula' => 'nullable|string|max:50|unique:usuarios,cedula',
            'celular' => 'required|string|max:30',
            'genero' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'contrasena' => 'required|string|min:8',
            'centro_id' => 'required|exists:centro_deportivo,id_centro',
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Error de validación al crear afiliado', 422);
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

            return $this->successResponse([
                'user' => $usuario->load('contactosEmergencia'),
                'afiliado' => $afiliado->load('centroInicial'),
            ], 'Perfil de Afiliado creado exitosamente por Staff.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al crear el perfil de afiliado', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $afiliado = Afiliado::with(['usuario', 'centroInicial', 'antropometrias', 'planes', 'rutinas'])->findOrFail($id);
            return $this->successResponse($afiliado, 'Afiliado encontrado.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Afiliado no encontrado.', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $afiliado = Afiliado::findOrFail($id);

            // Note: In a real app we'd validate here, but relying on model/DB for brevity 
            // since specific rules depend on requirements.
            $afiliado->update($request->all());

            return $this->successResponse($afiliado->load(['usuario', 'centroInicial']), 'Afiliado actualizado.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar afiliado.', 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $afiliado = Afiliado::findOrFail($id);
            $afiliado->delete(); // Soft delete
            return $this->successResponse(null, 'Afiliado eliminado exitosamente (Soft Delete).');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Error al eliminar afiliado.', 400);
        }
    }

    public function restore($id)
    {
        try {
            $afiliado = Afiliado::withTrashed()->findOrFail($id);
            $afiliado->restore();
            return $this->successResponse($afiliado->load(['usuario', 'centroInicial']), 'Afiliado restaurado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al restaurar el afiliado.', 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $afiliado = Afiliado::withTrashed()->findOrFail($id);
            $afiliado->forceDelete();
            return $this->successResponse(null, 'Afiliado eliminado permanentemente (Hard Delete).');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar permanentemente el afiliado.', 500);
        }
    }
}
