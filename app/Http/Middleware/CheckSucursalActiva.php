<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Sucursal;

class CheckSucursalActiva
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si no hay sucursal en sesión, obtener la primera activa
        if (!session()->has('sucursal_id')) {
            $user = auth()->user();

             if (!$user) {
            return $next($request);
        }

            // Si es superadmin, obtener cualquier sucursal
            if ($user->hasRole('administrador')) {
                $sucursal = Sucursal::where('estado', 1)->first();
            } else {
                // Si es usuario normal, obtener su sucursal principal
                $sucursal = $user->sucursales()->wherePivot('es_principal', 1)->first();

                // Si no tiene principal, obtener la primera asignada
                if (!$sucursal) {
                    $sucursal = $user->sucursales()->where('estado', 1)->first();
                }
            }

            if ($sucursal) {
                session(['sucursal_id' => $sucursal->id]);
                session(['sucursal_nombre' => $sucursal->nombre]);
                session(['sucursal_codigo' => $sucursal->codigo]);
            }
        }

        return $next($request);
    }
}
