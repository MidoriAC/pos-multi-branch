@extends('layouts.app')

@section('title','Reportar Producto Dañado')

@push('css')
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .info-stock {
        padding: 15px;
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd;
        margin-bottom: 20px;
        display: none;
    }
    .info-stock.show {
        display: block;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Reportar Producto Dañado</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos-danados.index')}}">Productos Dañados</a></li>
        <li class="breadcrumb-item active">Reportar Producto Dañado</li>
    </ol>

    <div class="card">
        <form action="{{ route('productos-danados.store') }}" method="post" id="formProductoDanado">
            @csrf
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Formulario de Reporte</strong>
            </div>
            <div class="card-body">

                {{-- Información del Producto --}}
                <div class="mb-4">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-box me-2"></i>Información del Producto
                    </h5>
                </div>

                <div class="row g-4">
                    <!-- Sucursal -->
                    <div class="col-md-6">
                        <label for="sucursal_id" class="form-label required">Sucursal:</label>
                        <select name="sucursal_id"
                                id="sucursal_id"
                                class="form-select @error('sucursal_id') is-invalid @enderror"
                                required>
                            <option value="" selected disabled>Seleccione una sucursal</option>
                            @foreach ($sucursales as $sucursal)
                            <option value="{{$sucursal->id}}" @selected(old('sucursal_id')==$sucursal->id)>
                                {{$sucursal->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-store"></i> Sucursal donde se encuentra el producto dañado
                        </small>
                    </div>

                    <!-- Producto -->
                    <div class="col-md-6">
                        <label for="producto_id" class="form-label required">Producto:</label>
                        <select data-size="5"
                                title="Primero seleccione una sucursal"
                                data-live-search="true"
                                name="producto_id"
                                id="producto_id"
                                class="form-control selectpicker show-tick @error('producto_id') is-invalid @enderror"
                                required
                                disabled>
                            <option value="">Seleccione un producto</option>
                            @foreach ($productos as $producto)
                            <option value="{{$producto->id}}"
                                    data-codigo="{{$producto->codigo}}"
                                    @selected(old('producto_id')==$producto->id)>
                                {{$producto->codigo}} - {{$producto->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('producto_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-search"></i> Busque el producto dañado
                        </small>
                    </div>

                    <!-- Información de Stock (se muestra dinámicamente) -->
                    <div class="col-12">
                        <div class="info-stock" id="infoStock">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong><i class="fas fa-boxes"></i> Stock Disponible:</strong>
                                    <span id="stockDisponible" class="text-primary fs-4">-</span> unidades
                                </div>
                                <div class="col-md-6">
                                    <strong><i class="fas fa-dollar-sign"></i> Precio Venta:</strong>
                                    Q <span id="precioVenta" class="text-success fs-5">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="col-md-6">
                        <label for="ubicacion_id" class="form-label">Ubicación:</label>
                        <select name="ubicacion_id"
                                id="ubicacion_id"
                                class="form-select @error('ubicacion_id') is-invalid @enderror"
                                disabled>
                            <option value="">Sin ubicación específica</option>
                        </select>
                        @error('ubicacion_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> Ubicación exacta del producto (opcional)
                        </small>
                    </div>

                    <!-- Cantidad -->
                    <div class="col-md-6">
                        <label for="cantidad" class="form-label required">Cantidad Dañada:</label>
                        <input type="number"
                               name="cantidad"
                               id="cantidad"
                               class="form-control @error('cantidad') is-invalid @enderror"
                               value="{{old('cantidad')}}"
                               min="1"
                               placeholder="Ej: 5"
                               required>
                        @error('cantidad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-hashtag"></i> Cantidad de unidades dañadas
                        </small>
                    </div>
                </div>

                {{-- Motivo del Daño --}}
                <div class="mb-4 mt-5">
                    <h5 class="border-bottom pb-2">
                        <i class="fas fa-clipboard-list me-2"></i>Motivo del Daño
                    </h5>
                </div>

                <div class="row g-4">
                    <!-- Motivo -->
                    <div class="col-md-6">
                        <label for="motivo" class="form-label required">Motivo:</label>
                        <select name="motivo"
                                id="motivo"
                                class="form-select @error('motivo') is-invalid @enderror"
                                required>
                            <option value="" selected disabled>Seleccione un motivo</option>
                            <option value="vencido" @selected(old('motivo')=='vencido')>
                                <i class="fas fa-calendar-times"></i> Producto Vencido
                            </option>
                            <option value="roto" @selected(old('motivo')=='roto')>
                                <i class="fas fa-glass-broken"></i> Producto Roto
                            </option>
                            <option value="deteriorado" @selected(old('motivo')=='deteriorado')>
                                <i class="fas fa-sad-tear"></i> Deteriorado
                            </option>
                            <option value="humedad" @selected(old('motivo')=='humedad')>
                                <i class="fas fa-tint"></i> Daño por Humedad
                            </option>
                            <option value="contaminacion" @selected(old('motivo')=='contaminacion')>
                                <i class="fas fa-radiation"></i> Contaminación
                            </option>
                            <option value="defecto_fabrica" @selected(old('motivo')=='defecto_fabrica')>
                                <i class="fas fa-industry"></i> Defecto de Fábrica
                            </option>
                            <option value="otro" @selected(old('motivo')=='otro')>
                                <i class="fas fa-question-circle"></i> Otro
                            </option>
                        </select>
                        @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Costo de Pérdida -->
                    <div class="col-md-6">
                        <label for="costo_perdida" class="form-label">Costo de Pérdida (Q):</label>
                        <input type="number"
                               name="costo_perdida"
                               id="costo_perdida"
                               class="form-control @error('costo_perdida') is-invalid @enderror"
                               value="{{old('costo_perdida')}}"
                               min="0"
                               step="0.01"
                               placeholder="0.00"
                               readonly>
                        @error('costo_perdida')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-calculator"></i> Se calculará automáticamente (cantidad × precio venta)
                        </small>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <label for="descripcion" class="form-label required">Descripción Detallada:</label>
                        <textarea name="descripcion"
                                  id="descripcion"
                                  rows="4"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Describa detalladamente el estado del producto, cómo ocurrió el daño, etc."
                                  required>{{old('descripcion')}}</textarea>
                        @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Proporcione el máximo detalle posible sobre el daño
                        </small>
                    </div>
                </div>

                {{-- Advertencias --}}
                <div class="alert alert-warning mt-4" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle"></i> Importante
                    </h6>
                    <ul class="mb-0">
                        <li>Este reporte deberá ser <strong>aprobado por un supervisor</strong> antes de descontar el inventario.</li>
                        <li>Asegúrese de verificar el <strong>stock disponible</strong> antes de reportar.</li>
                        <li>Una vez aprobado, <strong>no se podrá revertir</strong> el descuento del inventario.</li>
                        <li>La descripción detallada es fundamental para el proceso de aprobación.</li>
                    </ul>
                </div>

            </div>

            <div class="card-footer text-center">
                <a href="{{ route('productos-danados.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-danger" id="btnSubmit" disabled>
                    <i class="fas fa-save"></i> Registrar Reporte
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
    let precioVenta = 0;

    // Cuando cambia la sucursal
    $('#sucursal_id').change(function() {
        const sucursalId = $(this).val();

        // Habilitar selector de producto
        $('#producto_id').prop('disabled', false);
        $('#producto_id').selectpicker('refresh');

        // Limpiar ubicaciones y cargar las de la sucursal seleccionada
        $('#ubicacion_id').html('<option value="">Cargando ubicaciones...</option>');

        $.ajax({
            url: `/productos-danados/ubicaciones/${sucursalId}`,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Sin ubicación específica</option>';
                data.forEach(function(ubicacion) {
                    options += `<option value="${ubicacion.id}">${ubicacion.codigo} - ${ubicacion.nombre}</option>`;
                });
                $('#ubicacion_id').html(options);
                $('#ubicacion_id').prop('disabled', false);
            },
            error: function() {
                $('#ubicacion_id').html('<option value="">Error al cargar ubicaciones</option>');
            }
        });

        // Resetear información de stock
        resetearStock();
    });

    // Cuando cambia el producto
    $('#producto_id').change(function() {
        const productoId = $(this).val();
        const sucursalId = $('#sucursal_id').val();

        if (productoId && sucursalId) {
            // Obtener stock del producto en la sucursal
            $.ajax({
                url: '/productos-danados/get-stock',
                type: 'GET',
                data: {
                    producto_id: productoId,
                    sucursal_id: sucursalId
                },
                success: function(response) {
                    if (response.success) {
                        stockDisponible = response.stock;
                        precioVenta = response.precio_venta;

                        $('#stockDisponible').text(stockDisponible);
                        $('#precioVenta').text(parseFloat(precioVenta).toFixed(2));
                        $('#infoStock').addClass('show');

                        // Establecer máximo de cantidad
                        $('#cantidad').attr('max', stockDisponible);

                        // Habilitar el botón de submit
                        validarFormulario();
                    } else {
                        alert(response.message);
                        resetearStock();
                    }
                },
                error: function() {
                    alert('Error al obtener información del producto');
                    resetearStock();
                }
            });
        }
    });

    // Calcular costo de pérdida cuando cambia la cantidad
    $('#cantidad').on('input', function() {
        const cantidad = parseInt($(this).val()) || 0;

        if (cantidad > stockDisponible) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after(`<div class="invalid-feedback d-block">La cantidad no puede ser mayor al stock disponible (${stockDisponible})</div>`);
            $('#btnSubmit').prop('disabled', true);
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();

            // Calcular costo de pérdida
            const costoPerdida = cantidad * precioVenta;
            $('#costo_perdida').val(costoPerdida.toFixed(2));

            validarFormulario();
        }
    });

    // Validar formulario completo
    function validarFormulario() {
        const sucursalId = $('#sucursal_id').val();
        const productoId = $('#producto_id').val();
        const cantidad = parseInt($('#cantidad').val()) || 0;
        const motivo = $('#motivo').val();
        const descripcion = $('#descripcion').val().trim();

        if (sucursalId && productoId && cantidad > 0 && cantidad <= stockDisponible && motivo && descripcion) {
            $('#btnSubmit').prop('disabled', false);
        } else {
            $('#btnSubmit').prop('disabled', true);
        }
    }

    function resetearStock() {
        stockDisponible = 0;
        precioVenta = 0;
        $('#stockDisponible').text('-');
        $('#precioVenta').text('0.00');
        $('#infoStock').removeClass('show');
        $('#cantidad').attr('max', '');
        $('#costo_perdida').val('');
        $('#btnSubmit').prop('disabled', true);
    }

    // Validar al cambiar otros campos
    $('#motivo, #descripcion').on('change input', validarFormulario);
});
</script>
@endpush
