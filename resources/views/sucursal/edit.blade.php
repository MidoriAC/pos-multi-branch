@extends('layouts.app')

@section('title','Editar Sucursal')

@push('css')
<style>
    .required:after {
        content: " *";
        color: red;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Sucursal</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sucursales.index') }}">Sucursales</a></li>
        <li class="breadcrumb-item active">Editar: {{ $sucursal->nombre }}</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Formulario de edición
        </div>
        <div class="card-body">
            <form action="{{ route('sucursales.update', $sucursal->id) }}" method="post">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <!-- Información Básica -->
                    <div class="col-12">
                        <h5 class="border-bottom pb-2">
                            <i class="fas fa-info-circle"></i> Información Básica
                        </h5>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6">
                        <label for="nombre" class="form-label required">Nombre de la Sucursal</label>
                        <input type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{ old('nombre', $sucursal->nombre) }}"
                               placeholder="Ej: Sucursal Centro">
                        @error('nombre')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Código -->
                    <div class="col-md-6">
                        <label for="codigo" class="form-label required">Código</label>
                        <input type="text"
                               name="codigo"
                               id="codigo"
                               class="form-control @error('codigo') is-invalid @enderror"
                               value="{{ old('codigo', $sucursal->codigo) }}"
                               placeholder="Ej: SUC-001">
                        @error('codigo')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                        <small class="form-text text-muted">
                            Código único identificador de la sucursal
                        </small>
                    </div>

                    <!-- Dirección -->
                    <div class="col-12">
                        <label for="direccion" class="form-label required">Dirección</label>
                        <textarea name="direccion"
                                  id="direccion"
                                  rows="2"
                                  class="form-control @error('direccion') is-invalid @enderror"
                                  placeholder="Ingrese la dirección completa de la sucursal">{{ old('direccion', $sucursal->direccion) }}</textarea>
                        @error('direccion')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Información de Contacto -->
                    <div class="col-12 mt-4">
                        <h5 class="border-bottom pb-2">
                            <i class="fas fa-address-book"></i> Información de Contacto
                        </h5>
                    </div>

                    <!-- Teléfono -->
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="text"
                               name="telefono"
                               id="telefono"
                               class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $sucursal->telefono) }}"
                               placeholder="Ej: 7777-7777">
                        @error('telefono')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $sucursal->email) }}"
                               placeholder="sucursal@empresa.com">
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Información FEL (Opcional) -->
                    <div class="col-12 mt-4">
                        <h5 class="border-bottom pb-2">
                            <i class="fas fa-file-invoice"></i> Información FEL (Opcional)
                        </h5>
                        <small class="text-muted">
                            Esta información es necesaria si la sucursal emitirá facturas electrónicas
                        </small>
                    </div>

                    <!-- NIT Establecimiento -->
                    <div class="col-md-6">
                        <label for="nit_establecimiento" class="form-label">NIT del Establecimiento</label>
                        <input type="text"
                               name="nit_establecimiento"
                               id="nit_establecimiento"
                               class="form-control @error('nit_establecimiento') is-invalid @enderror"
                               value="{{ old('nit_establecimiento', $sucursal->nit_establecimiento) }}"
                               placeholder="Ej: 123456-7">
                        @error('nit_establecimiento')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Código Establecimiento -->
                    <div class="col-md-6">
                        <label for="codigo_establecimiento" class="form-label">Código de Establecimiento</label>
                        <input type="text"
                               name="codigo_establecimiento"
                               id="codigo_establecimiento"
                               class="form-control @error('codigo_establecimiento') is-invalid @enderror"
                               value="{{ old('codigo_establecimiento', $sucursal->codigo_establecimiento) }}"
                               placeholder="Ej: 001">
                        @error('codigo_establecimiento')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                        <small class="form-text text-muted">
                            Código asignado por SAT para el establecimiento
                        </small>
                    </div>

                    <!-- Botones -->
                    <div class="col-12 mt-4">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Sucursal
                            </button>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

</div>

@endsection
