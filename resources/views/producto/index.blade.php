@extends('layouts.app')

@section('title','Productos')

@push('css-datatable')
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .badge-categoria {
        font-size: 0.7rem;
        margin: 2px;
    }
    .cantidad-input {
        width: 80px;
    }
</style>
@endpush

@section('content')

@include('layouts.partials.alert')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Productos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Productos</li>
    </ol>

    <div class="mb-4 d-flex justify-content-between">
        @can('crear-producto')
        <div>
            <a href="{{route('productos.create')}}">
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Añadir Nuevo Producto
                </button>
            </a>
        </div>
        @endcan

        {{-- <div>
            <a href="{{route('productos.codigos-barras-masivo')}}" target="_blank">
                <button type="button" class="btn btn-info">
                    <i class="fas fa-barcode"></i> Generar Códigos de Barras Masivos
                </button>
            </a>
        </div> --}}
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-box me-1"></i>
            Tabla de Productos
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped fs-6">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Presentación</th>
                        <th>Unidad</th>
                        <th>Categorías</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productos as $item)
                    <tr>
                        <td>
                            <strong>{{$item->codigo}}</strong>
                        </td>
                        <td>
                            {{$item->nombre}}
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{$item->marca->caracteristica->nombre}}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{$item->presentacione->caracteristica->nombre}}
                            </span>
                        </td>
                        <td>
                            @if($item->unidadMedida)
                                <span class="badge bg-secondary">
                                    {{$item->unidadMedida->abreviatura}}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @foreach ($item->categorias as $category)
                            <span class="badge rounded-pill bg-secondary badge-categoria">
                                {{$category->caracteristica->nombre}}
                            </span>
                            @endforeach
                        </td>
                        <td>
                            @if ($item->estado == 1)
                            <span class="badge bg-success">Activo</span>
                            @else
                            <span class="badge bg-danger">Eliminado</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <div>
                                    <button title="Opciones" class="btn btn-datatable btn-icon btn-transparent-dark me-2" data-bs-toggle="dropdown" aria-expanded="false">
                                        <svg class="svg-inline--fa fa-ellipsis-vertical" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="ellipsis-vertical" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512">
                                            <path fill="currentColor" d="M56 472a56 56 0 1 1 0-112 56 56 0 1 1 0 112zm0-160a56 56 0 1 1 0-112 56 56 0 1 1 0 112zM0 96a56 56 0 1 1 112 0A56 56 0 1 1 0 96z"></path>
                                        </svg>
                                    </button>
                                    <ul class="dropdown-menu text-bg-light" style="font-size: small;">
                                        <!-----Ver Producto--->
                                        @can('ver-producto')
                                        <li>
                                            <a class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#verModal-{{$item->id}}">
                                                <i class="fas fa-eye"></i> Ver Detalles
                                            </a>
                                        </li>
                                        @endcan
                                        <!-----Editar Producto--->
                                        @can('editar-producto')
                                        <li>
                                            <a class="dropdown-item" href="{{route('productos.edit',['producto' => $item])}}">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                        </li>
                                        @endcan
                                        <!-----Código de Barras--->
                                        @can('ver-producto')
                                        <li>
                                            <a class="dropdown-item" role="button" data-bs-toggle="modal" data-bs-target="#codigoBarrasModal-{{$item->id}}">
                                                <i class="fas fa-barcode"></i> Imprimir Código de Barras
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                                <div>
                                    <div class="vr"></div>
                                </div>
                                <div>
                                    <!------Eliminar/Restaurar producto---->
                                    @can('eliminar-producto')
                                    @if ($item->estado == 1)
                                    <button title="Eliminar" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" class="btn btn-datatable btn-icon btn-transparent-dark">
                                        <svg class="svg-inline--fa fa-trash-can" aria-hidden="true" focusable="false" data-prefix="far" data-icon="trash-can" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                            <path fill="currentColor" d="M170.5 51.6L151.5 80h145l-19-28.4c-1.5-2.2-4-3.6-6.7-3.6H177.1c-2.7 0-5.2 1.3-6.7 3.6zm147-26.6L354.2 80H368h48 8c13.3 0 24 10.7 24 24s-10.7 24-24 24h-8V432c0 44.2-35.8 80-80 80H112c-44.2 0-80-35.8-80-80V128H24c-13.3 0-24-10.7-24-24S10.7 80 24 80h8H80 93.8l36.7-55.1C140.9 9.4 158.4 0 177.1 0h93.7c18.7 0 36.2 9.4 46.6 24.9zM80 128V432c0 17.7 14.3 32 32 32H336c17.7 0 32-14.3 32-32V128H80zm80 64V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16zm80 0V400c0 8.8-7.2 16-16 16s-16-7.2-16-16V192c0-8.8 7.2-16 16-16s16 7.2 16 16z"></path>
                                        </svg>
                                    </button>
                                    @else
                                    <button title="Restaurar" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}" class="btn btn-datatable btn-icon btn-transparent-dark">
                                        <i class="fa-solid fa-rotate"></i>
                                    </button>
                                    @endif
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Código de Barras -->
                    {{-- <div class="modal fade" id="codigoBarrasModal-{{$item->id}}" tabindex="-1" aria-labelledby="codigoBarrasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="codigoBarrasLabel">
                    <i class="fas fa-barcode"></i> Imprimir Códigos de Barras
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('productos.codigo-barras', $item->id)}}" method="GET" target="_blank" id="formCodigo-{{$item->id}}">
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <strong>{{$item->nombre}}</strong><br>
                        <small class="text-muted">Código: {{$item->codigo}}</small>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="cantidad-{{$item->id}}" class="form-label">
                                <i class="fas fa-copy"></i> Cantidad de etiquetas a imprimir:
                            </label>
                            <input type="number"
                                   class="form-control text-center"
                                   id="cantidad-{{$item->id}}"
                                   name="cantidad"
                                   value="10"
                                   min="1"
                                   max="100"
                                   required>
                        </div>

                        <!-- Botones de cantidad rápida -->
                        <div class="col-12 mb-3">
                            <label class="form-label d-block">
                                <i class="fas fa-mouse-pointer"></i> Selección rápida:
                            </label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-cantidad" data-target="cantidad-{{$item->id}}" data-valor="5">5</button>
                                <button type="button" class="btn btn-outline-secondary btn-cantidad" data-target="cantidad-{{$item->id}}" data-valor="10">10</button>
                                <button type="button" class="btn btn-outline-secondary btn-cantidad" data-target="cantidad-{{$item->id}}" data-valor="20">20</button>
                                <button type="button" class="btn btn-outline-secondary btn-cantidad" data-target="cantidad-{{$item->id}}" data-valor="50">50</button>
                                <button type="button" class="btn btn-outline-secondary btn-cantidad" data-target="cantidad-{{$item->id}}" data-valor="100">100</button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Información:</strong>
                        <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                            <li>Se imprimirán <strong>8 etiquetas por hoja</strong> (tamaño carta)</li>
                            <li>Cada etiqueta mide <strong>80mm x 50mm</strong></li>
                            <li>Formato: <strong>2 columnas x 4 filas</strong></li>
                            <li>Se generará un PDF listo para imprimir</li>
                        </ul>
                    </div>

                    <!-- Preview de hojas -->
                    <div class="alert alert-secondary text-center" role="alert">
                        <i class="fas fa-file-alt"></i>
                        Se generarán aproximadamente <strong id="hojas-preview-{{$item->id}}">2</strong> hoja(s)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-print"></i> Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
                    <div class="modal fade" id="codigoBarrasModal-{{$item->id}}" tabindex="-1" aria-labelledby="codigoBarrasLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="codigoBarrasLabel">
                                        <i class="fas fa-barcode"></i> Imprimir Códigos de Barras
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{route('productos.codigo-barras', $item->id)}}" method="GET" target="_blank">
                                    <div class="modal-body">
                                        <div class="text-center mb-3">
                                            <strong>{{$item->nombre}}</strong><br>
                                            <small class="text-muted">Código: {{$item->codigo}}</small>
                                        </div>

                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="cantidad-{{$item->id}}" class="form-label">
                                                    <i class="fas fa-copy"></i> Cantidad de etiquetas a imprimir:
                                                </label>
                                                <input type="number"
                                                       class="form-control"
                                                       id="cantidad-{{$item->id}}"
                                                       name="cantidad"
                                                       value="10"
                                                       min="1"
                                                       max="100"
                                                       required>
                                                <small class="form-text text-muted">
                                                    Máximo 100 etiquetas por impresión
                                                </small>
                                            </div>
                                        </div>

                                        {{-- <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Información:</strong>
                                            <ul class="mb-0 mt-2" style="font-size: 0.9rem;">
                                                <li>Se imprimirán <strong>8 etiquetas por hoja</strong> (tamaño carta)</li>
                                                <li>Cada etiqueta mide <strong>80mm x 50mm</strong></li>
                                                <li>Se generará un PDF listo para imprimir</li>
                                            </ul>
                                        </div> --}}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-info">
                                            <i class="fas fa-print"></i> Generar PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Ver Detalles -->
                    <div class="modal fade" id="verModal-{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                                        <i class="fas fa-box"></i> Detalles del Producto
                                    </h1>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Código:</span> {{$item->codigo}}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Nombre:</span> {{$item->nombre}}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Marca:</span> {{$item->marca->caracteristica->nombre}}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Presentación:</span> {{$item->presentacione->caracteristica->nombre}}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Unidad de Medida:</span>
                                                @if($item->unidadMedida)
                                                    {{$item->unidadMedida->nombre}} ({{$item->unidadMedida->abreviatura}})
                                                @else
                                                    No especificada
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <p><span class="fw-bolder">Fecha de vencimiento:</span>
                                                {{$item->fecha_vencimiento ? $item->fecha_vencimiento->format('d/m/Y') : 'No tiene'}}
                                            </p>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <p><span class="fw-bolder">Descripción:</span>
                                                {{$item->descripcion ?? 'Sin descripción'}}
                                            </p>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <p><span class="fw-bolder">Categorías:</span></p>
                                            @foreach ($item->categorias as $category)
                                            <span class="badge bg-secondary me-1">{{$category->caracteristica->nombre}}</span>
                                            @endforeach
                                        </div>
                                        <div class="col-12">
                                            <p class="fw-bolder">Imagen:</p>
                                            <div class="text-center">
                                                @if ($item->img_path != null)
                                                <img src="{{ Storage::url('public/productos/'.$item->img_path) }}"
                                                     alt="{{$item->nombre}}"
                                                     class="img-fluid img-thumbnail border border-4 rounded"
                                                     style="max-height: 400px;">
                                                @else
                                                <div class="alert alert-info">
                                                    <i class="fas fa-image"></i> Este producto no tiene imagen
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times"></i> Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmación-->
                    <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mensaje de confirmación</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    {{ $item->estado == 1 ? '¿Seguro que quieres eliminar el producto?' : '¿Seguro que quieres restaurar el producto?' }}
                                    <br><strong>{{$item->nombre}}</strong>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <form action="{{ route('productos.destroy',['producto'=>$item->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Confirmar</button>
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
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botones de cantidad rápida
    document.querySelectorAll('.btn-cantidad').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const valor = this.getAttribute('data-valor');
            const input = document.getElementById(targetId);

            if (input) {
                input.value = valor;
                // Disparar evento para actualizar preview
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    // Calcular hojas necesarias en tiempo real
    document.querySelectorAll('input[name="cantidad"]').forEach(function(input) {
        input.addEventListener('input', function() {
            const cantidad = parseInt(this.value) || 0;
            const hojasPorPagina = 8; // 8 etiquetas por hoja
            const hojas = Math.ceil(cantidad / hojasPorPagina);

            // Buscar el elemento de preview correspondiente
            const productId = this.id.split('-')[1];
            const previewElement = document.getElementById('hojas-preview-' + productId);

            if (previewElement) {
                previewElement.textContent = hojas;
            }
        });

        // Inicializar el preview al cargar
        input.dispatchEvent(new Event('input'));
    });
});
</script>
@endpush
