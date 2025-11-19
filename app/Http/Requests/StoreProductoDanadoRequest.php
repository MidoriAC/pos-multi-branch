<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductoDanadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id' => 'required|exists:productos,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|in:vencido,roto,deteriorado,humedad,contaminacion,defecto_fabrica,otro',
            'descripcion' => 'required|string|max:500',
            'costo_perdida' => 'nullable|numeric|min:0'
        ];
    }

    public function attributes()
    {
        return [
            'producto_id' => 'producto',
            'sucursal_id' => 'sucursal',
            'ubicacion_id' => 'ubicación',
            'costo_perdida' => 'costo de pérdida'
        ];
    }

    public function messages()
    {
        return [
            'producto_id.required' => 'Debe seleccionar un producto',
            'sucursal_id.required' => 'Debe seleccionar una sucursal',
            'cantidad.required' => 'La cantidad es obligatoria',
            'cantidad.min' => 'La cantidad debe ser mayor a 0',
            'motivo.required' => 'Debe especificar el motivo del daño',
            'descripcion.required' => 'La descripción es obligatoria'
        ];
    }
}
