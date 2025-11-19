@extends('layouts.app')

@section('title','Ventas')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .card-stats {
        transition: transform 0.3s;
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .badge-tipo-factura {
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-shopping-cart"></i> Gestión de Ventas
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Total Ventas</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['total_ventas'] }}</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-success text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Monto Total</h5>
                            <span class="h2 font-weight-bold mb-0">Q {{ number_format($estadisticas['monto_total'], 2) }}</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-info text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Ventas Hoy</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['ventas_hoy'] }}</span>
                            <p class="mb-0 text-sm">Q {{ number_format($estadisticas['monto_hoy'], 2) }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Facturas FEL</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['ventas_fel'] }}</span>
                            <p class="mb-0 text-sm">Recibos: {{ $estadisticas['ventas_recibo'] }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('crear-venta')
    <div class="mb-4">
        <a href="{{route('ventas.create')}}">
            <button type="button" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Venta
            </button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Listado de Ventas
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Total</th>
                        <th>Vendedor</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $venta)
                    <tr>
                        <td>
                            <strong>{{ $venta->numero_comprobante }}</strong>
                            @if($venta->serie)
                                <br><small class="text-muted">Serie: {{ $venta->serie }}</small>
                            @endif
                            @if($venta->cotizacion_id)
                                <br><span class="badge bg-info">De Cotización</span>
                            @endif
                        </td>
                        <td>
                            @if($venta->tipo_factura === 'fel')
                                <span class="badge badge-tipo-factura bg-success">
                                    <i class="fas fa-file-invoice-dollar"></i> FEL
                                </span>
                                @if($venta->numero_autorizacion_fel)
                                    <br><small class="text-muted" title="{{ $venta->numero_autorizacion_fel }}">
                                        {{ substr($venta->numero_autorizacion_fel, 0, 15) }}...
                                    </small>
                                @endif
                            @else
                                <span class="badge badge-tipo-factura bg-secondary">
                                    <i class="fas fa-receipt"></i> Recibo
                                </span>
                            @endif
                        </td>
                        <td>
                            {{ $venta->fecha_hora->format('d/m/Y') }}<br>
                            <small class="text-muted">{{ $venta->fecha_hora->format('H:i') }}</small>
                        </td>
                        <td>
                            <strong>{{ $venta->cliente->persona->razon_social }}</strong><br>
                            <small class="text-muted">{{ $venta->cliente->persona->numero_documento }}</small>
                        </td>
                        <td>{{ $venta->sucursal->nombre }}</td>
                        <td class="text-end">
                            <strong>Q {{ number_format($venta->total, 2) }}</strong>
                        </td>
                        <td>{{ $venta->user->name }}</td>
                        <td>
                            @if($venta->estado == 1)
                                @if($venta->anulacionFel)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban"></i> Anulada
                                    </span>
                                    <br><small class="text-muted">
                                        {{ $venta->anulacionFel->fecha_anulacion->format('d/m/Y') }}
                                    </small>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Activa
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times-circle"></i> Inactiva
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <div class="btn-group" role="group">
                                    <button title="Opciones" class="btn btn-datatable btn-icon btn-transparent-dark dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu text-bg-light">
                                        @can('mostrar-venta')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('ventas.show', $venta->id) }}">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('ventas.pdf', $venta->id) }}" target="_blank">
                                                <i class="fas fa-file-pdf"></i> Generar PDF
                                            </a>
                                        </li>
                                        @endcan

                                        @if($venta->esFEL() && $venta->xml_fel)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="descargarXML({{ $venta->id }})">
                                                <i class="fas fa-file-code"></i> Descargar XML
                                            </a>
                                        </li>
                                        @endif

                                        <li><hr class="dropdown-divider"></li>

                                        @can('eliminar-venta')
                                        @if($venta->puedeAnularse())
                                        <li>
                                            <a class="dropdown-item text-danger" href="{{ route('ventas.anular', $venta->id) }}">
                                                <i class="fas fa-ban"></i> Anular Venta
                                            </a>
                                        </li>
                                        @endif
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
<script>
function descargarXML(ventaId) {
    window.location.href = '/ventas/' + ventaId + '/xml';
}
</script>
@endpush
