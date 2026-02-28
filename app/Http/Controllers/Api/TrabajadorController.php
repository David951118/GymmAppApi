<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Trabajador;
use App\Models\ContactoEmergencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Support\Str;

class TrabajadorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 15);
        $trabajadores = Trabajador::with(['usuario', 'centro'])
            ->search($request->all())
            ->paginate($limit);

        return $this->successResponse($trabajadores, 'Trabajadores obtenidos exitosamente.');
    }

    /**
     * Store a newly created resource in storage.
     * Creates Usuario + ContactoEmergencia + Trabajador in transaction
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
            'contrasena' => 'required_without:password|string|min:8|confirmed',
            'password' => 'required_without:contrasena|string|min:8|confirmed',

            // Contacto de emergencia (OBLIGATORIO)
            'contacto_emergencia.nombre' => 'required|string|max:150',
            'contacto_emergencia.celular' => 'required|string|max:30',

            // Datos de trabajador
            'centro_id' => 'nullable|exists:centro_deportivo,id_centro',
            'puesto' => 'nullable|string|max:100',
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
                'contrasena' => Hash::make($request->contrasena ?? $request->password),
                'estado_usuario' => 'activo',
            ]);

            // 2. Crear contacto de emergencia
            ContactoEmergencia::create([
                'usuario_id' => $usuario->id_usuario,
                'nombre' => $request->contacto_emergencia['nombre'],
                'celular' => $request->contacto_emergencia['celular'],
            ]);

            // 3. Crear trabajador
            $trabajador = Trabajador::create([
                'usuario_id' => $usuario->id_usuario,
                'centro_id' => $request->centro_id,
                'puesto' => $request->puesto,
            ]);

            DB::commit();

            return $this->successResponse([
                'usuario' => $usuario->load('contactosEmergencia'),
                'trabajador' => $trabajador->load('centro'),
            ], 'Trabajador creado exitosamente.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al crear trabajador', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $trabajador = Trabajador::with(['usuario.contactosEmergencia', 'centro'])
                ->findOrFail($id);

            return $this->successResponse($trabajador, 'Trabajador obtenido exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Trabajador no encontrado.', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // precargar modelos para las reglas únicas
        $trabajador = Trabajador::findOrFail($id);
        $usuario = $trabajador->usuario;

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
            'password' => 'sometimes|string|min:8',

            // Contacto de emergencia
            'contacto_emergencia' => 'sometimes|array',
            'contacto_emergencia.nombre' => 'sometimes|string|max:150',
            'contacto_emergencia.celular' => 'sometimes|string|max:30',
            'contactos_emergencia' => 'sometimes|array',

            // Datos de trabajador
            'centro_id' => 'sometimes|exists:centro_deportivo,id_centro',
            'puesto' => 'sometimes|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        DB::beginTransaction();
        try {
            $trabajador = Trabajador::findOrFail($id);
            $usuario = $trabajador->usuario;

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
            $newPass = null;
            if ($request->has('contrasena') && !empty($request->get('contrasena'))) {
                $newPass = $request->get('contrasena');
            } elseif ($request->has('password') && !empty($request->get('password'))) {
                $newPass = $request->get('password');
            }

            if ($newPass) {
                $datosUsuario['contrasena'] = Hash::make($newPass);
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

            // Actualizar datos del trabajador
            $datosTrabajador = [];
            if ($request->has('centro_id')) {
                $datosTrabajador['centro_id'] = $request->get('centro_id');
            }
            if ($request->has('puesto')) {
                $datosTrabajador['puesto'] = $request->get('puesto');
            }
            
            if (!empty($datosTrabajador)) {
                $trabajador->update($datosTrabajador);
            }

            DB::commit();

            return $this->successResponse($trabajador->load(['usuario.contactosEmergencia', 'centro']), 'Trabajador actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), 'Error al actualizar el trabajador.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $trabajador = Trabajador::findOrFail($id);
            $trabajador->delete();
            return $this->successResponse(null, 'Trabajador eliminado exitosamente.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al eliminar el trabajador.', 500);
        }
    }
}
