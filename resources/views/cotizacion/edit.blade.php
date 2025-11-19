@extends('layouts.app')

@section('title','Editar Cotización')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .required:after {
        content: " *";
        color: red;
    }
    .info-edicion {
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
        <i class="fas fa-edit"></i> Editar Cotización
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('cotizaciones.index')}}">Cotizaciones</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>

    <!-- Información de la cotización -->
    <div class="info-edicion">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice"></i> Editando:
                    <strong>{{$cotizacione->numero_cotizacion}}</strong>
                </h5>
                <small>Sucursal: {{$cotizacione->sucursal->nombre}}</small>
            </div>
            <div class="col-md-3 text-end">
                <span class="badge bg-light text-dark">
                    <i class="fas fa-calendar"></i>
                    Creada: {{$cotizacione->created_at->format('d/m/Y')}}
                </span>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('cotizaciones.update', $cotizacione->id) }}" method="post" id="formCotizacion">
    @csrf
    @method('PUT')

    <div class="container-lg mt-4">
        <div class="row gy-4">

            <!------Detalles de la cotización---->
            <div class="col-xl-8">
                <div class="text-white bg-primary p-1 text-center">
                    <h5 class="mb-0">Detalles de la Cotización</h5>
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
                                    title="Busque un producto aquí">
                                @foreach ($productos as $item)
                                <option value="{{$item->producto_id}}"
                                        data-stock="{{$item->stock_actual}}"
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
                        </div>

                        <!-----Precio de venta---->
                        <div class="col-md-3">
                            <label for="precio_venta" class="form-label required">Precio Unitario:</label>
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
                            <button id="btn_agregar" class="btn btn-primary w-100" type="button">
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
                                        @foreach($cotizacione->productos as $index => $producto)
                                        <tr id="fila{{$index}}">
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
                                            <td>
                                                <button class="btn btn-danger btn-sm" type="button" onClick="eliminarProducto({{$index}})" title="Eliminar producto">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-end">Subtotal:</th>
                                            <th colspan="2">Q <span id="sumas">{{number_format($cotizacione->subtotal, 2)}}</span></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-end">IVA (12%):</th>
                                            <th colspan="2">Q <span id="igv">{{number_format($cotizacione->impuesto, 2)}}</span></th>
                                        </tr>
                                        <tr class="table-primary">
                                            <th colspan="5" class="text-end">TOTAL:</th>
                                            <th colspan="2">
                                                <input type="hidden" name="total" value="{{$cotizacione->total}}" id="inputTotal">
                                                Q <span id="total">{{number_format($cotizacione->total, 2)}}</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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

                        <!--Cliente-->
                        <div class="col-12">
                            <label for="cliente_id" class="form-label required">Cliente:</label>
                            <select name="cliente_id" id="cliente_id"
                                    class="form-control selectpicker show-tick @error('cliente_id') is-invalid @enderror"
                                    data-live-search="true" title="Seleccione cliente" data-size='3' required>
                                @foreach ($clientes as $item)
                                <option value="{{$item->id}}"
                                        @if($cotizacione->cliente_id == $item->id) selected @endif>
                                    {{$item->persona->razon_social}}
                                </option>
                                @endforeach
                            </select>
                            @error('cliente_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Número cotización-->
                        <div class="col-12">
                            <label class="form-label">Número:</label>
                            <input readonly type="text" class="form-control border-success bg-light fw-bold"
                                   value="{{$cotizacione->numero_cotizacion}}">
                        </div>

                        <!--Días de validez-->
                        <div class="col-6">
                            <label for="validez_dias" class="form-label required">Días Validez:</label>
                            <input type="number" name="validez_dias" id="validez_dias"
                                   class="form-control @error('validez_dias') is-invalid @enderror"
                                   value="{{$cotizacione->validez_dias}}"
                                   min="1" max="365" required>
                            @error('validez_dias')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Fecha vencimiento (calculada)-->
                        <div class="col-6">
                            <label class="form-label">Vence:</label>
                            <input readonly type="text" id="fecha_vencimiento"
                                   class="form-control border-success bg-light">
                        </div>

                        <!--Observaciones-->
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones:</label>
                            <textarea name="observaciones" id="observaciones"
                                      class="form-control @error('observaciones') is-invalid @enderror"
                                      rows="3"
                                      maxlength="1000"
                                      placeholder="Condiciones, notas especiales...">{{$cotizacione->observaciones}}</textarea>
                            <small class="text-muted">
                                <span id="contador_obs">{{strlen($cotizacione->observaciones ?? '')}}</span>/1000 caracteres
                            </small>
                            @error('observaciones')
                            <small class="text-danger d-block">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!--Subtotal-->
                        <div class="col-6">
                            <label for="subtotal" class="form-label">Subtotal:</label>
                            <input readonly type="text" name="subtotal" id="subtotal"
                                   class="form-control border-success"
                                   value="{{$cotizacione->subtotal}}">
                        </div>

                        <!--Impuesto-->
                        <div class="col-6">
                            <label for="impuesto" class="form-label">IVA:</label>
                            <input readonly type="text" name="impuesto" id="impuesto"
                                   class="form-control border-success"
                                   value="{{$cotizacione->impuesto}}">
                        </div>

                        <!--Fecha-->
                        <div class="col-12">
                            <label for="fecha" class="form-label">Fecha:</label>
                            <input readonly type="date" name="fecha" id="fecha"
                                   class="form-control border-success"
                                   value="{{$cotizacione->fecha_hora->format('Y-m-d')}}">
                            <input type="hidden" name="fecha_hora" value="{{$cotizacione->fecha_hora}}">
                        </div>

                        <!--Información-->
                        <div class="col-12">
                            <div class="alert alert-warning mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Atención:</strong>
                                Al guardar se actualizará completamente la cotización.
                            </div>
                        </div>

                        <!--Botones-->
                        <div class="col-6">
                            <a href="{{ route('cotizaciones.show', $cotizacione->id) }}"
                               class="btn btn-secondary btn-lg w-100">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-success btn-lg w-100" id="guardar">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>

                    </div>
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
    calcularFechaVencimiento();

    $('#validez_dias').on('input', function() {
        calcularFechaVencimiento();
    });

    $('#observaciones').on('input', function() {
        const length = $(this).val().length;
        $('#contador_obs').text(length);
    });

    $('#producto_id').change(function() {
        mostrarValores();
    });

    $('#btn_agregar').click(function() {
        agregarProducto();
    });

    $('#formCotizacion').submit(function(e) {
        if (total === 0) {
            e.preventDefault();
            showModal('Debe agregar al menos un producto a la cotización', 'error');
            return false;
        }

        return confirm('¿Está seguro de actualizar esta cotización?');
    });
});

