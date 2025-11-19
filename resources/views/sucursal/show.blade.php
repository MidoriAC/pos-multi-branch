@extends('layouts.app')

@section('title', 'Detalle de Sucursal')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stat-card {
        transition: transform 0.2s;
        border-left: 4px solid;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.info { border-left-color: #17a2b8; }
    .stat-card.danger { border-left-color: #dc3545; }

    .info-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: #f8f9fa;
        border-radius: 0.25rem;
        margin-bottom: 0.5rem;
    }

    .section-title {
        border-bottom: 2px solid #007bff;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4">Detalle de Sucursal</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sucursales.index') }}">Sucursales</a></li>
        <li class="breadcrumb-item active">{{ $sucursal->nombre }}</li>
    </ol>

    {{-- Botones de acción --}}
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>

            @can('editar-sucursal')
            <a href="{{ route('sucursales.edit', $sucursal->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            @endcan

            @if(isset($sucursalActual) && $sucursalActual->id != $sucursal->id && $puedeHacerCambios)
            <button type="button"
                    class="btn btn-info"
                    onclick="cambiarSucursal({{ $sucursal->id }}, '{{ $sucursal->nombre }}')">
                <i class="fas fa-exchange-alt"></i> Cambiar a esta sucursal
            </button>
            @endif

            @if($sucursal->estado == 1)
                @can('eliminar-sucursal')
                <button type="button"
                        class="btn btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#desactivarModal">
                    <i class="fas fa-ban"></i> Desactivar
                </button>
                @endcan
            @else
                @can('editar-sucursal')
                <form action="{{ route('sucursales.reactivar', $sucursal->id) }}" method="GET" class="d-inline">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Reactivar
                    </button>
                </form>
                @endcan
            @endif
        </div>
    </div>

    {{-- Información General --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Información General</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted mb-1">Nombre de la Sucursal:</label>
                        <h4 class="mb-0">{{ $sucursal->nombre }}</h4>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted mb-1">Código:</label>
                        <div>
                            <span class="badge bg-primary fs-6">{{ $sucursal->codigo }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted mb-1">Dirección:</label>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            {{ $sucursal->direccion }}
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted mb-1">Estado:</label>
                        <div>
                            @if($sucursal->estado == 1)
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-check-circle"></i> Activa
                            </span>
                            @else
                            <span class="badge bg-danger fs-6">
                                <i class="fas fa-times-circle"></i> Inactiva
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="text-muted mb-1">Información de Contacto:</label>
                        @if($sucursal->telefono)
                        <p class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            {{ $sucursal->telefono }}
                        </p>
                        @endif
                        @if($sucursal->email)
                        <p class="mb-2">
                            <i class="fas fa-envelope text-info me-2"></i>
                            {{ $sucursal->email }}
                        </p>
                        @endif
                        @if(!$sucursal->telefono && !$sucursal->email)
                        <p class="text-muted mb-0">Sin información de contacto</p>
                        @endif
                    </div>

                    @if($sucursal->nit_establecimiento || $sucursal->codigo_establecimiento)
                    <div class="mb-3">
                        <label class="text-muted mb-1">Información FEL:</label>
                        @if($sucursal->nit_establecimiento)
                        <p class="mb-2">
                            <i class="fas fa-id-card text-success me-2"></i>
                            <strong>NIT:</strong> {{ $sucursal->nit_establecimiento }}
                        </p>
                        @endif
                        @if($sucursal->codigo_establecimiento)
                        <p class="mb-2">
                            <i class="fas fa-building text-warning me-2"></i>
                            <strong>Cod. Establecimiento:</strong> {{ $sucursal->codigo_establecimiento }}
                        </p>
                        @endif

                        @if($sucursal->tieneFelActivo())
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> FEL Configurado
                        </span>
                        @else
                        <span class="badge bg-warning">
                            <i class="fas fa-exclamation-triangle"></i> FEL No Configurado
                        </span>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="text-muted mb-1">Fechas:</label>
                        <p class="mb-1 small">
                            <i class="fas fa-calendar-plus text-success me-2"></i>
                            <strong>Creada:</strong> {{ $sucursal->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p class="mb-0 small">
                            <i class="fas fa-calendar-check text-info me-2"></i>
                            <strong>Última actualización:</strong> {{ $sucursal->updated_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas --}}
    <h4 class="section-title">
        <i class="fas fa-chart-bar me-2"></i> Estadísticas
    </h4>

    <div class="row mb-4">
        {{-- Total Ventas --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Ventas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Q {{ number_format($totalVentas, 2) }}
                            </div>
                            <small class="text-muted">
                                {{ $sucursal->ventas()->where('estado', 1)->count() }} ventas
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Compras --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Compras
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Q {{ number_format($totalCompras, 2) }}
                            </div>
                            <small class="text-muted">
                                {{ $sucursal->compras()->where('estado', 1)->count() }} compras
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Productos --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card info shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Stock Total
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalProductos) }} unidades
                            </div>
                            <small class="text-muted">
                                {{ $sucursal->inventarios()->count() }} productos diferentes
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Usuarios --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card primary shadow h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Usuarios Asignados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalUsuarios }}
                            </div>
                            <small class="text-muted">
                                {{ $totalUbicaciones }} ubicaciones
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ubicaciones --}}
    @if($sucursal->ubicaciones->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-map-marked-alt me-2"></i>
            <strong>Ubicaciones Físicas ({{ $sucursal->ubicaciones->count() }})</strong>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($sucursal->ubicaciones as $ubicacion)
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                @if($ubicacion->tipo == 'ESTANTE')
                                <i class="fas fa-layer-group text-primary"></i>
                                @elseif($ubicacion->tipo == 'BODEGA')
                                <i class="fas fa-warehouse text-warning"></i>
                                @elseif($ubicacion->tipo == 'MOSTRADOR')
                                <i class="fas fa-cash-register text-success"></i>
                                @else
                                <i class="fas fa-box text-info"></i>
                                @endif
                                {{ $ubicacion->nombre }}
                            </h6>
                            <p class="card-text mb-2">
                                <strong>Código:</strong>
                                <span class="badge bg-secondary">{{ $ubicacion->codigo }}</span>
                            </p>
                            <p class="card-text mb-2">
                                <strong>Tipo:</strong> {{ $ubicacion->tipo }}
                            </p>
                            @if($ubicacion->seccion)
                            <p class="card-text mb-2">
                                <strong>Sección:</strong> {{ $ubicacion->seccion }}
                            </p>
                            @endif
                            <p class="card-text mb-0">
                                <span class="badge {{ $ubicacion->estado ? 'bg-success' : 'bg-danger' }}">
                                    {{ $ubicacion->estado ? 'Activa' : 'Inactiva' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Usuarios Asignados --}}
    @if($sucursal->users->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-2"></i>
            <strong>Usuarios Asignados ({{ $sucursal->users->count() }})</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Sucursal Principal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sucursal->users as $user)
                        <tr>
                            <td>
                                <i class="fas fa-user-circle text-primary me-2"></i>
                                {{ $user->name }}
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($user->pivot->es_principal)
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i> Sí
                                </span>
                                @else
                                <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Últimas Ventas --}}
    @if($sucursal->ventas->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-receipt me-2"></i>
            <strong>Últimas Ventas (10)</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Cliente</th>
                            <th>Usuario</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sucursal->ventas as $venta)
                        <tr>
                            <td>{{ $venta->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $venta->numero_comprobante }}
                                </span>
                            </td>
                            <td>{{ $venta->cliente->persona->razon_social ?? 'N/A' }}</td>
                            <td>{{ $venta->user->name ?? 'N/A' }}</td>
                            <td class="text-end">
                                <strong>Q {{ number_format($venta->total, 2) }}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- Modal de confirmación para desactivar --}}
<div class="modal fade" id="desactivarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Desactivación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea desactivar la sucursal <strong>{{ $sucursal->nombre }}</strong>?</p>

                @if($sucursal->tieneMovimientos())
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    Esta sucursal tiene movimientos registrados y solo se desactivará.
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form action="{{ route('sucursales.destroy', $sucursal->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-2"></i>
                        Confirmar Desactivación
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// Función para cambiar sucursal
function cambiarSucursal(sucursalId, nombre) {
    Swal.fire({
        title: 'Cambiando sucursal...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("sucursal.cambiar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            sucursal_id: sucursalId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Sucursal cambiada!',
                text: 'Ahora está trabajando en: ' + nombre,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '{{ route("sucursales.index") }}';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'No se pudo cambiar de sucursal'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al cambiar de sucursal'
        });
    });
}
</script>
@endpush
