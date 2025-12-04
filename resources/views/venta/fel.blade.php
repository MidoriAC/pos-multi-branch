<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura FEL - {{ $venta->numero_comprobante }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 30px;
        }
        /* Encabezado */
        .header-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .company-info h1 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 10px;
            color: #555;
        }
        .invoice-details {
            text-align: right;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .invoice-details h2 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #e67e22; /* Color naranja para destacar */
            text-transform: uppercase;
        }
        .fel-data {
            margin-top: 5px;
            font-size: 9px;
            color: #7f8c8d;
        }

        /* Marca de agua ANULADO */
        .watermark-anulado {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(220, 53, 69, 0.15); /* Rojo transparente */
            border: 10px solid rgba(220, 53, 69, 0.15);
            padding: 10px 40px;
            z-index: -1000;
            font-weight: bold;
            pointer-events: none;
            text-transform: uppercase;
        }

        /* Sección Cliente */
        .client-section {
            background-color: #f1f2f6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2c3e50;
        }
        .client-section h3 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #2c3e50;
            text-transform: uppercase;
        }

        /* Tabla Productos */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th {
            background-color: #2c3e50;
            color: #fff;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .products-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 10px;
        }
        .products-table tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Totales */
        .totals-container {
            width: 40%;
            float: right;
        }
        .total-row {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .total-row.final {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
            font-size: 12px;
            border-radius: 3px;
        }
        .total-label { display: inline-block; width: 50%; }
        .total-value { display: inline-block; width: 45%; text-align: right; }

        /* Información de Anulación (Visible solo si anulada) */
        .anulacion-alert {
            border: 1px solid #e74c3c;
            background-color: #fdedec;
            color: #c0392b;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 10px;
        }

        /* Footer y Firmas */
        .signatures {
            margin-top: 60px;
            width: 100%;
        }
        .signature-line {
            width: 40%;
            border-top: 1px solid #ccc;
            text-align: center;
            padding-top: 5px;
            display: inline-block;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    @if($venta->estado == 0 || $venta->anulacionFel)
        <div class="watermark-anulado">ANULADO</div>
    @endif

    <div class="container">

        <table class="header-table">
            <tr>
                <td width="60%" class="company-info">
                    {{-- <img src="{{ public_path('img/logo.png') }}" style="height: 50px; margin-bottom: 10px;"> --}}
                    <h1>{{ $venta->sucursal->nombre }}</h1>
                    <p><strong>NIT:</strong> {{ $venta->sucursal->configuracionFel->nit_emisor ?? 'N/A' }}</p>
                    <p>{{ $venta->sucursal->direccion }}</p>
                    <p>Tel: {{ $venta->sucursal->telefono }} | Email: {{ $venta->sucursal->email }}</p>
                </td>
                <td width="40%">
                    <div class="invoice-details">
                        <h2>Factura Electrónica</h2>
                        <div><strong>No.</strong> {{ $venta->numero_comprobante }}</div>
                        @if($venta->serie)
                            <div><strong>Serie:</strong> {{ $venta->serie }}</div>
                        @endif
                        <div><strong>Fecha:</strong> {{ $venta->fecha_hora->format('d/m/Y H:i') }}</div>

                        @if($venta->numero_autorizacion_fel)
                            <div class="fel-data" style="margin-top: 8px; padding-top: 5px; border-top: 1px dashed #ccc;">
                                <strong>UUID:</strong><br>{{ $venta->numero_autorizacion_fel }}
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        @if($venta->estado == 0 || $venta->anulacionFel)
        <div class="anulacion-alert">
            <strong>DOCUMENTO ANULADO</strong><br>
            @if($venta->anulacionFel)
                Fecha de Anulación: {{ $venta->anulacionFel->fecha_anulacion->format('d/m/Y H:i') }}<br>
                Motivo: {{ $venta->anulacionFel->motivo }}
            @else
                Este documento ha sido cancelado internamente.
            @endif
        </div>
        @endif

        <div class="client-section">
            <table width="100%">
                <tr>
                    <td width="10%"><h3>Cliente:</h3></td>
                    <td width="50%">{{ $venta->cliente->persona->razon_social }}</td>
                    <td width="10%"><h3>NIT:</h3></td>
                    <td width="30%">{{ $venta->cliente->persona->nit ?? 'CF' }}</td>
                </tr>
                <tr>
                    <td><h3>Dirección:</h3></td>
                    <td colspan="3">{{ $venta->cliente->persona->direccion ?? 'Ciudad' }}</td>
                </tr>
            </table>
        </div>

        <table class="products-table">
            <thead>
                <tr>
                    <th width="10%" class="text-center">CANT</th>
                    <th width="50%">DESCRIPCIÓN</th>
                    <th width="20%" class="text-right">PRECIO UNIT.</th>
                    <th width="20%" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->productos as $producto)
                <tr>
                    <td class="text-center">{{ $producto->pivot->cantidad }}</td>
                    <td>
                        {{ $producto->nombre }}
                        @if($producto->pivot->descuento > 0)
                            <br><span style="font-size: 8px; color: #e67e22;">(Desc: Q {{ number_format($producto->pivot->descuento, 2) }})</span>
                        @endif
                    </td>
                    <td class="text-right">Q {{ number_format($producto->pivot->precio_venta, 2) }}</td>
                    <td class="text-right">
                        Q {{ number_format(($producto->pivot->cantidad * $producto->pivot->precio_venta) - $producto->pivot->descuento, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-container">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span class="total-value">Q {{ number_format($venta->total - $venta->impuesto, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">IVA (12%):</span>
                <span class="total-value">Q {{ number_format($venta->impuesto, 2) }}</span>
            </div>
            <div class="total-row final">
                <span class="total-label">TOTAL:</span>
                <span class="total-value">Q {{ number_format($venta->total, 2) }}</span>
            </div>
        </div>

        <div style="clear: both;"></div>

        <div class="signatures">
            <div class="signature-line" style="float: left;">
                Firma Cliente
            </div>
            <div class="signature-line" style="float: right;">
                Firma Emisor
            </div>
        </div>

        <div class="footer" style="clear: both;">
            <p>Sujeto a pagos trimestrales ISR | Régimen FEL</p>
            <p>Certificador: {{ $venta->sucursal->configuracionFel->proveedor_fel ?? 'DIGIFACT' }} | NIT: {{ $venta->sucursal->configuracionFel->nit_emisor }}</p>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
