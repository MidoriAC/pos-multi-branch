@extends('layouts.app')

@section('title','Detalles de Producto Dañado')

@push('css')
<style>
    .detail-card {
        border: none;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .detail-label {
        font-weight: bold;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .detail-value {
        font-size: 1.1rem;
        color: #212529;
    }
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -22px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #0d6efd;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #0d6efd;
    }
    .timeline-item:after {
        content: '';
        position: absolute;
        left: -17px;
        top: 17px;
        width: 2px;
        height: calc(100% - 10px);
        background: #dee2e6;
    }
    .timeline-item:last-child:after {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalles del Reporte de Producto Dañado</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos-danados.index')}}">Productos Dañados</a></li>
        <li class="breadcrumb-item active">Detalles del Reporte</li>
    </ol>

    <div class="row">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Información del Producto -->
            <div class="card detail-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-box"></i> Información del Producto
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-label">Código</div>
                            <div class="detail-value">
                                <span class="badge bg-secondary fs-6">{{$productosDanado->producto->codigo}}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Nombre del Producto</div>
                            <div class="detail-value">{{$productosDanado->producto->nombre}}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Marca</div>
                            <div class="detail-value">
                                @if($productosDanado->producto->marca)
                                    {{$productosDanado->producto->marca->caracteristica->nombre}}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Presentación</div>
                            <div class="detail-value">
                                @if($productosDanado->producto->presentacione)
                                    {{$productosDanado->producto->presentacione->caracteristica->nombre}}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Unidad de Medida</div>
                            <div class="detail-value">
                                @if($productosDanado->producto->unidadMedida)
                                    {{$productosDanado->producto->unidadMedida->nombre}} ({{$productosDanado->producto->unidadMedida->abreviatura}})
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles del Daño -->
            <div class="card detail-card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Detalles del Daño
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detail-label">Cantidad Dañada</div>
                            <div class="detail-value">
                                <span class="badge bg-danger fs-5">{{$productosDanado->cantidad}} unidades</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Motivo</div>
                            <div class="detail-value">
                                @php
                                    $motivoBadge = match($productosDanado->motivo) {
                                        'vencido' => 'bg-warning',
                                        'roto' => 'bg-danger',
                                        'deteriorado' => 'bg-secondary',
                                        'humedad' => 'bg-primary',
                                        'contaminacion' => 'bg-dark',
                                        'defecto_fabrica' => 'bg-info',
                                        'otro' => 'bg-light text-dark',
                                        default => 'bg-secondary'
                                    };
                                    $motivoTexto = match($productosDanado->motivo) {
                                        'vencido' => 'Vencido',
                                        'roto' => 'Roto',
                                        'deteriorado' => 'Deteriorado',
                                        'humedad' => 'Humedad',
                                        'contaminacion' => 'Contaminación',
                                        'defecto_fabrica' => 'Defecto de Fábrica',
                                        'otro' => 'Otro',
                                        default => $productosDanado->motivo
                                    };
                                @endphp
                                <span class="badge {{$motivoBadge}} fs-6">{{$motivoTexto}}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-label">Costo de Pérdida</div>
                            <div class="detail-value text-danger">
                                <strong>Q {{number_format($productosDanado->costo_perdida, 2)}}</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="detail-label">Descripción</div>
                            <div class="detail-value">
                                <div class="alert alert-light">
                                    {{$productosDanado->descripcion}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ubicación -->
            <div class="card detail-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt"></i> Ubicación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-label">Sucursal</div>
                            <div class="detail-value">
                                <i class="fas fa-store text-info"></i>
                                {{$productosDanado->sucursal->nombre}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-label">Ubicación Específica</div>
                            <div class="detail-value">
                                @if($productosDanado->ubicacion)
                                    <i class="fas fa-map-pin text-info"></i>
                                    {{$productosDanado->ubicacion->codigo}} - {{$productosDanado->ubicacion->nombre}}
                                @else
                                    <span class="text-muted">No especificada</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Lateral -->
        <div class="col-lg-4">
            <!-- Estado y Acciones -->
            <div class="card detail-card mb-4">
                <div class="card-header
                    @if($productosDanado->estado == 'PENDIENTE') bg-warning
                    @elseif($productosDanado->estado == 'APROBADO') bg-success
                    @else bg-danger
                    @endif text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Estado
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($productosDanado->estado == 'PENDIENTE')
                        <div class="mb-3">
                            <i class="fas fa-clock fa-3x text-warning"></i>
                            <h4 class="mt-2">Pendiente de Aprobación</h4>
                            <p class="text-muted">Este reporte está esperando revisión</p>
                        </div>

                        @can('aprobar-producto-danado')
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#aprobarModal">
                                <i class="fas fa-check"></i> Aprobar Reporte
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rechazarModal">
                                <i class="fas fa-times"></i> Rechazar Reporte
                            </button>
                        </div>
                        @endcan

                    @elseif($productosDanado->estado == 'APROBADO')
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-3x text-success"></i>
                            <h4 class="mt-2">Aprobado</h4>
                            <p class="text-muted">El stock ha sido descontado</p>
                        </div>
                    @else
                        <div class="mb-3">
                            <i class="fas fa-times-circle fa-3x text-danger"></i>
                            <h4 class="mt-2">Rechazado</h4>
                            <p class="text-muted">Este reporte fue rechazado</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Línea de Tiempo -->
            <div class="card detail-card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Historial
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="detail-label">
                                <i class="fas fa-plus-circle"></i> Reportado
                            </div>
                            <div>{{$productosDanado->fecha_registro->format('d/m/Y H:i')}}</div>
                            <small class="text-muted">
                                Por: {{$productosDanado->usuario->name}}
                            </small>
                        </div>

                        @if($productosDanado->estado != 'PENDIENTE')
                        <div class="timeline-item">
                            <div class="detail-label">
                                @if($productosDanado->estado == 'APROBADO')
                                    <i class="fas fa-check text-success"></i> Aprobado
                                @else
                                    <i class="fas fa-times text-danger"></i> Rechazado
                                @endif
                            </div>
                            <div>{{$productosDanado->updated_at->format('d/m/Y H:i')}}</div>
                            @if($productosDanado->aprobador)
                            <small class="text-muted">
                                Por: {{$productosDanado->aprobador->name}}
                            </small>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card detail-card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info"></i> Información</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="detail-label">ID del Reporte</small>
                        <div class="detail-value">#{{$productosDanado->id}}</div>
                    </div>
                    <div class="mb-3">
                        <small class="detail-label">Fecha de Registro</small>
                        <div class="detail-value">{{$productosDanado->fecha_registro->format('d/m/Y H:i:s')}}</div>
                    </div>
                    <div class="mb-3">
                        <small class="detail-label">Última Actualización</small>
                        <div class="detail-value">{{$productosDanado->updated_at->format('d/m/Y H:i:s')}}</div>
                    </div>
                    <div>
                        <small class="detail-label">Reportado Por</small>
                        <div class="detail-value">
                            <i class="fas fa-user"></i> {{$productosDanado->usuario->name}}
                            <br>
                            <small class="text-muted">{{$productosDanado->usuario->email}}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de Regresar -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('productos-danados.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
</div>

<!-- Modal Aprobar -->
<div class="modal fade" id="aprobarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Aprobar Reporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="lead">¿Está seguro de aprobar este reporte?</p>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> Al aprobar este reporte:
                </div>

                <ul class="list-group mb-3">
                    <li class="list-group-item">
                        <strong>Producto:</strong> {{$productosDanado->producto->nombre}}
                    </li>
                    <li class="list-group-item">
                        <strong>Cantidad a descontar:</strong>
                        <span class="badge bg-danger">{{$productosDanado->cantidad}} unidades</span>
                    </li>
                    <li class="list-group-item">
                        <strong>Sucursal:</strong> {{$productosDanado->sucursal->nombre}}
                    </li>
                    <li class="list-group-item">
                        <strong>Costo de pérdida:</strong>
                        <span class="text-danger fw-bold">Q {{number_format($productosDanado->costo_perdida, 2)}}</span>
                    </li>
                </ul>

                <div class="alert alert-danger">
                    <i class="fas fa-info-circle"></i>
                    Esta acción <strong>descontará automáticamente</strong> el stock del inventario y <strong>no se puede revertir</strong>.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form action="{{route('productos-danados.aprobar', $productosDanado->id)}}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Aprobación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rechazar -->
<div class="modal fade" id="rechazarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Rechazar Reporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="lead">¿Está seguro de rechazar este reporte?</p>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Al rechazar este reporte:
                </div>

                <ul>
                    <li><strong>NO</strong> se descontará el stock del inventario</li>
                    <li>El reporte quedará marcado como <strong>rechazado</strong></li>
                    <li>El usuario que reportó será notificado del rechazo</li>
                </ul>

                <div class="card bg-light">
                    <div class="card-body">
                        <strong>Producto:</strong> {{$productosDanado->producto->nombre}}<br>
                        <strong>Reportado por:</strong> {{$productosDanado->usuario->name}}<br>
                        <strong>Motivo:</strong>
                        @php
                            $motivoTexto = match($productosDanado->motivo) {
                                'vencido' => 'Vencido',
                                'roto' => 'Roto',
                                'deteriorado' => 'Deteriorado',
                                'humedad' => 'Humedad',
                                'contaminacion' => 'Contaminación',
                                'defecto_fabrica' => 'Defecto de Fábrica',
                                'otro' => 'Otro',
                                default => $productosDanado->motivo
                            };
                        @endphp
                        {{$motivoTexto}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form action="{{route('productos-danados.rechazar', $productosDanado->id)}}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Confirmar Rechazo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
