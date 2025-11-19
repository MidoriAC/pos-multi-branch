<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sucursales', 'nombre')->ignore($this->route('sucursal'))
            ],
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sucursales', 'codigo')->ignore($this->route('sucursal'))
            ],
            'direccion' => 'required|string|max:200',
            'telefono' => 'nullable|string|max:8',
            'email' => 'nullable|email|max:100',
            'nit_establecimiento' => 'nullable|string|max:20',
            'codigo_establecimiento' => 'nullable|string|max:10'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sucursal es obligatorio',
            'nombre.unique' => 'Ya existe una sucursal con este nombre',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres',
            'codigo.required' => 'El código de la sucursal es obligatorio',
            'codigo.unique' => 'Ya existe una sucursal con este código',
            'codigo.max' => 'El código no puede tener más de 20 caracteres',
            'direccion.required' => 'La dirección es obligatoria',
            'direccion.max' => 'La dirección no puede tener más de 200 caracteres',
            'telefono.max' => 'El teléfono no puede tener más de 8 caracteres',
            'email.email' => 'El email no tiene un formato válido',
            'email.max' => 'El email no puede tener más de 100 caracteres',
            'nit_establecimiento.max' => 'El NIT no puede tener más de 20 caracteres',
            'codigo_establecimiento.max' => 'El código de establecimiento no puede tener más de 10 caracteres'
        ];
    }
}
