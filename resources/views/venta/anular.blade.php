@extends('layouts.app')

@section('title','Anular Venta')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .info-venta {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }

    .warning-anulacion {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .tiempo-restante {
        font-size: 2rem;
        font-weight: bold;
    }

    .producto-item {
        background: #f8f9fa;
        padding: 10px;
        border-left: 3px solid #007bff;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center text-danger">
        <i class="fas fa-ban"></i> Anular Venta
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Anular Venta</li>
    </ol>

    <div class="row justify-content-center">
        <div class="col-lg-10">

            <!-- Información de la venta -->
            <div class="info-venta mb-4">
                <h4 class="mb-3"><i class="fas fa-file-invoice"></i> Información de la Venta</h4>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Número:</strong> {{$venta->numero_comprobante}}</p>
                        <p class="mb-2"><strong>Tipo:</strong>
                            @if($venta->tipo_factura === 'FACT')
                                <span class="badge bg-success">FEL</span>
                            @else
                                <span class="badge bg-secondary">Recibo</span>
                            @endif
                        </p>
                        <p class="mb-2"><strong>Cliente:</strong> {{$venta->cliente->persona->razon_social}}</p>
                        <p class="mb-0"><strong>NIT:</strong> {{$venta->cliente->persona->nit ?? 'CF'}}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Fecha:</strong> {{$venta->fecha_hora->format('d/m/Y H:i')}}</p>
                        <p class="mb-2"><strong>Total:</strong> Q {{number_format($venta->total, 2)}}</p>
                        <p class="mb-2"><strong>Vendedor:</strong> {{$venta->user->name}}</p>
                        <p class="mb-0"><strong>Sucursal:</strong> {{$venta->sucursal->nombre}}</p>
                    </div>
                </div>

                @if($venta->tipo_factura === 'FACT' && $venta->logFel)
                <div class="mt-3 pt-3 border-top">
                    <p class="mb-2"><strong>UUID FEL:</strong> <code>{{$venta->logFel->uuid}}</code></p>
                    <p class="mb-0"><strong>Certificación:</strong> {{$venta->fecha_certificacion_fel->format('d/m/Y H:i:s')}}</p>
                </div>
                @endif
            </div>

            <!-- Productos de la venta -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos de la Venta</h5>
                </div>
                <div class="card-body">
                    @foreach($venta->productos as $producto)
                    <div class="producto-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <strong>{{$producto->codigo}}</strong> - {{$producto->nombre}}
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-primary">{{$producto->pivot->cantidad}} unidades</span>
                            </div>
                            <div class="col-md-2 text-end">
                                Q {{number_format($producto->pivot->precio_venta, 2)}}
                            </div>
                            <div class="col-md-2 text-end">
                                <strong>Q {{number_format($producto->pivot->cantidad * $producto->pivot->precio_venta - $producto->pivot->descuento, 2)}}</strong>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Advertencia de tiempo -->
            {{-- <div class="warning-anulacion">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-2"><i class="fas fa-exclamation-triangle"></i> Tiempo para Anular</h4>
                        <p class="mb-2">Han transcurrido <strong>{{$diasTranscurridos}} de {{$diasLimite}} días</strong> desde la emisión.</p>
                        <p class="mb-0">
                            @if($diasLimite - $diasTranscurridos <= 1)
                                <span class="badge bg-danger">¡ÚLTIMO DÍA PARA ANULAR!</span>
                            @elseif($diasLimite - $diasTranscurridos <= 2)
                                <span class="badge bg-warning text-dark">¡QUEDAN POCOS DÍAS!</span>
                            @else
                                <span class="badge bg-info">Aún puede anular esta venta</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="tiempo-restante">
                            {{$diasLimite - $diasTranscurridos}}
                        </div>
                        <p class="mb-0">días restantes</p>
                    </div>
                </div>
            </div> --}}

            <!-- Formulario de anulación -->
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Motivo de Anulación</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ventas.anulacion.store', $venta->id) }}" method="POST" id="formAnular">
                        @csrf

                        <div class="mb-3">
                            <label for="motivo_anulacion" class="form-label">
                                <strong>Motivo de la anulación: <span class="text-danger">*</span></strong>
                            </label>
                            <textarea
                                name="motivo_anulacion"
                                id="motivo_anulacion"
                                class="form-control @error('motivo_anulacion') is-invalid @enderror"
                                rows="4"
                                placeholder="Describa el motivo por el cual se anula esta venta..."
                                required
                                maxlength="500">{{ old('motivo_anulacion') }}</textarea>
                            <small class="text-muted">
                                <span id="contador">0</span>/500 caracteres
                            </small>
                            @error('motivo_anulacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Advertencia:</strong> Esta acción es irreversible.
                            @if($venta->tipo_factura === 'FACT')
                                La anulación será enviada al certificador SAT.
                            @endif
                            Los productos serán devueltos al inventario de la sucursal.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-lg w-100">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-danger btn-lg w-100" id="btnAnular">
                                    <i class="fas fa-ban"></i> Anular Venta
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
    $('#motivo_anulacion').on('input', function() {
        const length = $(this).val().length;
        $('#contador').text(length);
    });

    // Confirmación antes de enviar
    $('#formAnular').on('submit', function(e) {
        e.preventDefault();

        const motivo = $('#motivo_anulacion').val().trim();

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
            title: '¿Está seguro?',
            html: `
                <p>Esta acción anulará permanentemente la venta:</p>
                <strong>{{$venta->numero_comprobante}}</strong>
                <br><br>
                @if($venta->tipo_factura === 'FACT')
                <div class="alert alert-warning">
                    Se enviará la anulación al certificador SAT
                </div>
                @endif
                <p>Los productos serán devueltos al inventario.</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loader
                Swal.fire({
                    title: 'Procesando anulación...',
                    html: '@if($venta->tipo_factura === "FACT") Comunicándose con el certificador... @else Procesando... @endif',
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
