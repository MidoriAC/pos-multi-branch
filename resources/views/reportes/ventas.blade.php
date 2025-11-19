@extends('layouts.app')

@section('title','Reporte de Ventas')

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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-chart-line"></i> Reporte de Ventas
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Ventas</li>
    </ol>

    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes.ventas') }}" method="GET" id="formFiltros">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control"
                               value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-file-invoice"></i> Tipo</label>
                        <select name="tipo_factura" class="form-select">
                            <option value="TODOS" {{ $tipoFactura == 'TODOS' ? 'selected' : '' }}>Todos</option>
                            <option value="FACT" {{ $tipoFactura == 'FACT' ? 'selected' : '' }}>FEL</option>
                            <option value="RECI" {{ $tipoFactura == 'RECI' ? 'selected' : '' }}>Recibo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-light w-100">
                            <i class="fas fa-search"></i> Generar
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-toggle-on"></i> Estado</label>
                        <select name="estado" class="form-select">
                            <option value="todos" {{ $estado == 'todos' ? 'selected' : '' }}>Todos</option>
                            <option value="activos" {{ $estado == 'activos' ? 'selected' : '' }}>Activos</option>
                            <option value="anulados" {{ $estado == 'anulados' ? 'selected' : '' }}>Anulados</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('reportes.ventas.pdf', request()->query()) }}"
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
            <div class="card stat-card" style="border-left-color: #667eea;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Ventas</div>
                            <div class="h4 mb-0">{{ $estadisticas['total_ventas'] }}</div>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Monto Total</div>
                            <div class="h4 mb-0">Q {{ number_format($estadisticas['total_monto'], 2) }}</div>
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
                            <div class="text-muted small">Promedio Venta</div>
                            <div class="h4 mb-0">Q {{ number_format($estadisticas['promedio_venta'], 2) }}</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #17a2b8;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Productos Vendidos</div>
                            <div class="h4 mb-0">{{ $estadisticas['productos_vendidos'] }}</div>
                        </div>
                        <i class="fas fa-boxes fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-chart-pie"></i> Ventas por Tipo
                </div>
                <div class="card-body">
                    <canvas id="chartTipos"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-chart-bar"></i> Top 5 Vendedores
                </div>
                <div class="card-body">
                    <canvas id="chartVendedores"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle por Día -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-calendar-alt"></i> Ventas por Día
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th class="text-center">Cantidad Ventas</th>
                            <th class="text-end">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventasPorDia as $dia)
                        <tr>
                            <td><strong>{{ $dia['fecha'] }}</strong></td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $dia['cantidad'] }}</span>
                            </td>
                            <td class="text-end"><strong>Q {{ number_format($dia['monto'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No hay ventas en el período seleccionado</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th>TOTAL</th>
                            <th class="text-center">{{ $estadisticas['total_ventas'] }}</th>
                            <th class="text-end">Q {{ number_format($estadisticas['total_monto'], 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Listado Detallado -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list"></i> Detalle de Ventas
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>N° Comprobante</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Sucursal</th>
                            <th class="text-end">Total</th>
                            <th>Vendedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ventas as $venta)
                        <tr>
                            <td><strong>{{ $venta->numero_comprobante }}</strong></td>
                            <td>{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>{{ $venta->cliente->persona->razon_social }}</td>
                            <td>
                                @if($venta->tipo_factura === 'FACT')
                                <span class="badge bg-success">FEL</span>
                                @else
                                <span class="badge bg-secondary">Recibo</span>
                                @endif
                            </td>
                            <td>{{ $venta->sucursal->nombre }}</td>
                            <td class="text-end"><strong>Q {{ number_format($venta->total, 2) }}</strong></td>
                            <td>{{ $venta->user->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay ventas en el período seleccionado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script>
// Gráfico de Tipos
const ctxTipos = document.getElementById('chartTipos').getContext('2d');
new Chart(ctxTipos, {
    type: 'doughnut',
    data: {
        labels: ['FEL', 'Recibo'],
        datasets: [{
            data: [{{ $estadisticas['ventas_fel'] }}, {{ $estadisticas['ventas_recibo'] }}],
            backgroundColor: ['#28a745', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de Vendedores
const ctxVendedores = document.getElementById('chartVendedores').getContext('2d');
new Chart(ctxVendedores, {
    type: 'bar',
    data: {
        labels: [
            @foreach($topVendedores as $vendedor)
            '{{ $vendedor["vendedor"] }}',
            @endforeach
        ],
        datasets: [{
            label: 'Monto (Q)',
            data: [
                @foreach($topVendedores as $vendedor)
                {{ $vendedor['monto'] }},
                @endforeach
            ],
            backgroundColor: '#667eea'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
