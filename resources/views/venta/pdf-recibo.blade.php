<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - {{ $venta->numero_comprobante }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 0px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .header h2 {
            font-size: 18px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        .header-info {
            font-size: 10px;
            color: #7f8c8d;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        .info-box h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 3px;
        }
        .info-item {
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .productos-table thead {
            background-color: #34495e;
            color: white;
        }
        .productos-table th,
        .productos-table td {
            padding: 8px;
            text-align: left;
            border: 1px solid #bdc3c7;
        }
        .productos-table th {
            font-size: 10px;
            font-weight: bold;
        }
        .productos-table tbody tr:nth-child(even) {
            background-color: #ecf0f1;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .totales {
            width: 100%;
            margin-top: 20px;
        }
        .totales-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .totales-label {
            display: table-cell;
            width: 70%;
            text-align: right;
            padding-right: 10px;
            font-weight: bold;
        }
        .totales-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: bold;
        }
        .total-final {
            font-size: 16px;
            background-color: #ecf0f1;
            padding: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(255, 0, 0, 0.1);
            z-index: -1;
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    @if($venta->anulacionFel)
    <div class="watermark">ANULADO</div>
    @endif

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $venta->sucursal->nombre }}</h1>
            <h2>RECIBO DE VENTA</h2>
            <div class="header-info">
                <div>{{ $venta->sucursal->direccion }}</div>
                <div>Tel: {{ $venta->sucursal->telefono }} | Email: {{ $venta->sucursal->email }}</div>
            </div>
        </div>

        <!-- Información del Recibo -->
        <div class="info-section">
            <div class="info-box">
                <h3>DATOS DEL RECIBO</h3>
                <div class="info-item">
                    <span class="info-label">N° Recibo:</span>
                    <strong>{{ $venta->numero_comprobante }}</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha:</span>
                    {{ $venta->fecha_hora->format('d/m/Y H:i') }}
                </div>
                <div class="info-item">
                    <span class="info-label">Vendedor:</span>
                    {{ $venta->user->name }}
                </div>
                @if($venta->comprobante)
                <div class="info-item">
                    <span class="info-label">Tipo:</span>
                    {{ $venta->comprobante->tipo_comprobante }}
                </div>
                @endif
            </div>

            <div class="info-box">
                <h3>DATOS DEL CLIENTE</h3>
                <div class="info-item">
                    <span class="info-label">Nombre/Razón Social:</span>
                    <strong>{{ $venta->cliente->persona->razon_social }}</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">NIT:</span>
                    {{ $venta->cliente->persona->nit }}
                </div>
                <div class="info-item">
                    <span class="info-label">Documento:</span>
                    {{ $venta->cliente->persona->numero_documento }}
                </div>
                 <div class="info-item">
                    <span class="info-label">Dirección:</span>
                    {{ $venta->cliente->persona->direccion }}
                </div>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <table class="productos-table">
            <thead>
                <tr>
                    <th width="8%">Cant.</th>
                    <th width="10%">Código</th>
                    <th width="42%">Descripción</th>
                    <th width="15%" class="text-right">Precio Unit.</th>
                    <th width="10%" class="text-right">Descuento</th>
                    <th width="15%" class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->productos as $producto)
                <tr>
                    <td class="text-center">{{ $producto->pivot->cantidad }}</td>
                    <td>{{ $producto->codigo }}</td>
                    <td>
                        <strong>{{ $producto->nombre }}</strong>
                        @if($producto->marca)
                            <br><small>Marca: {{ $producto->marca->caracteristica->nombre }}</small>
                        @endif
                    </td>
                    <td class="text-right">Q {{ number_format($producto->pivot->precio_venta, 2) }}</td>
                    <td class="text-right">
                        @if($producto->pivot->descuento > 0)
                            Q {{ number_format($producto->pivot->descuento, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        Q {{ number_format(($producto->pivot->cantidad * $producto->pivot->precio_venta) - $producto->pivot->descuento, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="totales">
            <div class="totales-row">
                <div class="totales-label">SUBTOTAL:</div>
                <div class="totales-value">Q {{ number_format($venta->total - $venta->impuesto, 2) }}</div>
            </div>
            <div class="totales-row">
                <div class="totales-label">IVA (12%):</div>
                <div class="totales-value">Q {{ number_format($venta->impuesto, 2) }}</div>
            </div>
            <div class="totales-row total-final">
                <div class="totales-label" style="font-size: 16px;">TOTAL:</div>
                <div class="totales-value" style="font-size: 18px;">Q {{ number_format($venta->total, 2) }}</div>
            </div>
        </div>

        @if($venta->anulacionFel)
        <div style="margin-top: 30px; padding: 15px; background-color: #f8d7da; border: 2px solid #dc3545; border-radius: 5px;">
            <h3 style="color: #dc3545; text-align: center; margin-bottom: 10px;">RECIBO ANULADO</h3>
            <div><strong>Fecha de Anulación:</strong> {{ $venta->anulacionFel->fecha_anulacion->format('d/m/Y H:i:s') }}</div>
            <div><strong>Motivo:</strong> {{ $venta->anulacionFel->motivo }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>¡Gracias por su compra!</strong></p>
            <p>Este documento es un recibo interno y no constituye factura fiscal</p>
            <p>Documento generado electrónicamente el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
