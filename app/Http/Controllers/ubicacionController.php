<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UbicacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ubicaciones = Ubicacion::with('sucursal')
            ->orderBy('sucursal_id')
            ->orderBy('codigo')
            ->get();

        return view('ubicacion.index', compact('ubicaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sucursales = Sucursal::where('estado', 1)->get();
        return view('ubicacion.create', compact('sucursales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'codigo' => 'required|max:50|unique:ubicaciones,codigo',
            'nombre' => 'required|max:100',
            'tipo' => 'required|in:estante,pasillo,zona,bodega,mostrador,deposito',
            'seccion' => 'nullable|max:50',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|max:500'
        ]);

        try {
            DB::beginTransaction();

            Ubicacion::create([
                'sucursal_id' => $request->sucursal_id,
                'codigo' => strtoupper($request->codigo),
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'seccion' => $request->seccion,
                'capacidad_maxima' => $request->capacidad_maxima,
                'descripcion' => $request->descripcion,
                'estado' => 1
            ]);

            DB::commit();

            return redirect()->route('ubicaciones.index')
                ->with('success', 'Ubicación creada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la ubicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ubicacion $ubicacione)
    {
        $sucursales = Sucursal::where('estado', 1)->get();
        return view('ubicacion.edit', compact('ubicacione', 'sucursales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ubicacion $ubicacione)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'codigo' => 'required|max:50|unique:ubicaciones,codigo,' . $ubicacione->id,
            'nombre' => 'required|max:100',
            'tipo' => 'required|in:estante,pasillo,zona,bodega,mostrador,deposito',
            'seccion' => 'nullable|max:50',
            'capacidad_maxima' => 'nullable|integer|min:0',
            'descripcion' => 'nullable|max:500'
        ]);

        try {
            DB::beginTransaction();

            $ubicacione->update([
                'sucursal_id' => $request->sucursal_id,
                'codigo' => strtoupper($request->codigo),
                'nombre' => $request->nombre,
                'tipo' => $request->tipo,
                'seccion' => $request->seccion,
                'capacidad_maxima' => $request->capacidad_maxima,
                'descripcion' => $request->descripcion
            ]);

            DB::commit();

            return redirect()->route('ubicaciones.index')
                ->with('success', 'Ubicación actualizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la ubicación: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Ubicacion $ubicacione)
    {
        try {
            DB::beginTransaction();

            // Verificar si tiene inventario asociado
            if ($ubicacione->inventarios()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la ubicación porque tiene productos asignados');
            }

            $ubicacione->update(['estado' => 0]);

            DB::commit();

            return redirect()->route('ubicaciones.index')
                ->with('success', 'Ubicación desactivada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al desactivar la ubicación: ' . $e->getMessage());
        }
    }
}
