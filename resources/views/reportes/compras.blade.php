@extends('layouts.app')

@section('title','Reporte de Compras')

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
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-truck"></i> Reporte de Compras
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Compras</li>
    </ol>

    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes.compras') }}" method="GET" id="formFiltros">
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
                        <label class="form-label"><i class="fas fa-building"></i> Proveedor</label>
                        <select name="proveedor_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" {{ $proveedorId == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->persona->razon_social }}
                            </option>
                            @endforeach
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
                        <a href="{{ route('reportes.compras.pdf', request()->query()) }}"
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
            <div class="card stat-card" style="border-left-color: #f093fb;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Compras</div>
                            <div class="h4 mb-0">{{ $estadisticas['total_compras'] }}</div>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #dc3545;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Monto Total</div>
                            <div class="h4 mb-0">Q {{ number_format($estadisticas['total_monto'], 2) }}</div>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card" style="border-left-color: #ffc107;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Promedio Compra</div>
                            <div class="h4 mb-0">Q {{ number_format($estadisticas['promedio_compra'], 2) }}</div>
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
                            <div class="text-muted small">Productos Comprados</div>
                            <div class="h4 mb-0">{{ $estadisticas['productos_comprados'] }}</div>
                        </div>
                        <i class="fas fa-boxes fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-calculator"></i> Resumen Financiero
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted">Subtotal</h6>
                            <h3 class="text-success">Q {{ number_format($estadisticas['subtotal'], 2) }}</h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">IVA (12%)</h6>
                            <h3 class="text-warning">Q {{ number_format($estadisticas['total_iva'], 2) }}</h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Total</h6>
                            <h3 class="text-danger">Q {{ number_format($estadisticas['total_monto'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compras por Proveedor -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-chart-bar"></i> Compras por Proveedor
        </div>
        <div class="card-body">
            <canvas id="chartProveedores" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <!-- Top Proveedores -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-trophy"></i> Top 10 Proveedores
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Proveedor</th>
                            <th class="text-center">Cantidad Compras</th>
                            <th class="text-end">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $posicion = 1; @endphp
                        @forelse($comprasPorProveedor as $proveedorCompra)
                        <tr>
                            <td><strong>{{ $posicion++ }}</strong></td>
                            <td>{{ $proveedorCompra['proveedor'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $proveedorCompra['cantidad'] }}</span>
                            </td>
                            <td class="text-end"><strong>Q {{ number_format($proveedorCompra['monto'], 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay compras en el período seleccionado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Listado Detallado -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list"></i> Detalle de Compras
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>N° Comprobante</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Sucursal</th>
                            <th class="text-center">Productos</th>
                            <th class="text-end">Total</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($compras as $compra)
                        <tr>
                            <td><strong>{{ $compra->numero_comprobante }}</strong></td>
                            <td>{{ $compra->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>{{ $compra->proveedore->persona->razon_social }}</td>
                            <td>{{ $compra->sucursal->nombre }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $compra->productos->count() }}</span>
                            </td>
                            <td class="text-end"><strong>Q {{ number_format($compra->total, 2) }}</strong></td>
                            <td>{{ $compra->user->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay compras en el período seleccionado</td>
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
// Gráfico de Proveedores
const ctxProveedores = document.getElementById('chartProveedores').getContext('2d');
new Chart(ctxProveedores, {
    type: 'bar',
    data: {
        labels: [
            @foreach($comprasPorProveedor->take(10) as $prov)
            '{{ $prov["proveedor"] }}',
            @endforeach
        ],
        datasets: [{
            label: 'Monto (Q)',
            data: [
                @foreach($comprasPorProveedor->take(10) as $prov)
                {{ $prov['monto'] }},
                @endforeach
            ],
            backgroundColor: '#f093fb',
            borderColor: '#f5576c',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Q ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Q ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
