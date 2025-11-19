@extends('layouts.app')

@section('title','Alertas de Stock')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .alerta-card {
        transition: all 0.3s ease;
        border-left: 4px solid;
    }
    .alerta-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .alerta-sin-stock {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }
    .alerta-stock-minimo {
        border-left-color: #ffc107;
        background-color: #fffbf0;
    }
    .alerta-stock-bajo {
        border-left-color: #17a2b8;
        background-color: #f0faff;
    }
    .badge-alerta {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .stat-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .alerta-leida {
        opacity: 0.6;
    }
    .producto-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .producto-imagen {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }
    .stock-indicator {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .filtros-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-bell"></i> Alertas de Stock
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Alertas de Stock</li>
    </ol>

    <!-- Selector de Sucursal y Acciones -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('alertas-stock.index') }}" method="GET" id="formFiltros" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="sucursal_id" class="form-label">
                        <i class="fas fa-store"></i> Sucursal:
                    </label>
                    <select name="sucursal_id" id="sucursal_id" class="form-select" onchange="this.form.submit()">
                        @foreach($sucursales as $sucursal)
                        <option value="{{$sucursal->id}}" @selected($sucursalSeleccionada == $sucursal->id)>
                            {{$sucursal->nombre}}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tipo_alerta" class="form-label">
                        <i class="fas fa-filter"></i> Tipo de Alerta:
                    </label>
                    <select name="tipo_alerta" id="tipo_alerta" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        <option value="sin_stock" @selected($tipoAlerta == 'STOCK_AGOTADO')>Sin Stock</option>
                        <option value="stock_minimo" @selected($tipoAlerta == 'STOCK_MINIMO')>Stock Mínimo</option>
                        <option value="stock_bajo" @selected($tipoAlerta == 'PROXIMO_VENCER')>Stock Bajo</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch" style="margin-top: 30px;">
                        <input class="form-check-input" type="checkbox" id="solo_no_leidas" name="solo_no_leidas"
                               value="1" @checked($soloNoLeidas) onchange="this.form.submit()">
                        <label class="form-check-label" for="solo_no_leidas">
                            Solo alertas no leídas
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4 stat-card" onclick="filtrarPorTipo('sin_stock')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Sin Stock</div>
                            <div class="h2 mb-0">{{$estadisticas['sin_stock']}}</div>
                        </div>
                        <i class="fas fa-times-circle fa-3x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Ver detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 stat-card" onclick="filtrarPorTipo('STOCK_MINIMO')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Stock Mínimo</div>
                            <div class="h2 mb-0">{{$estadisticas['STOCK_MINIMO']}}</div>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Ver detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4 stat-card" onclick="filtrarPorTipo('PROXIMO_VENCER')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Stock Bajo</div>
                            <div class="h2 mb-0">{{$estadisticas['PROXIMO_VENCER']}}</div>
                        </div>
                        <i class="fas fa-exclamation-circle fa-3x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Ver detalles</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white mb-4 stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Alertas</div>
                            <div class="h2 mb-0">{{$estadisticas['total_alertas']}}</div>
                        </div>
                        <i class="fas fa-bell fa-3x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Ver todas</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-tasks"></i> Acciones Rápidas
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-success" onclick="marcarTodasLeidas()">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
                <button type="button" class="btn btn-danger" onclick="limpiarAlertas()">
                    <i class="fas fa-trash"></i> Limpiar alertas antiguas
                </button>
                <a href="{{route('inventario-sucursal.index')}}" class="btn btn-info">
                    <i class="fas fa-warehouse"></i> Ir al Inventario
                </a>
                {{-- <a href="{{route('pedidos- compra.index')}}" class="btn btn-warning">
                    <i class="fas fa-shopping-cart"></i> Crear Pedido de Compra
                </a> --}}
            </div>
        </div>
    </div>

    <!-- Lista de Alertas -->
    @if($alertas->count() > 0)
    <div class="row">
        @foreach($alertas as $alerta)
        <div class="col-md-12 mb-3">
            <div class="card alerta-card alerta-{{$alerta->tipo_alerta}} {{$alerta->leida ? 'alerta-leida' : ''}}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Información del Producto -->
                        <div class="col-md-5">
                            <div class="producto-info">
                                @if($alerta->producto->img_path)
                                <img src="{{ asset('storage/' . $alerta->producto->img_path) }}"
                                     alt="{{$alerta->producto->nombre}}"
                                     class="producto-imagen">
                                @else
                                <div class="producto-imagen bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="fas fa-box text-white"></i>
                                </div>
                                @endif
                                <div>
                                    <h5 class="mb-1">{{$alerta->producto->nombre}}</h5>
                                    <div class="text-muted small">
                                        <strong>Código:</strong> {{$alerta->producto->codigo}}
                                        @if($alerta->producto->marca)
                                        | <strong>Marca:</strong> {{$alerta->producto->marca->caracteristica->nombre}}
                                        @endif
                                    </div>
                                    @if($alerta->producto->presentacione)
                                    <span class="badge bg-secondary">
                                        {{$alerta->producto->presentacione->caracteristica->nombre}}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Información de Stock -->
                        <div class="col-md-3 text-center">
                            <div class="stock-indicator
                                @if($alerta->tipo_alerta == 'STOCK_AGOTADO') text-danger
                                @elseif($alerta->tipo_alerta == 'STOCK_MINIMO') text-warning
                                @else text-info
                                @endif">
                                {{$alerta->stock_actual}}
                            </div>
                            <small class="text-muted">Stock Actual</small>
                            <div class="mt-2">
                                <span class="badge badge-alerta
                                    @if($alerta->tipo_alerta == 'STOCK_AGOTADO') bg-danger
                                    @elseif($alerta->tipo_alerta == 'STOCK_MINIMO') bg-warning
                                    @else bg-info
                                    @endif">
                                    @if($alerta->tipo_alerta == 'STOCK_AGOTADO')
                                    <i class="fas fa-times-circle"></i> SIN STOCK
                                    @elseif($alerta->tipo_alerta == 'STOCK_MINIMO')
                                    <i class="fas fa-exclamation-triangle"></i> STOCK MÍNIMO
                                    @else
                                    <i class="fas fa-exclamation-circle"></i> STOCK BAJO
                                    @endif
                                </span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Mínimo requerido: <strong>{{$alerta->stock_minimo}}</strong>
                            </small>
                        </div>

                        <!-- Información Adicional -->
                        <div class="col-md-2">
                            <small class="text-muted d-block">
                                <i class="fas fa-clock"></i> {{$alerta->fecha_alerta->diffForHumans()}}
                            </small>
                            <small class="text-muted d-block">
                                <i class="fas fa-calendar"></i> {{$alerta->fecha_alerta->format('d/m/Y H:i')}}
                            </small>
                            @if($alerta->leida)
                            <small class="text-success d-block mt-2">
                                <i class="fas fa-check"></i> Leída
                            </small>
                            @endif
                        </div>

                        <!-- Acciones -->
                        <div class="col-md-2 text-end">
                            <div class="btn-group-vertical gap-1">
                                @if(!$alerta->leida)
                                <button type="button" class="btn btn-sm btn-success"
                                        onclick="marcarLeida({{$alerta->id}})">
                                    <i class="fas fa-check"></i> Marcar leída
                                </button>
                                @endif
                                <a href="{{route('inventario-sucursal.index', ['sucursal_id' => $alerta->sucursal_id])}}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver Inventario
                                </a>
                                {{-- <a href="{{route('pedidos -compra.create')}}"
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-shopping-cart"></i> Crear Pedido
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <h3 class="mt-3">¡No hay alertas pendientes!</h3>
            <p class="text-muted">Todos los productos tienen stock adecuado.</p>
            <a href="{{route('inventario-sucursal.index')}}" class="btn btn-primary mt-3">
                <i class="fas fa-warehouse"></i> Ver Inventario
            </a>
        </div>
    </div>
    @endif
</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 🔹 Filtrar por tipo de alerta desde las tarjetas de estadísticas
    function filtrarPorTipo(tipo) {
        const form = document.getElementById('formFiltros');
        const tipoSelect = document.getElementById('tipo_alerta');
        tipoSelect.value = tipo;
        form.submit();
    }

    // 🔹 Marcar una alerta como leída
    function marcarLeida(id) {
        Swal.fire({
            title: '¿Marcar como leída?',
            text: "Esta alerta se marcará como revisada.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, marcar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/alertas-stock/${id}/marcar-leida`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 🔹 Marcar todas las alertas como leídas
    function marcarTodasLeidas() {
        Swal.fire({
            title: '¿Marcar todas como leídas?',
            text: "Todas las alertas de esta sucursal se marcarán como revisadas.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, marcar todas',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("alertas-stock.marcar-todas-leidas") }}';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const sucursal = document.createElement('input');
                sucursal.type = 'hidden';
                sucursal.name = 'sucursal_id';
                sucursal.value = '{{ $sucursalSeleccionada }}';
                form.appendChild(sucursal);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 🔹 Limpiar alertas antiguas (leídas y con más de 30 días)
    function limpiarAlertas() {
        Swal.fire({
            title: '¿Limpiar alertas antiguas?',
            text: "Se eliminarán las alertas leídas con más de 30 días.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("alertas-stock.limpiar") }}';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const sucursal = document.createElement('input');
                sucursal.type = 'hidden';
                sucursal.name = 'sucursal_id';
                sucursal.value = '{{ $sucursalSeleccionada }}';
                form.appendChild(sucursal);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 🔹 Autoactualizar cada 5 minutos si hay nuevas alertas
    setInterval(() => {
        fetch(`/alertas-stock/api/no-leidas?sucursal_id={{ $sucursalSeleccionada }}`)
            .then(response => response.json())
            .then(data => {
                if (data.total > {{ $estadisticas['total_alertas'] }}) {
                    Swal.fire({
                        title: 'Nuevas alertas detectadas',
                        text: 'Se encontraron nuevas alertas de stock. ¿Deseas actualizar?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Actualizar ahora',
                        cancelButtonText: 'Después',
                    }).then((res) => {
                        if (res.isConfirmed) location.reload();
                    });
                }
            })
            .catch(err => console.error('Error verificando alertas:', err));
    }, 300000); // 5 minutos
</script>
@endpush
