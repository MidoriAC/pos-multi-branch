@extends('layouts.app')

@section('title','Transferir Inventario')

@push('css')
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .transfer-diagram {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .sucursal-box {
        background: rgba(255,255,255,0.2);
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }
    .info-producto-transfer {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        margin-bottom: 20px;
        display: none;
    }
    .info-producto-transfer.show {
        display: block;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Transferir Inventario Entre Sucursales</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('inventario-sucursal.index') }}">Inventario</a></li>
        <li class="breadcrumb-item active">Transferir Inventario</li>
    </ol>

    <!-- Diagrama Visual -->
    <div class="transfer-diagram">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="sucursal-box">
                    <i class="fas fa-store fa-3x mb-3"></i>
                    <h4>Sucursal Origen</h4>
                    <p class="mb-0" id="nombreOrigen">Seleccione sucursal</p>
                </div>
            </div>
            <div class="col-md-2 text-center">
                <i class="fas fa-arrow-right fa-3x"></i>
                <p class="mt-2">TRANSFERENCIA</p>
            </div>
            <div class="col-md-5">
                <div class="sucursal-box">
                    <i class="fas fa-store fa-3x mb-3"></i>
                    <h4>Sucursal Destino</h4>
                    <p class="mb-0" id="nombreDestino">Seleccione sucursal</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Transferencia -->
    <div class="card">
        <form action="{{route('inventario-sucursal.store-transferencia')}}" method="POST" id="formTransferencia">
            @csrf
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exchange-alt"></i> Formulario de Transferencia
                </h5>
            </div>
            <div class="card-body">

                {{-- Sucursales --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-store me-2"></i>Sucursales
                    </h5>
                </div>

                <div class="row g-4">
                    <!-- Sucursal Origen -->
                    <div class="col-md-6">
                        <label for="sucursal_origen_id" class="form-label required">Sucursal Origen:</label>
                        <select name="sucursal_origen_id"
                                id="sucursal_origen_id"
                                class="form-select @error('sucursal_origen_id') is-invalid @enderror"
                                required>
                            <option value="" selected disabled>Seleccione sucursal origen</option>
                            @foreach($sucursales as $sucursal)
                            <option value="{{$sucursal->id}}" @selected(old('sucursal_origen_id')==$sucursal->id)>
                                {{$sucursal->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_origen_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Sucursal desde donde se enviará el producto
                        </small>
                    </div>

                    <!-- Sucursal Destino -->
                    <div class="col-md-6">
                        <label for="sucursal_destino_id" class="form-label required">Sucursal Destino:</label>
                        <select name="sucursal_destino_id"
                                id="sucursal_destino_id"
                                class="form-select @error('sucursal_destino_id') is-invalid @enderror"
                                required
                                disabled>
                            <option value="" selected disabled>Primero seleccione origen</option>
                            @foreach($sucursales as $sucursal)
                            <option value="{{$sucursal->id}}" @selected(old('sucursal_destino_id')==$sucursal->id)>
                                {{$sucursal->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_destino_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Sucursal que recibirá el producto
                        </small>
                    </div>
                </div>

                {{-- Producto --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-box me-2"></i>Producto a Transferir
                    </h5>
                </div>

                <div class="row g-4">
                    <!-- Producto -->
                    <div class="col-md-6">
                        <label for="producto_id" class="form-label required">Producto:</label>
                        <select data-size="5"
                                title="Primero seleccione sucursal origen"
                                data-live-search="true"
                                name="producto_id"
                                id="producto_id"
                                class="form-control selectpicker show-tick @error('producto_id') is-invalid @enderror"
                                required
                                disabled>
                            <option value="">Seleccione un producto</option>
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-search"></i> Solo se muestran productos con stock disponible
                        </small>
                    </div>

                    <!-- Ubicación Destino -->
                    <div class="col-md-6">
                        <label for="ubicacion_destino_id" class="form-label">Ubicación en Destino:</label>
                        <select name="ubicacion_destino_id"
                                id="ubicacion_destino_id"
                                class="form-select @error('ubicacion_destino_id') is-invalid @enderror"
                                disabled>
                            <option value="">Sin ubicación específica</option>
                        </select>
                        @error('ubicacion_destino_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> Ubicación donde se colocará el producto (opcional)
                        </small>
                    </div>

                    <!-- Información del Producto -->
                    <div class="col-12">
                        <div class="info-producto-transfer" id="infoProducto">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong><i class="fas fa-barcode"></i> Código:</strong>
                                    <span id="productoCodigo">-</span>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fas fa-boxes"></i> Stock Disponible:</strong>
                                    <span id="productoStock" class="text-primary fs-5">0</span> unidades
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fas fa-dollar-sign"></i> Precio:</strong>
                                    Q <span id="productoPrecio">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cantidad y Motivo --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-clipboard-list me-2"></i>Detalles de la Transferencia
                    </h5>
                </div>

                <div class="row g-4">
                    <!-- Cantidad -->
                    <div class="col-md-6">
                        <label for="cantidad" class="form-label required">Cantidad a Transferir:</label>
                        <input type="number"
                               name="cantidad"
                               id="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{old('cantidad')}}"
                               min="1"
                               placeholder="0"
                               required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-hashtag"></i> Cantidad de unidades a transferir
                        </small>
                    </div>

                    <!-- Motivo -->
                    <div class="col-12">
                        <label for="motivo" class="form-label required">Motivo de la Transferencia:</label>
                        <textarea name="motivo"
                                  id="motivo"
                                  rows="3"
                                  class="form-control @error('motivo') is-invalid @enderror"
                                  placeholder="Explique el motivo de la transferencia (ej: Reabastecimiento, baja rotación, solicitud de sucursal, etc.)"
                                  required>{{old('motivo')}}</textarea>
                        @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-comment-alt"></i> Justificación de la transferencia
                        </small>
                    </div>
                </div>

                {{-- Advertencias --}}
                <div class="alert alert-info mt-4" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle"></i> Información Importante
                    </h6>
                    <ul class="mb-0">
                        <li>La transferencia se registrará en el historial de ambas sucursales.</li>
                        <li>El stock se <strong>descontará inmediatamente</strong> de la sucursal origen.</li>
                        <li>El stock se <strong>agregará automáticamente</strong> a la sucursal destino.</li>
                        <li>No se puede transferir más cantidad de la disponible en la sucursal origen.</li>
                        <li>Esta operación quedará registrada para auditoría.</li>
                    </ul>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('inventario-sucursal.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit" disabled>
                    <i class="fas fa-exchange-alt"></i> Realizar Transferencia
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function() {
    let stockDisponible = 0;

    // Cuando cambia sucursal origen
    $('#sucursal_origen_id').change(function() {
        const sucursalOrigenId = $(this).val();
        const nombreOrigen = $(this).find('option:selected').text();

        $('#nombreOrigen').text(nombreOrigen);

        // Habilitar sucursal destino
        $('#sucursal_destino_id').prop('disabled', false);

        // Filtrar opciones de destino (excluir origen)
        $('#sucursal_destino_id option').each(function() {
            if ($(this).val() == sucursalOrigenId) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });

        // Cargar productos de la sucursal origen
        cargarProductos(sucursalOrigenId);

        // Resetear
        resetearFormulario();
    });

    // Cuando cambia sucursal destino
    $('#sucursal_destino_id').change(function() {
        const sucursalDestinoId = $(this).val();
        const nombreDestino = $(this).find('option:selected').text();

        $('#nombreDestino').text(nombreDestino);

        // Cargar ubicaciones de la sucursal destino
        if (sucursalDestinoId) {
            $.ajax({
                url: `/productos-danados/ubicaciones/${sucursalDestinoId}`,
                type: 'GET',
                success: function(data) {
                    let options = '<option value="">Sin ubicación específica</option>';
                    data.forEach(function(ubicacion) {
                        options += `<option value="${ubicacion.id}">${ubicacion.codigo} - ${ubicacion.nombre}</option>`;
                    });
                    $('#ubicacion_destino_id').html(options);
                    $('#ubicacion_destino_id').prop('disabled', false);
                },
                error: function() {
                    $('#ubicacion_destino_id').html('<option value="">Error al cargar ubicaciones</option>');
                }
            });
        }

        validarFormulario();
    });

    // Cargar productos con stock de la sucursal
    function cargarProductos(sucursalId) {
        $.ajax({
            url: `/inventario-sucursal/productos-sucursal/${sucursalId}`,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Seleccione un producto</option>';
                data.forEach(function(producto) {
                    options += `<option value="${producto.id}" data-codigo="${producto.codigo}" data-stock="${producto.stock}">
                        ${producto.codigo} - ${producto.nombre} (Stock: ${producto.stock})
                    </option>`;
                });
                $('#producto_id').html(options);
                $('#producto_id').prop('disabled', false);
                $('#producto_id').selectpicker('refresh');
            },
            error: function() {
                $('#producto_id').html('<option value="">Error al cargar productos</option>');
                $('#producto_id').selectpicker('refresh');
            }
        });
    }

    // Cuando cambia el producto
    $('#producto_id').change(function() {
        const productoId = $(this).val();
        const sucursalOrigenId = $('#sucursal_origen_id').val();

        if (productoId && sucursalOrigenId) {
            $.ajax({
                url: '/inventario-sucursal/get-inventario',
                type: 'GET',
                data: {
                    producto_id: productoId,
                    sucursal_id: sucursalOrigenId
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        stockDisponible = data.stock_actual;

                        $('#productoCodigo').text(data.producto.codigo);
                        $('#productoStock').text(stockDisponible);
                        $('#productoPrecio').text(parseFloat(data.precio_venta).toFixed(2));
                        $('#infoProducto').addClass('show');

                        // Establecer máximo de cantidad
                        $('#cantidad').attr('max', stockDisponible);

                        validarFormulario();
                    } else {
                        alert(response.message);
                        resetearProducto();
                    }
                },
                error: function() {
                    alert('Error al obtener información del producto');
                    resetearProducto();
                }
            });
        }
    });

    // Validar cantidad en tiempo real
    $('#cantidad').on('input', function() {
        const cantidad = parseInt($(this).val()) || 0;

        if (cantidad > stockDisponible) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback d-block">La cantidad no puede ser mayor al stock disponible (${stockDisponible})</div>`);
            $('#btnSubmit').prop('disabled', true);
        } else if (cantidad <= 0) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback d-block">La cantidad debe ser mayor a 0</div>');
            $('#btnSubmit').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            validarFormulario();
        }
    });

    // Validar formulario completo
    function validarFormulario() {
        const sucursalOrigen = $('#sucursal_origen_id').val();
        const sucursalDestino = $('#sucursal_destino_id').val();
        const producto = $('#producto_id').val();
        const cantidad = parseInt($('#cantidad').val()) || 0;
        const motivo = $('#motivo').val().trim();

        if (sucursalOrigen && sucursalDestino && producto &&
            cantidad > 0 && cantidad <= stockDisponible && motivo) {
            $('#btnSubmit').prop('disabled', false);
        } else {
            $('#btnSubmit').prop('disabled', true);
        }
    }

    function resetearFormulario() {
        $('#producto_id').html('<option value="">Seleccione un producto</option>');
        $('#producto_id').prop('disabled', true);
        $('#producto_id').selectpicker('refresh');
        $('#ubicacion_destino_id').prop('disabled', true);
        resetearProducto();
    }

    function resetearProducto() {
        stockDisponible = 0;
        $('#productoCodigo').text('-');
        $('#productoStock').text('0');
        $('#productoPrecio').text('0.00');
        $('#infoProducto').removeClass('show');
        $('#cantidad').attr('max', '');
        $('#cantidad').val('');
        $('#btnSubmit').prop('disabled', true);
    }

    // Validar al cambiar motivo
    $('#motivo').on('input', validarFormulario);
});
</script>
@endpush
