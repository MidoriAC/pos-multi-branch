<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class UnidadMedidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unidadesMedida = UnidadMedida::orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        return view('unidad-medida.index', compact('unidadesMedida'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unidad-medida.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:unidades_medida,nombre',
            'abreviatura' => 'required|max:20|unique:unidades_medida,abreviatura',
            'tipo' => 'required|in:peso,volumen,longitud,unidad,area,tiempo',
            'codigo_fel' => 'nullable|max:20'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe una unidad de medida con este nombre',
            'abreviatura.required' => 'La abreviatura es obligatoria',
            'abreviatura.unique' => 'Ya existe una unidad de medida con esta abreviatura',
            'tipo.required' => 'Debe seleccionar un tipo'
        ]);

        try {
            DB::beginTransaction();

            UnidadMedida::create([
                'nombre' => $request->nombre,
                'abreviatura' => strtoupper($request->abreviatura),
                'tipo' => $request->tipo,
                'codigo_fel' => $request->codigo_fel,
                'estado' => 1
            ]);

            DB::commit();

            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida creada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear la unidad de medida: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnidadMedida $unidadesMedida)
    {
        return view('unidad-medida.edit', compact('unidadesMedida'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnidadMedida $unidadesMedida)
    {
        $request->validate([
            'nombre' => 'required|max:100|unique:unidades_medida,nombre,' . $unidadesMedida->id,
            'abreviatura' => 'required|max:20|unique:unidades_medida,abreviatura,' . $unidadesMedida->id,
            'tipo' => 'required|in:peso,volumen,longitud,unidad,area,tiempo',
            'codigo_fel' => 'nullable|max:20'
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe una unidad de medida con este nombre',
            'abreviatura.required' => 'La abreviatura es obligatoria',
            'abreviatura.unique' => 'Ya existe una unidad de medida con esta abreviatura',
            'tipo.required' => 'Debe seleccionar un tipo'
        ]);

        try {
            DB::beginTransaction();

            $unidadesMedida->update([
                'nombre' => $request->nombre,
                'abreviatura' => strtoupper($request->abreviatura),
                'tipo' => $request->tipo,
                'codigo_fel' => $request->codigo_fel
            ]);

            DB::commit();

            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida actualizada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la unidad de medida: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(UnidadMedida $unidadesMedida)
    {
        try {
            DB::beginTransaction();

            // Verificar si tiene productos asociados
            if ($unidadesMedida->productos()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la unidad de medida porque tiene productos asignados');
            }

            $unidadesMedida->update(['estado' => 0]);

            DB::commit();

            return redirect()->route('unidades-medida.index')
                ->with('success', 'Unidad de medida desactivada exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al desactivar la unidad de medida: ' . $e->getMessage());
        }
    }
}
