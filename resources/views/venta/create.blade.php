@extends('layouts.app')

@section('title','Crear Venta')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .required:after {
        content: " *";
        color: red;
    }

    @keyframes parpadeo {
        0%, 100% { background-color: #fff3cd; }
        50% { background-color: #dc3545; color: white; }
    }

    .stock-bajo {
        animation: parpadeo 1.5s infinite;
        font-weight: bold;
    }

    .stock-critico {
        background-color: #dc3545 !important;
        color: white !important;
        animation: parpadeo 0.8s infinite;
    }

    .tipo-factura-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
        height: 100%;
    }

    .tipo-factura-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .tipo-factura-card.selected {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }

    .info-cotizacion {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .info-sucursal {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">
        @if($cotizacion)
            <i class="fas fa-sync-alt"></i> Convertir Cotización a Venta
        @else
            <i class="fas fa-cash-register"></i> Nueva Venta
        @endif
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('ventas.index')}}">Ventas</a></li>
        <li class="breadcrumb-item active">Crear Venta</li>
    </ol>

    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

    @if($cotizacion)
    <div class="info-cotizacion">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4><i class="fas fa-file-invoice"></i> Datos de la Cotización</h4>
                <p class="mb-1"><strong>Número:</strong> {{$cotizacion->numero_cotizacion}}</p>
                <p class="mb-1"><strong>Cliente:</strong> {{$cotizacion->cliente->persona->razon_social}}</p>
                <p class="mb-0"><strong>Total:</strong> Q {{number_format($cotizacion->total, 2)}}</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-sync-alt fa-3x"></i>
                <p class="mb-0 mt-2">Convirtiendo a Venta</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Información de sucursal activa -->
    {{-- <div class="info-sucursal">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h5 class="mb-0"><i class="fas fa-store"></i> Sucursal Activa: <strong>{{$sucursalUsuario->nombre}}</strong></h5>
                <small>{{$sucursalUsuario->direccion}}</small>
            </div>
            <div class="col-md-3 text-end">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-box"></i> {{$productos->count()}} productos disponibles
                </span>
            </div>
        </div>
    </div> --}}
</div>

<form action="{{ route('ventas.store') }}" method="post" id="formVenta">
    @csrf
    {{-- <input type="hidden" name="sucursal_id" value="{{$sucursalUsuario->id}}"> --}}
    @if($cotizacion)
        <input type="hidden" name="cotizacion_id" value="{{$cotizacion->id}}">
    @endif

    <div class="container-lg mt-4">
        <div class="row gy-4">

            <!------Detalles de la venta---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    <h5 class="mb-0">Detalles de la Venta</h5>
                </div>
                <div class="p-3 border border-3 border-primary">
                    <div class="row gy-3">

                        <!-----Producto---->
                        <div class="col-md-8">
                            <label class="form-label">Producto:</label>
                            <select name="producto_id" id="producto_id"
                                    class="form-control selectpicker"
                                    data-live-search="true"
                                    data-size="5"
                                    title="Busque un producto aquí"
                                    @if($cotizacion) disabled @endif>
                                @foreach ($productos as $item)
                                <option value="{{$item->producto_id}}"
                                        data-stock="{{$item->stock_actual}}"
                                        data-stock-min="{{$item->stock_minimo}}"
                                        data-precio="{{$item->precio_venta}}"
                                        data-nombre="{{$item->producto->nombre}}"
                                        data-codigo="{{$item->producto->codigo}}">
                                    {{$item->producto->codigo}} - {{$item->producto->nombre}}
                                    (Stock: {{$item->stock_actual}})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-----Stock Info---->
                        <div class="col-md-4">
                            <label class="form-label">Stock Disponible:</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                <input disabled id="stock" type="text" class="form-control text-center fw-bold" value="0">
                            </div>
                            <small id="stock-warning" class="text-danger" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i> Stock bajo mínimo
                            </small>
                        </div>

                        <!-----Precio de venta---->
                        <div class="col-md-3">
                            <label for="precio_venta" class="form-label required">Precio Venta:</label>
                            <div class="input-group">
                                <span class="input-group-text">Q</span>
                                <input type="number" name="precio_venta" id="precio_venta"
                                       class="form-control" step="0.01" min="0">
                            </div>
                        </div>

                        <!-----Cantidad---->
                        <div class="col-md-3">
                            <label for="cantidad" class="form-label required">Cantidad:</label>
                            <input type="number" name="cantidad" id="cantidad"
                                   class="form-control" min="1" step="1">
                        </div>

                        <!----Descuento---->
                        <div class="col-md-3">
                            <label for="descuento" class="form-label">Descuento:</label>
                            <div class="input-group">
                                <span class="input-group-text">Q</span>
                                <input type="number" name="descuento" id="descuento"
                                       class="form-control" min="0" step="0.01" value="0">
                            </div>
                        </div>

                        <!-----Botón agregar--->
                        <div class="col-md-3 d-flex align-items-end">
                            <button id="btn_agregar" class="btn btn-primary w-100" type="button"
                                    @if($cotizacion) disabled @endif>
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>

                        <!-----Tabla de productos--->
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="tabla_detalle" class="table table-hover table-bordered">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 35%">Producto</th>
                                            <th style="width: 10%">Cantidad</th>
                                            <th style="width: 15%">Precio</th>
                                            <th style="width: 12%">Descuento</th>
                                            <th style="width: 15%">Subtotal</th>
                                            <th style="width: 8%">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($cotizacion)
                                            @foreach($cotizacion->productos as $index => $producto)
                                            <tr>
                                                <td>{{$index + 1}}</td>
                                                <td>
                                                    <input type="hidden" name="arrayidproducto[]" value="{{$producto->id}}">
                                                    <strong>{{$producto->codigo}}</strong><br>
                                                    <small class="text-muted">{{$producto->nombre}}</small>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="arraycantidad[]" value="{{$producto->pivot->cantidad}}">
                                                    <span class="badge bg-primary">{{$producto->pivot->cantidad}}</span>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="arrayprecioventa[]" value="{{$producto->pivot->precio_unitario}}">
                                                    Q {{number_format($producto->pivot->precio_unitario, 2)}}
                                                </td>
                                                <td>
                                                    <input type="hidden" name="arraydescuento[]" value="{{$producto->pivot->descuento}}">
                                                    Q {{number_format($producto->pivot->descuento, 2)}}
                                                </td>
                                                <td><strong>Q {{number_format($producto->pivot->subtotal, 2)}}</strong></td>
                                                <td>-</td>
                                            </tr>
                                            @endforeach
                                        @else
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x"></i>
                                                <p>No hay productos agregados</p>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Subtotal:</th>
                                            <th colspan="2">Q <span id="sumas">{{$cotizacion ? number_format($cotizacion->subtotal, 2) : '0.00'}}</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-end">IVA (12%):</th>
                                            <th colspan="2">Q <span id="iva">{{$cotizacion ? number_format($cotizacion->impuesto, 2) : '0.00'}}</span></th>
                                        </tr>
                                        <tr class="table-primary">
                                            <th colspan="5" class="text-end">TOTAL:</th>
                                            <th colspan="2">
                                                <input type="hidden" name="total" value="{{$cotizacion ? $cotizacion->total : 0}}" id="inputTotal">
                                                Q <span id="total">{{$cotizacion ? number_format($cotizacion->total, 2) : '0.00'}}</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!--Botón cancelar-->
                        <div class="col-12">
                            <button id="cancelar" type="button" class="btn btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#cancelModal"
                                    @if($cotizacion) style="display: block;" @else style="display: none;" @endif>
                                <i class="fas fa-times"></i> Cancelar Venta
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-----Datos Generales---->
            <div class="col-xl-4">
                <div class="text-white bg-success p-1 text-center">
                    <h5 class="mb-0">Datos Generales</h5>
                </div>
                <div class="p-3 border border-3 border-success">
                    <div class="row gy-3">

                        <!--Tipo de Factura-->
                        {{-- @if(!$cotizacion) --}}
                        <div class="col-12">
                            <label class="form-label required">Tipo de Factura:</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="card tipo-factura-card" data-tipo="RECI" id="card-recibo">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                                            <p class="mb-0"><strong>Recibo Simple</strong></p>
                                            <small class="text-muted">Documento interno</small>
                                        </div>
                                    </div>
                                </div>
                                @if($tieneFEL)
                                <div class="col-6">
                                    <div class="card tipo-factura-card" data-tipo="FACT" id="card-fel">
                                        <div class="card-body text-center p-3">
                                            <i class="fas fa-file-invoice fa-2x text-success mb-2"></i>
                                            <p class="mb-0"><strong>Factura FEL</strong></p>
                                            <small class="text-muted">Certificada SAT</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <input type="hidden" name="tipo_factura" id="tipo_factura" required>
                            @error('tipo_factura')
                            <small class="text-danger d-block mt-1">{{ '*'.$message }}</small>
                            @enderror
                        </div>
                        {{-- @else
                        <input type="hidden" name="tipo_factura" value="RECI">
                        @endif --}}

                        <!--Cliente-->
                        <div class="col-12">
                            <label for="cliente_id" class="form-label required">Cliente:</label>
                            <select name="cliente_id" id="cliente_id"
                                    class="form-control selectpicker show-tick @error('cliente_id') is-invalid @enderror"
                                    data-live-search="true" title="Seleccione cliente" data-size='3' required>
                                @foreach ($clientes as $item)
                                <option value="{{$item->id}}"
                                        @if($cotizacion && $cotizacion->cliente_id == $item->id) selected @endif
                                        @selected(old('cliente_id')==$item->id)>
                                    {{$item->persona->razon_social}}
                                </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Comprobante-->
                        {{-- <div class="col-12">
                            <label for="comprobante_id" class="form-label required">Comprobante:</label>
                            <select name="comprobante_id" id="comprobante_id"
                                    class="form-select @error('comprobante_id') is-invalid @enderror" required>
                                <option value="" selected disabled>Seleccione comprobante</option>
                                @foreach ($comprobantes as $item)
                                <option value="{{$item->id}}" @selected(old('comprobante_id')==$item->id)>
                                    {{$item->tipo_comprobante}}
                                </option>
                                @endforeach
                            </select>
                            @error('comprobante_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div> --}}

                        <!--Impuesto-->
                        <div class="col-6">
                            <label for="impuesto" class="form-label">IVA:</label>
                            <input readonly type="text" name="impuesto" id="impuesto"
                                   class="form-control border-success"
                                   value="{{$cotizacion ? $cotizacion->impuesto : '0.00'}}">
                        </div>

                        <!--Fecha-->
                        <div class="col-6">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input readonly type="date" name="fecha" id="fecha"
                                   class="form-control border-success"
                                   value="<?php echo date("Y-m-d") ?>">
                            <?php
                            use Carbon\Carbon;
                            $fecha_hora = Carbon::now()->toDateTimeString();
                            ?>
                            <input type="hidden" name="fecha_hora" value="{{$fecha_hora}}">
                        </div>

                        <!--Información-->
                        <div class="col-12">
                            <div class="alert alert-info mb-0" role="alert">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong>
                                @if($tieneFEL)
                                    El número de documento será generado automáticamente por el sistema.
                                    Si selecciona FEL, la factura será certificada con SAT.
                                @else
                                    Esta sucursal no tiene configuración FEL activa. Solo puede emitir recibos.
                                @endif
                            </div>
                        </div>

                        <!--Botón guardar-->
                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-success btn-lg w-100" id="guardar"
                                    @if(!$cotizacion) style="display: none;" @endif>
                                <i class="fas fa-cash-register"></i> Realizar Venta
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cancelar -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Advertencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Seguro que quieres cancelar la venta? Se perderán todos los productos agregados.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, continuar</button>
                    <button id="btnCancelarVenta" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Sí, cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function() {
    @if(!$cotizacion)
    disableButtons();
    @endif

    // Seleccionar tipo de factura
    $('.tipo-factura-card').click(function() {
        $('.tipo-factura-card').removeClass('selected');
        $(this).addClass('selected');
        const tipo = $(this).data('tipo');
        $('#tipo_factura').val(tipo);
    });

    $('#producto_id').change(function() {
        mostrarValores();
    });

    $('#btn_agregar').click(function() {
        agregarProducto();
    });

    $('#btnCancelarVenta').click(function() {
        cancelarVenta();
    });

    $('#formVenta').submit(function(e) {
        if (total === 0) {
            e.preventDefault();
            showModal('Debe agregar al menos un producto a la venta', 'error');
            return false;
        }

        if (!$('#tipo_factura').val() && !@json($cotizacion ? true : false)) {
            e.preventDefault();
            showModal('Debe seleccionar un tipo de factura', 'error');
            return false;
        }

        return confirm('¿Está seguro de realizar esta venta?');
    });
});

let cont = @if($cotizacion) {{$cotizacion->productos->count()}} @else 0 @endif;
let subtotal = [];
let sumas = @if($cotizacion) {{$cotizacion->subtotal}} @else 0 @endif;
let iva = @if($cotizacion) {{$cotizacion->impuesto}} @else 0 @endif;
let total = @if($cotizacion) {{$cotizacion->total}} @else 0 @endif;
const impuesto = 12;

@if($cotizacion)
    @foreach($cotizacion->productos as $index => $producto)
        subtotal[{{$index}}] = {{$producto->pivot->subtotal}};
    @endforeach
@endif

function mostrarValores() {
    const selected = $('#producto_id option:selected');
    const stock = selected.data('stock');
    const stockMin = selected.data('stock-min');
    const precio = selected.data('precio');

    $('#stock').val(stock);
    $('#precio_venta').val(precio);

    if (stock <= stockMin) {
        $('#stock').addClass('stock-bajo');
        $('#stock-warning').show();

        if (stock === 0) {
            $('#stock').addClass('stock-critico');
            showModal('¡ATENCIÓN! Producto sin stock disponible', 'warning');
        }
    } else {
        $('#stock').removeClass('stock-bajo stock-critico');
        $('#stock-warning').hide();
    }
}

function agregarProducto() {
    const selected = $('#producto_id option:selected');
    const idProducto = selected.val();
    const nombreProducto = selected.data('nombre');
    const codigoProducto = selected.data('codigo');
    const stockDisponible = parseInt(selected.data('stock'));
    const cantidad = parseInt($('#cantidad').val()) || 0;
    const precioVenta = parseFloat($('#precio_venta').val()) || 0;
    const descuento = parseFloat($('#descuento').val()) || 0;

    if (!idProducto || !nombreProducto || !cantidad || !precioVenta) {
        showModal('Complete todos los campos requeridos', 'error');
        return;
    }

    if (cantidad <= 0) {
        showModal('La cantidad debe ser mayor a 0', 'error');
        return;
    }

    if (precioVenta <= 0) {
        showModal('El precio debe ser mayor a 0', 'error');
        return;
    }

    if (cantidad > stockDisponible) {
        showModal(`Stock insuficiente. Disponible: ${stockDisponible}`, 'error');
        return;
    }

    if (descuento < 0) {
        showModal('El descuento no puede ser negativo', 'error');
        return;
    }

    const subtotalProducto = (cantidad * precioVenta);
    if (descuento > subtotalProducto) {
        showModal('El descuento no puede ser mayor al subtotal del producto', 'error');
        return;
    }

    let existe = false;
    $('#tabla_detalle tbody tr').each(function() {
        const id = $(this).find('input[name="arrayidproducto[]"]').val();
        if (id == idProducto) {
            existe = true;
            return false;
        }
    });

    if (existe) {
        showModal('El producto ya fue agregado. Elimínelo primero si desea modificarlo', 'warning');
        return;
    }

    subtotal[cont] = round((cantidad * precioVenta) - descuento);
    sumas += subtotal[cont];
    iva = round(sumas * (impuesto / 100));
    total = round(sumas);

    if ($('#tabla_detalle tbody tr td').attr('colspan') == '7') {
        $('#tabla_detalle tbody').empty();
    }

    let fila = '<tr id="fila' + cont + '">' +
        '<td class="text-center">' + (cont + 1) + '</td>' +
        '<td>' +
            '<input type="hidden" name="arrayidproducto[]" value="' + idProducto + '">' +
            '<strong>' + codigoProducto + '</strong><br>' +
            '<small class="text-muted">' + nombreProducto + '</small>' +
        '</td>' +
        '<td class="text-center">' +
            '<input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' +
            '<span class="badge bg-primary">' + cantidad + '</span>' +
        '</td>' +
        '<td class="text-end">' +
            '<input type="hidden" name="arrayprecioventa[]" value="' + precioVenta + '">' +
            'Q ' + precioVenta.toFixed(2) +
        '</td>' +
        '<td class="text-end">' +
            '<input type="hidden" name="arraydescuento[]" value="' + descuento + '">' +
            'Q ' + descuento.toFixed(2) +
        '</td>' +
        '<td class="text-end"><strong>Q ' + subtotal[cont].toFixed(2) + '</strong></td>' +
        '<td class="text-center">' +
            '<button class="btn btn-danger btn-sm" type="button" onClick="eliminarProducto(' + cont + ')" title="Eliminar producto">' +
                '<i class="fas fa-trash"></i>' +
            '</button>' +
        '</td>' +
    '</tr>';

    $('#tabla_detalle tbody').append(fila);
    limpiarCampos();
    cont++;
    disableButtons();

    $('#sumas').html(sumas.toFixed(2));
    $('#iva').html(iva.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#impuesto').val(iva.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    showModal('Producto agregado correctamente', 'success');
}

function eliminarProducto(indice) {
    sumas -= round(subtotal[indice]);
    iva = round(sumas * (impuesto / 100));
    // total = round(sumas + iva);
    total = round(sumas);

    $('#sumas').html(sumas.toFixed(2));
    $('#iva').html(iva.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#impuesto').val(iva.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    $('#fila' + indice).remove();

    $('#tabla_detalle tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });

    if ($('#tabla_detalle tbody tr').length === 0) {
        $('#tabla_detalle tbody').html(`
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="fas fa-inbox fa-2x"></i>
                    <p>No hay productos agregados</p>
                </td>
            </tr>
        `);
    }

    disableButtons();
    showModal('Producto eliminado', 'info');
}

function cancelarVenta() {
    $('#tabla_detalle tbody').html(`
        <tr>
            <td colspan="7" class="text-center text-muted">
                <i class="fas fa-inbox fa-2x"></i>
                <p>No hay productos agregados</p>
            </td>
        </tr>
    `);

    cont = 0;
    subtotal = [];
    sumas = 0;
    iva = 0;
    total = 0;

    $('#sumas').html('0.00');
    $('#iva').html('0.00');
    $('#total').html('0.00');
    $('#impuesto').val('0.00');
    $('#inputTotal').val('0');

    limpiarCampos();
    disableButtons();

    $('.tipo-factura-card').removeClass('selected');
    $('#tipo_factura').val('');
}

function disableButtons() {
    if (total == 0) {
        $('#guardar').hide();
        $('#cancelar').hide();
    } else {
        $('#guardar').show();
        $('#cancelar').show();
    }
}

function limpiarCampos() {
    $('#producto_id').selectpicker('val', '');
    $('#cantidad').val('');
    $('#precio_venta').val('');
    $('#descuento').val('0');
    $('#stock').val('0');
    $('#stock').removeClass('stock-bajo stock-critico');
    $('#stock-warning').hide();
}

function round(num, decimales = 2) {
    return Math.round((num + Number.EPSILON) * Math.pow(10, decimales)) / Math.pow(10, decimales);
}

function showModal(message, icon = 'info') {
    const iconColors = {
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107',
        'info': '#17a2b8'
    };

    Swal.fire({
        text: message,
        icon: icon,
        iconColor: iconColors[icon],
        confirmButtonColor: iconColors[icon],
        confirmButtonText: 'Aceptar',
        timer: icon === 'success' ? 2000 : null,
        timerProgressBar: icon === 'success',
        toast: icon === 'success',
        position: icon === 'success' ? 'top-end' : 'center',
        showConfirmButton: icon !== 'success'
    });
}
</script>
@endpush
