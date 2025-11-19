<?php
namespace App\Http\Controllers;

use App\Helpers\SucursalHelper;
use Illuminate\Http\Request;

class SucursalSwitchController extends Controller
{
    /**
     * Cambiar la sucursal activa
     */
    public function cambiar(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id'
        ]);

        if (SucursalHelper::cambiarSucursal($request->sucursal_id)) {
            return response()->json([
                'success' => true,
                'message' => 'Sucursal cambiada exitosamente',
                'sucursal' => [
                    'id' => session('sucursal_id'),
                    'nombre' => session('sucursal_nombre'),
                    'codigo' => session('sucursal_codigo')
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No tiene acceso a esta sucursal'
        ], 403);
    }
}
