<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Afiliado;
use Illuminate\Http\Request;

class AfiliadoController extends Controller
{
    public function index()
    {
        $afiliados = Afiliado::with(['usuario', 'centroInicial'])->paginate(15);
        return response()->json($afiliados);
    }

    // Store method removed - Affiliates are created via AuthController::register

    public function show($id)
    {
        $afiliado = Afiliado::with(['usuario', 'centroInicial', 'antropometrias', 'planes', 'rutinas'])->findOrFail($id);
        return response()->json($afiliado);
    }

    public function update(Request $request, $id)
    {
        $afiliado = Afiliado::findOrFail($id);
        $afiliado->update($request->all());
        return response()->json($afiliado->load(['usuario', 'centroInicial']));
    }

    public function destroy($id)
    {
        Afiliado::findOrFail($id)->delete();
        return response()->json(['message' => 'Afiliado eliminado'], 200);
    }
}
