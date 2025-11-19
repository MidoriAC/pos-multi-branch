<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{$cotizacione->numero_cotizacion}}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            padding: 20px;
        }

        .header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .logo-section {
            display: table-cell;
            width: 30%;
            vertical-align: top;
        }

        .company-info {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            padding-left: 15px;
        }

        .quote-info {
            display: table-cell;
            width: 30%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 10px;
            line-height: 1.5;
        }

        .quote-number {
            background-color: #667eea;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .quote-status {
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
            display: inline-block;
        }

        .status-pendiente { background-color: #ffc107; color: #000; }
        .status-convertida { background-color: #28a745; color: #fff; }
        .status-vencida { background-color: #dc3545; color: #fff; }
        .status-cancelada { background-color: #6c757d; color: #fff; }

        .client-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .client-section h3 {
            color: #667eea;
            font-size: 12px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .client-details {
            display: table;
            width: 100%;
        }

        .client-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .detail-row {
            margin-bottom: 5px;
        }

        .detail-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .products-table thead {
            background-color: #667eea;
            color: white;
        }

        .products-table th {
            padding: 10px 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        .products-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }

        .products-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            margin-top: 20px;
            float: right;
            width: 300px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .totals-table tr:last-child td {
            border-bottom: 2px solid #667eea;
            font-weight: bold;
            font-size: 12px;
            background-color: #f8f9fa;
        }

        .observations {
            clear: both;
            margin-top: 30px;
            padding: 15px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 3px;
        }

        .observations h4 {
            color: #856404;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .observations p {
            font-size: 10px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .validity-info {
            background-color: #e7f1ff;
            border: 1px solid #667eea;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }

        .validity-info strong {
            color: #667eea;
            font-size: 12px;
        }

        .product-code {
            color: #667eea;
            font-weight: bold;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(220, 53, 69, 0.1);
            font-weight: bold;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="container">

        @if($cotizacione->estado === 'CANCELADA')
        <div class="watermark">CANCELADA</div>
        @elseif($cotizacione->estado === 'VENCIDA')
        <div class="watermark">VENCIDA</div>
        @elseif($cotizacione->estado === 'CONVERTIDA')
        <div class="watermark">CONVERTIDA</div>
        @endif

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="logo-section">
                    {{-- Aquí puedes agregar tu logo --}}
                    <div style="width: 80px; height: 80px; border: 2px solid #667eea; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #667eea; font-weight: bold; font-size: 10px; text-align: center;">
                        LOGO<br>EMPRESA
                    </div>
                </div>

                <div class="company-info">
                    <div class="company-name">{{$cotizacione->sucursal->nombre}}</div>
                    <div class="company-details">
                        <strong>Dirección:</strong> {{$cotizacione->sucursal->direccion}}<br>
                        @if($cotizacione->sucursal->telefono)
                        <strong>Teléfono:</strong> {{$cotizacione->sucursal->telefono}}<br>
                        @endif
                        @if($cotizacione->sucursal->email)
                        <strong>Email:</strong> {{$cotizacione->sucursal->email}}<br>
                        @endif
                        @if($cotizacione->sucursal->nit_establecimiento)
                        <strong>NIT:</strong> {{$cotizacione->sucursal->nit_establecimiento}}
                        @endif
                    </div>
                </div>

                <div class="quote-info">
                    <div class="quote-number">
                        COTIZACIÓN<br>
                        {{$cotizacione->numero_cotizacion}}
                    </div>
                    <div style="margin-top: 5px; font-size: 9px;">
                        <strong>Fecha:</strong> {{$cotizacione->fecha_hora->format('d/m/Y')}}<br>
                        <strong>Hora:</strong> {{$cotizacione->fecha_hora->format('H:i')}}
                    </div>
                    <div style="margin-top: 5px;">
                        @php
                            $statusClass = 'status-' . strtolower($cotizacione->estado);
                        @endphp
                        <span class="quote-status {{$statusClass}}">
                            {{$cotizacione->obtenerEstadoTexto()}}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-section">
            <h3>INFORMACIÓN DEL CLIENTE</h3>
            <div class="client-details">
                <div class="client-col">
                    <div class="detail-row">
                        <span class="detail-label">Cliente:</span>
                        <span>{{$cotizacione->cliente->persona->razon_social}}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">NIT:</span>
                        <span>{{$cotizacione->cliente->persona->nit ?? 'CF'}}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dirección:</span>
                        <span>{{$cotizacione->cliente->persona->direccion}}</span>
                    </div>
                </div>
                <div class="client-col">
                    @if($cotizacione->cliente->persona->telefono)
                    <div class="detail-row">
                        <span class="detail-label">Teléfono:</span>
                        <span>{{$cotizacione->cliente->persona->telefono}}</span>
                    </div>
                    @endif
                    @if($cotizacione->cliente->persona->email)
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span>{{$cotizacione->cliente->persona->email}}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Vendedor:</span>
                        <span>{{$cotizacione->user->name}}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <table class="products-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Código</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 10%;" class="text-center">Cantidad</th>
                    <th style="width: 14%;" class="text-right">Precio Unit.</th>
                    <th style="width: 14%;" class="text-right">Descuento</th>
                    <th style="width: 14%;" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cotizacione->productos as $producto)
                <tr>
                    <td class="product-code">{{$producto->codigo}}</td>
                    <td>
                        <strong>{{$producto->nombre}}</strong>
                        @if($producto->marca || $producto->presentacione)
                        <br>
                        <span style="font-size: 9px; color: #666;">
                            {{$producto->marca->caracteristica->nombre ?? ''}}
                            {{$producto->presentacione->caracteristica->nombre ?? ''}}
                        </span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{$producto->pivot->cantidad}}
                        {{$producto->unidadMedida->abreviatura ?? 'Unid'}}
                    </td>
                    <td class="text-right">Q {{number_format($producto->pivot->precio_unitario, 2)}}</td>
                    <td class="text-right">
                        @if($producto->pivot->descuento > 0)
                        <span style="color: #dc3545;">-Q {{number_format($producto->pivot->descuento, 2)}}</span>
                        @else
                        Q 0.00
                        @endif
                    </td>
                    <td class="text-right"><strong>Q {{number_format($producto->pivot->subtotal, 2)}}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">Q {{number_format($cotizacione->subtotal, 2)}}</td>
                </tr>
                @if($cotizacione->obtenerDescuentoTotal() > 0)
                <tr>
                    <td>Descuentos:</td>
                    <td class="text-right" style="color: #dc3545;">-Q {{number_format($cotizacione->obtenerDescuentoTotal(), 2)}}</td>
                </tr>
                @endif
                <tr>
                    <td>IVA (12%):</td>
                    <td class="text-right">Q {{number_format($cotizacione->impuesto, 2)}}</td>
                </tr>
                <tr>
                    <td>TOTAL:</td>
                    <td class="text-right" style="color: #667eea; font-size: 14px;">Q {{number_format($cotizacione->total, 2)}}</td>
                </tr>
            </table>
        </div>

        <!-- Validity Information -->
        @if($cotizacione->estado === 'PENDIENTE')
        <div class="validity-info">
            <strong>Validez de esta cotización: {{$cotizacione->validez_dias}} días</strong><br>
            <span style="font-size: 10px;">
                Válida hasta: {{$cotizacione->fecha_vencimiento->format('d/m/Y')}}
                @if($cotizacione->diasRestantes() > 0)
                    ({{$cotizacione->diasRestantes()}} días restantes)
                @endif
            </span>
        </div>
        @endif

        <!-- Observations -->
        @if($cotizacione->observaciones)
        <div class="observations">
            <h4>OBSERVACIONES / CONDICIONES:</h4>
            <p>{{$cotizacione->observaciones}}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Resumen:</strong>
                {{$cotizacione->productos->count()}} productos |
                {{$cotizacione->obtenerCantidadProductos()}} unidades totales
            </p>
            <p style="margin-top: 10px;">
                Este documento es una cotización y no constituye un comprobante de venta.<br>
                Los precios están sujetos a cambios sin previo aviso.
            </p>
            <p style="margin-top: 10px; font-size: 8px;">
                Impreso el: {{now()->format('d/m/Y H:i:s')}} |
                Sistema de Gestión - {{config('app.name')}}
            </p>
        </div>

    </div>
</body>
</html>
