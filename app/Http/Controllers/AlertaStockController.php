<?php
// app/Http/Controllers/AlertaStockController.php

namespace App\Http\Controllers;

use App\Models\AlertaStock;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertaStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener sucursales disponibles para el usuario
        $sucursales = $this->getSucursalesUsuario();

        // Sucursal seleccionada (por defecto la primera)
        $sucursalSeleccionada = $request->get('sucursal_id', $sucursales->first()->id ?? null);

        // Filtros
        $tipoAlerta = $request->get('tipo_alerta');
        $soloNoLeidas = $request->get('solo_no_leidas', true);

        // Query de alertas
        $query = AlertaStock::with(['producto.marca.caracteristica', 'producto.presentacione.caracteristica', 'sucursal'])
            ->where('sucursal_id', $sucursalSeleccionada);

        // Aplicar filtros
        if ($tipoAlerta) {
            $query->where('tipo_alerta', $tipoAlerta);
        }

        if ($soloNoLeidas) {
            $query->where('leida', false);
        }

        // Ordenar por fecha de alerta (más recientes primero)
        $alertas = $query->orderBy('fecha_alerta', 'desc')->get();

        // Estadísticas
        $estadisticas = [
            'total_alertas' => AlertaStock::where('sucursal_id', $sucursalSeleccionada)
                ->where('leida', false)
                ->count(),
            'STOCK_AGOTADO' => AlertaStock::where('sucursal_id', $sucursalSeleccionada)
                ->where('leida', false)
                ->where('tipo_alerta', 'STOCK_AGOTADO')
                ->count(),
            'STOCK_MINIMO' => AlertaStock::where('sucursal_id', $sucursalSeleccionada)
                ->where('leida', false)
                ->where('tipo_alerta', 'STOCK_MINIMO')
                ->count(),
            'PROXIMO_VENCER' => AlertaStock::where('sucursal_id', $sucursalSeleccionada)
                ->where('leida', false)
                ->where('tipo_alerta', 'PROXIMO_VENCER')
                ->count(),
        ];

        return view('alertas-stock.index', compact(
            'alertas',
            'sucursales',
            'sucursalSeleccionada',
            'estadisticas',
            'tipoAlerta',
            'soloNoLeidas'
        ));
    }

    /**
     * Marcar una alerta como leída
     */
    public function marcarLeida($id)
    {
        $alerta = AlertaStock::findOrFail($id);

        $alerta->update([
            'leida' => true,
            'fecha_lectura' => now(),
            'user_id_lectura' => Auth::id()
        ]);

        return back()->with('success', 'Alerta marcada como leída');
    }

    /**
     * Marcar todas las alertas como leídas
     */
    public function marcarTodasLeidas(Request $request)
    {
        $sucursalId = $request->get('sucursal_id');

        AlertaStock::where('sucursal_id', $sucursalId)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'fecha_lectura' => now(),
                'user_id_lectura' => Auth::id()
            ]);

        return back()->with('success', 'Todas las alertas han sido marcadas como leídas');
    }

    /**
     * Eliminar alertas antiguas
     */
    public function limpiarAlertas(Request $request)
    {
        $diasAntiguedad = $request->get('dias', 30);
        $sucursalId = $request->get('sucursal_id');

        $eliminadas = AlertaStock::where('sucursal_id', $sucursalId)
            ->where('leida', true)
            ->where('fecha_lectura', '<', now()->subDays($diasAntiguedad))
            ->delete();

        return back()->with('success', "Se eliminaron {$eliminadas} alertas antiguas");
    }

    /**
     * Obtener alertas no leídas (para notificaciones en tiempo real)
     */
    public function getAlertasNoLeidas(Request $request)
    {
        $sucursalId = $request->get('sucursal_id');

        $alertas = AlertaStock::with(['producto', 'sucursal'])
            ->where('sucursal_id', $sucursalId)
            ->where('leida', false)
            ->orderBy('fecha_alerta', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'total' => $alertas->count(),
            'alertas' => $alertas
        ]);
    }

    /**
     * Obtener sucursales del usuario según sus permisos
     */
    private function getSucursalesUsuario()
    {
        $user = Auth::user();

        // Si es superadmin, puede ver todas las sucursales
        if ($user->hasRole('Administrador')) {
            return Sucursal::where('estado', 1)->get();
        }

        // Sino, solo las sucursales asignadas
        return $user->sucursales()->where('estado', 1)->get();
    }
}
