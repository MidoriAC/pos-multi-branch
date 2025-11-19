@extends('layouts.app')

@section('title','Cotizaciones')

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
    .badge-estado {
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
    }
    .vencimiento-pronto {
        color: #dc3545;
        font-weight: bold;
    }
    .vencimiento-normal {
        color: #28a745;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-file-invoice"></i> Gestión de Cotizaciones
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Cotizaciones</li>
    </ol>

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Total Cotizaciones</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['total_cotizaciones'] }}</span>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x"></i>
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
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Pendientes</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['pendientes'] }}</span>
                            <p class="mb-0 text-sm">Q {{ number_format($estadisticas['monto_pendiente'], 2) }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x"></i>
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
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Convertidas</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['convertidas'] }}</span>
                            <p class="mb-0 text-sm">Q {{ number_format($estadisticas['monto_convertido'], 2) }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-stats bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title text-uppercase text-white-50 mb-0">Vencidas</h5>
                            <span class="h2 font-weight-bold mb-0">{{ $estadisticas['vencidas'] }}</span>
                            <p class="mb-0 text-sm">Canceladas: {{ $estadisticas['canceladas'] }}</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('crear-cotizacion')
    <div class="mb-4">
        <a href="{{route('cotizaciones.create')}}">
            <button type="button" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Cotización
            </button>
        </a>
    </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Listado de Cotizaciones
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>N° Cotización</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Total</th>
                        <th>Validez</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cotizaciones as $cotizacion)
                    <tr>
                        <td>
                            <strong>{{ $cotizacion->numero_cotizacion }}</strong>
                        </td>
                        <td>
                            {{ $cotizacion->fecha_hora->format('d/m/Y') }}<br>
                            <small class="text-muted">{{ $cotizacion->fecha_hora->format('H:i') }}</small>
                        </td>
                        <td>
                            <strong>{{ $cotizacion->cliente->persona->razon_social }}</strong><br>
                            <small class="text-muted">{{ $cotizacion->cliente->persona->numero_documento }}</small>
                        </td>
                        <td>{{ $cotizacion->sucursal->nombre }}</td>
                        <td class="text-end">
                            <strong>Q {{ number_format($cotizacion->total, 2) }}</strong>
                        </td>
                        <td>
                            <span class="{{ $cotizacion->diasRestantes() <= 3 && $cotizacion->estado === 'PENDIENTE' ? 'vencimiento-pronto' : 'vencimiento-normal' }}">
                                @if($cotizacion->estado === 'PENDIENTE')
                                    @if($cotizacion->diasRestantes() > 0)
                                        {{ $cotizacion->diasRestantes() }} días restantes
                                    @else
                                        Vencida
                                    @endif
                                @else
                                    {{ $cotizacion->fecha_vencimiento->format('d/m/Y') }}
                                @endif
                            </span>
                        </td>
                        <td>
                            {!! $cotizacion->obtenerEstadoBadge() !!}
                            @if($cotizacion->venta)
                                <br><small class="text-muted">
                                    Venta: {{ $cotizacion->venta->numero_comprobante }}
                                </small>
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
                                        @can('ver-cotizacion')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotizaciones.show', $cotizacion->id) }}">
                                                <i class="fas fa-eye"></i> Ver Detalle
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotizaciones.pdf', $cotizacion->id) }}" target="_blank">
                                                <i class="fas fa-file-pdf"></i> Generar PDF
                                            </a>
                                        </li>
                                        @endcan

                                        <li><hr class="dropdown-divider"></li>

                                        @can('editar-cotizacion')
                                        @if($cotizacion->puedeEditarse())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotizaciones.edit', $cotizacion->id) }}">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </li>
                                        @endif
                                        @endcan

                                        @can('convertir-cotizacion')
                                        @if($cotizacion->puedeConvertirse())
                                        <li>
                                            <a class="dropdown-item text-success" href="{{ route('cotizaciones.convertir', $cotizacion->id) }}">
                                                <i class="fas fa-exchange-alt"></i> Convertir a Venta
                                            </a>
                                        </li>
                                        @endif
                                        @endcan

                                        <li>
                                            <a class="dropdown-item" href="{{ route('cotizaciones.duplicar', $cotizacion->id) }}">
                                                <i class="fas fa-copy"></i> Duplicar
                                            </a>
                                        </li>

                                        <li><hr class="dropdown-divider"></li>

                                        @can('eliminar-cotizacion')
                                        @if($cotizacion->puedeCancelarse())
                                        <li>
                                            <a class="dropdown-item text-danger" href="{{ route('cotizaciones.cancelar', $cotizacion->id) }}">
                                                <i class="fas fa-ban"></i> Cancelar Cotización
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
@endpush
