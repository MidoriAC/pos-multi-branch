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
        // ->where('estado', 1)
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

    public function store(StoreVentaRequest $request)
    {
        // Variable para guardar la venta creada y usarla fuera del try/catch de DB
        $ventaGuardada = null;
        $sucursalActiva = null;

        // ---------------------------------------------------------
        // PASO 1: CREACIÓN LOCAL DE LA VENTA (Transacción Rápida)
        // ---------------------------------------------------------
        try {
            DB::beginTransaction();

            $sucursalActiva = $this->getSucursalActiva();

            if (!$sucursalActiva) {
                throw new Exception('No tiene una sucursal activa');
            }

            if (!Auth::user()->sucursales->contains($sucursalActiva->id)) {
                throw new Exception('No tiene permisos para vender en esta sucursal');
            }

            $tipoFactura = $request->tipo_factura;

            // ... [Lógica de Series y Recibo se mantiene igual] ...
            $numeroComprobante = null;
            $serie = null;

            if ($tipoFactura === 'FACT') {
                $serieFel = SerieFel::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_documento', 'FACT')
                    ->where('estado', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$serieFel) throw new Exception('No hay serie FEL configurada');

                $serieFel->numero_actual += 1;
                $serieFel->save();
                $serie = $serieFel->serie;
                $numeroComprobante = str_pad($serieFel->numero_actual, 8, '0', STR_PAD_LEFT);
            } else {
                // Lógica Recibo
                $ultimoRecibo = Venta::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_factura', 'RECI')
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();
                $numero = $ultimoRecibo ? (intval(substr($ultimoRecibo->numero_comprobante, -8)) + 1) : 1;
                $numeroComprobante = 'REC-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
            }

            // Crear Venta
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

            // Procesar Productos
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento', []);

            for ($i = 0; $i < count($arrayProducto_id); $i++) {
                $productoId = $arrayProducto_id[$i];
                $cantidad = $arrayCantidad[$i];
                $precioVenta = $arrayPrecioVenta[$i];
                $descuento = $arrayDescuento[$i] ?? 0;

                $inventario = InventarioSucursal::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursalActiva->id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventario || $inventario->stock_actual < $cantidad) {
                    throw new Exception("Stock insuficiente para el producto ID: $productoId");
                }

                $venta->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_venta' => $precioVenta,
                    'descuento' => $descuento
                ]);

                $inventario->stock_actual -= $cantidad;
                $inventario->save();

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

            if ($request->cotizacion_id) {
                $cotizacion = Cotizacion::find($request->cotizacion_id);
                if($cotizacion) $cotizacion->update(['estado' => 'CONVERTIDA', 'venta_id' => $venta->id]);
            }

            DB::commit(); // <--- AQUÍ CERRAMOS LA CONEXIÓN DB PARA EVITAR EL ERROR 2006

            $ventaGuardada = $venta; // Guardamos la instancia para usarla abajo

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear venta local: ' . $e->getMessage())->withInput();
        }

        // ---------------------------------------------------------
        // PASO 2: CERTIFICACIÓN FEL (Fuera de la transacción principal)
        // ---------------------------------------------------------
        if ($request->tipo_factura === 'FACT' && $ventaGuardada) {
            try {
                $felService = new FELService($sucursalActiva);
                $resultado = $felService->certificarFactura($ventaGuardada);

                if ($resultado['success']) {
                    // Si Laravel perdió la conexión durante la espera, update() intentará reconectar automáticamente
                    $ventaGuardada->update([
                        'numero_autorizacion_fel' => $resultado['uuid'],
                        'fecha_certificacion_fel' => $resultado['fecha_certificacion'],
                        'xml_fel' => $resultado['xml_certificado'],
                        'serie' => $resultado['serie'],
                        'numero_comprobante' => $resultado['numero']
                    ]);

                    // Limpieza para Log
                    $respuestaLimpia = $resultado['respuesta_completa'];
                    unset($respuestaLimpia['responseData1'], $respuestaLimpia['responseData2'], $respuestaLimpia['responseData3']);

                    LogFel::create([
                        'venta_id' => $ventaGuardada->id,
                        'tipo_documento' => 'FACT',
                        'serie' => $resultado['serie'],
                        'numero' => $resultado['numero'],
                        'uuid' => $resultado['uuid'],
                        'respuesta_certificador' => json_encode($respuestaLimpia),
                        'estado' => 'CERTIFICADO',
                        'fecha_certificacion' => $resultado['fecha_certificacion'],
                        'intentos' => 1
                    ]);

                } else {
                    // FALLO FEL: REVERTIR LA VENTA QUE YA HABÍAMOS GUARDADO
                    $errorMsg = $resultado['error'] ?? 'Desconocido';
                    $this->revertirVentaLocal($ventaGuardada, $errorMsg);

                    return redirect()->back()
                        ->with('error', 'La venta NO se realizó. Falló la certificación FEL: ' . $errorMsg)
                        ->withInput();
                }

            } catch (Exception $e) {
                // FALLO CRÍTICO (Timeout, Conexión, etc): REVERTIR
                $this->revertirVentaLocal($ventaGuardada, $e->getMessage());

                return redirect()->back()
                    ->with('error', 'Error de comunicación FEL (Timeout). La venta ha sido revertida. Intente de nuevo.')
                    ->withInput();
            }
        }

        // Éxito final
        return redirect()->route('ventas.show', $ventaGuardada->id)
            ->with('success', 'Venta registrada exitosamente');
    }

    /**
     * Store a newly created resource in storage.
     */
  public function storeORIGIANL(StoreVentaRequest $request)
    {

        $ventaGuardada = null;
        $sucursalActiva = null;

        try {
            DB::beginTransaction();

            $sucursalActiva = $this->getSucursalActiva();

            if (!$sucursalActiva) {
                throw new Exception('No tiene una sucursal activa');
            }

            if (!Auth::user()->sucursales->contains($sucursalActiva->id)) {
                throw new Exception('No tiene permisos para vender en esta sucursal');
            }

            $tipoFactura = $request->tipo_factura;
            $numeroComprobante = null;
            $serie = null;

            // Manejo de Correlativos Internos
            // NOTA: Aunque Digifact asigna su propia Serie/Numero, es bueno mantener un control interno
            // hasta que se certifique.
            if ($tipoFactura === 'FACT') {
                $serieFel = SerieFel::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_documento', 'FACT')
                    ->where('estado', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$serieFel) {
                    throw new Exception('No hay una serie FEL configurada para esta sucursal');
                }

                $serieFel->numero_actual += 1;

                if ($serieFel->numero_final && $serieFel->numero_actual > $serieFel->numero_final) {
                    throw new Exception('Se ha alcanzado el límite de la serie FEL interna');
                }

                $serieFel->save();

                $serie = $serieFel->serie;
                $numeroComprobante = str_pad($serieFel->numero_actual, 8, '0', STR_PAD_LEFT);

            } else {
                $ultimoRecibo = Venta::where('sucursal_id', $sucursalActiva->id)
                    ->where('tipo_factura', 'RECI')
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();

                $numero = $ultimoRecibo ? (intval(substr($ultimoRecibo->numero_comprobante, -8)) + 1) : 1;
                $numeroComprobante = 'REC-' . str_pad($numero, 8, '0', STR_PAD_LEFT);
            }

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

            // Procesamiento de Productos
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento', []);

            $sizeArray = count($arrayProducto_id);

            for ($i = 0; $i < $sizeArray; $i++) {
                $productoId = $arrayProducto_id[$i];
                $cantidad = $arrayCantidad[$i];
                $precioVenta = $arrayPrecioVenta[$i];
                $descuento = $arrayDescuento[$i] ?? 0;

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

                $venta->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_venta' => $precioVenta,
                    'descuento' => $descuento
                ]);

                $inventario->stock_actual -= $cantidad;
                $inventario->save();

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

            if ($request->cotizacion_id) {
                $cotizacion = Cotizacion::find($request->cotizacion_id);
                $cotizacion->update([
                    'estado' => 'CONVERTIDA',
                    'venta_id' => $venta->id,
                    'fecha_conversion' => now()
                ]);
            }

            // --- LÓGICA FEL ---
         if ($tipoFactura === 'FACT') {
                $felService = new FELService($sucursalActiva);
                $resultado = $felService->certificarFactura($venta);

                if ($resultado['success']) {
                    // ÉXITO: Actualizamos venta y Log
                    $venta->update([
                        'numero_autorizacion_fel' => $resultado['uuid'],
                        'fecha_certificacion_fel' => $resultado['fecha_certificacion'],
                        'xml_fel' => $resultado['xml_certificado'],
                        'serie' => $resultado['serie'],
                        'numero_comprobante' => $resultado['numero'],
                        'estado' => 1 // Venta Completada
                    ]);

                    // Limpiar respuesta para Log ligero
                    $respuestaLimpia = $resultado['respuesta_completa'];
                    unset($respuestaLimpia['responseData1'], $respuestaLimpia['responseData2'], $respuestaLimpia['responseData3']);

                    LogFel::create([
                        'venta_id' => $venta->id,
                        'tipo_documento' => 'FACT',
                        'serie' => $resultado['serie'],
                        'numero' => $resultado['numero'],
                        'uuid' => $resultado['uuid'],
                        'respuesta_certificador' => json_encode($respuestaLimpia),
                        'estado' => 'CERTIFICADO',
                        'fecha_certificacion' => $resultado['fecha_certificacion'],
                        'intentos' => 1
                    ]);

                } else {
                    // FALLO: HACEMOS ROLLBACK
                    // Esto deshará la creación de la venta, el descuento de inventario, todo.
                    DB::rollBack();

                    // Registramos el error en logs del sistema (laravel.log) para que tú sepas qué pasó
                    \Log::error('Fallo FEL Crítico (Venta cancelada): ' . json_encode($resultado['error']));

                    return redirect()->back()
                        ->with('error', 'Error al certificar FEL: ' . ($resultado['error'] ?? 'Error desconocido'). '. La venta NO se ha realizado.')
                        ->withInput(); // Devuelve los datos al formulario
                }
            }
            DB::commit();

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
     * Revertir venta localmente si falla FEL
     */

    private function revertirVentaLocal(Venta $venta, string $motivoError)
    {
        DB::beginTransaction();
        try {
            // 1. Devolver Stock
            foreach ($venta->productos as $producto) {
                $inventario = InventarioSucursal::where('producto_id', $producto->id)
                    ->where('sucursal_id', $venta->sucursal_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventario) {
                    $inventario->stock_actual += $producto->pivot->cantidad;
                    $inventario->save();
                }

                // Registrar devolución en kardex
                MovimientoInventario::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'DEVOLUCION',
                    'cantidad' => $producto->pivot->cantidad,
                    'sucursal_destino_id' => $venta->sucursal_id,
                    'venta_id' => $venta->id,
                    'motivo' => 'Reversión automática por fallo FEL: ' . substr($motivoError, 0, 100),
                    'user_id' => Auth::id(),
                    'fecha_movimiento' => now()
                ]);
            }

            // 2. Marcar venta como anulada/cancelada
            $venta->update(['estado' => 0]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error crítico al revertir venta local: ' . $e->getMessage());
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
            'anulacionFel',
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

    public function generarTicket(Venta $venta)
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
            $pdf = Pdf::loadView('venta.ticket', compact('venta'));
        } else {
            $pdf = Pdf::loadView('venta.ticket', compact('venta'));
        }

        // $pdf->setPaper('letter');
        $pdf->setPaper([0, 0, 226, 800]);

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

        $diasLimite = config('ventas.dias_limite_anulacion', 15); // FEL GT suele permitir más días, ajustar según config
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

            $diasLimite = config('ventas.dias_limite_anulacion', 15);
            $fechaLimite = Carbon::parse($venta->fecha_hora)->addDays($diasLimite);

            if (now()->gt($fechaLimite)) {
                throw new Exception("No se puede anular. Han pasado más de {$diasLimite} días");
            }

            if ($venta->esFEL()) {
                $felService = new FELService($venta->sucursal);
                $resultado = $felService->anularFactura($venta, $request->motivo_anulacion);

                if (!$resultado['success']) {
                    throw new Exception('Error al anular en FEL: ' . $resultado['error']);
                }

                AnulacionFel::create([
                    'venta_id' => $venta->id,
                    'uuid_documento_anular' => $venta->numero_autorizacion_fel,
                    'uuid_anulacion' => $resultado['uuid_anulacion'] ?? null,
                    'motivo' => $request->motivo_anulacion,
                    'fecha_anulacion' => now(),
                    'user_id' => Auth::id(),
                    'estado' => 'CERTIFICADO'
                ]);

                if ($venta->logFel) {
                    $venta->logFel->update(['estado' => 'ANULADO']);
                }
            }

            // Revertir inventario
            foreach ($venta->productos as $producto) {
                $inventario = InventarioSucursal::where('producto_id', $producto->id)
                    ->where('sucursal_id', $venta->sucursal_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventario) {
                    $inventario->stock_actual += $producto->pivot->cantidad;
                    $inventario->save();
                }

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
                // CAMBIO: Actualizamos con datos de Digifact
                $venta->update([
                    'numero_autorizacion_fel' => $resultado['uuid'],
                    'fecha_certificacion_fel' => $resultado['fecha_certificacion'],
                    'xml_fel' => $resultado['xml_certificado'], // Llave correcta
                    'serie' => $resultado['serie'], // Llave correcta
                    'numero_comprobante' => $resultado['numero'] // Llave correcta
                ]);

                if ($venta->logFel) {
                    $venta->logFel->update([
                        'uuid' => $resultado['uuid'],
                        'serie' => $resultado['serie'],
                        'numero' => $resultado['numero'],
                        'estado' => 'CERTIFICADO',
                        'fecha_certificacion' => $resultado['fecha_certificacion'],
                        'respuesta_certificador' => json_encode($resultado['respuesta_completa']), // Llave correcta
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
                    ->with('error', 'Error al certificar: ' . ($resultado['error'] ?? 'Desconocido'));
            }
        } catch (Exception $e) {
            \Log::error('Error al reintentar certificación: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al reintentar certificación: ' . $e->getMessage());
        }
    }

    /**
 * Buscar producto por código de barras o nombre (AJAX)
 * Soporta tanto búsqueda manual como scanner
 */
public function buscarProducto(Request $request)
{
    try {
        $termino = $request->termino;
        $sucursalActiva = $this->getSucursalActiva();

        if (!$sucursalActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursal activa'
            ], 400);
        }

        // Buscar en inventario de la sucursal
        $inventarios = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida',
            'ubicacion'
        ])
        ->where('sucursal_id', $sucursalActiva->id)
        ->where('stock_actual', '>', 0)
        ->where('estado', 1)
        ->whereHas('producto', function($query) use ($termino) {
            $query->where('estado', 1)
                  ->where(function($q) use ($termino) {
                      $q->where('codigo', 'LIKE', "%{$termino}%")
                        ->orWhere('nombre', 'LIKE', "%{$termino}%");
                  });
        })
        ->limit(10)
        ->get();

        $resultados = $inventarios->map(function($inventario) {
            $producto = $inventario->producto;
            $bajoCantidad = $inventario->stock_actual <= $inventario->stock_minimo;

            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'nombre_completo' => $producto->nombre_completo,
                'marca' => $producto->marca->caracteristica->nombre ?? 'Sin marca',
                'presentacion' => $producto->presentacione->caracteristica->nombre ?? '',
                'stock_actual' => $inventario->stock_actual,
                'stock_minimo' => $inventario->stock_minimo,
                'stock_maximo' => $inventario->stock_maximo,
                'precio_venta' => $inventario->precio_venta,
                'bajo_stock' => $bajoCantidad,
                'ubicacion' => [
                    'id' => $inventario->ubicacion_id,
                    'codigo' => $inventario->ubicacion->codigo ?? 'Sin ubicación',
                    'nombre' => $inventario->ubicacion->nombre ?? 'Sin ubicación',
                    'tipo' => $inventario->ubicacion->tipo ?? null,
                    'seccion' => $inventario->ubicacion->seccion ?? null,
                    'texto_completo' => $inventario->ubicacion
                        ? "{$inventario->ubicacion->codigo} - {$inventario->ubicacion->nombre}"
                        : 'Sin ubicación'
                ]
            ];
        });

        return response()->json([
            'success' => true,
            'productos' => $resultados,
            'total' => $resultados->count()
        ]);

    } catch (\Exception $e) {
        \Log::error('Error al buscar producto: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al buscar producto: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener producto por código exacto (para scanner)
 */
public function obtenerPorCodigo(Request $request)
{
    try {
        $codigo = $request->codigo;
        $sucursalActiva = $this->getSucursalActiva();

        if (!$sucursalActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sucursal activa'
            ], 400);
        }

        $inventario = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida',
            'ubicacion'
        ])
        ->where('sucursal_id', $sucursalActiva->id)
        ->where('estado', 1)
        ->whereHas('producto', function($query) use ($codigo) {
            $query->where('codigo', $codigo)
                  ->where('estado', 1);
        })
        ->first();

        if (!$inventario) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado o sin stock en esta sucursal'
            ], 404);
        }

        if ($inventario->stock_actual <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Producto sin stock disponible'
            ], 400);
        }

        $producto = $inventario->producto;
        $bajoCantidad = $inventario->stock_actual <= $inventario->stock_minimo;

        return response()->json([
            'success' => true,
            'producto' => [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'nombre_completo' => $producto->nombre_completo,
                'marca' => $producto->marca->caracteristica->nombre ?? 'Sin marca',
                'presentacion' => $producto->presentacione->caracteristica->nombre ?? '',
                'stock_actual' => $inventario->stock_actual,
                'stock_minimo' => $inventario->stock_minimo,
                'stock_maximo' => $inventario->stock_maximo,
                'precio_venta' => $inventario->precio_venta,
                'bajo_stock' => $bajoCantidad,
                'ubicacion' => [
                    'id' => $inventario->ubicacion_id,
                    'codigo' => $inventario->ubicacion->codigo ?? 'Sin ubicación',
                    'nombre' => $inventario->ubicacion->nombre ?? 'Sin ubicación',
                    'tipo' => $inventario->ubicacion->tipo ?? null,
                    'seccion' => $inventario->ubicacion->seccion ?? null,
                    'texto_completo' => $inventario->ubicacion
                        ? "{$inventario->ubicacion->codigo} - {$inventario->ubicacion->nombre}"
                        : 'Sin ubicación'
                ]
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error al obtener producto: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener producto'
        ], 500);
    }
}
}
