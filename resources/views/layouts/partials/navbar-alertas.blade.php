{{-- Componente de alertas para el navbar --}}
@php
    $sucursalActual = session('sucursal_id', auth()->user()->sucursales->first()->id ?? null);
    $alertasPendientes = \App\Models\AlertaStock::where('sucursal_id', $sucursalActual)
        ->where('leida', false)
        ->orderBy('fecha_alerta', 'desc')
        ->limit(5)
        ->get();
    $totalAlertas = $alertasPendientes->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle position-relative" id="navbarDropdownAlertas"
       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fa-fw"></i>
        @if($totalAlertas > 0)
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            {{$totalAlertas > 9 ? '9+' : $totalAlertas}}
            <span class="visually-hidden">alertas sin leer</span>
        </span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownAlertas" style="min-width: 350px;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-bell me-2"></i>Alertas de Stock</span>
            @if($totalAlertas > 0)
            <span class="badge bg-danger">{{$totalAlertas}}</span>
            @endif
        </li>
        <li><hr class="dropdown-divider" /></li>

        @if($alertasPendientes->count() > 0)
            @foreach($alertasPendientes as $alerta)
            <li>
                <a class="dropdown-item py-2" href="{{ route('alertas-stock.index') }}">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            @if($alerta->tipo_alerta == 'STOCK_AGOTADO')
                                <i class="fas fa-times-circle text-danger fa-lg"></i>
                            @elseif($alerta->tipo_alerta == 'STOCK_MINIMO')
                                <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                            @else
                                <i class="fas fa-exclamation-circle text-info fa-lg"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="fw-bold small">{{$alerta->producto->nombre}}</div>
                            <div class="small text-muted">
                                @if($alerta->tipo_alerta == 'STOCK_AGOTADO')
                                    Sin stock disponible
                                @elseif($alerta->tipo_alerta == 'STOCK_MINIMO')
                                    Stock mínimo alcanzado ({{$alerta->stock_actual}})
                                @else
                                    Stock bajo ({{$alerta->stock_actual}})
                                @endif
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-clock"></i> {{$alerta->fecha_alerta->diffForHumans()}}
                            </div>
                        </div>
                    </div>
                </a>
            </li>
            <li><hr class="dropdown-divider" /></li>
            @endforeach

            <li>
                <a class="dropdown-item text-center small text-primary" href="{{ route('alertas-stock.index') }}">
                    <i class="fas fa-eye"></i> Ver todas las alertas
                </a>
            </li>
        @else
            <li class="dropdown-item text-center py-3">
                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                <div class="small text-muted">No hay alertas pendientes</div>
            </li>
        @endif
    </ul>
</li>

@push('js')
<script>
    // Actualizar badge de alertas cada 2 minutos
    setInterval(() => {
        fetch('/alertas-stock/api/no-leidas?sucursal_id={{ $sucursalActual }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('#navbarDropdownAlertas .badge');
                if (data.total > 0) {
                    if (!badge) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                        newBadge.innerHTML = `${data.total > 9 ? '9+' : data.total}<span class="visually-hidden">alertas sin leer</span>`;
                        document.querySelector('#navbarDropdownAlertas').appendChild(newBadge);
                    } else {
                        badge.textContent = data.total > 9 ? '9+' : data.total;
                    }
                } else if (badge) {
                    badge.remove();
                }
            })
            .catch(error => console.error('Error al actualizar alertas:', error));
    }, 120000); // 2 minutos
</script>
@endpush
