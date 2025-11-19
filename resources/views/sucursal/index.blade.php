@extends('layouts.app')

@section('title','Sucursales')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .badge-activo {
        background-color: #28a745;
    }
    .badge-inactivo {
        background-color: #dc3545;
    }
    .card-sucursal {
        transition: transform 0.2s;
    }
    .card-sucursal:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .sucursal-actual {
        background-color: #e7f3ff !important;
        border-left: 4px solid #0d6efd;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Gestión de Sucursales</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Sucursales</li>
    </ol>

    {{-- Indicador de sucursal actual --}}
    @if(isset($sucursalActual))
    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-info-circle me-3 fs-4"></i>
        <div>
            <strong>Sucursal actual de trabajo:</strong> {{ $sucursalActual->nombre }}
            <span class="badge bg-primary ms-2">{{ $sucursalActual->codigo }}</span>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            @can('crear-sucursal')
            <a href="{{route('sucursales.create')}}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Añadir nueva sucursal
            </a>
            @endcan
        </div>
        <div class="col-md-6 text-end">
            {{-- @can('ver-sucursal')
            <a href="{{route('sucursales.inactivas')}}" class="btn btn-secondary">
                <i class="fas fa-eye-slash"></i> Ver inactivas ({{ \App\Models\Sucursal::where('estado', 0)->count() }})
            </a>
            @endcan --}}
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-store me-1"></i>
            Tabla de sucursales activas
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Contacto</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sucursales as $item)
                    <tr class="{{ isset($sucursalActual) && $sucursalActual->id == $item->id ? 'sucursal-actual' : '' }}">
                        <td>
                            <span class="badge bg-primary">{{$item->codigo}}</span>
                            @if(isset($sucursalActual) && $sucursalActual->id == $item->id)
                            <span class="badge bg-success ms-1">
                                <i class="fas fa-check"></i> Actual
                            </span>
                            @endif
                        </td>
                        <td>
                            <p class="fw-semibold mb-1">{{$item->nombre}}</p>
                            @if($item->codigo_establecimiento)
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-building"></i> Est: {{$item->codigo_establecimiento}}
                            </p>
                            @endif
                        </td>
                        <td>
                            <p class="mb-0">{{$item->direccion}}</p>
                        </td>
                        <td>
                            @if($item->telefono)
                            <p class="mb-1 small">
                                <i class="fas fa-phone text-primary"></i> {{$item->telefono}}
                            </p>
                            @endif
                            @if($item->email)
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-envelope"></i> {{$item->email}}
                            </p>
                            @endif
                            @if(!$item->telefono && !$item->email)
                            <span class="text-muted small">Sin contacto</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">
                                {{$item->users->count()}}
                                <i class="fas fa-users"></i>
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->estado == 1)
                            <span class="badge badge-activo">
                                <i class="fas fa-check-circle"></i> Activa
                            </span>
                            @else
                            <span class="badge badge-inactivo">
                                <i class="fas fa-times-circle"></i> Inactiva
                            </span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">

                                {{-- Cambiar a esta sucursal --}}
                                @if(isset($sucursalActual) && $sucursalActual->id != $item->id && $puedeHacerCambios)
                                <button type="button"
                                        class="btn btn-info"
                                        onclick="cambiarSucursalDesdeTabla({{ $item->id }}, '{{ $item->nombre }}')"
                                        title="Cambiar a esta sucursal">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                @endif

                                {{-- Ver detalles --}}
                                {{-- @can('ver-sucursal')
                                <a href="{{route('sucursales.show', ['sucursal'=>$item])}}"
                                   class="btn btn-success"
                                   title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan --}}

                                {{-- Editar --}}
                                @can('editar-sucursal')
                                <a href="{{route('sucursales.edit', ['sucursal'=>$item])}}"
                                   class="btn btn-warning"
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                {{-- Desactivar --}}
                                @can('eliminar-sucursal')
                                <button type="button"
                                        class="btn btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmModal-{{$item->id}}"
                                        title="Desactivar">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endcan

                            </div>
                        </td>
                    </tr>

                    <!-- Modal de confirmación-->
                    <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h1 class="modal-title fs-5">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Confirmar desactivación
                                    </h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning" role="alert">
                                        <strong>¿Está seguro que desea desactivar la sucursal?</strong>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Sucursal:</strong> {{ $item->nombre }}<br>
                                        <strong>Código:</strong> {{ $item->codigo }}
                                    </div>

                                    @if($item->tieneMovimientos())
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Información:</strong> Esta sucursal tiene movimientos registrados.
                                        Se desactivará pero no se eliminará permanentemente.
                                        <ul class="mt-2 mb-0">
                                            <li>Ventas: {{ $item->ventas()->count() }}</li>
                                            <li>Compras: {{ $item->compras()->count() }}</li>
                                        </ul>
                                    </div>
                                    @else
                                    <div class="alert alert-success" role="alert">
                                        <i class="fas fa-check-circle"></i>
                                        Esta sucursal no tiene movimientos y puede ser eliminada.
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                    <form action="{{ route('sucursales.destroy',['sucursal'=>$item->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-ban"></i> Confirmar desactivación
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Estadísticas rápidas --}}
    {{-- <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sucursales Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sucursales->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Usuarios
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sucursales->sum(function($s) { return $s->users->count(); }) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Con FEL Activo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $sucursales->filter(function($s) { return $s->tieneFelActivo(); })->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Inactivas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Sucursal::where('estado', 0)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

</div>

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>

<script>
// Función para cambiar sucursal desde la tabla
function cambiarSucursalDesdeTabla(sucursalId, nombre) {
    Swal.fire({
        title: '¿Cambiar de sucursal?',
        html: `¿Desea cambiar a la sucursal: <strong>${nombre}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            cambiarSucursal(sucursalId, nombre);
        }
    });
}

// Función para cambiar sucursal (compartida con el selector del header)
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
                window.location.reload();
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
