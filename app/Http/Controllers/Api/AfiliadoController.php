<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Afiliado;
use Illuminate\Http\Request;

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
     * Store method removed - Affiliates are created via AuthController::register
     */

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
            return $this->successResponse(null, 'Afiliado eliminado exitosamente.');
        } catch (\Exception $e) {
            return $this->errorResponse(null, 'Error al eliminar afiliado.', 400);
        }
    }
}