let cont = {{$cotizacione->productos->count()}};
let subtotal = [];
let sumas = {{$cotizacione->subtotal}};
let igv = {{$cotizacione->impuesto}};
let total = {{$cotizacione->total}};
const impuesto = 12;

@foreach($cotizacione->productos as $index => $producto)
    subtotal[{{$index}}] = {{$producto->pivot->subtotal}};
@endforeach

function calcularFechaVencimiento() {
    const diasValidez = parseInt($('#validez_dias').val()) || 0;
    const fechaActual = new Date('{{$cotizacione->fecha_hora->format('Y-m-d')}}');
    fechaActual.setDate(fechaActual.getDate() + diasValidez);

    const dia = String(fechaActual.getDate()).padStart(2, '0');
    const mes = String(fechaActual.getMonth() + 1).padStart(2, '0');
    const anio = fechaActual.getFullYear();

    $('#fecha_vencimiento').val(`${dia}/${mes}/${anio}`);
}

function mostrarValores() {
    const selected = $('#producto_id option:selected');
    const stock = selected.data('stock');
    const precio = selected.data('precio');

    $('#stock').val(stock);
    $('#precio_venta').val(precio);
}

function agregarProducto() {
    const selected = $('#producto_id option:selected');
    const idProducto = selected.val();
    const nombreProducto = selected.data('nombre');
    const codigoProducto = selected.data('codigo');
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

    let existe = false;
    $('#tabla_detalle tbody tr').each(function() {
        const id = $(this).find('input[name="arrayidproducto[]"]').val();
        if (id == idProducto) {
            existe = true;
            return false;
        }
    });

    if (existe) {
        showModal('El producto ya fue agregado', 'warning');
        return;
    }

    subtotal[cont] = round((cantidad * precioVenta) - descuento);
    sumas += subtotal[cont];
    igv = round(sumas * (impuesto / 100));
    total = round(sumas + igv);

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
            '<button class="btn btn-danger btn-sm" type="button" onClick="eliminarProducto(' + cont + ')">' +
                '<i class="fas fa-trash"></i>' +
            '</button>' +
        '</td>' +
    '</tr>';

    $('#tabla_detalle tbody').append(fila);
    limpiarCampos();
    cont++;

    $('#sumas').html(sumas.toFixed(2));
    $('#igv').html(igv.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#subtotal').val(sumas.toFixed(2));
    $('#impuesto').val(igv.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    showModal('Producto agregado', 'success');
}

function eliminarProducto(indice) {
    sumas -= round(subtotal[indice]);
    igv = round(sumas * (impuesto / 100));
    total = round(sumas + igv);

    $('#sumas').html(sumas.toFixed(2));
    $('#igv').html(igv.toFixed(2));
    $('#total').html(total.toFixed(2));
    $('#subtotal').val(sumas.toFixed(2));
    $('#impuesto').val(igv.toFixed(2));
    $('#inputTotal').val(total.toFixed(2));

    $('#fila' + indice).remove();

    $('#tabla_detalle tbody tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
    });
}

function limpiarCampos() {
    $('#producto_id').selectpicker('val', '');
    $('#cantidad').val('');
    $('#precio_venta').val('');
    $('#descuento').val('0');
    $('#stock').val('0');
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
