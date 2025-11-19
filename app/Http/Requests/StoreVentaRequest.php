<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'cliente_id' => 'required|exists:clientes,id',
            // 'comprobante_id' => 'required|exists:comprobantes,id',
            'tipo_factura' => 'required|in:RECI,FACT',
            'impuesto' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0.01',
            'arrayidproducto' => 'required|array|min:1',
            'arrayidproducto.*' => 'required|exists:productos,id',
            'arraycantidad' => 'required|array|min:1',
            'arraycantidad.*' => 'required|integer|min:1',
            'arrayprecioventa' => 'required|array|min:1',
            'arrayprecioventa.*' => 'required|numeric|min:0',
            'arraydescuento' => 'nullable|array',
            'arraydescuento.*' => 'nullable|numeric|min:0',
            'cotizacion_id' => 'nullable|exists:cotizaciones,id'
        ];

        // Ya no se requieren numero_comprobante ni serie porque se generan automáticamente

        return $rules;
    }

    public function attributes()
    {
        return [
            'cliente_id' => 'cliente',
            // 'comprobante_id' => 'tipo de comprobante',
            'tipo_factura' => 'tipo de factura',
            'arrayidproducto' => 'productos',
            'arraycantidad' => 'cantidades',
            'arrayprecioventa' => 'precios de venta',
            'impuesto' => 'IVA',
            'total' => 'total'
        ];
    }

    public function messages()
    {
        return [
            'arrayidproducto.required' => 'Debe agregar al menos un producto',
            'arrayidproducto.min' => 'Debe agregar al menos un producto',
            'tipo_factura.in' => 'El tipo de factura debe ser Recibo (RECI) o FEL (FACT)',
            'total.min' => 'El total debe ser mayor a 0'
        ];
    }

    protected function prepareForValidation()
    {
        // Asegurar que los arrays tengan el mismo tamaño
        if ($this->has('arrayidproducto')) {
            $count = count($this->arrayidproducto);

            if (!$this->has('arraydescuento') || count($this->arraydescuento) < $count) {
                $descuentos = $this->arraydescuento ?? [];
                $this->merge([
                    'arraydescuento' => array_pad($descuentos, $count, 0)
                ]);
            }
        }
    }
}
