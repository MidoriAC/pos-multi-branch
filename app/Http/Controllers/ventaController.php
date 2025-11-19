<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\Sucursal;
use App\Models\InventarioSucursal;
use App\Models\MovimientoInventario;
use App\Models\LogFel;
use App\Models\AnulacionFel;
use App\Models\Cotizacion;
use App\Models\SerieFel;
use App\Services\FELService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class VentaController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-venta|crear-venta|mostrar-venta|eliminar-venta', ['only' => ['index']]);
        $this->middleware('permission:crear-venta', ['only' => ['create', 'store']]);
        $this->middleware('permission:mostrar-venta', ['only' => ['show']]);
        $this->middleware('permission:eliminar-venta', ['only' => ['anular', 'storeAnulacion']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ventas = Venta::with([
            'comprobante',
            'cliente.persona',
            'user',
            'sucursal',
            'logFel',
            'anulacionFel'
        ])
        ->where('estado', 1)
        ->latest()
        ->get();

        // Estadísticas
        $estadisticas = [
            'total_ventas' => $ventas->count(),
            'monto_total' => $ventas->sum('total'),
            'ventas_hoy' => $ventas->where('fecha_hora', '>=', now()->startOfDay())->count(),
            'monto_hoy' => $ventas->where('fecha_hora', '>=', now()->startOfDay())->sum('total'),
            'ventas_fel' => $ventas->where('tipo_factura', 'FACT')->count(),
            'ventas_recibo' => $ventas->where('tipo_factura', 'RECI')->count()
        ];

        return view('venta.index', compact('ventas', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Obtener sucursal activa de la sesión
        $sucursalActiva = $this->getSucursalActiva();

        if (!$sucursalActiva) {
            return redirect()->route('ventas.index')
                ->with('error', 'No tiene una sucursal activa. Por favor seleccione una sucursal.');
        }

        // Obtener productos con stock en la sucursal activa
        $productos = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida'
        ])
        ->where('sucursal_id', $sucursalActiva->id)
        ->where('stock_actual', '>', 0)
        ->where('estado', 1)
        ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        $comprobantes = Comprobante::where('estado', 1)->get();

        // Si viene de una cotización
        $cotizacion = null;
        if ($request->has('cotizacion_id')) {
            $cotizacion = Cotizacion::with(['productos', 'cliente'])
                ->findOrFail($request->cotizacion_id);

            if ($cotizacion->estado !== 'PENDIENTE') {
                return redirect()->route('cotizaciones.index')
                    ->with('error', 'Esta cotización no puede convertirse en venta');
            }
        }

        // Verificar si la sucursal tiene configuración FEL activa
        $tieneFEL = $sucursalActiva->configuracionFel &&
                    $sucursalActiva->configuracionFel->estado == 1;

        return view('venta.create', compact(
            'productos',
            'clientes',
            'comprobantes',
            'sucursalActiva',
            'tieneFEL',
            'cotizacion'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVentaRequest $request)
    {
        try {
            DB::beginTransaction();

            // Obtener sucursal activa
            $sucursalActiva = $this->getSucursalActiva();

            if (!$sucursalActiva) {
                throw new Exception('No tiene una sucursal activa');
            }

            // Validar que tenga acceso a la sucursal
            if (!Auth::user()->sucursales->contains($sucursalActiva->id)) {
                throw new Exception('No tiene permisos para vender en esta sucursal');
            }

            // Generar número de comprobante según el tipo
            $tipoFactura = $request->tipo_factura;
            $numeroComprobante = null;
            $serie = null;

            if ($tipoFactura === 'FACT') {
                // Para FEL, obtener la serie activa
                $serieFel = SerieFel::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_documento', 'FACT')
                    ->where('estado', 1)
                    ->first();

                if (!$serieFel) {
                    throw new Exception('No hay una serie FEL configurada para esta sucursal');
                }

                // Incrementar número
                $serieFel->numero_actual += 1;

                // Validar límite si existe
                if ($serieFel->numero_final && $serieFel->numero_actual > $serieFel->numero_final) {
                    throw new Exception('Se ha alcanzado el límite de la serie FEL');
                }

                $serieFel->save();

                $serie = $serieFel->serie;
                $numeroComprobante = str_pad($serieFel->numero_actual, 8, '0', STR_PAD_LEFT);

            } else {
                // Para recibo simple, generar número secuencial
                $ultimoRecibo = Venta::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_factura', 'RECI')
                    ->orderBy('id', 'desc')
                    ->first();

                $numero = $ultimoRecibo ? (intval(substr($ultimoRecibo->numero_comprobante, -8)) + 1) : 1;
                $numeroComprobante = 'REC-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
            }

            // Crear la venta
            $venta = Venta::create([
                'sucursal_id' => $sucursalActiva->id,
                'cliente_id' => $request->cliente_id,
                'user_id' => Auth::id(),
                'comprobante_id' => 1,
                'fecha_hora' => now(),
                'numero_comprobante' => $numeroComprobante,
                'serie' => $serie,
                'impuesto' => $request->impuesto,
                'total' => $request->total,
                'tipo_factura' => $tipoFactura,
                'estado' => 1
            ]);

            // Recuperar arrays de productos
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento', []);

            $sizeArray = count($arrayProducto_id);

            // Procesar cada producto
            for ($i = 0; $i < $sizeArray; $i++) {
                $productoId = $arrayProducto_id[$i];
                $cantidad = $arrayCantidad[$i];
                $precioVenta = $arrayPrecioVenta[$i];
                $descuento = $arrayDescuento[$i] ?? 0;

                // Verificar stock en inventario de sucursal
                $inventario = InventarioSucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursalActiva->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventario) {
                    throw new Exception('El producto no existe en el inventario de esta sucursal');
                }

                if ($inventario->stock_actual < $cantidad) {
                    $producto = Producto::find($productoId);
                    throw new Exception("Stock insuficiente para: {$producto->nombre}. Stock disponible: {$inventario->stock_actual}");
                }

                // Registrar en tabla pivot venta_producto
                $venta->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_venta' => $precioVenta,
                    'descuento' => $descuento
                ]);

                // Descontar del inventario de la sucursal
                $inventario->stock_actual -= $cantidad;
                $inventario->save();

                // Actualizar stock general del producto
                // $producto = Producto::find($productoId);
                // $producto->stock -= $cantidad;
                // $producto->save();

                // Registrar movimiento de inventario
                MovimientoInventario::create([
                    'producto_id' => $productoId,
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $cantidad,
                    'sucursal_origen_id' => $sucursalActiva->id,
                    'venta_id' => $venta->id,
                    'motivo' => 'Venta - ' . $numeroComprobante,
                    'user_id' => Auth::id(),
                    'fecha_movimiento' => now()
                ]);
            }

            // Si viene de cotización, marcarla como convertida
            if ($request->cotizacion_id) {
                $cotizacion = Cotizacion::find($request->cotizacion_id);
                $cotizacion->update([
                    'estado' => 'CONVERTIDA',
                    'venta_id' => $venta->id,
                    'fecha_conversion' => now()
                ]);
            }

            // Si es FEL, certificar con el servicio
            if ($tipoFactura === 'FACT') {
                $felService = new FELService($sucursalActiva);
                $resultado = $felService->certificarFactura($venta);

                if ($resultado['success']) {
                    // Actualizar venta con datos FEL
                    $venta->update([
                        'numero_autorizacion_fel' => $resultado['uuid'],
                        'fecha_certificacion_fel' => $resultado['fecha_certificacion'],
                        'xml_fel' => $resultado['xml']
                    ]);

                    // Registrar en log FEL
                    LogFel::create([
                        'venta_id' => $venta->id,
                        'tipo_documento' => 'FACT',
                        'serie' => $serie,
                        'numero' => $numeroComprobante,
                        'uuid' => $resultado['uuid'],
                        'respuesta_certificador' => json_encode($resultado['respuesta']),
                        'estado' => 'CERTIFICADO',
                        'fecha_certificacion' => $resultado['fecha_certificacion'],
                        'intentos' => 1
                    ]);
                } else {
                    // Registrar error pero no revertir la venta
                    LogFel::create([
                        'venta_id' => $venta->id,
                        'tipo_documento' => 'FACT',
                        'serie' => $serie,
                        'numero' => $numeroComprobante,
                        'respuesta_certificador' => json_encode($resultado['error']),
                        'estado' => 'ERROR',
                        'intentos' => 1
                    ]);

                    DB::commit();

                    return redirect()->route('ventas.show', $venta->id)
                        ->with('warning', 'Venta registrada pero hubo un error al certificar FEL: ' . $resultado['error']);
                }
            }

            DB::commit();

            // Redirigir según el tipo de factura
            $mensaje = $tipoFactura === 'FACT'
                ? 'Venta FEL registrada y certificada exitosamente'
                : 'Venta registrada exitosamente';

            return redirect()->route('ventas.show', $venta->id)
                ->with('success', $mensaje);

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error en store venta: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al registrar la venta: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'productos.marca.caracteristica',
            'productos.presentacione.caracteristica',
            'productos.unidadMedida',
            'cliente.persona',
            'user',
            'comprobante',
            'sucursal',
            'logFel',
            'anulacionFel'
        ]);

        return view('venta.show', compact('venta'));
    }

    /**
     * Generar PDF de la venta
     */
    public function generarPDF(Venta $venta)
    {
        $venta->load([
            'productos',
            'cliente.persona',
            'user',
            'comprobante',
            'sucursal',
            'logFel'
        ]);

        if ($venta->tipo_factura === 'FACT') {
            $pdf = Pdf::loadView('venta.fel', compact('venta'));
        } else {
            $pdf = Pdf::loadView('venta.pdf-recibo', compact('venta'));
        }

        $pdf->setPaper('letter');

        return $pdf->stream('venta-' . $venta->numero_comprobante . '.pdf');
    }

    /**
     * Descargar XML de factura FEL
     */
    public function descargarXML(Venta $venta)
    {
        if (!$venta->esFEL() || !$venta->xml_fel) {
            abort(404, 'XML no disponible');
        }

        $filename = 'FEL-' . $venta->numero_comprobante . '.xml';

        return response($venta->xml_fel, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Mostrar formulario de anulación
     */
    public function anular(Venta $venta)
    {
        if (!$venta->puedeAnularse()) {
            return redirect()->route('ventas.index')
                ->with('error', 'Esta venta no puede ser anulada');
        }

        // Verificar tiempo límite de anulación (configurable)
        $diasLimite = config('ventas.dias_limite_anulacion', 3);
        $fechaLimite = Carbon::parse($venta->fecha_hora)->addDays($diasLimite);

        if (now()->gt($fechaLimite)) {
            return redirect()->route('ventas.index')
                ->with('error', "No se puede anular. Han pasado más de {$diasLimite} días desde la venta");
        }

        return view('venta.anular', compact('venta', 'diasLimite'));
    }

    /**
     * Procesar anulación de venta
     */
    public function storeAnulacion(Request $request, Venta $venta)
    {
        $request->validate([
            'motivo_anulacion' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Verificar nuevamente el tiempo límite
            $diasLimite = config('ventas.dias_limite_anulacion', 3);
            $fechaLimite = Carbon::parse($venta->fecha_hora)->addDays($diasLimite);

            if (now()->gt($fechaLimite)) {
                throw new Exception("No se puede anular. Han pasado más de {$diasLimite} días");
            }

            // Si es FEL, anular en el certificador
            if ($venta->esFEL()) {
                $felService = new FELService($venta->sucursal);
                $resultado = $felService->anularFactura($venta, $request->motivo_anulacion);

                if (!$resultado['success']) {
                    throw new Exception('Error al anular en FEL: ' . $resultado['error']);
                }

                // Registrar anulación FEL
                AnulacionFel::create([
                    'venta_id' => $venta->id,
                    'uuid_documento_anular' => $venta->numero_autorizacion_fel,
                    'uuid_anulacion' => $resultado['uuid_anulacion'] ?? null,
                    'motivo' => $request->motivo_anulacion,
                    'fecha_anulacion' => now(),
                    'user_id' => Auth::id(),
                    'estado' => 'CERTIFICADO'
                ]);

                // Actualizar log FEL
                if ($venta->logFel) {
                    $venta->logFel->update(['estado' => 'ANULADO']);
                }
            }

            // Revertir inventario
            foreach ($venta->productos as $producto) {
                // Devolver al inventario de sucursal
                $inventario = InventarioSucursal::where('producto_id', $producto->id)
                    ->where('sucursal_id', $venta->sucursal_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventario) {
                    $inventario->stock_actual += $producto->pivot->cantidad;
                    $inventario->save();
                }

                // Devolver al stock general
                $producto->stock += $producto->pivot->cantidad;
                $producto->save();

                // Registrar movimiento
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'DEVOLUCION',
                    'cantidad' => $producto->pivot->cantidad,
                    'sucursal_destino_id' => $venta->sucursal_id,
                    'venta_id' => $venta->id,
                    'motivo' => 'Anulación de venta: ' . $request->motivo_anulacion,
                    'user_id' => Auth::id(),
                    'fecha_movimiento' => now()
                ]);
            }

            // Marcar venta como anulada (no se elimina)
            $venta->update(['estado' => 0]);

            DB::commit();

            return redirect()->route('ventas.index')
                ->with('success', 'Venta anulada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error al anular venta: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al anular la venta: ' . $e->getMessage());
        }
    }

    /**
     * Obtener stock de producto en sucursal (AJAX)
     */
    public function getStockSucursal(Request $request)
    {
        $sucursalActiva = $this->getSucursalActiva();

        if (!$sucursalActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursal activa'
            ], 400);
        }

        $inventario = InventarioSucursal::with(['producto', 'ubicacion'])
            ->where('producto_id', $request->producto_id)
            ->where('sucursal_id', $sucursalActiva->id)
            ->first();

        if ($inventario) {
            $bajoCantidad = $inventario->stock_actual <= $inventario->stock_minimo;

            return response()->json([
                'success' => true,
                'stock' => $inventario->stock_actual,
                'stock_minimo' => $inventario->stock_minimo,
                'stock_maximo' => $inventario->stock_maximo,
                'precio_venta' => $inventario->precio_venta,
                'bajo_stock' => $bajoCantidad,
                'ubicacion' => $inventario->ubicacion ? $inventario->ubicacion->codigo : null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Producto no encontrado en esta sucursal'
        ], 404);
    }

    /**
     * Obtener sucursal activa de la sesión
     */
 private function getSucursalActiva()
{
    $sucursalId = session('sucursal_id');

    // Si ya hay sucursal en sesión, devolverla
    if ($sucursalId) {
        return \App\Models\Sucursal::find($sucursalId);
    }

    // Si no hay sucursal en sesión, intentar obtener la principal del usuario
    $user = \Illuminate\Support\Facades\Auth::user();

    if (!$user) {
        return null; // No hay usuario autenticado
    }

    // Buscar la sucursal principal asignada al usuario
    $sucursal = $user->sucursales()
        ->wherePivot('es_principal', 1)
        ->first();

    // Si tiene una sucursal principal, establecerla como activa
    if ($sucursal) {
        session([
            'sucursal_id' => $sucursal->id,
            'sucursal_nombre' => $sucursal->nombre,
            'sucursal_codigo' => $sucursal->codigo,
        ]);
        return $sucursal;
    }

    // Si el usuario no tiene sucursal principal, asignarle la primera disponible
    $sucursal = $user->sucursales()->first();

    if ($sucursal) {
        session([
            'sucursal_id' => $sucursal->id,
            'sucursal_nombre' => $sucursal->nombre,
            'sucursal_codigo' => $sucursal->codigo,
        ]);
        return $sucursal;
    }

    // Si no tiene ninguna sucursal asignada
    return null;
}


    /**
     * Reintentar certificación FEL
     */
    public function reintentarCertificacion(Venta $venta)
    {
        if (!$venta->esFEL()) {
            return redirect()->back()
                ->with('error', 'Esta venta no es una factura FEL');
        }

        if ($venta->numero_autorizacion_fel) {
            return redirect()->back()
                ->with('info', 'Esta factura ya está certificada');
        }

        try {
            $felService = new FELService($venta->sucursal);
            $resultado = $felService->certificarFactura($venta);

            if ($resultado['success']) {
                $venta->update([
                    'numero_autorizacion_fel' => $resultado['uuid'],
                    'fecha_certificacion_fel' => $resultado['fecha_certificacion'],
                    'xml_fel' => $resultado['xml']
                ]);

                if ($venta->logFel) {
                    $venta->logFel->update([
                        'uuid' => $resultado['uuid'],
                        'estado' => 'CERTIFICADO',
                        'fecha_certificacion' => $resultado['fecha_certificacion'],
                        'respuesta_certificador' => json_encode($resultado['respuesta']),
                        'intentos' => $venta->logFel->intentos + 1
                    ]);
                }

                return redirect()->back()
                    ->with('success', 'Factura certificada exitosamente');
            } else {
                if ($venta->logFel) {
                    $venta->logFel->increment('intentos');
                }

                return redirect()->back()
                    ->with('error', 'Error al certificar: ' . $resultado['error']);
            }
        } catch (Exception $e) {
            \Log::error('Error al reintentar certificación: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al reintentar certificación: ' . $e->getMessage());
        }
    }
}
