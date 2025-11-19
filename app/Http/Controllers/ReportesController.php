<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Proveedore;
use App\Models\InventarioSucursal;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportesController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-reportes', ['only' => ['index']]);
        $this->middleware('permission:ver-reportes', ['only' => ['ventas', 'ventasPDF']]);
        $this->middleware('permission:ver-reportes', ['only' => ['compras', 'comprasPDF']]);
        // $this->middleware('permission:reporte-inventario', ['only' => ['inventario', 'inventarioPDF']]);
    }

    /**
     * Index - Menú principal de reportes
     */
    public function index()
    {
        return view('reportes.index');
    }

    /**
     * ============================================
     * REPORTE DE VENTAS
     * ============================================
     */
    public function ventas(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');
        $sucursales = Auth::user()->sucursales;

        // Valores por defecto
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $tipoFactura = $request->tipo_factura ?? 'TODOS';
        $estado = $request->estado ?? 'activos';

        // Query base
        $query = Venta::with(['cliente.persona', 'user', 'sucursal', 'productos'])
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        // Filtros
        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($tipoFactura !== 'TODOS') {
            $query->where('tipo_factura', $tipoFactura);
        }

        if ($estado === 'activos') {
            $query->where('estado', 1)->whereDoesntHave('anulacionFel');
        } elseif ($estado === 'anulados') {
            $query->where(function($q) {
                $q->where('estado', 0)->orWhereHas('anulacionFel');
            });
        }

        $ventas = $query->orderBy('fecha_hora', 'desc')->get();

        // Estadísticas
        $estadisticas = [
            'total_ventas' => $ventas->count(),
            'total_monto' => $ventas->sum('total'),
            'total_iva' => $ventas->sum('impuesto'),
            'subtotal' => $ventas->sum('total') - $ventas->sum('impuesto'),
            'ventas_fel' => $ventas->where('tipo_factura', 'FACT')->count(),
            'ventas_recibo' => $ventas->where('tipo_factura', 'RECI')->count(),
            'monto_fel' => $ventas->where('tipo_factura', 'FACT')->sum('total'),
            'monto_recibo' => $ventas->where('tipo_factura', 'RECI')->sum('total'),
            'promedio_venta' => $ventas->count() > 0 ? $ventas->sum('total') / $ventas->count() : 0,
            'productos_vendidos' => $ventas->sum(function($venta) {
                return $venta->productos->sum('pivot.cantidad');
            })
        ];

        // Ventas por día
        $ventasPorDia = $ventas->groupBy(function($venta) {
            return $venta->fecha_hora->format('Y-m-d');
        })->map(function($ventasDia) {
            return [
                'fecha' => $ventasDia->first()->fecha_hora->format('d/m/Y'),
                'cantidad' => $ventasDia->count(),
                'monto' => $ventasDia->sum('total')
            ];
        });

        // Top vendedores
        $topVendedores = $ventas->groupBy('user_id')->map(function($ventasUser) {
            return [
                'vendedor' => $ventasUser->first()->user->name,
                'cantidad' => $ventasUser->count(),
                'monto' => $ventasUser->sum('total')
            ];
        })->sortByDesc('monto')->take(5);

        return view('reportes.ventas', compact(
            'ventas',
            'estadisticas',
            'ventasPorDia',
            'topVendedores',
            'fechaInicio',
            'fechaFin',
            'sucursales',
            'sucursalFiltro',
            'tipoFactura',
            'estado'
        ));
    }

    public function ventasPDF(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');

        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $tipoFactura = $request->tipo_factura ?? 'TODOS';
        $estado = $request->estado ?? 'activos';

        $query = Venta::with(['cliente.persona', 'user', 'sucursal', 'productos'])
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);

        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($tipoFactura !== 'TODOS') {
            $query->where('tipo_factura', $tipoFactura);
        }

        if ($estado === 'activos') {
            $query->where('estado', 1)->whereDoesntHave('anulacionFel');
        } elseif ($estado === 'anulados') {
            $query->where(function($q) {
                $q->where('estado', 0)->orWhereHas('anulacionFel');
            });
        }

        $ventas = $query->orderBy('fecha_hora', 'desc')->get();

        $estadisticas = [
            'total_ventas' => $ventas->count(),
            'total_monto' => $ventas->sum('total'),
            'total_iva' => $ventas->sum('impuesto'),
            'subtotal' => $ventas->sum('total') - $ventas->sum('impuesto'),
        ];

        $sucursal = $sucursalFiltro && $sucursalFiltro !== 'todas'
            ? Sucursal::find($sucursalFiltro)
            : null;

        $pdf = Pdf::loadView('reportes.ventas-pdf', compact(
            'ventas',
            'estadisticas',
            'fechaInicio',
            'fechaFin',
            'sucursal',
            'tipoFactura',
            'estado'
        ));

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-ventas-' . $fechaInicio . '-' . $fechaFin . '.pdf');
    }

    /**
     * ============================================
     * REPORTE DE COMPRAS
     * ============================================
     */
    public function compras(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');
        $sucursales = Auth::user()->sucursales;

        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $proveedorId = $request->proveedor_id ?? null;

        $query = Compra::with(['proveedore.persona', 'user', 'sucursal', 'productos'])
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('estado', 1);

        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($proveedorId) {
            $query->where('proveedore_id', $proveedorId);
        }

        $compras = $query->orderBy('fecha_hora', 'desc')->get();

        $proveedores = Proveedore::whereHas('persona', function($q) {
            $q->where('estado', 1);
        })->get();

        // Estadísticas
        $estadisticas = [
            'total_compras' => $compras->count(),
            'total_monto' => $compras->sum('total'),
            'total_iva' => $compras->sum('impuesto'),
            'subtotal' => $compras->sum('total') - $compras->sum('impuesto'),
            'promedio_compra' => $compras->count() > 0 ? $compras->sum('total') / $compras->count() : 0,
            'productos_comprados' => $compras->sum(function($compra) {
                return $compra->productos->sum('pivot.cantidad');
            })
        ];

        // Compras por proveedor
        $comprasPorProveedor = $compras->groupBy('proveedore_id')->map(function($comprasProv) {
            return [
                'proveedor' => $comprasProv->first()->proveedore->persona->razon_social,
                'cantidad' => $comprasProv->count(),
                'monto' => $comprasProv->sum('total')
            ];
        })->sortByDesc('monto')->take(10);

        return view('reportes.compras', compact(
            'compras',
            'estadisticas',
            'comprasPorProveedor',
            'fechaInicio',
            'fechaFin',
            'sucursales',
            'sucursalFiltro',
            'proveedores',
            'proveedorId'
        ));
    }

    public function comprasPDF(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');

        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $proveedorId = $request->proveedor_id ?? null;

        $query = Compra::with(['proveedore.persona', 'user', 'sucursal', 'productos'])
            ->whereBetween('fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('estado', 1);

        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($proveedorId) {
            $query->where('proveedore_id', $proveedorId);
        }

        $compras = $query->orderBy('fecha_hora', 'desc')->get();

        $estadisticas = [
            'total_compras' => $compras->count(),
            'total_monto' => $compras->sum('total'),
            'total_iva' => $compras->sum('impuesto'),
            'subtotal' => $compras->sum('total') - $compras->sum('impuesto'),
        ];

        $sucursal = $sucursalFiltro && $sucursalFiltro !== 'todas'
            ? Sucursal::find($sucursalFiltro)
            : null;

        $proveedor = $proveedorId ? Proveedore::find($proveedorId) : null;

        $pdf = Pdf::loadView('reportes.compras-pdf', compact(
            'compras',
            'estadisticas',
            'fechaInicio',
            'fechaFin',
            'sucursal',
            'proveedor'
        ));

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-compras-' . $fechaInicio . '-' . $fechaFin . '.pdf');
    }

    /**
     * ============================================
     * REPORTE DE INVENTARIO
     * ============================================
     */
    public function inventario(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');
        $sucursales = Auth::user()->sucursales;
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $filtroStock = $request->filtro_stock ?? 'todos'; // todos, bajo, agotado

        $query = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'sucursal',
            'ubicacion'
        ])->where('estado', 1);

        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($filtroStock === 'bajo') {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                  ->where('stock_actual', '>', 0);
        } elseif ($filtroStock === 'agotado') {
            $query->where('stock_actual', 0);
        }

        $inventarios = $query->orderBy('stock_actual', 'asc')->get();

        // Estadísticas
        $estadisticas = [
            'total_productos' => $inventarios->count(),
            'valor_total' => $inventarios->sum(function($inv) {
                return $inv->stock_actual * $inv->precio_venta;
            }),
            'productos_bajo_stock' => $inventarios->filter(function($inv) {
                return $inv->stock_actual <= $inv->stock_minimo && $inv->stock_actual > 0;
            })->count(),
            'productos_agotados' => $inventarios->where('stock_actual', 0)->count(),
            'unidades_totales' => $inventarios->sum('stock_actual')
        ];

        return view('reportes.inventario', compact(
            'inventarios',
            'estadisticas',
            'sucursales',
            'sucursalFiltro',
            'filtroStock'
        ));
    }

    public function inventarioPDF(Request $request)
    {
        $sucursalId = Session::get('sucursal_id');
        $sucursalFiltro = $request->sucursal_id ?? $sucursalId;
        $filtroStock = $request->filtro_stock ?? 'todos';

        $query = InventarioSucursal::with([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'sucursal',
            'ubicacion'
        ])->where('estado', 1);

        if ($sucursalFiltro && $sucursalFiltro !== 'todas') {
            $query->where('sucursal_id', $sucursalFiltro);
        }

        if ($filtroStock === 'bajo') {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                  ->where('stock_actual', '>', 0);
        } elseif ($filtroStock === 'agotado') {
            $query->where('stock_actual', 0);
        }

        $inventarios = $query->orderBy('stock_actual', 'asc')->get();

        $estadisticas = [
            'total_productos' => $inventarios->count(),
            'valor_total' => $inventarios->sum(function($inv) {
                return $inv->stock_actual * $inv->precio_venta;
            }),
            'productos_bajo_stock' => $inventarios->filter(function($inv) {
                return $inv->stock_actual <= $inv->stock_minimo && $inv->stock_actual > 0;
            })->count(),
            'productos_agotados' => $inventarios->where('stock_actual', 0)->count(),
        ];

        $sucursal = $sucursalFiltro && $sucursalFiltro !== 'todas'
            ? Sucursal::find($sucursalFiltro)
            : null;

        $pdf = Pdf::loadView('reportes.inventario-pdf', compact(
            'inventarios',
            'estadisticas',
            'sucursal',
            'filtroStock'
        ));

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('reporte-inventario-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * ============================================
     * REPORTE DE PRODUCTOS MÁS VENDIDOS
     * ============================================
     */
    public function productosVendidos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');
        $limite = $request->limite ?? 20;

        $productosVendidos = DB::table('producto_venta')
            ->join('productos', 'producto_venta.producto_id', '=', 'productos.id')
            ->join('ventas', 'producto_venta.venta_id', '=', 'ventas.id')
            ->whereBetween('ventas.fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('ventas.estado', 1)
            ->select(
                'productos.id',
                'productos.codigo',
                'productos.nombre',
                DB::raw('SUM(producto_venta.cantidad) as cantidad_vendida'),
                DB::raw('SUM(producto_venta.cantidad * producto_venta.precio_venta) as monto_total'),
                DB::raw('AVG(producto_venta.precio_venta) as precio_promedio')
            )
            ->groupBy('productos.id', 'productos.codigo', 'productos.nombre')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit($limite)
            ->get();

        return view('reportes.productos-vendidos', compact(
            'productosVendidos',
            'fechaInicio',
            'fechaFin',
            'limite'
        ));
    }

    /**
     * ============================================
     * REPORTE DE CLIENTES
     * ============================================
     */
    public function clientes(Request $request)
    {
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->format('Y-m-d');
        $fechaFin = $request->fecha_fin ?? now()->format('Y-m-d');

        $clientesVentas = DB::table('ventas')
            ->join('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->join('personas', 'clientes.persona_id', '=', 'personas.id')
            ->whereBetween('ventas.fecha_hora', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('ventas.estado', 1)
            ->select(
                'clientes.id',
                'personas.razon_social',
                'personas.nit',
                'personas.telefono',
                DB::raw('COUNT(ventas.id) as total_compras'),
                DB::raw('SUM(ventas.total) as monto_total'),
                DB::raw('AVG(ventas.total) as promedio_compra'),
                DB::raw('MAX(ventas.fecha_hora) as ultima_compra')
            )
            ->groupBy('clientes.id', 'personas.razon_social', 'personas.nit', 'personas.telefono')
            ->orderBy('monto_total', 'desc')
            ->get();

        return view('reportes.clientes', compact(
            'clientesVentas',
            'fechaInicio',
            'fechaFin'
        ));
    }
}
