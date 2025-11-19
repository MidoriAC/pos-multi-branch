<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'cliente_id' => 'required|exists:clientes,id',
            'numero_cotizacion' => 'required|string|max:255|unique:cotizaciones,numero_cotizacion',
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
            'sucursal_id' => 'sucursal',
            'cliente_id' => 'cliente',
            'numero_cotizacion' => 'número de cotización',
            'validez_dias' => 'días de validez',
            'arrayidproducto' => 'productos',
            'arraycantidad' => 'cantidades',
            'arrayprecioventa' => 'precios'
        ];
    }
}
