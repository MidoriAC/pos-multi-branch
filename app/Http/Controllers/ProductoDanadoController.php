<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoDanadoRequest;
use App\Models\ProductoDanado;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\Ubicacion;
use App\Models\InventarioSucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProductoDanadoController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-producto-danado|registrar-producto-danado|aprobar-producto-danado', ['only' => ['index']]);
        $this->middleware('permission:registrar-producto-danado', ['only' => ['create', 'store']]);
        $this->middleware('permission:aprobar-producto-danado', ['only' => ['aprobar', 'rechazar']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productosDanados = ProductoDanado::with([
            'producto',
            'sucursal',
            'ubicacion',
            'usuario',
            'aprobador'
        ])->latest()->get();

        return view('producto-danado.index', compact('productosDanados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productos = Producto::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $sucursales = Sucursal::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('producto-danado.create', compact('productos', 'sucursales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoDanadoRequest $request)
    {
        try {
            DB::beginTransaction();

            // Verificar que hay stock suficiente en la sucursal
            $inventario = InventarioSucursal::where('producto_id', $request->producto_id)
                ->where('sucursal_id', $request->sucursal_id)
                ->first();

            if (!$inventario) {
                return redirect()->back()
                    ->with('error', 'El producto no existe en el inventario de esta sucursal')
                    ->withInput();
            }

            if ($inventario->stock_actual < $request->cantidad) {
                return redirect()->back()
                    ->with('error', 'Stock insuficiente. Stock actual: ' . $inventario->stock_actual)
                    ->withInput();
            }

            // Crear registro de producto dañado
            $productoDanado = ProductoDanado::create([
                'producto_id' => $request->producto_id,
                'sucursal_id' => $request->sucursal_id,
                'ubicacion_id' => $request->ubicacion_id,
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'costo_perdida' => $request->costo_perdida ?? 0,
                'fecha_registro' => now(),
                'user_id' => Auth::id(),
                'estado' => 'PENDIENTE' // pendiente, aprobado, rechazado
            ]);

            DB::commit();

            return redirect()->route('productos-danados.index')
                ->with('success', 'Reporte de producto dañado registrado exitosamente. Pendiente de aprobación.');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al registrar el producto dañado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductoDanado $productosDanado)
    {
        $productosDanado->load([
            'producto.marca.caracteristica',
            'producto.presentacione.caracteristica',
            'producto.unidadMedida',
            'sucursal',
            'ubicacion',
            'usuario',
            'aprobador'
        ]);

        return view('producto-danado.show', compact('productosDanado'));
    }

    /**
     * Aprobar producto dañado y descontar del inventario
     */
    public function aprobar($id)
    {
        try {
            DB::beginTransaction();

            $productoDanado = ProductoDanado::findOrFail($id);

            if ($productoDanado->estado != 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Este reporte ya fue procesado anteriormente');
            }

            // Verificar stock actual
            $inventario = InventarioSucursal::where('producto_id', $productoDanado->producto_id)
                ->where('sucursal_id', $productoDanado->sucursal_id)
                ->first();

            if (!$inventario || $inventario->stock_actual < $productoDanado->cantidad) {
                return redirect()->back()
                    ->with('error', 'Stock insuficiente para aprobar este reporte');
            }

            // Descontar del inventario
            $inventario->stock_actual -= $productoDanado->cantidad;
            $inventario->save();

            // Actualizar estado del reporte
            $productoDanado->update([
                'estado' => 'aprobado',
                'aprobado_por' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('productos-danados.index')
                ->with('success', 'Reporte aprobado y stock actualizado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al aprobar el reporte: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar producto dañado
     */
    public function rechazar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $productoDanado = ProductoDanado::findOrFail($id);

            if ($productoDanado->estado != 'pendiente') {
                return redirect()->back()
                    ->with('error', 'Este reporte ya fue procesado anteriormente');
            }

            $productoDanado->update([
                'estado' => 'rechazado',
                'aprobado_por' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('productos-danados.index')
                ->with('success', 'Reporte rechazado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al rechazar el reporte: ' . $e->getMessage());
        }
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

    /**
     * Obtener stock de producto en sucursal (AJAX)
     */
    public function getStock(Request $request)
    {
        $inventario = InventarioSucursal::where('producto_id', $request->producto_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->first();

        if ($inventario) {
            return response()->json([
                'success' => true,
                'stock' => $inventario->stock_actual,
                'precio_venta' => $inventario->precio_venta
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Producto no encontrado en esta sucursal'
        ]);
    }
}
