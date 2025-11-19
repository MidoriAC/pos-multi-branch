<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => 'required|unique:productos,codigo|max:50',
            'nombre' => 'required|unique:productos,nombre|max:80',
            'descripcion' => 'nullable|max:255',
            'fecha_vencimiento' => 'nullable|date',
            'img_path' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'marca_id' => 'required|integer|exists:marcas,id',
            'presentacione_id' => 'required|integer|exists:presentaciones,id',
            'unidad_medida_id' => 'nullable|integer|exists:unidades_medida,id',
            'categorias' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'marca_id' => 'marca',
            'presentacione_id' => 'presentación',
            'unidad_medida_id' => 'unidad de medida'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'Se necesita un campo código',
            'nombre.required' => 'El nombre es obligatorio',
            'marca_id.required' => 'Debe seleccionar una marca',
            'presentacione_id.required' => 'Debe seleccionar una presentación',
            'categorias.required' => 'Debe seleccionar al menos una categoría'
        ];
    }
}
