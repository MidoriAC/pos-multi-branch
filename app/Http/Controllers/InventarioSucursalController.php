<?php

namespace App\Http\Controllers;

use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InventarioSucursalController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-inventario|ajustar-inventario|transferir-inventario', ['only' => ['index']]);
        $this->middleware('permission:ajustar-inventario', ['only' => ['ajustar', 'storeAjuste']]);
        $this->middleware('permission:transferir-inventario', ['only' => ['transferir', 'storeTransferencia']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::where('estado', 1)->get();
        $sucursalSeleccionada = $request->get('sucursal_id', $sucursales->first()->id ?? null);

        $inventarios = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida',
            'sucursal',
            'ubicacion'
        ])
        ->where('sucursal_id', $sucursalSeleccionada)
        ->where('estado', 1)
        ->get();

        // Calcular estadísticas
        $estadisticas = [
            'total_productos' => $inventarios->count(),
            'stock_total' => $inventarios->sum('stock_actual'),
            'valor_inventario' => $inventarios->sum(function($item) {
                return $item->stock_actual * $item->precio_venta;
            }),
            'productos_bajo_minimo' => $inventarios->filter(function($item) {
                return $item->stock_actual <= $item->stock_minimo;
            })->count(),
            'productos_sin_stock' => $inventarios->where('stock_actual', 0)->count()
        ];

        return view('inventario-sucursal.index', compact('inventarios', 'sucursales', 'sucursalSeleccionada', 'estadisticas'));
    }

    /**
     * Mostrar formulario de ajuste de inventario
     */
    public function ajustar($id)
    {
        $inventario = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida',
            'sucursal',
            'ubicacion'
        ])->findOrFail($id);

        return view('inventario-sucursal.ajustar', compact('inventario'));
    }

    /**
     * Procesar ajuste de inventario
     */
    public function storeAjuste(Request $request, $id)
    {
        $request->validate([
            'tipo_ajuste' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $inventario = InventarioSucursal::findOrFail($id);
            $cantidadAnterior = $inventario->stock_actual;

            // Calcular nuevo stock
            if ($request->tipo_ajuste == 'entrada') {
                $nuevoStock = $cantidadAnterior + $request->cantidad;
                $tipoMovimiento = 'ajuste_entrada';
            } else {
                if ($cantidadAnterior < $request->cantidad) {
                    return redirect()->back()
                        ->with('error', 'No hay suficiente stock para realizar esta salida')
                        ->withInput();
                }
                $nuevoStock = $cantidadAnterior - $request->cantidad;
                $tipoMovimiento = 'ajuste_salida';
            }

            // Actualizar inventario
            $inventario->update([
                'stock_actual' => $nuevoStock
            ]);

            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $inventario->producto_id,
                'tipo_movimiento' => $tipoMovimiento,
                'cantidad' => $request->cantidad,
                'sucursal_origen_id' => $inventario->sucursal_id,
                'ubicacion_origen_id' => $inventario->ubicacion_id,
                'motivo' => $request->motivo,
                'user_id' => Auth::id(),
                'fecha_movimiento' => now()
            ]);

            DB::commit();

            return redirect()->route('inventario-sucursal.index', ['sucursal_id' => $inventario->sucursal_id])
                ->with('success', 'Ajuste de inventario realizado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al realizar el ajuste: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar formulario de transferencia entre sucursales
     */
    public function transferir()
    {
        $sucursales = Sucursal::where('estado', 1)->get();

        return view('inventario-sucursal.transferir', compact('sucursales'));
    }

    /**
     * Procesar transferencia entre sucursales
     */
    public function storeTransferencia(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'sucursal_origen_id' => 'required|exists:sucursales,id|different:sucursal_destino_id',
            'sucursal_destino_id' => 'required|exists:sucursales,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:500',
            'ubicacion_destino_id' => 'nullable|exists:ubicaciones,id'
        ]);

        try {
            DB::beginTransaction();

            // Verificar inventario origen
            $inventarioOrigen = InventarioSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $request->sucursal_origen_id)
                ->first();

            if (!$inventarioOrigen) {
                return redirect()->back()
                    ->with('error', 'El producto no existe en la sucursal origen')
                    ->withInput();
            }

            if ($inventarioOrigen->stock_actual < $request->cantidad) {
                return redirect()->back()
                    ->with('error', 'Stock insuficiente en sucursal origen. Stock actual: ' . $inventarioOrigen->stock_actual)
                    ->withInput();
            }

            // Descontar de sucursal origen
            $inventarioOrigen->stock_actual -= $request->cantidad;
            $inventarioOrigen->save();

            // Buscar o crear inventario destino
            $inventarioDestino = InventarioSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $request->sucursal_destino_id)
                ->first();

            if ($inventarioDestino) {
                $inventarioDestino->stock_actual += $request->cantidad;
                $inventarioDestino->save();
            } else {
                // Crear nuevo registro de inventario en destino
                $inventarioDestino = InventarioSucursal::create([
                    'producto_id' => $request->producto_id,
                    'sucursal_id' => $request->sucursal_destino_id,
                    'ubicacion_id' => $request->ubicacion_destino_id,
                    'stock_actual' => $request->cantidad,
                    'stock_minimo' => $inventarioOrigen->stock_minimo,
                    'stock_maximo' => $inventarioOrigen->stock_maximo,
                    'precio_venta' => $inventarioOrigen->precio_venta,
                    'estado' => 1
                ]);
            }

            // Registrar movimiento
            MovimientoInventario::create([
                'producto_id' => $request->producto_id,
                'tipo_movimiento' => 'transferencia',
                'cantidad' => $request->cantidad,
                'sucursal_origen_id' => $request->sucursal_origen_id,
                'sucursal_destino_id' => $request->sucursal_destino_id,
                'ubicacion_origen_id' => $inventarioOrigen->ubicacion_id,
                'ubicacion_destino_id' => $request->ubicacion_destino_id,
                'motivo' => $request->motivo,
                'user_id' => Auth::id(),
                'fecha_movimiento' => now()
            ]);

            DB::commit();

            return redirect()->route('inventario-sucursal.index', ['sucursal_id' => $request->sucursal_origen_id])
                ->with('success', 'Transferencia realizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al realizar la transferencia: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Obtener inventario de un producto en sucursal (AJAX)
     */
    public function getInventario(Request $request)
    {
        $inventario = InventarioSucursal::with(['producto', 'ubicacion'])
            ->where('producto_id', $request->producto_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->first();

        if ($inventario) {
            return response()->json([
                'success' => true,
                'data' => $inventario
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Producto no encontrado en esta sucursal'
        ]);
    }

    /**
     * Obtener productos con stock en una sucursal (AJAX)
     */
    public function getProductosSucursal($sucursalId)
    {
        $productos = InventarioSucursal::with(['producto'])
            ->where('sucursal_id', $sucursalId)
            ->where('stock_actual', '>', 0)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->producto_id,
                    'codigo' => $item->producto->codigo,
                    'nombre' => $item->producto->nombre,
                    'stock' => $item->stock_actual
                ];
            });

        return response()->json($productos);
    }

    /**
     * Ver historial de movimientos
     */
    public function historial($id)
    {
        $inventario = InventarioSucursal::with(['producto', 'sucursal'])->findOrFail($id);

        $movimientos = MovimientoInventario::with([
            'usuario',
            'sucursalOrigen',
            'sucursalDestino',
            'compra',
            'venta'
        ])
        ->where(function($query) use ($inventario) {
            $query->where('producto_id', $inventario->producto_id)
                  ->where(function($q) use ($inventario) {
                      $q->where('sucursal_origen_id', $inventario->sucursal_id)
                        ->orWhere('sucursal_destino_id', $inventario->sucursal_id);
                  });
        })
        ->orderBy('fecha_movimiento', 'desc')
        ->limit(50)
        ->get();

        return view('inventario-sucursal.historial', compact('inventario', 'movimientos'));
    }

    /**
     * Actualizar configuración de stock (mínimo, máximo, precio)
     */
    public function updateConfig(Request $request, $id)
    {
        $request->validate([
            'stock_minimo' => 'required|integer|min:0',
            'stock_maximo' => 'required|integer|min:0|gte:stock_minimo',
            'precio_venta' => 'required|numeric|min:0',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id'
        ]);

        try {
            $inventario = InventarioSucursal::findOrFail($id);

            $inventario->update([
                'stock_minimo' => $request->stock_minimo,
                'stock_maximo' => $request->stock_maximo,
                'precio_venta' => $request->precio_venta,
                'ubicacion_id' => $request->ubicacion_id
            ]);

            return redirect()->back()
                ->with('success', 'Configuración actualizada exitosamente');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }
}
