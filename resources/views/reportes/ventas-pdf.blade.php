<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 2px solid #667eea;
            background-color: #f8f9fa;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #667eea;
            color: white;
            padding: 10px 5px;
            text-align: left;
            font-size: 9px;
        }
        td {
            padding: 8px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #667eea !important;
            color: white;
            font-weight: bold;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-fel {
            background-color: #28a745;
            color: white;
        }
        .badge-recibo {
            background-color: #6c757d;
            color: white;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE VENTAS</h1>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        @if($sucursal)
        <p>Sucursal: {{ $sucursal->nombre }}</p>
        @else
        <p>Todas las Sucursales</p>
        @endif
    </div>

    <!-- Información del Reporte -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Fecha de Generación:</span>
            <span>{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo de Factura:</span>
            <span>
                @if($tipoFactura === 'TODOS')
                    Todas
                @elseif($tipoFactura === 'FACT')
                    Solo FEL
                @else
                    Solo Recibos
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span>{{ ucfirst($estado) }}</span>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['total_ventas'] }}</div>
            <div class="stat-label">Total Ventas</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['total_monto'], 2) }}</div>
            <div class="stat-label">Monto Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['total_iva'], 2) }}</div>
            <div class="stat-label">Total IVA</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['subtotal'], 2) }}</div>
            <div class="stat-label">Subtotal</div>
        </div>
    </div>

    <!-- Tabla de Ventas -->
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">N° Comprobante</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 15%;">Sucursal</th>
                <th style="width: 13%;" class="text-right">Total</th>
                <th style="width: 15%;">Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td><strong>{{ $venta->numero_comprobante }}</strong></td>
                <td>{{ $venta->fecha_hora->format('d/m/Y') }}</td>
                <td>{{ $venta->cliente->persona->razon_social }}</td>
                <td class="text-center">
                    @if($venta->tipo_factura === 'FACT')
                    <span class="badge badge-fel">FEL</span>
                    @else
                    <span class="badge badge-recibo">Recibo</span>
                    @endif
                </td>
                <td>{{ $venta->sucursal->nombre }}</td>
                <td class="text-right"><strong>Q {{ number_format($venta->total, 2) }}</strong></td>
                <td>{{ $venta->user->name }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No hay ventas en el período seleccionado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTALES:</strong></td>
                <td class="text-right"><strong>Q {{ number_format($estadisticas['total_monto'], 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Sistema de Gestión - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Página 1 de 1</p>
    </div>

</body>
</html>
