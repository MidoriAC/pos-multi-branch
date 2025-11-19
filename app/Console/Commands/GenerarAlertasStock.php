<?php
// app/Console/Commands/GenerarAlertasStock.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventarioSucursal;
use App\Models\AlertaStock;

class GenerarAlertasStock extends Command
{
    protected $signature = 'alertas:generar-stock';
    protected $description = 'Genera alertas de stock para productos bajo mínimo';

    public function handle()
    {
        $this->info('Generando alertas de stock...');

        $inventarios = InventarioSucursal::with(['producto', 'sucursal'])
            ->where('STOCK_MINIMO', '>', 0)
            ->get();

        $alertasGeneradas = 0;
        $alertasActualizadas = 0;

        foreach ($inventarios as $inventario) {
            $tipoAlerta = null;

            if ($inventario->stock_actual == 0) {
                $tipoAlerta = 'STOCK_AGOTADO';
            } elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                $tipoAlerta = 'STOCK_MINIMO';
            } elseif ($inventario->stock_actual <= ($inventario->stock_minimo * 1.1)) {
                $tipoAlerta = 'PROXIMO_VENCER';
            }

            if ($tipoAlerta) {
                $alertaExistente = AlertaStock::where('producto_id', $inventario->producto_id)
                    ->where('sucursal_id', $inventario->sucursal_id)
                    ->where('leida', false)
                    ->first();

                if ($alertaExistente) {
                    $alertaExistente->update([
                        'tipo_alerta' => $tipoAlerta,
                        'stock_actual' => $inventario->stock_actual,
                        'stock_minimo' => $inventario->stock_minimo,
                        'fecha_alerta' => now()
                    ]);
                    $alertasActualizadas++;
                } else {
                    AlertaStock::create([
                        'producto_id' => $inventario->producto_id,
                        'sucursal_id' => $inventario->sucursal_id,
                        'tipo_alerta' => $tipoAlerta,
                        'stock_actual' => $inventario->stock_actual,
                        'stock_minimo' => $inventario->stock_minimo,
                        'fecha_alerta' => now(),
                        'leida' => false
                    ]);
                    $alertasGeneradas++;
                }
            }
        }

        $this->info("✓ Alertas generadas: {$alertasGeneradas}");
        $this->info("✓ Alertas actualizadas: {$alertasActualizadas}");
        $this->info('Proceso completado exitosamente.');

        return 0;
    }
}
