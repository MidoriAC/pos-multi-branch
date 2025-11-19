@extends('layouts.app')

@section('title','Editar usuario')

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
    <h1 class="mt-4 text-center">Editar Usuario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index')}}">Usuarios</a></li>
        <li class="breadcrumb-item active">Editar Usuario</li>
    </ol>

    <div class="card">
        <form action="{{ route('users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-header bg-primary text-white">
                <i class="fas fa-user-edit me-2"></i>
                <strong>Formulario de Edición</strong>
            </div>

            <div class="card-body">

                {{-- Información Personal --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-user me-2"></i>Información Personal
                    </h5>
                </div>

                <!-- Nombre -->
                <div class="row mb-4">
                    <label for="name" class="col-lg-2 col-form-label required">Nombre:</label>
                    <div class="col-lg-4">
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}"
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

                <!-- Email -->
                <div class="row mb-4">
                    <label for="email" class="col-lg-2 col-form-label required">Email:</label>
                    <div class="col-lg-4">
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}"
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
                        <i class="fas fa-lock me-2"></i>Actualizar Contraseña (opcional)
                    </h5>
                </div>

                <!-- Password -->
                <div class="row mb-4">
                    <label for="password" class="col-lg-2 col-form-label">Nueva Contraseña:</label>
                    <div class="col-lg-4">
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Dejar en blanco si no desea cambiarla">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-shield-alt"></i> Solo ingrese una nueva contraseña si desea cambiarla
                        </small>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="row mb-4">
                    <label for="password_confirm" class="col-lg-2 col-form-label">Confirmar:</label>
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
                </div>

                {{-- Rol y Permisos --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-user-shield me-2"></i>Rol y Permisos
                    </h5>
                </div>

                <!-- Roles -->
                <div class="row mb-4">
                    <label for="role" class="col-lg-2 col-form-label required">Rol:</label>
                    <div class="col-lg-4">
                        <select name="role"
                                id="role"
                                class="form-select @error('role') is-invalid @enderror">
                            <option value="" disabled>Seleccione un rol</option>
                            @foreach ($roles as $item)
                            <option value="{{ $item->name }}"
                                    {{ $user->roles->contains('name', $item->name) ? 'selected' : '' }}>
                                {{ ucfirst($item->name) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Sucursales --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-store me-2"></i>Asignación de Sucursales
                    </h5>
                </div>

                <!-- Sucursales -->
                <div class="row mb-4">
                    <label class="col-lg-2 col-form-label required">Sucursales:</label>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                @foreach ($sucursales as $sucursal)
                                <div class="form-check sucursal-checkbox">
                                    <input class="form-check-input sucursal-check"
                                           type="checkbox"
                                           name="sucursales[]"
                                           value="{{ $sucursal->id }}"
                                           id="sucursal{{ $sucursal->id }}"
                                           {{ in_array($sucursal->id, old('sucursales', $sucursalesUsuario)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="sucursal{{ $sucursal->id }}">
                                        <strong>{{ $sucursal->nombre }}</strong>
                                        <span class="badge bg-primary">{{ $sucursal->codigo }}</span>
                                        @if($sucursal->direccion)
                                        <br><small class="text-muted">{{ $sucursal->direccion }}</small>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sucursal principal -->
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
                                    @selected(old('sucursal_principal', optional($sucursalPrincipal)->id) == $sucursal->id)>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_principal')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Usuario
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

    function actualizarSucursalPrincipal() {
        options.forEach(option => option.style.display = 'none');
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const option = selectPrincipal.querySelector(`option[value="${checkbox.value}"]`);
                if (option) option.style.display = 'block';
            }
        });

        // si ninguna está seleccionada, limpiar el select
        const algunaSeleccionada = Array.from(checkboxes).some(cb => cb.checked);
        if (!algunaSeleccionada) selectPrincipal.value = '';
    }

    checkboxes.forEach(checkbox => checkbox.addEventListener('change', actualizarSucursalPrincipal));
    actualizarSucursalPrincipal();
});
</script>
@endpush
