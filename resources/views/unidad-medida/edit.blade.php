@extends('layouts.app')

@section('title','Editar Unidad de Medida')

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
    <h1 class="mt-4 text-center">Editar Unidad de Medida</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('unidades-medida.index')}}">Unidades de Medida</a></li>
        <li class="breadcrumb-item active">Editar Unidad de Medida</li>
    </ol>

    <div class="card">
        <form action="{{ route('unidades-medida.update', $unidadesMedida->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-header bg-primary text-white">
                <i class="fas fa-balance-scale me-2"></i>
                <strong>Formulario de Edición</strong>
            </div>

            <div class="card-body">

                {{-- Información Básica --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i>Información Básica
                    </h5>
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
                               value="{{old('nombre', $unidadesMedida->nombre)}}"
                               placeholder="Ej: Kilogramo, Litro, Metro">
                        @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-tag"></i> Nombre completo de la unidad de medida
                        </small>
                    </div>
                </div>

                <!-- Abreviatura -->
                <div class="row mb-4">
                    <label for="abreviatura" class="col-lg-2 col-form-label required">Abreviatura:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="abreviatura"
                               id="abreviatura"
                               class="form-control text-uppercase @error('abreviatura') is-invalid @enderror"
                               value="{{old('abreviatura', $unidadesMedida->abreviatura)}}"
                               placeholder="Ej: KG, L, M"
                               maxlength="20">
                        @error('abreviatura')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-font"></i> Abreviatura o símbolo de la unidad
                        </small>
                    </div>
                </div>

                {{-- Clasificación --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-layer-group me-2"></i>Clasificación
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
                            <option value="peso" @selected(old('tipo', $unidadesMedida->tipo)=='peso')>Peso</option>
                            <option value="volumen" @selected(old('tipo', $unidadesMedida->tipo)=='volumen')>Volumen</option>
                            <option value="longitud" @selected(old('tipo', $unidadesMedida->tipo)=='longitud')>Longitud</option>
                            <option value="unidad" @selected(old('tipo', $unidadesMedida->tipo)=='unidad')>Unidad</option>
                            <option value="area" @selected(old('tipo', $unidadesMedida->tipo)=='area')>Área</option>
                            <option value="tiempo" @selected(old('tipo', $unidadesMedida->tipo)=='tiempo')>Tiempo</option>
                        </select>
                        @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-shapes"></i> Tipo de magnitud que representa
                        </small>
                    </div>
                </div>

                {{-- Información FEL --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-file-invoice me-2"></i>Facturación Electrónica (FEL)
                    </h5>
                </div>

                <!-- Código FEL -->
                <div class="row mb-4">
                    <label for="codigo_fel" class="col-lg-2 col-form-label">Código FEL:</label>
                    <div class="col-lg-4">
                        <input autocomplete="off"
                               type="text"
                               name="codigo_fel"
                               id="codigo_fel"
                               class="form-control @error('codigo_fel') is-invalid @enderror"
                               value="{{old('codigo_fel', $unidadesMedida->codigo_fel)}}"
                               placeholder="Ej: UNI, KGM"
                               maxlength="20">
                        @error('codigo_fel')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-lg-6">
                        <small class="form-text text-muted">
                            <i class="fas fa-barcode"></i> Código para facturación electrónica (opcional)
                        </small>
                    </div>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('unidades-medida.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Unidad de Medida
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Convertir abreviatura a mayúsculas automáticamente
    const abreviaturaInput = document.getElementById('abreviatura');
    abreviaturaInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush
