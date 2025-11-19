@extends('layouts.app')

@section('title','Crear usuario')

@push('css')
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .sucursal-checkbox {
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Usuario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuarios</a></li>
        <li class="breadcrumb-item active">Crear Usuario</li>
    </ol>

    <div class="card">
        <form action="{{ route('users.store') }}" method="post">
            @csrf
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user-plus me-2"></i>
                <strong>Formulario de Registro</strong>
            </div>
            <div class="card-body">

                {{-- Información Personal --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-user me-2"></i>Información Personal
                    </h5>
                </div>

                <!---Nombre---->
                <div class="row mb-4">
                    <label for="name" class="col-lg-2 col-form-label required">Nombre:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{old('name')}}"
                               placeholder="Ej: Juan Pérez">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Nombre completo del usuario
                        </small>
                    </div>
                </div>

                <!---Email---->
                <div class="row mb-4">
                    <label for="email" class="col-lg-2 col-form-label required">Email:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{old('email')}}"
                               placeholder="usuario@ejemplo.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-envelope"></i> Correo electrónico para acceder al sistema
                        </small>
                    </div>
                </div>

                {{-- Seguridad --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-lock me-2"></i>Contraseña
                    </h5>
                </div>

                <!---Password---->
                <div class="row mb-4">
                    <label for="password" class="col-lg-2 col-form-label required">Contraseña:</label>
                    <div class="col-lg-4">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Mínimo 8 caracteres">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-shield-alt"></i> Debe incluir letras y números (mínimo 8 caracteres)
                        </small>
                    </div>
                </div>

                <!---Confirm_Password---->
                <div class="row mb-4">
                    <label for="password_confirm" class="col-lg-2 col-form-label required">Confirmar:</label>
                    <div class="col-lg-4">
                        <input type="password"
                               name="password_confirm"
                               id="password_confirm"
                               class="form-control @error('password_confirm') is-invalid @enderror"
                               placeholder="Repetir contraseña">
                        @error('password_confirm')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-check-double"></i> Vuelva a escribir la contraseña
                        </small>
                    </div>
                </div>

                {{-- Rol y Permisos --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-user-shield me-2"></i>Rol y Permisos
                    </h5>
                </div>

                <!---Roles---->
                <div class="row mb-4">
                    <label for="role" class="col-lg-2 col-form-label required">Rol:</label>
                    <div class="col-lg-4">
                        <select name="role"
                                id="role"
                                class="form-select @error('role') is-invalid @enderror">
                            <option value="" selected disabled>Seleccione un rol</option>
                            @foreach ($roles as $item)
                            <option value="{{$item->name}}"
                                    @selected(old('role')==$item->name)>
                                {{ucfirst($item->name)}}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-users-cog"></i> Define los permisos del usuario
                        </small>
                    </div>
                </div>

                {{-- Sucursales --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-store me-2"></i>Asignación de Sucursales
                    </h5>
                </div>

                <!---Sucursales---->
                <div class="row mb-4">
                    <label class="col-lg-2 col-form-label required">Sucursales:</label>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                @if($sucursales->count() > 0)
                                    @foreach ($sucursales as $sucursal)
                                    <div class="form-check sucursal-checkbox">
                                        <input class="form-check-input sucursal-check"
                                               type="checkbox"
                                               name="sucursales[]"
                                               value="{{ $sucursal->id }}"
                                               id="sucursal{{ $sucursal->id }}"
                                               {{ in_array($sucursal->id, old('sucursales', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sucursal{{ $sucursal->id }}">
                                            <strong>{{ $sucursal->nombre }}</strong>
                                            <span class="badge bg-primary">{{ $sucursal->codigo }}</span>
                                            @if($sucursal->direccion)
                                            <br><small class="text-muted">{{ $sucursal->direccion }}</small>
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-warning" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        No hay sucursales disponibles.
                                        <a href="{{ route('sucursales.create') }}">Crear una sucursal</a>
                                    </div>
                                @endif
                                @error('sucursales')
                                <div class="text-danger mt-2">
                                    <small>{{ $message }}</small>
                                </div>
                                @enderror
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle"></i> Seleccione las sucursales donde trabajará el usuario
                        </small>
                    </div>
                </div>

                <!---Sucursal Principal---->
                <div class="row mb-4">
                    <label for="sucursal_principal" class="col-lg-2 col-form-label">Sucursal Principal:</label>
                    <div class="col-lg-4">
                        <select name="sucursal_principal"
                                id="sucursal_principal"
                                class="form-select @error('sucursal_principal') is-invalid @enderror">
                            <option value="">Sin sucursal principal</option>
                            @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}"
                                    class="sucursal-principal-option"
                                    style="display: none;"
                                    @selected(old('sucursal_principal')==$sucursal->id)>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_principal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-star"></i> Sucursal por defecto al iniciar sesión
                        </small>
                    </div>
                </div>

            </div>
            <div class="card-footer text-center">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.sucursal-check');
    const selectPrincipal = document.getElementById('sucursal_principal');
    const options = selectPrincipal.querySelectorAll('.sucursal-principal-option');

    // Función para actualizar opciones de sucursal principal
    function actualizarSucursalPrincipal() {
        // Ocultar todas las opciones primero
        options.forEach(option => {
            option.style.display = 'none';
        });

        // Mostrar solo las opciones de sucursales seleccionadas
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const sucursalId = checkbox.value;
                const option = selectPrincipal.querySelector(`option[value="${sucursalId}"]`);
                if (option) {
                    option.style.display = 'block';
                }
            }
        });

        // Si no hay ninguna seleccionada, resetear select
        const hasChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (!hasChecked) {
            selectPrincipal.value = '';
        }
    }

    // Agregar evento a cada checkbox
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', actualizarSucursalPrincipal);
    });

    // Ejecutar al cargar
    actualizarSucursalPrincipal();
});
</script>
@endpush
