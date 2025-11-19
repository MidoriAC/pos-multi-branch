{{-- Widget de alertas para el dashboard --}}
@php
    $sucursalActual = session('sucursal_id', auth()->user()->sucursales->first()->id ?? null);
    $alertasRecientes = \App\Models\AlertaStock::with(['producto.marca.caracteristica'])
        ->where('sucursal_id', $sucursalActual)
        ->where('leida', false)
        ->orderBy('fecha_alerta', 'desc')
        ->limit(8)
        ->get();

    $estadisticasAlertas = [
        'STOCK_AGOTADO' => \App\Models\AlertaStock::where('sucursal_id', $sucursalActual)
            ->where('leida', false)
            ->where('tipo_alerta', 'STOCK_AGOTADO')
            ->count(),
        'STOCK_MINIMO' => \App\Models\AlertaStock::where('sucursal_id', $sucursalActual)
            ->where('leida', false)
            ->where('tipo_alerta', 'STOCK_MINIMO')
            ->count(),
        'PROXIMO_VENCER' => \App\Models\AlertaStock::where('sucursal_id', $sucursalActual)
            ->where('leida', false)
            ->where('tipo_alerta', 'PROXIMO_VENCER')
            ->count(),
    ];
@endphp

<div class="card shadow-sm mb-4">
    <div class="card-header bg-gradient-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-bell"></i> Alertas de Stock Críticas
        </h5>
        <span class="badge bg-white text-danger">{{$alertasRecientes->count()}}</span>
    </div>
    <div class="card-body p-0">
        @if($alertasRecientes->count() > 0)
            <!-- Mini estadísticas -->
            <div class="row g-0 text-center border-bottom">
                <div class="col-4 border-end p-2">
                    <div class="text-danger fw-bold h4 mb-0">{{$estadisticasAlertas['STOCK_AGOTADO']}}</div>
                    <small class="text-muted">Sin Stock</small>
                </div>
                <div class="col-4 border-end p-2">
                    <div class="text-warning fw-bold h4 mb-0">{{$estadisticasAlertas['STOCK_MINIMO']}}</div>
                    <small class="text-muted">Stock Mínimo</small>
                </div>
                <div class="col-4 p-2">
                    <div class="text-info fw-bold h4 mb-0">{{$estadisticasAlertas['PROXIMO_VENCER']}}</div>
                    <small class="text-muted">Stock Bajo</small>
                </div>
            </div>

            <!-- Lista de alertas -->
            <div class="list-group list-group-flush">
                @foreach($alertasRecientes as $alerta)
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                @if($alerta->tipo_alerta == 'STOCK_AGOTADO')
                                    <i class="fas fa-times-circle text-danger me-2"></i>
                                @elseif($alerta->tipo_alerta == 'STOCK_MINIMO')
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                @else
                                    <i class="fas fa-exclamation-circle text-info me-2"></i>
                                @endif
                                <h6 class="mb-0">{{$alerta->producto->nombre}}</h6>
                            </div>
                            <small class="text-muted d-block">
                                Código: <strong>{{$alerta->producto->codigo}}</strong>
                                @if($alerta->producto->marca)
                                | Marca: <strong>{{$alerta->producto->marca->caracteristica->nombre}}</strong>
                                @endif
                            </small>
                            <div class="mt-1">
                                <span class="badge
                                    @if($alerta->tipo_alerta == 'STOCK_AGOTADO') bg-danger
                                    @elseif($alerta->tipo_alerta == 'STOCK_MINIMO') bg-warning
                                    @else bg-info
                                    @endif">
                                    Stock: {{$alerta->stock_actual}} / Mín: {{$alerta->stock_minimo}}
                                </span>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-clock"></i> {{$alerta->fecha_alerta->diffForHumans()}}
                                </small>
                            </div>
                        </div>
                        {{-- <div class="text-end ms-3">
                            <a href="{{route('pedidos-compra.create')}}"
                               class="btn btn-sm btn-outline-primary"
                               title="Crear pedido">
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                        </div> --}}
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Footer con acciones -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <a href="{{route('alertas-stock.index')}}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> Ver todas
                    </a>
                    {{-- <a href="{{route('pedidos-compra.index')}}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-shopping-cart"></i> Pedidos de Compra
                    </a> --}}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-success">¡Todo en orden!</h5>
                <p class="text-muted">No hay alertas de stock pendientes</p>
            </div>
        @endif
    </div>
</div>

<style>
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }
    .list-group-item-action:hover {
        background-color: #f8f9fa;
    }
</style>
