<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $cliente = $this->route('cliente');
        $personaId = $cliente->persona->id;

        return [
            'razon_social' => 'required|max:80',
            'direccion' => 'required|max:80',
            'nit' => [
                'required',
                'max:20',
                Rule::unique('personas', 'nit')->ignore($personaId)
            ],
            'documento_id' => 'nullable|exists:documentos,id',
            'numero_documento' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'telefono' => 'nullable|max:20',
            'nombre_comercial' => 'nullable|max:100',
        ];
    }

    public function attributes()
    {
        return [
            'razon_social' => 'razón social',
            'direccion' => 'dirección',
            'nit' => 'NIT',
            'documento_id' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'telefono' => 'teléfono',
        ];
    }
}
