<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'proveedore_id' => 'required|exists:proveedores,id',
            'comprobante_id' => 'required|exists:comprobantes,id',
            'numero_comprobante' => 'required|string|max:255',
            'fecha_hora' => 'required|date',
            'impuesto' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arraypreciocompra' => 'required|array|min:1',
            'arraypreciocompra.*' => 'required|numeric|min:0',
            'arrayprecioventa' => 'required|array|min:1',
            'arrayprecioventa.*' => 'required|numeric|min:0',
            'arrayubicacion' => 'nullable|array',
            'arrayubicacion.*' => 'nullable|exists:ubicaciones,id'
        ];
    }

    public function messages()
    {
        return [
            'sucursal_id.required' => 'Debe seleccionar una sucursal',
            'proveedore_id.required' => 'Debe seleccionar un proveedor',
            'comprobante_id.required' => 'Debe seleccionar un tipo de comprobante',
            'numero_comprobante.required' => 'El número de comprobante es obligatorio',
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arrayidproducto.min' => 'Debe agregar al menos un producto'
        ];
    }
}
