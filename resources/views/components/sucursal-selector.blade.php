{{-- ============================================ --}}
{{-- resources/views/components/sucursal-selector.blade.php --}}
{{-- ============================================ --}}

@if(auth()->check() && isset($sucursalActual))
<div class="dropdown ms-3">
    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center"
            type="button"
            id="sucursalDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="fas fa-store me-2"></i>
        <div class="text-start">
            <small class="d-block" style="font-size: 0.7rem; opacity: 0.8;">Sucursal:</small>
            <strong style="font-size: 0.9rem;">{{ $sucursalActual->nombre ?? 'Sin sucursal' }}</strong>
        </div>
    </button>

    @if($puedeHacerCambios && isset($sucursalesDisponibles))
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="sucursalDropdown" style="min-width: 280px;">
        <li class="px-3 py-2 bg-light border-bottom">
            <small class="text-muted">Seleccione una sucursal:</small>
        </li>

        @foreach($sucursalesDisponibles as $sucursal)
        <li>
            <a class="dropdown-item sucursal-option {{ $sucursalActual && $sucursalActual->id == $sucursal->id ? 'active' : '' }}"
               href="#"
               data-sucursal-id="{{ $sucursal->id }}"
               onclick="event.preventDefault(); cambiarSucursal({{ $sucursal->id }}, '{{ $sucursal->nombre }}')">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        @if($sucursalActual && $sucursalActual->id == $sucursal->id)
                        <i class="fas fa-check-circle text-success"></i>
                        @else
                        <i class="fas fa-store text-muted"></i>
                        @endif
                    </div>
                    <div>
                        <div class="fw-bold">{{ $sucursal->nombre }}</div>
                        <small class="text-muted">{{ $sucursal->codigo }}</small>
                    </div>
                </div>
            </a>
        </li>
        @endforeach

        @can('ver-sucursal')
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('sucursales.index') }}">
                <i class="fas fa-cog me-2"></i> Administrar sucursales
            </a>
        </li>
        @endcan
    </ul>
    @endif
</div>

<style>
    .dropdown-item.active {
        background-color: #e7f3ff;
        color: #0d6efd;
    }

    .sucursal-option:hover {
        background-color: #f8f9fa;
    }

    #sucursalDropdown {
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
    }

    #sucursalDropdown:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
    }
</style>

<script>
function cambiarSucursal(sucursalId, nombre) {
    // Mostrar loading
    Swal.fire({
        title: 'Cambiando sucursal...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Hacer petición AJAX
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
                title: 'Sucursal cambiada',
                text: 'Ahora está trabajando en: ' + nombre,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Recargar la página para actualizar todos los datos
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
@endif

{{-- ============================================ --}}
{{-- resources/views/components/navigation-header.blade.php --}}
{{-- ACTUALIZAR TU HEADER ACTUAL --}}
{{-- ============================================ --}}

{{-- <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="{{ route('panel') }}">Sistema Ventas</a>

    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search (si lo tienes)-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <!-- Tu search aquí si lo tienes -->
    </form>

    <!-- SELECTOR DE SUCURSAL -->
    <x-sucursal-selector />

    <!-- Navbar (User dropdown original)-->
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user fa-fw"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#!">{{ auth()->user()->name }}</a></li>
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav> --}}

{{-- ============================================ --}}
{{-- BADGE INDICADOR DE SUCURSAL (OPCIONAL) --}}
{{-- Agregar en cualquier página donde quieras mostrar la sucursal actual --}}
{{-- ============================================ --}}

{{-- @if(isset($sucursalActual))
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="fas fa-info-circle me-2"></i>
    <div>
        <strong>Trabajando en:</strong> {{ $sucursalActual->nombre }}
        <span class="badge bg-primary ms-2">{{ $sucursalActual->codigo }}</span>
    </div>
</div>
@endif --}}
