@extends('layouts.app')

@section('title','Inventario por Sucursal')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stock-badge {
        font-size: 1rem;
        padding: 0.5em 0.8em;
    }
    .stock-bajo {
        background-color: #dc3545 !important;
    }
    .stock-medio {
        background-color: #ffc107 !important;
    }
    .stock-alto {
        background-color: #28a745 !important;
    }
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
    <h1 class="mt-4 text-center">Inventario por Sucursal</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Inventario</li>
    </ol>

    <!-- Selector de Sucursal -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('inventario-sucursal.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="sucursal_id" class="form-label">
                        <i class="fas fa-store"></i> Seleccionar Sucursal:
                    </label>
                    <select name="sucursal_id" id="sucursal_id" class="form-select" onchange="this.form.submit()">
                        @foreach($sucursales as $sucursal)
                        <option value="{{$sucursal->id}}" @selected($sucursalSeleccionada == $sucursal->id)>
                            {{$sucursal->nombre}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        {{-- @can('transferir-inventario')
                        <a href="{{route('inventario-sucursal.transferir')}}" class="btn btn-primary">
                            <i class="fas fa-exchange-alt"></i> Transferir Entre Sucursales
                        </a>
                        @endcan --}}
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Productos</div>
                            <div class="h2 mb-0">{{$estadisticas['total_productos']}}</div>
                        </div>
                        <i class="fas fa-boxes fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Stock Total</div>
                            <div class="h2 mb-0">{{number_format($estadisticas['stock_total'])}}</div>
                        </div>
                        <i class="fas fa-cubes fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Valor Inventario</div>
                            <div class="h2 mb-0">Q {{number_format($estadisticas['valor_inventario'], 2)}}</div>
                        </div>
                        <i class="fas fa-dollar-sign fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Bajo Mínimo</div>
                            <div class="h2 mb-0">{{$estadisticas['productos_bajo_minimo']}}</div>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-warehouse me-1"></i>
            Inventario - {{$sucursales->firstWhere('id', $sucursalSeleccionada)->nombre ?? 'Sucursal'}}
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-hover fs-6">
                <thead>
                    <tr>
                        {{-- <th></th> --}}
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Ubicación</th>
                        <th>Stock Actual</th>
                        <th>Stock Mín/Máx</th>
                        <th>Precio Venta</th>
                        <th>Valor Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventarios as $item)
                    <tr>
                        {{-- @if($item->tieneAlertaActiva())
                            <span class="badge bg-danger mb-1">
                                <i class="fas fa-bell"></i> Alerta Activa
                            </span>
                            <br>
                        @endif --}}
                        <td><strong>{{$item->producto->codigo}}</strong></td>
                        <td>
                            {{$item->producto->nombre}}
                            @if($item->producto->presentacione)
                            <br><small class="text-muted">{{$item->producto->presentacione->caracteristica->nombre}}</small>
                            @endif
                        </td>
                        <td>
                            @if($item->producto->marca)
                            <span class="badge bg-primary">{{$item->producto->marca->caracteristica->nombre}}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->ubicacion)
                            <span class="badge bg-info">{{$item->ubicacion->codigo}}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $porcentaje = $item->stock_maximo > 0 ? ($item->stock_actual / $item->stock_maximo) * 100 : 0;
                                $badgeClass = 'stock-alto';
                                if ($item->stock_actual <= $item->stock_minimo) {
                                    $badgeClass = 'stock-bajo';
                                } elseif ($porcentaje < 50) {
                                    $badgeClass = 'stock-medio';
                                }
                            @endphp
                            <span class="badge {{$badgeClass}} stock-badge">
                                {{$item->stock_actual}}
                                @if($item->producto->unidadMedida)
                                    {{$item->producto->unidadMedida->abreviatura}}
                                @endif
                            </span>
                        </td>
                        <td>
                            <small>
                                Mín: <strong>{{$item->stock_minimo}}</strong><br>
                                Máx: <strong>{{$item->stock_maximo}}</strong>
                            </small>
                        </td>
                        <td>
                            <strong>Q {{number_format($item->precio_venta, 2)}}</strong>
                        </td>
                        <td>
                            <strong class="text-success">
                                Q {{number_format($item->stock_actual * $item->precio_venta, 2)}}
                            </strong>
                        </td>
                        <td>
                            @if($item->stock_actual <= $item->stock_minimo)
                            <span class="badge bg-danger">
                                <i class="fas fa-exclamation-triangle"></i> Bajo Stock
                            </span>
                            @elseif($item->stock_actual == 0)
                            <span class="badge bg-dark">
                                <i class="fas fa-times"></i> Sin Stock
                            </span>
                            @else
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Normal
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    {{-- @can('ajustar-inventario')
                                    <li>
                                        <a class="dropdown-item" href="{{route('inventario-sucursal.ajustar', $item->id)}}">
                                            <i class="fas fa-edit"></i> Ajustar Stock
                                        </a>
                                    </li>
                                    @endcan --}}
                                    {{-- <li>
                                        <a class="dropdown-item" href="{{route('inventario-sucursal.historial', $item->id)}}">
                                            <i class="fas fa-history"></i> Ver Historial
                                        </a>
                                    </li> --}}
                                    <li>
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#configModal-{{$item->id}}">
                                            <i class="fas fa-cog"></i> Configurar
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Configuración -->
                    <div class="modal fade" id="configModal-{{$item->id}}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Configurar Inventario</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{route('inventario-sucursal.update-config', $item->id)}}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>{{$item->producto->nombre}}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Stock Mínimo:</label>
                                            <input type="number" name="stock_minimo" class="form-control" value="{{$item->stock_minimo}}" min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Stock Máximo:</label>
                                            <input type="number" name="stock_maximo" class="form-control" value="{{$item->stock_maximo}}" min="0" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Precio Venta (Q):</label>
                                            <input type="number" name="precio_venta" class="form-control" value="{{$item->precio_venta}}" min="0" step="0.01" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ubicación:</label>
                                            <select name="ubicacion_id" class="form-select">
                                                <option value="">Sin ubicación</option>
                                                @foreach(App\Models\Ubicacion::where('sucursal_id', $item->sucursal_id)->where('estado', 1)->get() as $ubicacion)
                                                <option value="{{$ubicacion->id}}" @selected($item->ubicacion_id == $ubicacion->id)>
                                                    {{$ubicacion->codigo}} - {{$ubicacion->nombre}}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </form>
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
