<?php
// app/Observers/InventarioSucursalObserver.php

namespace App\Observers;

use App\Models\InventarioSucursal;
use App\Models\AlertaStock;
use Illuminate\Support\Facades\Log;

class InventarioSucursalObserver
{
    /**
     * Handle the InventarioSucursal "updated" event.
     */
    public function updated(InventarioSucursal $inventario)
    {
        $this->verificarStockMinimo($inventario);
    }

    /**
     * Handle the InventarioSucursal "saved" event.
     */
    public function saved(InventarioSucursal $inventario)
    {
        $this->verificarStockMinimo($inventario);
    }

    /**
     * Verifica si el stock está por debajo del mínimo y genera alerta
     */
    private function verificarStockMinimo(InventarioSucursal $inventario)
    {
        // Solo verificar si el stock mínimo está configurado
        if ($inventario->stock_minimo <= 0) {
            return;
        }

        // Determinar el tipo de alerta
        $tipoAlerta = $this->determinarTipoAlerta($inventario);

        if ($tipoAlerta) {
            // Verificar si ya existe una alerta sin leer para este producto y sucursal
            $alertaExistente = AlertaStock::where('producto_id', $inventario->producto_id)
                ->where('sucursal_id', $inventario->sucursal_id)
                ->where('leida', false)
                ->first();

            if ($alertaExistente) {
                // Actualizar la alerta existente
                $alertaExistente->update([
                    'tipo_alerta' => $tipoAlerta,
                    'stock_actual' => $inventario->stock_actual,
                    'stock_minimo' => $inventario->stock_minimo,
                    'fecha_alerta' => now()
                ]);
            } else {
                // Crear nueva alerta
                AlertaStock::create([
                    'producto_id' => $inventario->producto_id,
                    'sucursal_id' => $inventario->sucursal_id,
                    'tipo_alerta' => $tipoAlerta,
                    'stock_actual' => $inventario->stock_actual,
                    'stock_minimo' => $inventario->stock_minimo,
                    'fecha_alerta' => now(),
                    'leida' => false
                ]);

                Log::info("Alerta de stock generada: Producto {$inventario->producto->nombre} en sucursal {$inventario->sucursal->nombre}");
            }
        } else {
            // Si el stock está normal, marcar alertas existentes como leídas
            AlertaStock::where('producto_id', $inventario->producto_id)
                ->where('sucursal_id', $inventario->sucursal_id)
                ->where('leida', false)
                ->update(['leida' => true, 'fecha_lectura' => now()]);
        }
    }

    /**
     * Determina el tipo de alerta según el nivel de stock
     */
    private function determinarTipoAlerta(InventarioSucursal $inventario): ?string
    {
        if ($inventario->stock_actual == 0) {
            return 'STOCK_AGOTADO';
        }

        if ($inventario->stock_actual <= $inventario->stock_minimo) {
            return 'STOCK_MINIMO';
        }

        // Alerta preventiva cuando está cerca del mínimo (10% de margen)
        $margenPreventivo = $inventario->stock_minimo * 1.1;
        if ($inventario->stock_actual <= $margenPreventivo) {
            return 'PROXIMO_VENCER';
        }

        return null; // Stock normal
    }
}
