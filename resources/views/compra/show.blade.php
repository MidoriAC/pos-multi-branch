@extends('layouts.app')

@section('title','Ver Compra')

@push('css')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<style>
    .info-card {
        border-left: 4px solid #0d6efd;
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Detalles de la Compra</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('compras.index')}}">Compras</a></li>
        <li class="breadcrumb-item active">Ver Compra</li>
    </ol>

    <!-- Información General -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-info-circle"></i> Datos Generales de la Compra
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Tipo de Comprobante -->
                <div class="col-md-6">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-file-alt"></i> Tipo de Comprobante:
                        </label>
                        <p class="mb-0 fs-5">{{$compra->comprobante->tipo_comprobante}}</p>
                    </div>
                </div>

                <!-- Número de Comprobante -->
                <div class="col-md-6">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-hashtag"></i> Número de Comprobante:
                        </label>
                        <p class="mb-0 fs-5">{{$compra->numero_comprobante}}</p>
                    </div>
                </div>

                <!-- Proveedor -->
                <div class="col-md-6">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-user-tie"></i> Proveedor:
                        </label>
                        <p class="mb-0 fs-5">{{$compra->proveedore->persona->razon_social}}</p>
                        <small class="text-muted">
                            Tipo: {{ ucfirst($compra->proveedore->persona->tipo_persona) }}
                        </small>
                    </div>
                </div>

                <!-- Sucursal -->
                <div class="col-md-6">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-store"></i> Sucursal:
                        </label>
                        <p class="mb-0 fs-5">{{$compra->sucursal->nombre}}</p>
                    </div>
                </div>

                <!-- Fecha -->
                <div class="col-md-4">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-calendar-day"></i> Fecha:
                        </label>
                        <p class="mb-0 fs-5">{{ \Carbon\Carbon::parse($compra->fecha_hora)->format('d/m/Y') }}</p>
                    </div>
                </div>

                <!-- Hora -->
                <div class="col-md-4">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-clock"></i> Hora:
                        </label>
                        <p class="mb-0 fs-5">{{ \Carbon\Carbon::parse($compra->fecha_hora)->format('H:i:s') }}</p>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="col-md-4">
                    <div class="info-card p-3">
                        <label class="fw-bold text-muted">
                            <i class="fas fa-user"></i> Registrado por:
                        </label>
                        <p class="mb-0 fs-5">{{$compra->usuario->name ?? 'N/A'}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-box"></i> Detalle de Productos
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($compra->productos as $index => $item)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>
                                <strong>{{$item->codigo}}</strong> - {{$item->nombre}}
                                @if($item->marca)
                                <br><small class="text-muted">
                                    Marca: {{$item->marca->caracteristica->nombre}}
                                </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{$item->pivot->cantidad}}</span>
                            </td>
                            <td>Q {{number_format($item->pivot->precio_compra, 2)}}</td>
                            <td>Q {{number_format($item->pivot->precio_venta, 2)}}</td>
                            <td class="td-subtotal">
                                <strong>Q {{number_format(($item->pivot->cantidad) * ($item->pivot->precio_compra), 2)}}</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Subtotal:</th>
                            <th>Q <span id="th-suma">0.00</span></th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">IVA (12%):</th>
                            <th>Q <span id="th-igv">0.00</span></th>
                        </tr>
                        <tr class="table-success">
                            <th colspan="5" class="text-end">TOTAL:</th>
                            <th>Q <span id="th-total">0.00</span></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Botón Volver -->
    <div class="text-center mb-4">
        <a href="{{route('compras.index')}}" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
</div>

<input type="hidden" id="input-impuesto" value="{{$compra->impuesto}}">

@endsection

@push('js')
<script>
$(document).ready(function() {
    calcularValores();
});

function calcularValores() {
    let filasSubtotal = document.getElementsByClassName('td-subtotal');
    let cont = 0;
    let impuesto = parseFloat($('#input-impuesto').val());

    for (let i = 0; i < filasSubtotal.length; i++) {
        let valor = filasSubtotal[i].innerText.replace('Q', '').replace(',', '').trim();
        cont += parseFloat(valor);
    }

    $('#th-suma').html(cont.toFixed(2));
    $('#th-igv').html(impuesto.toFixed(2));
    $('#th-total').html((cont + impuesto).toFixed(2));
}
</script>
@endpush
