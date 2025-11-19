@extends('layouts.app')

@section('title','Reporte de Inventario')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .stat-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .filters-card {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    .stock-critico {
        background-color: #dc3545;
        color: white;
    }
    .stock-bajo {
        background-color: #ffc107;
    }
    .stock-normal {
        background-color: #28a745;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-boxes"></i> Reporte de Inventario
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Inventario</li>
    </ol>

    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes.inventario') }}" method="GET" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-store"></i> Sucursal</label>
                        <select name="sucursal_id" class="form-select">
                            <option value="todas" {{ $sucursalFiltro == 'todas' ? 'selected' : '' }}>Todas</option>
                            @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ $sucursalFiltro == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-filter"></i> Filtro Stock</label>
                        <select name="filtro_stock" class="form-select">
                            <option value="todos" {{ $filtroStock == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="bajo" {{ $filtroStock == 'bajo' ? 'selected' : '' }}>Bajo Stock</option>
                            <option value="agotado" {{ $filtroStock == 'agotado' ? 'selected' : '' }}>Agotados</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-light w-100">
                            <i class="fas fa-search"></i> Generar
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('reportes.inventario.pdf', request()->query()) }}"
                           class="btn btn-danger w-100" target="_blank">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #4facfe;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Productos</div>
                            <div class="h4 mb-0">{{ $estadisticas['total_productos'] }}</div>
                        </div>
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Valor Total</div>
                            <div class="h4 mb-0">Q {{ number_format($estadisticas['valor_total'], 2) }}</div>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Bajo Stock</div>
                            <div class="h4 mb-0">{{ $estadisticas['productos_bajo_stock'] }}</div>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Agotados</div>
                            <div class="h4 mb-0">{{ $estadisticas['productos_agotados'] }}</div>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Estado de Stock -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-pie"></i> Estado del Stock
                </div>
                <div class="card-body">
                    <canvas id="chartEstadoStock"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-info-circle"></i> Resumen
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tr>
                                <td><i class="fas fa-boxes text-primary"></i> Total Productos:</td>
                                <td class="text-end"><strong>{{ $estadisticas['total_productos'] }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-cubes text-info"></i> Total Unidades:</td>
                                <td class="text-end"><strong>{{ number_format($estadisticas['unidades_totales']) }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-dollar-sign text-success"></i> Valor Total:</td>
                                <td class="text-end"><strong>Q {{ number_format($estadisticas['valor_total'], 2) }}</strong></td>
                            </tr>
                            <tr class="table-warning">
                                <td><i class="fas fa-exclamation-triangle"></i> Productos Bajo Stock:</td>
                                <td class="text-end"><strong>{{ $estadisticas['productos_bajo_stock'] }}</strong></td>
                            </tr>
                            <tr class="table-danger">
                                <td><i class="fas fa-times-circle"></i> Productos Agotados:</td>
                                <td class="text-end"><strong>{{ $estadisticas['productos_agotados'] }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado Detallado -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list"></i> Detalle de Inventario
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Marca</th>
                            <th>Sucursal</th>
                            <th>Ubicación</th>
                            <th class="text-center">Stock Actual</th>
                            <th class="text-center">Stock Mín.</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Valor Total</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventarios as $inventario)
                        <tr>
                            <td><strong>{{ $inventario->producto->codigo }}</strong></td>
                            <td>{{ $inventario->producto->nombre }}</td>
                            <td>{{ $inventario->producto->marca->caracteristica->nombre ?? '-' }}</td>
                            <td>{{ $inventario->sucursal->nombre }}</td>
                            <td>
                                @if($inventario->ubicacion)
                                <span class="badge bg-info">{{ $inventario->ubicacion->codigo }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge
                                    @if($inventario->stock_actual == 0)
                                        bg-danger
                                    @elseif($inventario->stock_actual <= $inventario->stock_minimo)
                                        bg-warning
                                    @else
                                        bg-success
                                    @endif
                                ">
                                    {{ $inventario->stock_actual }}
                                </span>
                            </td>
                            <td class="text-center">{{ $inventario->stock_minimo }}</td>
                            <td class="text-end">Q {{ number_format($inventario->precio_venta, 2) }}</td>
                            <td class="text-end">
                                <strong>Q {{ number_format($inventario->stock_actual * $inventario->precio_venta, 2) }}</strong>
                            </td>
                            <td class="text-center">
                                @if($inventario->stock_actual == 0)
                                <span class="badge stock-critico">AGOTADO</span>
                                @elseif($inventario->stock_actual <= $inventario->stock_minimo)
                                <span class="badge stock-bajo">BAJO STOCK</span>
                                @else
                                <span class="badge stock-normal">NORMAL</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No hay productos en inventario</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="8" class="text-end">TOTAL VALORIZACIÓN:</th>
                            <th class="text-end">Q {{ number_format($estadisticas['valor_total'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
// Calcular productos normales
const productosNormales = {{ $estadisticas['total_productos'] - $estadisticas['productos_bajo_stock'] - $estadisticas['productos_agotados'] }};

// Gráfico de Estado de Stock
const ctxEstado = document.getElementById('chartEstadoStock').getContext('2d');
new Chart(ctxEstado, {
    type: 'doughnut',
    data: {
        labels: ['Normal', 'Bajo Stock', 'Agotado'],
        datasets: [{
            data: [
                productosNormales,
                {{ $estadisticas['productos_bajo_stock'] }},
                {{ $estadisticas['productos_agotados'] }}
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>
@endpush
