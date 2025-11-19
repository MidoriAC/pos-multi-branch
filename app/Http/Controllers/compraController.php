<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Proveedore;
use App\Models\Sucursal;
use App\Models\Ubicacion;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CompraController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-compra|crear-compra|mostrar-compra|eliminar-compra', ['only' => ['index']]);
        $this->middleware('permission:crear-compra', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-compra', ['only' => ['show']]);
        $this->middleware('permission:eliminar-compra', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compras = Compra::with([
            'comprobante',
            'proveedore.persona',
            'sucursal',
            'usuario'
        ])
        ->where('estado', 1)
        ->latest()
        ->get();

        // Calcular estadísticas
        $estadisticas = [
            'total_compras' => $compras->count(),
            'monto_total' => $compras->sum('total'),
            'compras_hoy' => $compras->where('fecha_hora', '>=', now()->startOfDay())->count(),
            'monto_hoy' => $compras->where('fecha_hora', '>=', now()->startOfDay())->sum('total')
        ];

        return view('compra.index', compact('compras', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedore::whereHas('persona', function($query) {
            $query->where('estado', 1);
        })->get();

        $comprobantes = Comprobante::where('estado', 1)->get();

        $productos = Producto::where('estado', 1)
            ->with(['marca.caracteristica', 'presentacione.caracteristica', 'unidadMedida'])
            ->get();

        $sucursales = Sucursal::where('estado', 1)->get();

        return view('compra.create', compact('proveedores', 'comprobantes', 'productos', 'sucursales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompraRequest $request)
    {
        // dd($request->all());
        \Log::info('Entró al store');
        try {
            \Log::info('Entró al try del store');
            DB::beginTransaction();

            // Crear la compra
            $compra = Compra::create([
                'sucursal_id' => $request->sucursal_id,
                'proveedore_id' => $request->proveedore_id,
                'comprobante_id' => $request->comprobante_id,
                'numero_comprobante' => $request->numero_comprobante,
                'fecha_hora' => $request->fecha_hora,
                'impuesto' => $request->impuesto,
                'total' => $request->total,
                'user_id' => Auth::id(),
                'estado' => 1
            ]);

            // Recuperar los arrays
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioCompra = $request->get('arraypreciocompra');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayUbicacion = $request->get('arrayubicacion', []);

            // Procesar cada producto
            $sizeArray = count($arrayProducto_id);

            for ($i = 0; $i < $sizeArray; $i++) {
                $productoId = $arrayProducto_id[$i];
                $cantidad = $arrayCantidad[$i];
                $precioCompra = $arrayPrecioCompra[$i];
                $precioVenta = $arrayPrecioVenta[$i];
                $ubicacionId = $arrayUbicacion[$i] ?? null;

                // 1. Registrar en la tabla pivot compra_producto
                $compra->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta
                ]);

                // 2. Actualizar o crear inventario en la sucursal
                $inventario = InventarioSucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $request->sucursal_id)
                    ->first();

                if ($inventario) {
                    // Actualizar inventario existente
                    $inventario->stock_actual += $cantidad;
                    $inventario->precio_venta = $precioVenta; // Actualizar precio de venta
                    if ($ubicacionId) {
                        $inventario->ubicacion_id = $ubicacionId;
                    }
                    $inventario->save();
                } else {
                    // Crear nuevo inventario
                    InventarioSucursal::create([
                        'producto_id' => $productoId,
                        'sucursal_id' => $request->sucursal_id,
                        'ubicacion_id' => $ubicacionId,
                        'stock_actual' => $cantidad,
                        'stock_minimo' => 10, // Valor por defecto
                        'stock_maximo' => 100, // Valor por defecto
                        'precio_venta' => $precioVenta,
                        'estado' => 1
                    ]);
                }

                // 3. Registrar movimiento de inventario
                MovimientoInventario::create([
                    'producto_id' => $productoId,
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $cantidad,
                    'sucursal_destino_id' => $request->sucursal_id,
                    'ubicacion_destino_id' => $ubicacionId,
                    'compra_id' => $compra->id,
                    'motivo' => 'Compra #' . $compra->numero_comprobante,
                    'user_id' => Auth::id(),
                    'fecha_movimiento' => now()
                ]);
            }

            DB::commit();

             \Log::info('Terminó correctamente el store');
            return redirect()->route('compras.index')
                ->with('success', 'Compra registrada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
              \Log::error('Error en store: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al registrar la compra: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        $compra->load([
            'comprobante',
            'proveedore.persona',
            'sucursal',
            'usuario',
            'productos.marca.caracteristica',
            'productos.presentacione.caracteristica',
            'productos.unidadMedida'
        ]);

        return view('compra.show', compact('compra'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $compra = Compra::findOrFail($id);

            // Verificar si se puede eliminar (no debería revertir el inventario automáticamente)
            // La eliminación es lógica, el inventario se mantiene

            $compra->update(['estado' => 0]);

            DB::commit();

            return redirect()->route('compras.index')
                ->with('success', 'Compra anulada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al anular la compra: ' . $e->getMessage());
        }
    }

    /**
     * Generar PDF de la compra
     */
    public function pdf($id)
    {
        $compra = Compra::with([
            'comprobante',
            'proveedore.persona',
            'sucursal',
            'usuario',
            'productos.marca.caracteristica',
            'productos.presentacione.caracteristica',
            'productos.unidadMedida'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('compra.pdf', compact('compra'));

        return $pdf->stream('compra-' . $compra->numero_comprobante . '.pdf');
    }

    /**
     * Obtener ubicaciones de una sucursal (AJAX)
     */
    public function getUbicaciones($sucursalId)
    {
        $ubicaciones = Ubicacion::where('sucursal_id', $sucursalId)
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre']);

        return response()->json($ubicaciones);
    }
}
