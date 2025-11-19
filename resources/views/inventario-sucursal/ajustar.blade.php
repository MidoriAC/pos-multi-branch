@extends('layouts.app')

@section('title','Ajustar Inventario')

@push('css')
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .info-producto {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .stock-info {
        font-size: 2.5rem;
        font-weight: bold;
    }
    .ajuste-preview {
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    .tipo-badge {
        font-size: 1.2rem;
        padding: 10px 20px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .tipo-badge:hover {
        transform: scale(1.05);
    }
    input[type="radio"]:checked + .tipo-badge {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Ajustar Inventario</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventario-sucursal.index') }}">Inventario</a></li>
        <li class="breadcrumb-item active">Ajustar Inventario</li>
    </ol>

    <!-- Información del Producto -->
    <div class="info-producto">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-3">
                    <i class="fas fa-box"></i> {{$inventario->producto->nombre}}
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <i class="fas fa-barcode"></i>
                            <strong>Código:</strong> {{$inventario->producto->codigo}}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-store"></i>
                            <strong>Sucursal:</strong> {{$inventario->sucursal->nombre}}
                        </p>
                        @if($inventario->producto->marca)
                        <p class="mb-2">
                            <i class="fas fa-tag"></i>
                            <strong>Marca:</strong> {{$inventario->producto->marca->caracteristica->nombre}}
                        </p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($inventario->ubicacion)
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <strong>Ubicación:</strong> {{$inventario->ubicacion->codigo}} - {{$inventario->ubicacion->nombre}}
                        </p>
                        @endif
                        @if($inventario->producto->presentacione)
                        <p class="mb-2">
                            <i class="fas fa-box-open"></i>
                            <strong>Presentación:</strong> {{$inventario->producto->presentacione->caracteristica->nombre}}
                        </p>
                        @endif
                        <p class="mb-2">
                            <i class="fas fa-dollar-sign"></i>
                            <strong>Precio Venta:</strong> Q {{number_format($inventario->precio_venta, 2)}}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="stock-info" id="stockActual">
                    {{$inventario->stock_actual}}
                </div>
                <p class="mb-0">
                    @if($inventario->producto->unidadMedida)
                        {{$inventario->producto->unidadMedida->nombre}}
                    @else
                        Unidades
                    @endif
                </p>
                <small>Stock Actual</small>
            </div>
        </div>
    </div>

    <!-- Formulario de Ajuste -->
    <div class="card">
        <form action="{{route('inventario-sucursal.store-ajuste', $inventario->id)}}" method="POST" id="formAjuste">
            @csrf
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-edit"></i> Formulario de Ajuste
                </h5>
            </div>
            <div class="card-body">

                {{-- Tipo de Ajuste --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-exchange-alt me-2"></i>Tipo de Ajuste
                    </h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <input type="radio" name="tipo_ajuste" id="entrada" value="entrada" class="d-none" required>
                        <label for="entrada" class="tipo-badge badge bg-success w-100">
                            <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                            <strong>ENTRADA</strong>
                            <p class="mb-0 small">Agregar productos al inventario</p>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <input type="radio" name="tipo_ajuste" id="salida" value="salida" class="d-none" required>
                        <label for="salida" class="tipo-badge badge bg-danger w-100">
                            <i class="fas fa-minus-circle fa-2x d-block mb-2"></i>
                            <strong>SALIDA</strong>
                            <p class="mb-0 small">Retirar productos del inventario</p>
                        </label>
                    </div>
                    @error('tipo_ajuste')
                    <div class="invalid-feedback d-block text-center">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Cantidad --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-hashtag me-2"></i>Cantidad
                    </h5>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mx-auto">
                        <label for="cantidad" class="form-label required">Cantidad a Ajustar:</label>
                        <input type="number"
                               name="cantidad"
                               id="cantidad"
                               class="form-control form-control-lg text-center @error('cantidad') is-invalid @enderror"
                               value="{{old('cantidad')}}"
                               min="1"
                               max="{{$inventario->stock_actual}}"
                               placeholder="0"
                               required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i>
                            Stock disponible: <strong>{{$inventario->stock_actual}}</strong>
                            @if($inventario->producto->unidadMedida)
                                {{$inventario->producto->unidadMedida->abreviatura}}
                            @endif
                        </small>
                    </div>
                </div>

                {{-- Motivo --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-comment-alt me-2"></i>Motivo del Ajuste
                    </h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <label for="motivo" class="form-label required">Descripción del Motivo:</label>
                        <textarea name="motivo"
                                  id="motivo"
                                  rows="4"
                                  class="form-control @error('motivo') is-invalid @enderror"
                                  placeholder="Explique detalladamente el motivo del ajuste (ej: Inventario físico, corrección de error, devolución, etc.)"
                                  required>{{old('motivo')}}</textarea>
                        @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-exclamation-circle"></i>
                            Es importante proporcionar una justificación clara del ajuste
                        </small>
                    </div>
                </div>

                {{-- Vista Previa del Ajuste --}}
                <div class="ajuste-preview" id="ajustePreview" style="display: none;">
                    <h5 class="text-center mb-3">
                        <i class="fas fa-eye"></i> Vista Previa del Ajuste
                    </h5>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <small class="text-muted">Stock Actual</small>
                                    <div class="h3" id="previewActual">{{$inventario->stock_actual}}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-center">
                            <i class="fas fa-arrow-right fa-3x text-primary" id="previewIcono"></i>
                        </div>
                        <div class="col-md-4">
                            <div class="card" id="previewCard">
                                <div class="card-body">
                                    <small class="text-muted">Nuevo Stock</small>
                                    <div class="h3" id="previewNuevo">{{$inventario->stock_actual}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-info fs-6" id="previewDiferencia"></span>
                    </div>
                </div>

                {{-- Advertencias --}}
                <div class="alert alert-warning mt-4" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> Advertencias Importantes
                    </h6>
                    <ul class="mb-0">
                        <li>Los ajustes de inventario quedan <strong>registrados permanentemente</strong> en el historial.</li>
                        <li>Asegúrese de verificar la <strong>cantidad física</strong> antes de realizar el ajuste.</li>
                        <li>Las <strong>salidas</strong> no pueden exceder el stock actual disponible.</li>
                        <li>Este movimiento será visible para los supervisores y administradores.</li>
                    </ul>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('inventario-sucursal.index', ['sucursal_id' => $inventario->sucursal_id]) }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-warning btn-lg" id="btnSubmit" disabled>
                    <i class="fas fa-save"></i> Guardar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stockActual = {{$inventario->stock_actual}};
    const tipoEntrada = document.getElementById('entrada');
    const tipoSalida = document.getElementById('salida');
    const cantidadInput = document.getElementById('cantidad');
    const motivoInput = document.getElementById('motivo');
    const btnSubmit = document.getElementById('btnSubmit');
    const ajustePreview = document.getElementById('ajustePreview');

    // Actualizar límites según tipo de ajuste
    function actualizarLimites() {
        const tipoSeleccionado = document.querySelector('input[name="tipo_ajuste"]:checked');

        if (tipoSeleccionado) {
            if (tipoSeleccionado.value === 'salida') {
                cantidadInput.setAttribute('max', stockActual);
            } else {
                cantidadInput.removeAttribute('max');
            }
            actualizarPreview();
        }
    }

    // Actualizar vista previa
    function actualizarPreview() {
        const tipoSeleccionado = document.querySelector('input[name="tipo_ajuste"]:checked');
        const cantidad = parseInt(cantidadInput.value) || 0;

        if (tipoSeleccionado && cantidad > 0) {
            ajustePreview.style.display = 'block';

            let nuevoStock;
            let diferencia;
            let previewCard = document.getElementById('previewCard');
            let previewIcono = document.getElementById('previewIcono');

            if (tipoSeleccionado.value === 'entrada') {
                nuevoStock = stockActual + cantidad;
                diferencia = `+${cantidad}`;
                previewCard.classList.remove('bg-danger');
                previewCard.classList.add('bg-success', 'text-white');
                previewIcono.classList.remove('fa-minus-circle', 'text-danger');
                previewIcono.classList.add('fa-plus-circle', 'text-success');
            } else {
                nuevoStock = stockActual - cantidad;
                diferencia = `-${cantidad}`;
                previewCard.classList.remove('bg-success');
                previewCard.classList.add('bg-danger', 'text-white');
                previewIcono.classList.remove('fa-plus-circle', 'text-success');
                previewIcono.classList.add('fa-minus-circle', 'text-danger');
            }

            document.getElementById('previewNuevo').textContent = nuevoStock;
            document.getElementById('previewDiferencia').textContent = `Diferencia: ${diferencia} unidades`;

            // Validar si la salida es mayor al stock
            if (tipoSeleccionado.value === 'salida' && cantidad > stockActual) {
                cantidadInput.classList.add('is-invalid');
                btnSubmit.disabled = true;

                // Mostrar mensaje de error
                let errorMsg = cantidadInput.nextElementSibling;
                if (!errorMsg || !errorMsg.classList.contains('invalid-feedback')) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback d-block';
                    cantidadInput.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = `La cantidad no puede ser mayor al stock disponible (${stockActual})`;
            } else {
                cantidadInput.classList.remove('is-invalid');
                const errorMsg = cantidadInput.parentNode.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.remove();
                validarFormulario();
            }
        } else {
            ajustePreview.style.display = 'none';
        }
    }

    // Validar formulario completo
    function validarFormulario() {
        const tipoSeleccionado = document.querySelector('input[name="tipo_ajuste"]:checked');
        const cantidad = parseInt(cantidadInput.value) || 0;
        const motivo = motivoInput.value.trim();

        if (tipoSeleccionado && cantidad > 0 && motivo) {
            // Validar que las salidas no excedan el stock
            if (tipoSeleccionado.value === 'salida' && cantidad > stockActual) {
                btnSubmit.disabled = true;
            } else {
                btnSubmit.disabled = false;
            }
        } else {
            btnSubmit.disabled = true;
        }
    }

    // Event listeners
    tipoEntrada.addEventListener('change', actualizarLimites);
    tipoSalida.addEventListener('change', actualizarLimites);
    cantidadInput.addEventListener('input', actualizarPreview);
    motivoInput.addEventListener('input', validarFormulario);

    // Validar al enviar
    document.getElementById('formAjuste').addEventListener('submit', function(e) {
        const tipoSeleccionado = document.querySelector('input[name="tipo_ajuste"]:checked');
        const cantidad = parseInt(cantidadInput.value) || 0;

        if (tipoSeleccionado.value === 'salida' && cantidad > stockActual) {
            e.preventDefault();
            alert('La cantidad no puede ser mayor al stock disponible');
        }
    });
});
</script>
@endpush
