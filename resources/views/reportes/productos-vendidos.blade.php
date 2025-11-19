@extends('layouts.app')

@section('title','Productos Más Vendidos')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .filters-card {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: white;
    }
    .product-card {
        transition: transform 0.2s;
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .ranking-number {
        font-size: 2rem;
        font-weight: bold;
        color: #fa709a;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-star"></i> Productos Más Vendidos
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
        <li class="breadcrumb-item active">Productos Vendidos</li>
    </ol>

    <!-- Filtros -->
    <div class="card filters-card mb-4">
        <div class="card-body">
            <form action="{{ route('reportes.productos-vendidos') }}" method="GET">
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
                        <label class="form-label"><i class="fas fa-list-ol"></i> Mostrar Top</label>
                        <select name="limite" class="form-select">
                            <option value="10" {{ $limite == 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="20" {{ $limite == 20 ? 'selected' : '' }}>Top 20</option>
                            <option value="50" {{ $limite == 50 ? 'selected' : '' }}>Top 50</option>
                            <option value="100" {{ $limite == 100 ? 'selected' : '' }}>Top 100</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-light w-100">
                            <i class="fas fa-search"></i> Generar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Top 5 Cards -->
    <div class="row mb-4">
        @foreach($productosVendidos->take(5) as $index => $producto)
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card product-card border-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'info') }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="ranking-number">#{{ $index + 1 }}</div>
                        <div>
                            @if($index == 0)
                            <i class="fas fa-trophy fa-2x text-warning"></i>
                            @elseif($index == 1)
                            <i class="fas fa-medal fa-2x text-secondary"></i>
                            @elseif($index == 2)
                            <i class="fas fa-award fa-2x" style="color: #cd7f32;"></i>
                            @else
                            <i class="fas fa-star fa-2x text-info"></i>
                            @endif
                        </div>
                    </div>
                    <h5 class="mt-2">{{ $producto->nombre }}</h5>
                    <p class="mb-1"><small class="text-muted">Código: {{ $producto->codigo }}</small></p>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted">Cantidad Vendida</small>
                            <h4 class="mb-0">{{ number_format($producto->cantidad_vendida) }}</h4>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Monto Total</small>
                            <h4 class="mb-0">Q {{ number_format($producto->monto_total, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Gráfico -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-chart-bar"></i> Ranking de Ventas
        </div>
        <div class="card-body">
            <canvas id="chartProductos" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <!-- Tabla Completa -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-list"></i> Listado Completo de Productos Vendidos
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 10%;">Código</th>
                            <th style="width: 35%;">Producto</th>
                            <th style="width: 15%;" class="text-center">Cantidad Vendida</th>
                            <th style="width: 15%;" class="text-end">Precio Promedio</th>
                            <th style="width: 20%;" class="text-end">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productosVendidos as $index => $producto)
                        <tr>
                            <td class="text-center">
                                <strong class="ranking-number" style="font-size: 1rem;">{{ $index + 1 }}</strong>
                            </td>
                            <td><strong>{{ $producto->codigo }}</strong></td>
                            <td>{{ $producto->nombre }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ number_format($producto->cantidad_vendida) }}</span>
                            </td>
                            <td class="text-end">Q {{ number_format($producto->precio_promedio, 2) }}</td>
                            <td class="text-end"><strong>Q {{ number_format($producto->monto_total, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay productos vendidos en el período</td>
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
const ctx = document.getElementById('chartProductos').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($productosVendidos->take(15) as $prod)
            '{{ $prod->codigo }}',
            @endforeach
        ],
        datasets: [{
            label: 'Cantidad Vendida',
            data: [
                @foreach($productosVendidos->take(15) as $prod)
                {{ $prod->cantidad_vendida }},
                @endforeach
            ],
            backgroundColor: 'rgba(250, 112, 154, 0.8)',
            borderColor: 'rgba(250, 112, 154, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endpush
