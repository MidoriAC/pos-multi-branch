<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreSucursalRequest;
use App\Http\Requests\UpdateSucursalRequest;
use App\Models\Sucursal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SucursalController extends Controller{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sucursales = Sucursal::with('users')->where('estado', 1)->get();
        return view('sucursal.index', compact('sucursales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sucursal.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSucursalRequest $request)
    {
        try {
            DB::beginTransaction();

            $sucursal = Sucursal::create([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'nit_establecimiento' => $request->nit_establecimiento,
                'codigo_establecimiento' => $request->codigo_establecimiento,
                'estado' => 1
            ]);

            DB::commit();

            return redirect()->route('sucursales.index')
                ->with('success', 'Sucursal registrada exitosamente');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('sucursales.index')
                ->with('error', 'Error al registrar la sucursal: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sucursal $sucursal)
    {
        // Cargar relaciones necesarias
        $sucursal->load([
            'ubicaciones' => function($query) {
                $query->where('estado', 1);
            },
            'users',
            'inventarios.producto',
            'ventas' => function($query) {
                $query->where('estado', 1)->latest()->take(10);
            }
        ]);

        // Estadísticas de la sucursal
        $totalVentas = $sucursal->ventas()->where('estado', 1)->sum('total');
        $totalCompras = $sucursal->compras()->where('estado', 1)->sum('total');
        $totalProductos = $sucursal->inventarios()->sum('stock_actual');
        $totalUbicaciones = $sucursal->ubicaciones()->where('estado', 1)->count();
        $totalUsuarios = $sucursal->users()->count();

        return view('sucursal.show', compact(
            'sucursal',
            'totalVentas',
            'totalCompras',
            'totalProductos',
            'totalUbicaciones',
            'totalUsuarios'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sucursal $sucursal)
    {
        return view('sucursal.edit', compact('sucursal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSucursalRequest $request, Sucursal $sucursal)
    {
        try {
            DB::beginTransaction();

            $sucursal->update([
                'nombre' => $request->nombre,
                'codigo' => $request->codigo,
                'direccion' => $request->direccion,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'nit_establecimiento' => $request->nit_establecimiento,
                'codigo_establecimiento' => $request->codigo_establecimiento
            ]);

            DB::commit();

            return redirect()->route('sucursales.index')
                ->with('success', 'Sucursal actualizada exitosamente');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('sucursales.index')
                ->with('error', 'Error al actualizar la sucursal: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $sucursal = Sucursal::findOrFail($id);

            // Verificar si tiene movimientos
            if ($sucursal->ventas()->count() > 0 || $sucursal->compras()->count() > 0) {
                // Solo desactivar si tiene movimientos
                $sucursal->update(['estado' => 0]);
                $message = 'Sucursal desactivada exitosamente';
            } else {
                // Si no tiene movimientos, se puede eliminar
                $sucursal->delete();
                $message = 'Sucursal eliminada exitosamente';
            }

            DB::commit();

            return redirect()->route('sucursales.index')
                ->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('sucursales.index')
                ->with('error', 'Error al eliminar la sucursal: ' . $e->getMessage());
        }
    }

    /**
     * Reactivar una sucursal desactivada
     */
    public function reactivar(string $id)
    {
        try {
            DB::beginTransaction();

            $sucursal = Sucursal::findOrFail($id);
            $sucursal->update(['estado' => 1]);

            DB::commit();

            return redirect()->route('sucursales.index')
                ->with('success', 'Sucursal reactivada exitosamente');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('sucursales.index')
                ->with('error', 'Error al reactivar la sucursal: ' . $e->getMessage());
        }
    }

    /**
     * Ver sucursales desactivadas
     */
    public function inactivas()
    {
        $sucursales = Sucursal::where('estado', 0)->get();
        return view('sucursal.inactivas', compact('sucursales'));
    }
}
?>
