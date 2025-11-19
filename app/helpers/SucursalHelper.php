<?php
namespace App\Helpers;

use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;

class SucursalHelper
{
    /**
     * Obtener la sucursal activa actual
     */
    public static function getSucursalActual()
    {
        $sucursalId = session('sucursal_id');
        return Sucursal::find($sucursalId);
    }

    /**
     * Obtener el ID de la sucursal activa
     */
    public static function getSucursalId()
    {
        return session('sucursal_id');
    }

    /**
     * Cambiar la sucursal activa
     */
    public static function cambiarSucursal($sucursalId)
    {
        $sucursal = Sucursal::findOrFail($sucursalId);

        // Verificar que el usuario tenga acceso a esta sucursal
        $user = Auth::user();

        if ($user->hasRole('administrador') || $user->sucursales->contains($sucursalId)) {
            session([
                'sucursal_id' => $sucursal->id,
                'sucursal_nombre' => $sucursal->nombre,
                'sucursal_codigo' => $sucursal->codigo
            ]);
            return true;
        }

        return false;
    }

    /**
     * Obtener sucursales disponibles para el usuario
     */
    public static function getSucursalesDisponibles()
    {
        $user = Auth::user();

        if ($user->hasRole('administrador')) {
            return Sucursal::where('estado', 1)->get();
        }

        return $user->sucursales()->where('estado', 1)->get();
    }

    /**
     * Verificar si el usuario puede cambiar de sucursal
     */
    public static function puedeHacerCambios()
    {
        $user = Auth::user();
        return $user->hasRole('administrador') || $user->sucursales()->count() > 1;
    }
}
