@extends('layouts.app')

@section('title','Reportes')

@push('css')
<style>
    .report-card {
        transition: all 0.3s;
        cursor: pointer;
        height: 100%;
    }
    .report-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .report-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    .gradient-ventas {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .gradient-compras {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .gradient-inventario {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .gradient-cotizaciones {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .gradient-productos {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .gradient-clientes {
        background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        <i class="fas fa-chart-bar"></i> Centro de Reportes
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Reportes</li>
    </ol>

    <div class="alert alert-info" role="alert">
        <i class="fas fa-info-circle"></i>
        <strong>Bienvenido al Centro de Reportes</strong><br>
        Aquí podrás generar diferentes reportes del sistema. Selecciona el reporte que deseas consultar.
    </div>

    <div class="row g-4 mb-5">

        <!-- Reporte de Ventas -->
        {{-- @can('reporte-ventas') --}}
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reportes.ventas') }}" class="text-decoration-none">
                <div class="card report-card gradient-ventas text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shopping-cart report-icon"></i>
                        <h4 class="card-title">Reporte de Ventas</h4>
                        <p class="card-text">Consulta ventas por período, sucursal y tipo de factura</p>
                        <ul class="list-unstyled text-start small mt-3">
                            <li><i class="fas fa-check me-2"></i> Filtros por fecha</li>
                            <li><i class="fas fa-check me-2"></i> Por sucursal</li>
                            <li><i class="fas fa-check me-2"></i> FEL vs Recibo</li>
                            <li><i class="fas fa-check me-2"></i> Estadísticas detalladas</li>
                            <li><i class="fas fa-check me-2"></i> Exportar a PDF</li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>
        {{-- @endcan --}}

        <!-- Reporte de Compras -->
        {{-- @can('reporte-compras') --}}
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reportes.compras') }}" class="text-decoration-none">
                <div class="card report-card gradient-compras text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-truck report-icon"></i>
                        <h4 class="card-title">Reporte de Compras</h4>
                        <p class="card-text">Analiza tus compras por período y proveedor</p>
                        <ul class="list-unstyled text-start small mt-3">
                            <li><i class="fas fa-check me-2"></i> Filtros por fecha</li>
                            <li><i class="fas fa-check me-2"></i> Por proveedor</li>
                            <li><i class="fas fa-check me-2"></i> Por sucursal</li>
                            <li><i class="fas fa-check me-2"></i> Análisis de costos</li>
                            <li><i class="fas fa-check me-2"></i> Exportar a PDF</li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>
        {{-- @endcan --}}

        <!-- Reporte de Inventario -->
        @can('reporte-inventario')
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reportes.inventario') }}" class="text-decoration-none">
                <div class="card report-card gradient-inventario text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-boxes report-icon"></i>
                        <h4 class="card-title">Reporte de Inventario</h4>
                        <p class="card-text">Estado actual del inventario por sucursal</p>
                        <ul class="list-unstyled text-start small mt-3">
                            <li><i class="fas fa-check me-2"></i> Stock actual</li>
                            <li><i class="fas fa-check me-2"></i> Productos bajo stock</li>
                            <li><i class="fas fa-check me-2"></i> Productos agotados</li>
                            <li><i class="fas fa-check me-2"></i> Valorización</li>
                            <li><i class="fas fa-check me-2"></i> Exportar a PDF</li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        <!-- Reporte de Productos Más Vendidos -->
        {{-- @can('reporte-ventas') --}}
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reportes.productos-vendidos') }}" class="text-decoration-none">
                <div class="card report-card gradient-productos text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-star report-icon"></i>
                        <h4 class="card-title">Productos Más Vendidos</h4>
                        <p class="card-text">Top de productos más demandados</p>
                        <ul class="list-unstyled text-start small mt-3">
                            <li><i class="fas fa-check me-2"></i> Ranking de productos</li>
                            <li><i class="fas fa-check me-2"></i> Cantidades vendidas</li>
                            <li><i class="fas fa-check me-2"></i> Montos generados</li>
                            <li><i class="fas fa-check me-2"></i> Por período</li>
                            <li><i class="fas fa-check me-2"></i> Análisis de rotación</li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>
        {{-- @endcan --}}

        <!-- Reporte de Clientes -->
        @can('reporte-ventas')
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('reportes.clientes') }}" class="text-decoration-none">
                <div class="card report-card gradient-clientes text-white">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-users report-icon"></i>
                        <h4 class="card-title">Reporte de Clientes</h4>
                        <p class="card-text">Análisis de ventas por cliente</p>
                        <ul class="list-unstyled text-start small mt-3">
                            <li><i class="fas fa-check me-2"></i> Ventas por cliente</li>
                            <li><i class="fas fa-check me-2"></i> Frecuencia de compra</li>
                            <li><i class="fas fa-check me-2"></i> Ticket promedio</li>
                            <li><i class="fas fa-check me-2"></i> Mejores clientes</li>
                            <li><i class="fas fa-check me-2"></i> Última compra</li>
                        </ul>
                    </div>
                </div>
            </a>
        </div>
        @endcan

        <!-- Próximamente: Más reportes -->
        {{-- <div class="col-md-6 col-lg-4">
            <div class="card report-card bg-secondary text-white" style="opacity: 0.7;">
                <div class="card-body text-center p-4">
                    <i class="fas fa-ellipsis-h report-icon"></i>
                    <h4 class="card-title">Más Reportes</h4>
                    <p class="card-text">Próximamente más reportes disponibles</p>
                    <ul class="list-unstyled text-start small mt-3">
                        <li><i class="fas fa-clock me-2"></i> Movimientos de inventario</li>
                        <li><i class="fas fa-clock me-2"></i> Productos dañados</li>
                        <li><i class="fas fa-clock me-2"></i> Análisis de rentabilidad</li>
                        <li><i class="fas fa-clock me-2"></i> Proyecciones</li>
                        <li><i class="fas fa-clock me-2"></i> Y más...</li>
                    </ul>
                </div>
            </div>
        </div> --}}

    </div>

    <!-- Información adicional -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-question-circle"></i> ¿Cómo usar los reportes?
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="fas fa-calendar text-primary"></i> Selecciona el período</h6>
                            <p class="small">Define la fecha de inicio y fin para filtrar la información que necesitas consultar.</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-filter text-success"></i> Aplica filtros</h6>
                            <p class="small">Utiliza los filtros disponibles (sucursal, tipo, estado) para obtener información específica.</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-file-pdf text-danger"></i> Exporta a PDF</h6>
                            <p class="small">Una vez generado el reporte, puedes exportarlo a PDF para imprimirlo o compartirlo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
