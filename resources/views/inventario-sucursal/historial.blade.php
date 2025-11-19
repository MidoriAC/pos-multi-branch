@extends('layouts.app')

@section('title','Historial de Movimientos')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<style>
    .info-producto {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .movimiento-entrada {
        border-left: 4px solid #28a745;
    }
    .movimiento-salida {
        border-left: 4px solid #dc3545;
    }
    .movimiento-transferencia {
        border-left: 4px solid #0d6efd;
    }
    .movimiento-ajuste {
        border-left: 4px solid #ffc107;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Historial de Movimientos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventario-sucursal.index') }}">Inventario</a></li>
        <li class="breadcrumb-item active">Historial</li>
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
                    </div>
                    <div class="col-md-6">
                        @if($inventario->ubicacion)
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <strong>Ubicación:</strong> {{$inventario->ubicacion->codigo}}
                        </p>
                        @endif
                        <p class="mb-2">
                            <i class="fas fa-dollar-sign"></i>
                            <strong>Precio:</strong> Q {{number_format($inventario->precio_venta, 2)}}
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div style="font-size: 2.5rem; font-weight: bold;">
                    {{$inventario->stock_actual}}
                </div>
                <p class="mb-0">Stock Actual</p>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Historial de Movimientos (Últimos 50)
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Origen/Destino</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                        <th>Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
                    <tr class="movimiento-{{str_replace('_', '-', explode('_', $movimiento->tipo_movimiento)[0])}}">
                        <td>
                            <small>{{$movimiento->fecha_movimiento->format('d/m/Y H:i:s')}}</small>
                        </td>
                        <td>
                            @php
                                $tipoBadge = match(true) {
                                    str_contains($movimiento->tipo_movimiento, 'entrada') => 'bg-success',
                                    str_contains($movimiento->tipo_movimiento, 'salida') => 'bg-danger',
                                    $movimiento->tipo_movimiento === 'transferencia' => 'bg-primary',
                                    str_contains($movimiento->tipo_movimiento, 'ajuste') => 'bg-warning',
                                    default => 'bg-secondary'
                                };

                                $tipoIcon = match(true) {
                                    str_contains($movimiento->tipo_movimiento, 'entrada') => 'fa-arrow-down',
                                    str_contains($movimiento->tipo_movimiento, 'salida') => 'fa-arrow-up',
                                    $movimiento->tipo_movimiento === 'transferencia' => 'fa-exchange-alt',
                                    str_contains($movimiento->tipo_movimiento, 'ajuste') => 'fa-edit',
                                    default => 'fa-circle'
                                };

                                $tipoTexto = match($movimiento->tipo_movimiento) {
                                    'entrada' => 'Entrada',
                                    'salida' => 'Salida',
                                    'transferencia' => 'Transferencia',
                                    'ajuste_entrada' => 'Ajuste Entrada',
                                    'ajuste_salida' => 'Ajuste Salida',
                                    'compra' => 'Compra',
                                    'venta' => 'Venta',
                                    default => ucfirst(str_replace('_', ' ', $movimiento->tipo_movimiento))
                                };
                            @endphp
                            <span class="badge {{$tipoBadge}}">
                                <i class="fas {{$tipoIcon}}"></i> {{$tipoTexto}}
                            </span>
                        </td>
                        <td>
                            @if(str_contains($movimiento->tipo_movimiento, 'entrada') || $movimiento->tipo_movimiento === 'compra')
                                <span class="badge bg-success">+{{$movimiento->cantidad}}</span>
                            @else
                                <span class="badge bg-danger">-{{$movimiento->cantidad}}</span>
                            @endif
                        </td>
                        <td>
                            @if($movimiento->tipo_movimiento === 'transferencia')
                                <div>
                                    <small class="text-muted">Origen:</small><br>
                                    <span class="badge bg-secondary">{{$movimiento->sucursalOrigen->nombre ?? 'N/A'}}</span>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">Destino:</small><br>
                                    <span class="badge bg-info">{{$movimiento->sucursalDestino->nombre ?? 'N/A'}}</span>
                                </div>
                            @elseif($movimiento->sucursalOrigen)
                                <span class="badge bg-secondary">{{$movimiento->sucursalOrigen->nombre}}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{$movimiento->motivo ?? '-'}}</small>
                        </td>
                        <td>
                            @if($movimiento->usuario)
                                <small>{{$movimiento->usuario->name}}</small>
                            @else
                                <small class="text-muted">Sistema</small>
                            @endif
                        </td>
                        <td>
                            @if($movimiento->compra_id)
                                <span class="badge bg-info">Compra #{{$movimiento->compra_id}}</span>
                            @elseif($movimiento->venta_id)
                                <span class="badge bg-success">Venta #{{$movimiento->venta_id}}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay movimientos registrados para este producto</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Botón de Regresar -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('inventario-sucursal.index', ['sucursal_id' => $inventario->sucursal_id]) }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Volver al Inventario
            </a>
        </div>
    </div>
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
