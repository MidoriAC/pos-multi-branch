@extends('layouts.app')

@section('title','Crear Compra')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .required:after {
        content: " *";
        color: red;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Crear Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Crear Compra</li>
    </ol>
</div>

<form action="{{ route('compras.store') }}" method="post">
    @csrf

    <div class="container-lg mt-4">
        <div class="row gy-4">
            <!------Detalles de la compra---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    <h5 class="mb-0">Detalles de la Compra</h5>
                </div>
                <div class="p-3 border border-3 border-primary">
                    <div class="row">
                        <!-----Producto---->
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Producto:</label>
                            <select  id="producto_id" class="form-control selectpicker"
                                    data-live-search="true" data-size="5" title="Busque un producto aquí">
                                @foreach ($productos as $item)
                                <option value="{{$item->id}}"
                                        data-codigo="{{$item->codigo}}"
                                        data-nombre="{{$item->nombre}}">
                                    {{$item->codigo}} - {{$item->nombre}}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-----Ubicación---->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ubicación:</label>
                            <select  id="ubicacion_temp"
                                    class="form-select" disabled>
                                <option value="">Primero seleccione sucursal</option>
                            </select>
                            <small class="text-muted">Opcional</small>
                        </div>

                        <!-----Cantidad---->
                        <div class="col-md-3 mb-3">
                            <label for="cantidad" class="form-label required">Cantidad:</label>
                            <input type="number"  id="cantidad"
                                   class="form-control" min="1" step="1">
                        </div>

                        <!-----Precio de compra---->
                        <div class="col-md-3 mb-3">
                            <label for="precio_compra" class="form-label required">Precio Compra:</label>
                            <input type="number"  id="precio_compra"
                                   class="form-control" min="0" step="0.01">
                        </div>

                        <!-----Precio de venta---->
                        <div class="col-md-3 mb-3">
                            <label for="precio_venta" class="form-label required">Precio Venta:</label>
                            <input type="number" id="precio_venta"
                                   class="form-control" min="0" step="0.01">
                        </div>

                        <!-----Botón para agregar--->
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button id="btn_agregar" class="btn btn-primary w-100" type="button">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </div>

                        <!-----Tabla para el detalle de la compra--->
                        <div class="col-12">
                            <div class="table-responsive">
                                <table id="tabla_detalle" class="table table-hover table-bordered">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 30%">Producto</th>
                                            <th style="width: 10%">Cantidad</th>
                                            <th style="width: 15%">P. Compra</th>
                                            <th style="width: 15%">P. Venta</th>
                                            <th style="width: 15%">Subtotal</th>
                                            <th style="width: 10%">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                <i class="fas fa-inbox fa-2x"></i>
                                                <p>No hay productos agregados</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Subtotal:</th>
                                            <th colspan="2">Q <span id="sumas">0.00</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-end">IVA (12%):</th>
                                            <th colspan="2">Q <span id="iva">0.00</span></th>
                                        </tr>
                                        <tr class="table-primary">
                                            <th colspan="5" class="text-end">TOTAL:</th>
                                            <th colspan="2">
                                                <input type="hidden" name="total" value="0" id="inputTotal">
                                                Q <span id="total">0.00</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!--Botón para cancelar compra-->
                        <div class="col-12 mt-2">
                            <button id="cancelar" type="button" class="btn btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#cancelModal" style="display: none;">
                                <i class="fas fa-times"></i> Cancelar Compra
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
                    <div class="row">
                        <!--Sucursal-->
                        <div class="col-12 mb-3">
                            <label for="sucursal_id" class="form-label required">Sucursal:</label>
                            <select name="sucursal_id" id="sucursal_id"
                                    class="form-select @error('sucursal_id') is-invalid @enderror"
                                    required>
                                <option value="" selected disabled>Seleccione sucursal</option>
                                @foreach ($sucursales as $sucursal)
                                <option value="{{$sucursal->id}}" @selected(old('sucursal_id')==$sucursal->id)>
                                    {{$sucursal->nombre}}
                                </option>
                                @endforeach
                            </select>
                            @error('sucursal_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Proveedor-->
                        <div class="col-12 mb-3">
                            <label for="proveedore_id" class="form-label required">Proveedor:</label>
                            <select name="proveedore_id" id="proveedore_id"
                                    class="form-control selectpicker show-tick @error('proveedore_id') is-invalid @enderror"
                                    data-live-search="true" title="Seleccione proveedor" data-size='3' required>
                                @foreach ($proveedores as $item)
                                <option value="{{$item->id}}" @selected(old('proveedore_id')==$item->id)>
                                    {{$item->persona->razon_social}}
                                </option>
                                @endforeach
                            </select>
                            @error('proveedore_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Tipo de comprobante-->
                        <div class="col-12 mb-3">
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
                        </div>

                        <!--Numero de comprobante-->
                        <div class="col-12 mb-3">
                            <label for="numero_comprobante" class="form-label required">Nº Comprobante:</label>
                            <input type="text" name="numero_comprobante" id="numero_comprobante"
                                   class="form-control @error('numero_comprobante') is-invalid @enderror"
                                   value="{{old('numero_comprobante')}}" required>
                            @error('numero_comprobante')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Impuesto-->
                        <div class="col-6 mb-3">
                            <label for="impuesto" class="form-label">Impuesto (IVA):</label>
                            <input readonly type="text" name="impuesto" id="impuesto"
                                   class="form-control border-success" value="0.00">
                            @error('impuesto')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Fecha-->
                        <div class="col-6 mb-3">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input readonly type="date" name="fecha" id="fecha"
                                   class="form-control border-success" value="<?php echo date("Y-m-d") ?>">
                            <?php
                            use Carbon\Carbon;
                            $fecha_hora = Carbon::now()->toDateTimeString();
                            ?>
                            <input type="hidden" name="fecha_hora" value="{{$fecha_hora}}">
                        </div>

                        <!--Información-->
                        <div class="col-12 mb-3">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> El stock se actualizará automáticamente en el inventario de la sucursal seleccionada.
                            </div>
                        </div>

                        <!--Botón guardar-->
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg w-100" id="guardar" style="display: none;">
                                <i class="fas fa-save"></i> Realizar Compra
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cancelar la compra -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Advertencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    ¿Seguro que quieres cancelar la compra? Se perderán todos los productos agregados.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, continuar</button>
                    <button id="btnCancelarCompra" type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        Sí, cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@endsection

@if($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
        {{  $error }}<br>

        @endforeach
    </div>
@endif
@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar
    $('#impuesto').val('Q 0.00');
    disableButtons();

    // Event listeners
    $('#btn_agregar').click(function() {
        agregarProducto();
    });

    $('#btnCancelarCompra').click(function() {
        cancelarCompra();
    });

    // Cargar ubicaciones cuando cambia la sucursal
    $('#sucursal_id').change(function() {
        const sucursalId = $(this).val();

        if (sucursalId) {
            $.ajax({
                url: `/compras/ubicaciones/${sucursalId}`,
                type: 'GET',
                success: function(data) {
                    let options = '<option value="">Sin ubicación</option>';
                    data.forEach(function(ubicacion) {
                        options += `<option value="${ubicacion.id}">${ubicacion.codigo} - ${ubicacion.nombre}</option>`;
                    });
                    $('#ubicacion_temp').html(options);
                    $('#ubicacion_temp').prop('disabled', false);
                },
                error: function() {
                    $('#ubicacion_temp').html('<option value="">Error al cargar</option>');
                }
            });
        } else {
            $('#ubicacion_temp').html('<option value="">Primero seleccione sucursal</option>');
            $('#ubicacion_temp').prop('disabled', true);
        }
    });
});

// Variables
let cont = 0;
let subtotal = [];
let sumas = 0;
let iva = 0;
let total = 0;

// Constantes
const impuesto = 12; // 12% IVA

function cancelarCompra() {
    // Eliminar tbody
    $('#tabla_detalle tbody').html(`
        <tr>
            <td colspan="7" class="text-center text-muted">
                <i class="fas fa-inbox fa-2x"></i>
                <p>No hay productos agregados</p>
            </td>
        </tr>
    `);

    // Reiniciar variables
    cont = 0;
    subtotal = [];
    sumas = 0;
    iva = 0;
    total = 0;

    // Actualizar campos
    $('#sumas').html('0.00');
    $('#iva').html('0.00');
    $('#total').html('0.00');
    $('#impuesto').val('0.00');
    $('#inputTotal').val('0');

    limpiarCampos();
    disableButtons();
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

function agregarProducto() {
    // Obtener valores
    let idProducto = $('#producto_id').val();
    let nameProducto = $('#producto_id option:selected').data('nombre');
    let codigoProducto = $('#producto_id option:selected').data('codigo');
    let cantidad = $('#cantidad').val();
    let precioCompra = $('#precio_compra').val();
    let precioVenta = $('#precio_venta').val();
    let ubicacionId = $('#ubicacion_temp').val();
    let ubicacionNombre = $('#ubicacion_temp option:selected').text();

    // Validaciones
    if (!idProducto || !nameProducto || !cantidad || !precioCompra || !precioVenta) {
        showModal('Le faltan campos por llenar', 'error');
        return;
    }

    if (parseInt(cantidad) <= 0 || cantidad % 1 != 0) {
        showModal('La cantidad debe ser un número entero positivo', 'error');
        return;
    }

    if (parseFloat(precioCompra) <= 0) {
        showModal('El precio de compra debe ser mayor a 0', 'error');
        return;
    }

    if (parseFloat(precioVenta) <= 0) {
        showModal('El precio de venta debe ser mayor a 0', 'error');
        return;
    }

    if (parseFloat(precioVenta) <= parseFloat(precioCompra)) {
        showModal('El precio de venta debe ser mayor al precio de compra', 'warning');
        return;
    }

    // Verificar si ya existe el producto en la tabla
    let existe = false;
    $('#tabla_detalle tbody tr').each(function() {
        let id = $(this).find('input[name="arrayidproducto[]"]').val();
        if (id == idProducto) {
            existe = true;
            showModal('El producto ya fue agregado', 'warning');
            return false;
        }
    });

    if (existe) return;

    // Calcular subtotal
    subtotal[cont] = round(cantidad * precioCompra);
    sumas += subtotal[cont];
    iva = round(sumas / 100 * impuesto);
    total = round(sumas);

    // Eliminar fila vacía si existe
    if ($('#tabla_detalle tbody tr td').attr('colspan') == '7') {
        $('#tabla_detalle tbody').empty();
    }

    // Crear la fila
    let fila = '<tr id="fila' + cont + '">' +
        '<td>' + (cont + 1) + '</td>' +
        '<td>' +
            '<input type="hidden" name="arrayidproducto[]" value="' + idProducto + '">' +
            '<input type="hidden" name="arrayubicacion[]" value="' + (ubicacionId || '') + '">' +
            '<strong>' + codigoProducto + '</strong><br>' +
            '<small class="text-muted">' + nameProducto + '</small>' +
            (ubicacionId ? '<br><span class="badge bg-info">' + ubicacionNombre + '</span>' : '') +
        '</td>' +
        '<td>' +
            '<input type="hidden" name="arraycantidad[]" value="' + cantidad + '">' +
            '<span class="badge bg-primary">' + cantidad + '</span>' +
        '</td>' +
        '<td>' +
            '<input type="hidden" name="arraypreciocompra[]" value="' + precioCompra + '">' +
            'Q ' + parseFloat(precioCompra).toFixed(2) +
        '</td>' +
        '<td>' +
            '<input type="hidden" name="arrayprecioventa[]" value="' + precioVenta + '">' +
            'Q ' + parseFloat(precioVenta).toFixed(2) +
        '</td>' +
        '<td><strong>Q ' + subtotal[cont].toFixed(2) + '</strong></td>' +
        '<td>' +
            '<button class="btn btn-danger btn-sm" type="button" onClick="eliminarProducto(' + cont + ')">' +
                '<i class="fa-solid fa-trash"></i>' +
            '</button>' +
        '</td>' +
    '</tr>';

    // Agregar fila
    $('#tabla_detalle tbody').append(fila);

    // Limpiar campos
    limpiarCampos();
    cont++;
    disableButtons();

    // Mostrar campos calculados
    $('#sumas').html(sumas.toFixed(2));
    $('#iva').html(iva.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#impuesto').val(iva.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    showModal('Producto agregado correctamente', 'success');
}

function eliminarProducto(indice) {
    // Calcular valores
    sumas -= round(subtotal[indice]);
    iva = round(sumas / 100 * impuesto);
    total = round(sumas);

    // Mostrar campos calculados
    $('#sumas').html(sumas.toFixed(2));
    $('#iva').html(iva.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#impuesto').val(iva.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    // Eliminar fila
    $('#fila' + indice).remove();

    // Si no quedan productos, mostrar mensaje
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

function limpiarCampos() {
    $('#producto_id').selectpicker('val', '');
    $('#cantidad').val('');
    $('#precio_compra').val('');
    $('#precio_venta').val('');
    $('#ubicacion_temp').val('');
}

function round(num, decimales = 2) {
    return Math.round((num + Number.EPSILON) * Math.pow(10, decimales)) / Math.pow(10, decimales);
}

function showModal(message, icon = 'error') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: icon,
        title: message
    });
}
</script>
@endpush
