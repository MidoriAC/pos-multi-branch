<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCotizacionRequest;
use App\Http\Requests\UpdateCotizacionRequest;
use App\Models\Cotizacion;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\InventarioSucursal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

// $this->middleware('permission:mostrar-cotizacion', ['only' => ['show']]);
class CotizacionController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-cotizacion|crear-cotizacion|mostrar-cotizacion|editar-cotizacion', ['only' => ['index']]);
        $this->middleware('permission:crear-cotizacion', ['only' => ['create', 'store']]);
        $this->middleware('permission:ver-cotizacion', ['only' => ['show']]);
        $this->middleware('permission:editar-cotizacion', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-cotizacion', ['only' => ['cancelar', 'storeCancelacion']]);
        $this->middleware('permission:convertir-cotizacion', ['only' => ['convertirAVenta']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Actualizar cotizaciones vencidas automáticamente
        Cotizacion::where('estado', 'PENDIENTE')
            ->where('fecha_vencimiento', '<', now())
            ->update(['estado' => 'VENCIDA']);

        $cotizaciones = Cotizacion::with([
            'cliente.persona',
            'user',
            'sucursal',
            'venta'
        ])
        ->latest()
        ->get();

        // Estadísticas
        $estadisticas = [
            'total_cotizaciones' => $cotizaciones->count(),
            'pendientes' => $cotizaciones->where('estado', 'PENDIENTE')->count(),
            'convertidas' => $cotizaciones->where('estado', 'CONVERTIDA')->count(),
            'vencidas' => $cotizaciones->where('estado', 'VENCIDA')->count(),
            'canceladas' => $cotizaciones->where('estado', 'CANCELADA')->count(),
            'monto_pendiente' => $cotizaciones->where('estado', 'PENDIENTE')->sum('total'),
            'monto_convertido' => $cotizaciones->where('estado', 'CONVERTIDA')->sum('total')
        ];

        return view('cotizacion.index', compact('cotizaciones', 'estadisticas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Obtener sucursal de la sesión
        $sucursalId = Session::get('sucursal_id');

        if (!$sucursalId) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'No hay una sucursal seleccionada. Por favor seleccione una sucursal primero.');
        }

        $sucursalUsuario = Auth::user()->sucursales()->find($sucursalId);

        if (!$sucursalUsuario) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'Sucursal no válida.');
        }

        // Obtener productos con stock (para referencia, aunque en cotización no se descuenta)
        $productos = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida'
        ])
        ->where('sucursal_id', $sucursalUsuario->id)
        ->where('estado', 1)
        ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        // Generar número de cotización
        $numeroCotizacion = Cotizacion::generarNumero($sucursalUsuario);

        // Días de validez por defecto
        $diasValidez = config('ventas.dias_validez_cotizacion', 15);

        return view('cotizacion.create', compact(
            'productos',
            'clientes',
            'sucursalUsuario',
            'numeroCotizacion',
            'diasValidez'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCotizacionRequest $request)
    {
        try {
            DB::beginTransaction();

            // Validar sucursal de sesión
            $sucursalId = Session::get('sucursal_id');
            if (!$sucursalId || $sucursalId != $request->sucursal_id) {
                throw new Exception('Sucursal no válida o no coincide con la sesión');
            }

            // Calcular fecha de vencimiento
            $fechaVencimiento = Carbon::parse($request->fecha_hora)
                ->addDays($request->validez_dias);

            // Crear cotización
            $cotizacion = Cotizacion::create([
                'sucursal_id' => $request->sucursal_id,
                'cliente_id' => $request->cliente_id,
                'user_id' => Auth::id(),
                'fecha_hora' => $request->fecha_hora,
                'numero_cotizacion' => $request->numero_cotizacion,
                'subtotal' => $request->subtotal,
                'impuesto' => $request->impuesto,
                'total' => $request->total,
                'observaciones' => $request->observaciones,
                'validez_dias' => $request->validez_dias,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'PENDIENTE'
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
                $precioUnitario = $arrayPrecioVenta[$i];
                $descuento = $arrayDescuento[$i] ?? 0;
                $subtotal = ($cantidad * $precioUnitario) - $descuento;

                $cotizacion->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal
                ]);
            }

            DB::commit();

            return redirect()->route('cotizaciones.show', $cotizacion->id)
                ->with('success', 'Cotización creada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cotizacion $cotizacione)
    {
        $cotizacione->load([
            'productos.marca.caracteristica',
            'productos.presentacione.caracteristica',
            'productos.unidadMedida',
            'cliente.persona',
            'user',
            'sucursal',
            'venta'
        ]);

        return view('cotizacion.show', compact('cotizacione'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cotizacion $cotizacione)
    {
        if (!$cotizacione->puedeEditarse()) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'Esta cotización no puede editarse');
        }

        // Obtener productos con stock
        $productos = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida'
        ])
        ->where('sucursal_id', $cotizacione->sucursal_id)
        ->where('estado', 1)
        ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        $cotizacione->load('productos');

        return view('cotizacion.edit', compact('cotizacione', 'productos', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCotizacionRequest $request, Cotizacion $cotizacione)
    {
        if (!$cotizacione->puedeEditarse()) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'Esta cotización no puede editarse');
        }

        try {
            DB::beginTransaction();

            // Recalcular fecha de vencimiento
            $fechaVencimiento = Carbon::parse($request->fecha_hora)
                ->addDays($request->validez_dias);

            // Actualizar cotización
            $cotizacione->update([
                'cliente_id' => $request->cliente_id,
                'fecha_hora' => $request->fecha_hora,
                'subtotal' => $request->subtotal,
                'impuesto' => $request->impuesto,
                'total' => $request->total,
                'observaciones' => $request->observaciones,
                'validez_dias' => $request->validez_dias,
                'fecha_vencimiento' => $fechaVencimiento
            ]);

            // Eliminar productos anteriores
            $cotizacione->productos()->detach();

            // Agregar nuevos productos
            $arrayProducto_id = $request->get('arrayidproducto');
            $arrayCantidad = $request->get('arraycantidad');
            $arrayPrecioVenta = $request->get('arrayprecioventa');
            $arrayDescuento = $request->get('arraydescuento', []);

            $sizeArray = count($arrayProducto_id);

            for ($i = 0; $i < $sizeArray; $i++) {
                $productoId = $arrayProducto_id[$i];
                $cantidad = $arrayCantidad[$i];
                $precioUnitario = $arrayPrecioVenta[$i];
                $descuento = $arrayDescuento[$i] ?? 0;
                $subtotal = ($cantidad * $precioUnitario) - $descuento;

                $cotizacione->productos()->attach($productoId, [
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => $subtotal
                ]);
            }

            DB::commit();

            return redirect()->route('cotizaciones.show', $cotizacione->id)
                ->with('success', 'Cotización actualizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la cotización: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mostrar formulario de cancelación
     */
    public function cancelar(Cotizacion $cotizacione)
    {
        if (!$cotizacione->puedeCancelarse()) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'Esta cotización no puede cancelarse');
        }

        return view('cotizacion.cancelar', compact('cotizacione'));
    }

    /**
     * Cancelar cotización (anular)
     */
    public function storeCancelacion(Request $request, Cotizacion $cotizacione)
    {
        $request->validate([
            'motivo_cancelacion' => 'required|string|max:500'
        ]);

        if (!$cotizacione->puedeCancelarse()) {
            return redirect()->route('cotizaciones.index')
                ->with('error', 'Esta cotización no puede cancelarse');
        }

        try {
            $cotizacione->update([
                'estado' => 'CANCELADA',
                'observaciones' => ($cotizacione->observaciones ? $cotizacione->observaciones . "\n\n" : '') .
                                  "CANCELADA: " . $request->motivo_cancelacion .
                                  " (Usuario: " . Auth::user()->name . " - " . now()->format('d/m/Y H:i') . ")"
            ]);

            return redirect()->route('cotizaciones.index')
                ->with('success', 'Cotización cancelada exitosamente');

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cancelar la cotización: ' . $e->getMessage());
        }
    }

    /**
     * Generar PDF de la cotización
     */
    public function generarPDF(Cotizacion $cotizacione)
    {
        $cotizacione->load([
            'productos.marca.caracteristica',
            'productos.presentacione.caracteristica',
            'productos.unidadMedida',
            'cliente.persona',
            'user',
            'sucursal'
        ]);

        $pdf = Pdf::loadView('cotizacion.pdf', compact('cotizacione'));
        $pdf->setPaper('letter');

        return $pdf->stream('cotizacion-' . $cotizacione->numero_cotizacion . '.pdf');
    }

    /**
     * Convertir cotización a venta
     */
    public function convertirAVenta(Cotizacion $cotizacione)
    {
        if (!$cotizacione->puedeConvertirse()) {
            return redirect()->back()
                ->with('error', 'Esta cotización no puede convertirse en venta');
        }

        return redirect()->route('ventas.create', ['cotizacion_id' => $cotizacione->id]);
    }

    /**
     * Duplicar cotización
     */
    public function duplicar(Cotizacion $cotizacione)
    {
        // Cargar productos
        $cotizacione->load('productos');

        // Obtener sucursal
        $sucursalUsuario = $cotizacione->sucursal;

        // Obtener productos disponibles
        $productos = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida'
        ])
        ->where('sucursal_id', $sucursalUsuario->id)
        ->where('estado', 1)
        ->get();

        $clientes = Cliente::whereHas('persona', function ($query) {
            $query->where('estado', 1);
        })->get();

        // Generar nuevo número
        $numeroCotizacion = Cotizacion::generarNumero($sucursalUsuario);
        $diasValidez = $cotizacione->validez_dias;

        return view('cotizacion.create', compact(
            'productos',
            'clientes',
            'sucursalUsuario',
            'numeroCotizacion',
            'diasValidez',
            'cotizacione'
        ));
    }
}
