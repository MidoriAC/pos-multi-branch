@extends('layouts.app')

@section('title','Reporte de Clientes')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .filters-card {
        background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        color: white;
    }
    .client-card {
        transition: transform 0.2s;
    }
    .client-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-users"></i> Reporte de Clientes
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Clientes</li>
    </ol>

    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes.clientes') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-calendar-alt"></i> Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control"
                               value="{{ $fechaInicio }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="fas fa-calendar-check"></i> Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control"
                               value="{{ $fechaFin }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-light w-100">
                            <i class="fas fa-search"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Top 5 Clientes -->
    <div class="row mb-4">
        @foreach($clientesVentas->take(5) as $index => $cliente)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card client-card border-{{ $index == 0 ? 'success' : 'primary' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">
                            @if($index == 0)
                            <i class="fas fa-crown text-warning"></i>
                            @endif
                            Top #{{ $index + 1 }}
                        </h6>
                        <span class="badge bg-{{ $index == 0 ? 'success' : 'primary' }}">
                            {{ $cliente->total_compras }} compras
                        </span>
                    </div>
                    <h5 class="card-title">{{ $cliente->razon_social }}</h5>
                    <p class="mb-2"><small class="text-muted">NIT: {{ $cliente->nit ?? 'CF' }}</small></p>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Total Compras</small>
                            <h4 class="mb-0 text-success">Q {{ number_format($cliente->monto_total, 2) }}</h4>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Promedio</small>
                            <h4 class="mb-0 text-primary">Q {{ number_format($cliente->promedio_compra, 2) }}</h4>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Última compra:
                        {{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}
                    </small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Gráfico Top 10 -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-chart-bar"></i> Top 10 Clientes por Monto
        </div>
        <div class="card-body">
            <canvas id="chartClientes" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <!-- Tabla Completa -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list"></i> Listado Completo de Clientes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Teléfono</th>
                            <th class="text-center">Total Compras</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-end">Promedio Compra</th>
                            <th>Última Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientesVentas as $index => $cliente)
                        <tr>
                            <td><strong>{{ $index + 1 }}</strong></td>
                            <td>
                                <strong>{{ $cliente->razon_social }}</strong>
                                @if($index < 3)
                                <i class="fas fa-star text-warning"></i>
                                @endif
                            </td>
                            <td>{{ $cliente->nit ?? 'CF' }}</td>
                            <td>{{ $cliente->telefono ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $cliente->total_compras }}</span>
                            </td>
                            <td class="text-end"><strong>Q {{ number_format($cliente->monto_total, 2) }}</strong></td>
                            <td class="text-end">Q {{ number_format($cliente->promedio_compra, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($cliente->ultima_compra)->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay ventas de clientes en el período</td>
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
const ctx = document.getElementById('chartClientes').getContext('2d');
new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: [
            @foreach($clientesVentas->take(10) as $cli)
            '{{ substr($cli->razon_social, 0, 30) }}',
            @endforeach
        ],
        datasets: [{
            label: 'Monto Total (Q)',
            data: [
                @foreach($clientesVentas->take(10) as $cli)
                {{ $cli->monto_total }},
                @endforeach
            ],
            backgroundColor: 'rgba(48, 207, 208, 0.8)',
            borderColor: 'rgba(48, 207, 208, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            x: {
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
                        return 'Q ' + context.parsed.x.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endpush
