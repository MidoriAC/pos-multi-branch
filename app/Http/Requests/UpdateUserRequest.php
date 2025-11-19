<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user'))
            ],
            'password' => 'nullable|string|min:8',
            'password_confirm' => 'nullable|required_with:password|same:password',
            'role' => 'required|exists:roles,name',
            'sucursales' => 'nullable|array',
            'sucursales.*' => 'exists:sucursales,id',
            'sucursal_principal' => 'nullable|exists:sucursales,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.max' => 'El nombre no puede tener más de 255 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email no tiene un formato válido',
            'email.unique' => 'Este email ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password_confirm.required_with' => 'Debe confirmar la contraseña',
            'password_confirm.same' => 'Las contraseñas no coinciden',
            'role.required' => 'Debe seleccionar un rol',
            'role.exists' => 'El rol seleccionado no es válido',
            'sucursales.array' => 'Las sucursales deben ser un array',
            'sucursales.*.exists' => 'Una o más sucursales no son válidas',
            'sucursal_principal.exists' => 'La sucursal principal seleccionada no es válida'
        ];
    }
}
