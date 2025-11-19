@extends('layouts.app')

@section('title','Editar Ubicación')

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
    <h1 class="mt-4 text-center">Editar Ubicación</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ubicaciones.index')}}">Ubicaciones</a></li>
        <li class="breadcrumb-item active">Editar Ubicación</li>
    </ol>

    <div class="card">
        <form action="{{ route('ubicaciones.update', $ubicacione->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-header bg-primary text-white">
                <i class="fas fa-map-marker-alt me-2"></i>
                <strong>Formulario de Edición</strong>
            </div>

            <div class="card-body">

                {{-- Información Básica --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i>Información Básica
                    </h5>
                </div>

                <!-- Sucursal -->
                <div class="row mb-4">
                    <label for="sucursal_id" class="col-lg-2 col-form-label required">Sucursal:</label>
                    <div class="col-lg-4">
                        <select name="sucursal_id"
                                id="sucursal_id"
                                class="form-select @error('sucursal_id') is-invalid @enderror">
                            <option value="" disabled>Seleccione una sucursal</option>
                            @foreach ($sucursales as $sucursal)
                            <option value="{{$sucursal->id}}"
                                    @selected(old('sucursal_id', $ubicacione->sucursal_id)==$sucursal->id)>
                                {{$sucursal->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-store"></i> Sucursal a la que pertenece la ubicación
                        </small>
                    </div>
                </div>

                <!-- Código -->
                <div class="row mb-4">
                    <label for="codigo" class="col-lg-2 col-form-label required">Código:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="codigo"
                               id="codigo"
                               class="form-control text-uppercase @error('codigo') is-invalid @enderror"
                               value="{{old('codigo', $ubicacione->codigo)}}"
                               placeholder="Ej: A-01, EST-001">
                        @error('codigo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-barcode"></i> Código único de identificación
                        </small>
                    </div>
                </div>

                <!-- Nombre -->
                <div class="row mb-4">
                    <label for="nombre" class="col-lg-2 col-form-label required">Nombre:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="nombre"
                               id="nombre"
                               class="form-control @error('nombre') is-invalid @enderror"
                               value="{{old('nombre', $ubicacione->nombre)}}"
                               placeholder="Ej: Estante Principal">
                        @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-tag"></i> Nombre descriptivo de la ubicación
                        </small>
                    </div>
                </div>

                {{-- Características --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-cogs me-2"></i>Características
                    </h5>
                </div>

                <!-- Tipo -->
                <div class="row mb-4">
                    <label for="tipo" class="col-lg-2 col-form-label required">Tipo:</label>
                    <div class="col-lg-4">
                        <select name="tipo"
                                id="tipo"
                                class="form-select @error('tipo') is-invalid @enderror">
                            <option value="" disabled>Seleccione un tipo</option>
                            <option value="estante" @selected(old('tipo', $ubicacione->tipo)=='estante')>Estante</option>
                            <option value="pasillo" @selected(old('tipo', $ubicacione->tipo)=='pasillo')>Pasillo</option>
                            <option value="zona" @selected(old('tipo', $ubicacione->tipo)=='zona')>Zona</option>
                            <option value="bodega" @selected(old('tipo', $ubicacione->tipo)=='bodega')>Bodega</option>
                            <option value="mostrador" @selected(old('tipo', $ubicacione->tipo)=='mostrador')>Mostrador</option>
                            <option value="deposito" @selected(old('tipo', $ubicacione->tipo)=='deposito')>Depósito</option>
                        </select>
                        @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-layer-group"></i> Tipo de ubicación física
                        </small>
                    </div>
                </div>

                <!-- Sección -->
                <div class="row mb-4">
                    <label for="seccion" class="col-lg-2 col-form-label">Sección:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="seccion"
                               id="seccion"
                               class="form-control @error('seccion') is-invalid @enderror"
                               value="{{old('seccion', $ubicacione->seccion)}}"
                               placeholder="Ej: Norte, Sur, Piso 2">
                        @error('seccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-map-signs"></i> Sección o área donde se encuentra (opcional)
                        </small>
                    </div>
                </div>

                <!-- Capacidad Máxima -->
                <div class="row mb-4">
                    <label for="capacidad_maxima" class="col-lg-2 col-form-label">Capacidad Máxima:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="number"
                               name="capacidad_maxima"
                               id="capacidad_maxima"
                               class="form-control @error('capacidad_maxima') is-invalid @enderror"
                               value="{{old('capacidad_maxima', $ubicacione->capacidad_maxima)}}"
                               min="0"
                               placeholder="Ej: 100">
                        @error('capacidad_maxima')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-boxes"></i> Cantidad máxima de productos (opcional)
                        </small>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="row mb-4">
                    <label for="descripcion" class="col-lg-2 col-form-label">Descripción:</label>
                    <div class="col-lg-8">
                        <textarea name="descripcion"
                                  id="descripcion"
                                  rows="3"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Descripción adicional de la ubicación (opcional)">{{old('descripcion', $ubicacione->descripcion)}}</textarea>
                        @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('ubicaciones.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Ubicación
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convertir código a mayúsculas automáticamente
    const codigoInput = document.getElementById('codigo');
    codigoInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
