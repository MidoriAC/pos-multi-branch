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

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            background: white;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .empresa {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .info-empresa {
            font-size: 9px;
            line-height: 1.3;
        }

        .tipo-doc {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            padding: 5px;
            border: 1px solid #000;
        }

        .section {
            margin: 8px 0;
            font-size: 10px;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 9px;
        }

        table td {
            padding: 3px 0;
            font-size: 10px;
        }

        .productos {
            margin: 10px 0;
        }

        .producto-item {
            margin: 5px 0;
        }

        .producto-nombre {
            font-size: 10px;
        }

        .producto-detalle {
            font-size: 9px;
            display: flex;
            justify-content: space-between;
        }

        .totales {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .total-final {
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .qr-code {
            text-align: center;
            margin: 10px 0;
        }

        @media print {
            body {
                width: 80mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="empresa">{{ $venta->sucursal->nombre }}</div>
        <div class="info-empresa">
            {{ $venta->sucursal->direccion }}<br>
            @if($venta->sucursal->telefono)
            Tel: {{ $venta->sucursal->telefono }}<br>
            @endif
            @if($venta->sucursal->nit_establecimiento)
            NIT: {{ $venta->sucursal->nit_establecimiento }}
            @endif
        </div>
    </div>

    <!-- Tipo de Documento -->
    <div class="tipo-doc text-center">
        @if($venta->tipo_factura === 'FACT')
        FACTURA ELECTRÓNICA FEL
        @else
        RECIBO SIMPLE
        @endif
    </div>

    <!-- Información de la Venta -->
    <div class="section">
        <div><strong>No. {{ $venta->numero_comprobante }}</strong></div>
        @if($venta->serie)
        <div>Serie: {{ $venta->serie }}</div>
        @endif
        <div>Fecha: {{ $venta->fecha_hora->format('d/m/Y H:i') }}</div>
        @if($venta->tipo_factura === 'FACT' && $venta->numero_autorizacion_fel)
        <div style="font-size: 8px; word-wrap: break-word;">
            UUID: {{ $venta->numero_autorizacion_fel }}
        </div>
        @endif
    </div>

    <div class="line"></div>

    <!-- Cliente -->
    <div class="section">
        <div><strong>CLIENTE</strong></div>
        <div>{{ $venta->cliente->persona->razon_social }}</div>
        <div>NIT: {{ $venta->cliente->persona->nit ?? 'CF' }}</div>
        @if($venta->cliente->persona->direccion)
        <div style="font-size: 9px;">{{ $venta->cliente->persona->direccion }}</div>
        @endif
    </div>

    <div class="line"></div>

    <!-- Productos -->
    <div class="productos">
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">PRODUCTO</th>
                    <th style="width: 15%; text-align: center;">CANT</th>
                    <th style="width: 20%; text-align: right;">P.U.</th>
                    <th style="width: 25%; text-align: right;">TOTAL</th>
                </tr>
            </thead>
        </table>

        @foreach($venta->productos as $producto)
        <div class="producto-item">
            <div class="producto-nombre">
                <strong>{{ $producto->nombre }}</strong>
            </div>
            <div class="producto-detalle">
                <span>{{ $producto->pivot->cantidad }} x Q{{ number_format($producto->pivot->precio_venta, 2) }}</span>
                <span><strong>Q{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_venta - $producto->pivot->descuento, 2) }}</strong></span>
            </div>
            @if($producto->pivot->descuento > 0)
            <div style="font-size: 8px; color: #666;">
                Descuento: -Q{{ number_format($producto->pivot->descuento, 2) }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="line"></div>

    <!-- Totales -->
    <div class="totales">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Q {{ number_format($venta->total - $venta->impuesto, 2) }}</span>
        </div>
        <div class="total-row">
            <span>IVA (12%):</span>
            <span>Q {{ number_format($venta->impuesto, 2) }}</span>
        </div>
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>Q {{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <div class="line"></div>

    <!-- Información adicional -->
    <div class="section" style="font-size: 9px;">
        <div>Productos: {{ $venta->productos->count() }}</div>
        <div>Unidades: {{ $venta->productos->sum('pivot.cantidad') }}</div>
        <div>Atendió: {{ $venta->user->name }}</div>
    </div>

    <!-- QR Code para FEL -->
    @if($venta->tipo_factura === 'FACT' && $venta->numero_autorizacion_fel)
    <div class="qr-code">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($venta->numero_autorizacion_fel) }}"
             alt="QR Code" style="width: 120px; height: 120px;">
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div style="font-size: 10px; margin-bottom: 5px;">
            ¡GRACIAS POR SU COMPRA!
        </div>
        @if($venta->tipo_factura === 'FACT')
        <div style="font-size: 8px;">
            Documento Tributario Electrónico<br>
            Certificado por SAT
        </div>
        @endif
        <div style="margin-top: 5px; font-size: 8px;">
            {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Botones de impresión (solo en pantalla) -->
    {{-- <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()"
                style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #667eea; color: white; border: none; border-radius: 5px;">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()"
                style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #dc3545; color: white; border: none; border-radius: 5px; margin-left: 10px;">
            Cerrar
        </button>
    </div> --}}

</body>
</html>
