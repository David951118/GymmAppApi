<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aplicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AplicacionController extends Controller
{
    /**
     * Display the application settings (Public or authenticated readers)
     */
    public function index()
    {
        $aplicacion = Aplicacion::first();
        if (!$aplicacion) {
            $aplicacion = Aplicacion::create();
        }
        return $this->successResponse($aplicacion, 'Configuración de la aplicación obtenida exitosamente.');
    }

    /**
     * Update the application settings (Super Admin Only)
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_aplicacion' => 'sometimes|string|max:255',
            'link_icono' => 'sometimes|string|max:255',
            'gama_colores' => 'sometimes|string|max:255',
            'logo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Errores de validación', 422);
        }

        try {
            $aplicacion = Aplicacion::first();
            if (!$aplicacion) {
                $aplicacion = Aplicacion::create($request->all());
            } else {
                $aplicacion->update($request->all());
            }

            return $this->successResponse($aplicacion, 'Configuración de la aplicación actualizada exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Error al actualizar configuración', 500);
        }
    }
}
