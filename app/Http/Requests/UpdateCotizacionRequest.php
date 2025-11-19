<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_hora' => 'required|date',
            'validez_dias' => 'required|integer|min:1|max:365',
            'observaciones' => 'nullable|string|max:1000',
            'subtotal' => 'required|numeric|min:0',
            'impuesto' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arrayprecioventa' => 'required|array|min:1',
            'arrayprecioventa.*' => 'required|numeric|min:0',
            'arraydescuento' => 'nullable|array',
            'arraydescuento.*' => 'nullable|numeric|min:0'
        ];
    }

    public function attributes()
    {
        return [
            'cliente_id' => 'cliente',
            'validez_dias' => 'días de validez',
            'arrayidproducto' => 'productos',
            'arraycantidad' => 'cantidades',
            'arrayprecioventa' => 'precios'
        ];
    }

    public function messages()
    {
        return [
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arrayidproducto.min' => 'Debe agregar al menos un producto'
        ];
    }
}
