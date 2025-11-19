@extends('layouts.app')

@section('title','Cancelar Cotización')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .info-cotizacion {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }

    .warning-cancelacion {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .producto-item {
        background: #f8f9fa;
        padding: 10px;
        border-left: 3px solid #dc3545;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center text-danger">
        <i class="fas fa-ban"></i> Cancelar Cotización
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cotizaciones.index')}}">Cotizaciones</a></li>
        <li class="breadcrumb-item active">Cancelar</li>
    </ol>

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Información de la cotización -->
            <div class="info-cotizacion mb-4">
                <h4 class="mb-3"><i class="fas fa-file-invoice"></i> Información de la Cotización</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Número:</strong> {{$cotizacione->numero_cotizacion}}</p>
                        <p class="mb-2"><strong>Estado:</strong> {!! $cotizacione->obtenerEstadoBadge() !!}</p>
                        <p class="mb-2"><strong>Cliente:</strong> {{$cotizacione->cliente->persona->razon_social}}</p>
                        <p class="mb-0"><strong>NIT:</strong> {{$cotizacione->cliente->persona->nit ?? 'CF'}}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Fecha:</strong> {{$cotizacione->fecha_hora->format('d/m/Y H:i')}}</p>
                        <p class="mb-2"><strong>Total:</strong> Q {{number_format($cotizacione->total, 2)}}</p>
                        <p class="mb-2"><strong>Vendedor:</strong> {{$cotizacione->user->name}}</p>
                        <p class="mb-0"><strong>Sucursal:</strong> {{$cotizacione->sucursal->nombre}}</p>
                    </div>
                </div>

                @if($cotizacione->observaciones)
                <div class="mt-3 pt-3 border-top">
                    <p class="mb-0">
                        <strong>Observaciones:</strong><br>
                        {{$cotizacione->observaciones}}
                    </p>
                </div>
                @endif
            </div>

            <!-- Productos de la cotización -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos de la Cotización</h5>
                </div>
                <div class="card-body">
                    @foreach($cotizacione->productos as $producto)
                    <div class="producto-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>{{$producto->codigo}}</strong> - {{$producto->nombre}}
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-primary">{{$producto->pivot->cantidad}} unidades</span>
                            </div>
                            <div class="col-md-2 text-end">
                                Q {{number_format($producto->pivot->precio_unitario, 2)}}
                            </div>
                            <div class="col-md-2 text-end">
                                <strong>Q {{number_format($producto->pivot->subtotal, 2)}}</strong>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="row justify-content-end mt-3">
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td class="text-end">Q {{number_format($cotizacione->subtotal, 2)}}</td>
                                </tr>
                                <tr>
                                    <th>IVA (12%):</th>
                                    <td class="text-end">Q {{number_format($cotizacione->impuesto, 2)}}</td>
                                </tr>
                                <tr class="table-danger">
                                    <th>TOTAL:</th>
                                    <td class="text-end"><strong>Q {{number_format($cotizacione->total, 2)}}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advertencia -->
            <div class="warning-cancelacion">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h4 class="mb-2">¿Está seguro de cancelar esta cotización?</h4>
                    <p class="mb-0">
                        Esta acción marcará la cotización como CANCELADA.
                        No podrá convertirse a venta ni editarse posteriormente.
                    </p>
                </div>
            </div>

            <!-- Formulario de cancelación -->
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Motivo de Cancelación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('cotizaciones.cancelacion.store', $cotizacione->id) }}" method="POST" id="formCancelar">
                        @csrf

                        <div class="mb-3">
                            <label for="motivo_cancelacion" class="form-label">
                                <strong>Motivo de la cancelación: <span class="text-danger">*</span></strong>
                            </label>
                            <textarea
                                name="motivo_cancelacion"
                                id="motivo_cancelacion"
                                class="form-control @error('motivo_cancelacion') is-invalid @enderror"
                                rows="4"
                                placeholder="Explique por qué se cancela esta cotización..."
                                required
                                maxlength="500">{{ old('motivo_cancelacion') }}</textarea>
                            <small class="text-muted">
                                <span id="contador">0</span>/500 caracteres
                            </small>
                            @error('motivo_cancelacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> El motivo se agregará a las observaciones de la cotización.
                            Esta información quedará registrada en el sistema.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('cotizaciones.show', $cotizacione->id) }}" class="btn btn-secondary btn-lg w-100">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-danger btn-lg w-100" id="btnCancelar">
                                    <i class="fas fa-ban"></i> Cancelar Cotización
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    // Contador de caracteres
    $('#motivo_cancelacion').on('input', function() {
        const length = $(this).val().length;
        $('#contador').text(length);
    });

    // Confirmación antes de enviar
    $('#formCancelar').on('submit', function(e) {
        e.preventDefault();

        const motivo = $('#motivo_cancelacion').val().trim();

        if (motivo.length < 10) {
            Swal.fire({
                icon: 'warning',
                title: 'Motivo muy corto',
                text: 'Por favor, proporcione un motivo más detallado (mínimo 10 caracteres)',
                confirmButtonColor: '#ffc107'
            });
            return false;
        }

        Swal.fire({
            title: '¿Está completamente seguro?',
            html: `
                <p>Está a punto de <strong class="text-danger">CANCELAR</strong> la cotización:</p>
                <strong>{{$cotizacione->numero_cotizacion}}</strong>
                <br><br>
                <div class="alert alert-warning">
                    Esta cotización no podrá convertirse a venta ni editarse después de cancelarla.
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, cancelar cotización',
            cancelButtonText: 'No, volver atrás',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader
                Swal.fire({
                    title: 'Cancelando cotización...',
                    html: 'Por favor espere',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar formulario
                e.target.submit();
            }
        });
    });
});
</script>
@endpush
