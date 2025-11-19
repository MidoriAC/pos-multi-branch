<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura FEL - {{ $venta->numero_comprobante }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 2px solid #000;
            padding: 10px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: center;
            border-left: 2px solid #000;
            padding-left: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .header-right h2 {
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            padding: 5px;
            margin-bottom: 5px;
        }
        .fel-box {
            background-color: #e8f5e9;
            padding: 8px;
            border: 1px solid #4CAF50;
            margin-top: 8px;
        }
        .fel-box .fel-label {
            font-size: 9px;
            font-weight: bold;
            color: #2e7d32;
        }
        .fel-box .fel-number {
            font-size: 8px;
            word-wrap: break-word;
            margin-top: 3px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #000;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            padding: 8px;
            border-right: 1px solid #000;
        }
        .info-box:last-child {
            border-right: none;
        }
        .info-box h3 {
            font-size: 11px;
            margin-bottom: 5px;
            background-color: #f5f5f5;
            padding: 3px;
            border-bottom: 1px solid #000;
        }
        .info-item {
            margin-bottom: 3px;
            font-size: 9px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            border: 1px solid #000;
        }
        .productos-table thead {
            background-color: #333;
            color: white;
        }
        .productos-table th,
        .productos-table td {
            padding: 5px;
            text-align: left;
            border: 1px solid #000;
            font-size: 9px;
        }
        .productos-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .totales-box {
            width: 45%;
            float: right;
            border: 2px solid #000;
            padding: 8px;
            margin-top: 10px;
        }
        .totales-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            font-size: 10px;
        }
        .totales-label {
            display: table-cell;
            width: 50%;
            text-align: right;
            padding-right: 8px;
            font-weight: bold;
        }
        .totales-value {
            display: table-cell;
            width: 50%;
            text-align: right;
            font-weight: bold;
        }
        .total-final {
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            padding: 8px;
            margin-top: 5px;
        }
        .firma-box {
            clear: both;
            margin-top: 40px;
            padding-top: 30px;
        }
        .firma {
            width: 45%;
            display: inline-block;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 20px;
            font-size: 9px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            border-top: 1px solid #000;
            padding-top: 8px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(255, 0, 0, 0.08);
            z-index: -1;
            font-weight: bold;
        }
        .anulado-box {
            border: 3px solid #dc3545;
            background-color: #f8d7da;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }
        @page {
            margin: 0.8cm;
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
            <div class="header-left">
                <h1>{{ $venta->sucursal->nombre }}</h1>
                <div style="margin-top: 5px;">
                    <div><strong>NIT:</strong> {{ $venta->sucursal->configuracionFel->nit_emisor ?? 'N/A' }}</div>
                    <div><strong>Dirección:</strong> {{ $venta->sucursal->direccion }}</div>
                    <div><strong>Teléfono:</strong> {{ $venta->sucursal->telefono }}</div>
                    <div><strong>Email:</strong> {{ $venta->sucursal->email }}</div>
                </div>
            </div>
            <div class="header-right">
                <h2>FACTURA ELECTRÓNICA</h2>
                <div style="font-size: 11px; font-weight: bold;">
                    N° {{ $venta->numero_comprobante }}
                </div>
                @if($venta->serie)
                <div style="font-size: 10px; margin-top: 3px;">
                    Serie: <strong>{{ $venta->serie }}</strong>
                </div>
                @endif

                @if($venta->numero_autorizacion_fel)
                <div class="fel-box">
                    <div class="fel-label">AUTORIZACIÓN FEL:</div>
                    <div class="fel-number">{{ $venta->numero_autorizacion_fel }}</div>
                    @if($venta->fecha_certificacion_fel)
                    <div style="font-size: 8px; margin-top: 3px;">
                        Certificado: {{ $venta->fecha_certificacion_fel->format('d/m/Y H:i:s') }}
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($venta->anulacionFel)
        <div class="anulado-box">
            <h2 style="color: #dc3545; font-size: 16px; margin-bottom: 5px;">FACTURA ANULADA</h2>
            <div><strong>Fecha:</strong> {{ $venta->anulacionFel->fecha_anulacion->format('d/m/Y H:i:s') }}</div>
            <div><strong>Motivo:</strong> {{ $venta->anulacionFel->motivo }}</div>
            @if($venta->anulacionFel->numero_autorizacion_anulacion)
            <div style="font-size: 8px; margin-top: 5px;">
                <strong>Autorización Anulación:</strong> {{ $venta->anulacionFel->numero_autorizacion_anulacion }}
            </div>
            @endif
        </div>
        @endif

        <!-- Información -->
        <div class="info-section">
            <div class="info-box">
                <h3>DATOS DE LA FACTURA</h3>
                <div class="info-item">
                    <span class="info-label">Fecha de Emisión:</span>
                    {{ $venta->fecha_hora->format('d/m/Y H:i:s') }}
                </div>
                <div class="info-item">
                    <span class="info-label">Vendedor:</span>
                    {{ $venta->user->name }}
                </div>
                <div class="info-item">
                    <span class="info-label">Tipo de Pago:</span>
                    Contado
                </div>
            </div>

            <div class="info-box">
                <h3>DATOS DEL RECEPTOR</h3>
                <div class="info-item">
                    <span class="info-label">Nombre/Razón Social:</span>
                    <strong>{{ $venta->cliente->persona->razon_social }}</strong>
                </div>
                <div class="info-item">
                    <span class="info-label">NIT:</span>
                    {{ $venta->cliente->persona->nit }}
                </div>
                <div class="info-item">
                    <span class="info-label">Dirección:</span>
                    {{ $venta->cliente->persona->direccion }}
                </div>
                @if($venta->cliente->persona->email)
                <div class="info-item">
                    <span class="info-label">Email:</span>
                    {{ $venta->cliente->persona->email }}
                </div>
                @endif
            </div>
        </div>

        <!-- Tabla de Productos -->
        <table class="productos-table">
            <thead>
                <tr>
                    <th width="7%">Cant.</th>
                    <th width="8%">U.M.</th>
                    <th width="10%">Código</th>
                    <th width="40%">Descripción</th>
                    <th width="13%" class="text-right">Precio Unit.</th>
                    <th width="9%" class="text-right">Desc.</th>
                    <th width="13%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->productos as $producto)
                <tr>
                    <td class="text-center">{{ $producto->pivot->cantidad }}</td>
                    <td class="text-center">
                        {{ $producto->unidadMedida->abreviatura ?? 'UNI' }}
                    </td>
                    <td>{{ $producto->codigo }}</td>
                    <td>
                        <strong>{{ $producto->nombre }}</strong>
                        @if($producto->marca)
                            <br><span style="font-size: 8px;">{{ $producto->marca->caracteristica->nombre }}</span>
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
        <div class="totales-box">
            <div class="totales-row">
                <div class="totales-label">SUBTOTAL:</div>
                <div class="totales-value">Q {{ number_format($venta->total - $venta->impuesto, 2) }}</div>
            </div>
            <div class="totales-row">
                <div class="totales-label">IVA (12%):</div>
                <div class="totales-value">Q {{ number_format($venta->impuesto, 2) }}</div>
            </div>
            <div class="totales-row total-final">
                <div class="totales-label">TOTAL:</div>
                <div class="totales-value">Q {{ number_format($venta->total, 2) }}</div>
            </div>
        </div>

        <!-- Firmas -->
        <div class="firma-box">
            <div class="firma" style="float: left;">
                RECIBÍ CONFORME<br>
                <strong>Cliente</strong>
            </div>
            <div class="firma" style="float: right;">
                ENTREGUÉ CONFORME<br>
                <strong>Vendedor</strong>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>FACTURA ELECTRÓNICA CERTIFICADA POR SAT</strong></p>
            <p>Sujeto a pagos trimestrales ISR | Régimen FEL</p>
            <p>Documento generado electrónicamente el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p style="margin-top: 5px; font-size: 7px;">
                Para verificar la autenticidad de este documento, ingrese a:<br>
                https://portal.sat.gob.gt/portal/ con el número de autorización
            </p>
        </div>
    </div>
</body>
</html>
