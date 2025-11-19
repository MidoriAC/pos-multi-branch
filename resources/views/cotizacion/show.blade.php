@extends('layouts.app')

@section('title','Detalle Cotización')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .info-cotizacion {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .producto-item {
        background: #f8f9fa;
        padding: 15px;
        border-left: 4px solid #667eea;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    .badge-estado-grande {
        font-size: 1.1rem;
        padding: 0.5em 1em;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-file-invoice"></i> Detalle de Cotización
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cotizaciones.index')}}">Cotizaciones</a></li>
        <li class="breadcrumb-item active">Detalle</li>
    </ol>

    <div class="row justify-content-center">
        <div class="col-lg-11">

            <!-- Información Principal -->
            <div class="info-cotizacion">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-3">
                            <i class="fas fa-file-invoice"></i> Cotización:
                            <strong>{{$cotizacione->numero_cotizacion}}</strong>
                        </h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><i class="fas fa-calendar"></i> <strong>Fecha:</strong> {{$cotizacione->fecha_hora->format('d/m/Y H:i')}}</p>
                                <p class="mb-2"><i class="fas fa-user"></i> <strong>Cliente:</strong> {{$cotizacione->cliente->persona->razon_social}}</p>
                                <p class="mb-0"><i class="fas fa-id-card"></i> <strong>NIT:</strong> {{$cotizacione->cliente->persona->nit ?? 'CF'}}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><i class="fas fa-store"></i> <strong>Sucursal:</strong> {{$cotizacione->sucursal->nombre}}</p>
                                <p class="mb-2"><i class="fas fa-user-tie"></i> <strong>Vendedor:</strong> {{$cotizacione->user->name}}</p>
                                <p class="mb-0"><i class="fas fa-hourglass-half"></i> <strong>Validez:</strong> {{$cotizacione->validez_dias}} días</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            {!! $cotizacione->obtenerEstadoBadge() !!}
                        </div>
                        @if($cotizacione->estado === 'PENDIENTE')
                            @if($cotizacione->diasRestantes() > 0)
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-clock"></i>
                                    <strong>{{$cotizacione->diasRestantes()}}</strong> días restantes
                                    <br>
                                    <small>Vence: {{$cotizacione->fecha_vencimiento->format('d/m/Y')}}</small>
                                </div>
                            @else
                                <div class="alert alert-danger mb-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Cotización Vencida</strong>
                                </div>
                            @endif
                        @elseif($cotizacione->estado === 'CONVERTIDA')
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle"></i>
                                Convertida a Venta
                                @if($cotizacione->venta)
                                <br><strong>{{$cotizacione->venta->numero_comprobante}}</strong>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                @if($cotizacione->observaciones)
                <div class="mt-3 pt-3 border-top">
                    <p class="mb-0">
                        <i class="fas fa-comment"></i> <strong>Observaciones:</strong><br>
                        {{$cotizacione->observaciones}}
                    </p>
                </div>
                @endif
            </div>

            <!-- Productos -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos Cotizados</h5>
                </div>
                <div class="card-body">
                    @foreach($cotizacione->productos as $producto)
                    <div class="producto-item">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h6 class="mb-1">
                                    <strong>{{$producto->codigo}}</strong> - {{$producto->nombre}}
                                </h6>
                                <small class="text-muted">
                                    {{$producto->marca->caracteristica->nombre ?? ''}} -
                                    {{$producto->presentacione->caracteristica->nombre ?? ''}}
                                </small>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge bg-primary" style="font-size: 1rem;">
                                    {{$producto->pivot->cantidad}}
                                    {{$producto->unidadMedida->abreviatura ?? 'Unid'}}
                                </span>
                            </div>
                            <div class="col-md-2 text-center">
                                <small class="text-muted d-block">Precio Unit.</small>
                                <strong>Q {{number_format($producto->pivot->precio_unitario, 2)}}</strong>
                            </div>
                            @if($producto->pivot->descuento > 0)
                            <div class="col-md-1 text-center">
                                <small class="text-muted d-block">Descuento</small>
                                <span class="text-danger">-Q {{number_format($producto->pivot->descuento, 2)}}</span>
                            </div>
                            @endif
                            <div class="col-md-2 text-end">
                                <small class="text-muted d-block">Subtotal</small>
                                <h5 class="mb-0 text-primary">Q {{number_format($producto->pivot->subtotal, 2)}}</h5>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Totales -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th class="text-end">Subtotal:</th>
                                    <td class="text-end"><strong>Q {{number_format($cotizacione->subtotal, 2)}}</strong></td>
                                </tr>
                                @if($cotizacione->obtenerDescuentoTotal() > 0)
                                <tr>
                                    <th class="text-end text-danger">Descuentos:</th>
                                    <td class="text-end text-danger">-Q {{number_format($cotizacione->obtenerDescuentoTotal(), 2)}}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-end">IVA (12%):</th>
                                    <td class="text-end"><strong>Q {{number_format($cotizacione->impuesto, 2)}}</strong></td>
                                </tr>
                                <tr class="table-primary">
                                    <th class="text-end h5">TOTAL:</th>
                                    <td class="text-end h4 text-primary"><strong>Q {{number_format($cotizacione->total, 2)}}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-info-circle"></i> Información de Cliente
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Razón Social:</strong><br>{{$cotizacione->cliente->persona->razon_social}}</p>
                            <p class="mb-2"><strong>Dirección:</strong><br>{{$cotizacione->cliente->persona->direccion}}</p>
                            <p class="mb-2"><strong>Teléfono:</strong> {{$cotizacione->cliente->persona->telefono ?? 'No registrado'}}</p>
                            <p class="mb-0"><strong>Email:</strong> {{$cotizacione->cliente->persona->email ?? 'No registrado'}}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-chart-line"></i> Resumen
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <i class="fas fa-boxes"></i>
                                <strong>Total Productos:</strong>
                                {{$cotizacione->productos->count()}}
                                ({{$cotizacione->obtenerCantidadProductos()}} unidades)
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-calendar-check"></i>
                                <strong>Creada:</strong>
                                {{$cotizacione->created_at->format('d/m/Y H:i')}}
                            </p>
                            @if($cotizacione->updated_at != $cotizacione->created_at)
                            <p class="mb-2">
                                <i class="fas fa-edit"></i>
                                <strong>Última modificación:</strong>
                                {{$cotizacione->updated_at->format('d/m/Y H:i')}}
                            </p>
                            @endif
                            @if($cotizacione->fecha_conversion)
                            <p class="mb-0">
                                <i class="fas fa-exchange-alt"></i>
                                <strong>Fecha conversión:</strong>
                                {{$cotizacione->fecha_conversion->format('d/m/Y H:i')}}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-cogs"></i> Acciones Disponibles
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-arrow-left"></i> Volver al Listado
                            </a>
                        </div>

                        {{-- @can('ver-cotizacion') --}}
                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.pdf', $cotizacione->id) }}"
                               class="btn btn-danger w-100" target="_blank">
                                <i class="fas fa-file-pdf"></i> Generar PDF
                            </a>
                        </div>
                        {{-- @endcan --}}

                        @can('editar-cotizacion')
                        @if($cotizacione->puedeEditarse())
                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.edit', $cotizacione->id) }}"
                               class="btn btn-warning w-100">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                        @endif
                        @endcan

                        @can('convertir-cotizacion')
                        @if($cotizacione->puedeConvertirse())
                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.convertir', $cotizacione->id) }}"
                               class="btn btn-success w-100">
                                <i class="fas fa-exchange-alt"></i> Convertir a Venta
                            </a>
                        </div>
                        @endif
                        @endcan

                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.duplicar', $cotizacione->id) }}"
                               class="btn btn-info w-100">
                                <i class="fas fa-copy"></i> Duplicar
                            </a>
                        </div>

                        @can('eliminar-cotizacion')
                        @if($cotizacione->puedeCancelarse())
                        <div class="col-md-3">
                            <a href="{{ route('cotizaciones.cancelar', $cotizacione->id) }}"
                               class="btn btn-outline-danger w-100">
                                <i class="fas fa-ban"></i> Cancelar Cotización
                            </a>
                        </div>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
