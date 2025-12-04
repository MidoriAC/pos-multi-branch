<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $venta->numero_comprobante }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace; /* Monospace se lee mejor en térmicas */
            font-size: 12px;
            width: 72mm; /* Ajustado para márgenes seguros */
            margin: 2mm auto;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 { font-size: 14px; margin-bottom: 5px; }

        .separator {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-group { margin-bottom: 5px; }
        .label { display: inline-block; width: 60px; font-weight: bold;}

        /* Estilo para ANULADO */
        .void-watermark {
            border: 2px dashed #000;
            padding: 5px;
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            font-size: 14px;
            background: #eee; /* En térmica se verá grisáceo */
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 11px;
        }
        .products-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .total-section {
            margin-top: 10px;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }
        .qr-img {
            display: block;
            margin: 10px auto;
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>

    @if($venta->estado == 0 || $venta->anulacionFel)
        <div class="void-watermark">
            *** ANULADO ***<br>
            <span style="font-size: 10px; font-weight: normal;">
                {{ $venta->anulacionFel ? $venta->anulacionFel->fecha_anulacion->format('d/m/Y') : date('d/m/Y') }}
            </span>
        </div>
    @endif

    <div class="header">
        <h2>{{ $venta->sucursal->nombre }}</h2>
        <div>{{ $venta->sucursal->direccion }}</div>
        <div>NIT: {{ $venta->sucursal->configuracionFel->nit_emisor ?? 'CF' }}</div>
    </div>

    <div class="separator"></div>

    <div class="info-group text-center">
        @if($venta->tipo_factura === 'FACT')
            <strong>FACTURA ELECTRÓNICA</strong>
        @else
            <strong>RECIBO DE VENTA</strong>
        @endif
        <br>
        No: {{ $venta->numero_comprobante }}
    </div>

    <div class="info-group">
        Fecha: {{ $venta->fecha_hora->format('d/m/Y H:i') }}<br>
        Cliente: {{ substr($venta->cliente->persona->razon_social, 0, 25) }}<br>
        NIT: {{ $venta->cliente->persona->nit ?? 'CF' }}
    </div>

    @if($venta->numero_autorizacion_fel)
        <div class="info-group" style="font-size: 10px;">
            <strong>UUID:</strong> {{ $venta->numero_autorizacion_fel }}<br>
            <strong>Serie:</strong> {{ $venta->serie }}
        </div>
    @endif

    <div class="separator"></div>

    <table class="products-table">
        <thead>
            <tr>
                <th width="50%">DESC</th>
                <th width="15%" class="text-center">CANT</th>
                <th width="35%" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->productos as $producto)
                <tr>
                    <td colspan="3">
                        {{ $producto->nombre }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 5px; color: #333; font-size: 10px;">
                        Q {{ number_format($producto->pivot->precio_venta, 2) }}
                    </td>
                    <td class="text-center">{{ $producto->pivot->cantidad }}</td>
                    <td class="text-right bold">
                        Q {{ number_format(($producto->pivot->cantidad * $producto->pivot->precio_venta) - $producto->pivot->descuento, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separator"></div>

    <div class="total-section">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>Q {{ number_format($venta->total - $venta->impuesto, 2) }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin-top: 5px;">
            <span>TOTAL:</span>
            <span>Q {{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    @if($venta->tipo_factura === 'FACT' && $venta->numero_autorizacion_fel)
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://reportefel.sat.gob.gt/chat/verificador_qr.aspx?nit_emisor={{$venta->sucursal->configuracionFel->nit_emisor}}&numero={{$venta->numero_autorizacion_fel}}"
             class="qr-img" alt="QR SAT">
    @endif

    <div class="footer">
        Atendido por: {{ $venta->user->name }}<br>
        ¡Gracias por su compra!<br>
        Sujeto a pagos trimestrales ISR
    </div>

</body>
</html>
