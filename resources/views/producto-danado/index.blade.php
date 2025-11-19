@extends('layouts.app')

@section('title','Productos Dañados')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .badge-motivo {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Control de Productos Dañados</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Productos Dañados</li>
    </ol>

    @can('registrar-producto-danado')
    <div class="mb-4">
        <a href="{{route('productos-danados.create')}}">
            <button type="button" class="btn btn-danger">
                <i class="fas fa-exclamation-triangle"></i> Reportar Producto Dañado
            </button>
        </a>
    </div>
    @endcan

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Pendientes</div>
                            <div class="h5 mb-0">
                                {{ $productosDanados->where('estado', 'PENDIENTE')->count() }}
                            </div>
                        </div>
                        <i class="fas fa-clock fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Aprobados</div>
                            <div class="h5 mb-0">
                                {{ $productosDanados->where('estado', 'APROBADO')->count() }}
                            </div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Rechazados</div>
                            <div class="h5 mb-0">
                                {{ $productosDanados->where('estado', 'RECHAZADO')->count() }}
                            </div>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Pérdida Total</div>
                            <div class="h5 mb-0">
                                Q {{ number_format($productosDanados->where('estado', 'aprobado')->sum('costo_perdida'), 2) }}
                            </div>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Tabla de Productos Dañados
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Cantidad</th>
                        <th>Motivo</th>
                        <th>Costo Pérdida</th>
                        <th>Estado</th>
                        <th>Reportado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productosDanados as $item)
                    <tr>
                        <td>
                            <small>{{$item->fecha_registro->format('d/m/Y H:i')}}</small>
                        </td>
                        <td>
                            <strong>{{$item->producto->nombre}}</strong><br>
                            <small class="text-muted">Código: {{$item->producto->codigo}}</small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{$item->sucursal->nombre}}</span>
                        </td>
                        <td>
                            <span class="badge bg-danger">{{$item->cantidad}}</span>
                        </td>
                        <td>
                            @php
                                $motivoBadge = match($item->motivo) {
                                    'vencido' => 'bg-warning',
                                    'roto' => 'bg-danger',
                                    'deteriorado' => 'bg-secondary',
                                    'humedad' => 'bg-primary',
                                    'contaminacion' => 'bg-dark',
                                    'defecto_fabrica' => 'bg-info',
                                    'otro' => 'bg-light text-dark',
                                    default => 'bg-secondary'
                                };
                                $motivoTexto = match($item->motivo) {
                                    'vencido' => 'Vencido',
                                    'roto' => 'Roto',
                                    'deteriorado' => 'Deteriorado',
                                    'humedad' => 'Humedad',
                                    'contaminacion' => 'Contaminación',
                                    'defecto_fabrica' => 'Defecto de Fábrica',
                                    'otro' => 'Otro',
                                    default => $item->motivo
                                };
                            @endphp
                            <span class="badge {{$motivoBadge}} badge-motivo">
                                {{$motivoTexto}}
                            </span>
                        </td>
                        <td>
                            <strong>Q {{ number_format($item->costo_perdida, 2) }}</strong>
                        </td>
                        <td>
                            @if($item->estado == 'PENDIENTE')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            @elseif($item->estado == 'APROBADO')
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Aprobado
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times"></i> Rechazado
                                </span>
                            @endif
                        </td>
                        <td>
                            <small>{{$item->usuario->name}}</small>
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <div>
                                    <button title="Opciones" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg class="svg-inline--fa fa-ellipsis-vertical" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="ellipsis-vertical" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512">
                                            <path fill="currentColor" d="M56 472a56 56 0 1 1 0-112 56 56 0 1 1 0 112zm0-160a56 56 0 1 1 0-112 56 56 0 1 1 0 112zM0 96a56 56 0 1 1 112 0A56 56 0 1 1 0 96z"></path>
                                        </svg>
                                    </button>
                                    <ul class="dropdown-menu text-bg-light" style="font-size: small;">
                                        <!-----Ver Detalles--->
                                        @can('ver-producto-danado')
                                        <li>
                                            <a class="dropdown-item" href="{{route('productos-danados.show', $item->id)}}">
                                                <i class="fas fa-eye"></i> Ver Detalles
                                            </a>
                                        </li>
                                        @endcan

                                        @if($item->estado == 'pendiente')
                                            @can('aprobar-producto-danado')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-success" data-bs-toggle="modal" data-bs-target="#aprobarModal-{{$item->id}}">
                                                    <i class="fas fa-check"></i> Aprobar
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#rechazarModal-{{$item->id}}">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            </li>
                                            @endcan
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Aprobar -->
                    <div class="modal fade" id="aprobarModal-{{$item->id}}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Aprobar Reporte</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de aprobar este reporte?</p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Advertencia:</strong> Al aprobar, se descontarán <strong>{{$item->cantidad}} unidades</strong> del inventario de la sucursal <strong>{{$item->sucursal->nombre}}</strong>.
                                    </div>
                                    <ul>
                                        <li><strong>Producto:</strong> {{$item->producto->nombre}}</li>
                                        <li><strong>Cantidad:</strong> {{$item->cantidad}}</li>
                                        <li><strong>Costo de pérdida:</strong> Q {{number_format($item->costo_perdida, 2)}}</li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{route('productos-danados.aprobar', $item->id)}}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Aprobar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Rechazar -->
                    <div class="modal fade" id="rechazarModal-{{$item->id}}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Rechazar Reporte</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro de rechazar este reporte?</p>
                                    <p><strong>Producto:</strong> {{$item->producto->nombre}}</p>
                                    <p><strong>Reportado por:</strong> {{$item->usuario->name}}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{route('productos-danados.rechazar', $item->id)}}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
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
