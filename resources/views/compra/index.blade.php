@extends('layouts.app')

@section('title','Compras')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Compras</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>

    @can('crear-compra')
    <div class="mb-4">
        <a href="{{route('compras.create')}}">
            <button type="button" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Compra
            </button>
        </a>
    </div>
    @endcan

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Compras</div>
                            <div class="h2 mb-0">{{$estadisticas['total_compras']}}</div>
                        </div>
                        <i class="fas fa-shopping-cart fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Monto Total</div>
                            <div class="h2 mb-0">Q {{number_format($estadisticas['monto_total'], 2)}}</div>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Compras Hoy</div>
                            <div class="h2 mb-0">{{$estadisticas['compras_hoy']}}</div>
                        </div>
                        <i class="fas fa-calendar-day fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Monto Hoy</div>
                            <div class="h2 mb-0">Q {{number_format($estadisticas['monto_hoy'], 2)}}</div>
                        </div>
                        <i class="fas fa-cash-register fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Compras -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Listado de Compras
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Proveedor</th>
                        <th>Sucursal</th>
                        <th>Fecha y Hora</th>
                        <th>Total</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($compras as $item)
                    <tr>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->comprobante->tipo_comprobante}}</p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-hashtag"></i> {{$item->numero_comprobante}}
                            </p>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">
                                <span class="badge bg-secondary">
                                    {{ ucfirst($item->proveedore->persona->tipo_persona) }}
                                </span>
                            </p>
                            <p class="text-muted mb-0">{{$item->proveedore->persona->razon_social}}</p>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <i class="fas fa-store"></i> {{$item->sucursal->nombre}}
                            </span>
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">
                                <i class="fa-solid fa-calendar-days"></i>
                                {{\Carbon\Carbon::parse($item->fecha_hora)->format('d/m/Y')}}
                            </p>
                            <p class="fw-semibold mb-0">
                                <i class="fa-solid fa-clock"></i>
                                {{\Carbon\Carbon::parse($item->fecha_hora)->format('H:i')}}
                            </p>
                        </td>
                        <td>
                            <strong class="text-success">Q {{number_format($item->total, 2)}}</strong>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-user"></i> {{$item->usuario->name ?? 'N/A'}}
                            </small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                @can('mostrar-compra')
                                <a href="{{route('compras.show', ['compra'=>$item])}}" class="btn btn-success btn-sm">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                @endcan

                                @can('eliminar-compra')
                                <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmModal-{{$item->id}}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    <!-- Modal de confirmación-->
                    <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Está seguro que desea eliminar esta compra?</p>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Advertencia:</strong> Esta acción no revertirá automáticamente el inventario.
                                    </div>
                                    <ul>
                                        <li><strong>Comprobante:</strong> {{$item->numero_comprobante}}</li>
                                        <li><strong>Proveedor:</strong> {{$item->proveedore->persona->razon_social}}</li>
                                        <li><strong>Total:</strong> Q {{number_format($item->total, 2)}}</li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{ route('compras.destroy',['compra'=>$item->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Confirmar Eliminación</button>
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
