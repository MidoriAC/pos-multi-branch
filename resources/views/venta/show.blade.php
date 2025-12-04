@extends('layouts.app')

@section('title','Detalle de Venta')

@push('css')
<style>
    .info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .info-value {
        color: #212529;
    }
    .section-title {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-file-invoice"></i> Detalle de Venta
    </h1>
    <ol class="breadcrumb mb-4 no-print">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    {{-- Botones de Acción --}}
    <div class="row mb-4 no-print">
        <div class="col-12">
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>

            <a href="{{ route('ventas.pdf', $venta->id) }}" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </a>
            <a href="{{ route('ventas.ticket', $venta->id) }}" target="_blank" class="btn btn-primary">
                <i class="fas fa-file"></i> Generar Ticket
            </a>

            {{-- @if($venta->esFEL() && $venta->xml_fel)
            <a href="{{ route('ventas.xml', $venta->id) }}" class="btn btn-info">
                <i class="fas fa-file-code"></i> Descargar XML
            </a>
            @endif --}}

            @can('eliminar-venta')
            @if($venta->puedeAnularse())
            <a href="{{ route('ventas.anular', $venta->id) }}" class="btn btn-warning">
                <i class="fas fa-ban"></i> Anular Venta
            </a>
            @endif
            @endcan

            <button onclick="window.print()" class="btn btn-dark">
                <i class="fas fa-print"></i> Imprimir
            </button>
        </div>
    </div>

    <div class="row">
        {{-- Información General --}}
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-info-circle"></i> Información General
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-5 info-label">N° Comprobante:</div>
                        <div class="col-7 info-value">
                            <strong>{{ $venta->numero_comprobante }}</strong>
                            @if($venta->serie)
                                <span class="badge bg-secondary ms-2">Serie: {{ $venta->serie }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-5 info-label">Tipo de Documento:</div>
                        <div class="col-7 info-value">
                            @if($venta->tipo_factura === 'FACT')
                                <span class="badge bg-success">
                                    <i class="fas fa-file-invoice-dollar"></i> Factura Electrónica FEL
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-receipt"></i> Recibo Simple
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($venta->esFEL() && $venta->numero_autorizacion_fel)
                    <div class="row mb-3">
                        <div class="col-5 info-label">Autorización FEL:</div>
                        <div class="col-7 info-value">
                            <small class="text-break">{{ $venta->numero_autorizacion_fel }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 info-label">Fecha Certificación:</div>
                        <div class="col-7 info-value">
                            {{ $venta->fecha_certificacion_fel ? $venta->fecha_certificacion_fel->format('d/m/Y H:i:s') : '-' }}
                        </div>
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-5 info-label">Fecha y Hora:</div>
                        <div class="col-7 info-value">{{ $venta->fecha_hora->format('d/m/Y H:i:s') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-5 info-label">Estado:</div>
                        <div class="col-7 info-value">
                            @if($venta->anulacionFel)
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban"></i> ANULADA
                                </span>
                            @elseif($venta->estado == 1)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> ACTIVA
                                </span>
                            @else
                                <span class="badge bg-secondary">INACTIVA</span>
                            @endif
                        </div>
                    </div>

                    @if($venta->cotizacion_id)
                    <div class="row mb-3">
                        <div class="col-5 info-label">Cotización:</div>
                        <div class="col-7 info-value">
                            <a href="{{ route('cotizaciones.show', $venta->cotizacion_id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-file-alt"></i> Ver Cotización #{{ $venta->cotizacion->numero_cotizacion }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Información de Anulación --}}
            @if($venta->anulacionFel)
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-ban"></i> Información de Anulación
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-5 info-label">Fecha Anulación:</div>
                        <div class="col-7 info-value">
                            {{ $venta->anulacionFel->fecha_anulacion->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 info-label">Anulada Por:</div>
                        <div class="col-7 info-value">{{ $venta->anulacionFel->user->name }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-5 info-label">Motivo:</div>
                        <div class="col-7 info-value">{{ $venta->anulacionFel->motivo }}</div>
                    </div>
                    @if($venta->anulacionFel->numero_autorizacion_anulacion)
                    <div class="row">
                        <div class="col-5 info-label">Autorización Anulación:</div>
                        <div class="col-7 info-value">
                            <small class="text-break">{{ $venta->anulacionFel->numero_autorizacion_anulacion }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Información de Cliente y Sucursal --}}
        <div class="col-lg-6">
            {{-- Cliente --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-user"></i> Información del Cliente
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4 info-label">Nombre/Razón Social:</div>
                        <div class="col-8 info-value">
                            <strong>{{ $venta->cliente->persona->razon_social }}</strong>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 info-label">NIT:</div>
                        <div class="col-8 info-value">{{ $venta->cliente->persona->nit }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 info-label">Documento:</div>
                        <div class="col-8 info-value">{{ $venta->cliente->persona->numero_documento }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 info-label">Dirección:</div>
                        <div class="col-8 info-value">{{ $venta->cliente->persona->direccion }}</div>
                    </div>
                    @if($venta->cliente->persona->email)
                    <div class="row">
                        <div class="col-4 info-label">Email:</div>
                        <div class="col-8 info-value">{{ $venta->cliente->persona->email }}</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sucursal --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-store"></i> Sucursal
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-4 info-label">Nombre:</div>
                        <div class="col-8 info-value"><strong>{{ $venta->sucursal->nombre }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 info-label">Dirección:</div>
                        <div class="col-8 info-value">{{ $venta->sucursal->direccion }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 info-label">Teléfono:</div>
                        <div class="col-8 info-value">{{ $venta->sucursal->telefono }}</div>
                    </div>
                    <div class="row">
                        <div class="col-4 info-label">Vendedor:</div>
                        <div class="col-8 info-value">{{ $venta->user->name }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detalle de Productos --}}
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-boxes"></i> Detalle de Productos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio Unit.</th>
                            <th class="text-end">Descuento</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $contador = 1; @endphp
                        @foreach($venta->productos as $producto)
                        <tr>
                            <td class="text-center">{{ $contador++ }}</td>
                            <td><code>{{ $producto->codigo }}</code></td>
                            <td>
                                <strong>{{ $producto->nombre }}</strong>
                                @if($producto->marca)
                                    <br><small class="text-muted">
                                        <i class="fas fa-tag"></i> {{ $producto->marca->caracteristica->nombre }}
                                    </small>
                                @endif
                                @if($producto->presentacione)
                                    <small class="text-muted"> - {{ $producto->presentacione->caracteristica->nombre }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $producto->pivot->cantidad }}</span>
                                @if($producto->unidadMedida)
                                    <small class="text-muted">{{ $producto->unidadMedida->abreviatura }}</small>
                                @endif
                            </td>
                            <td class="text-end">Q {{ number_format($producto->pivot->precio_venta, 2) }}</td>
                            <td class="text-end">
                                @if($producto->pivot->descuento > 0)
                                    <span class="text-danger">- Q {{ number_format($producto->pivot->descuento, 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <strong>Q {{ number_format(($producto->pivot->cantidad * $producto->pivot->precio_venta) - $producto->pivot->descuento, 2) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">SUBTOTAL:</th>
                            <th class="text-end">Q {{ number_format($venta->total - $venta->impuesto, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="6" class="text-end">IVA (12%):</th>
                            <th class="text-end">Q {{ number_format($venta->impuesto, 2) }}</th>
                        </tr>
                        <tr class="table-success">
                            <th colspan="6" class="text-end">TOTAL:</th>
                            <th class="text-end">
                                <h4 class="mb-0">Q {{ number_format($venta->total, 2) }}</h4>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
