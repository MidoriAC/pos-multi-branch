<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            border-left: 4px solid #f093fb;
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
            border: 2px solid #f093fb;
            background-color: #f8f9fa;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #f5576c;
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
            background-color: #f093fb;
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
            background-color: #f093fb !important;
            color: white;
            font-weight: bold;
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
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE COMPRAS</h1>
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
        @if($sucursal)
        <p>Sucursal: {{ $sucursal->nombre }}</p>
        @else
        <p>Todas las Sucursales</p>
        @endif
        @if($proveedor)
        <p>Proveedor: {{ $proveedor->persona->razon_social }}</p>
        @endif
    </div>

    <!-- Información del Reporte -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Fecha de Generación:</span>
            <span>{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Compras:</span>
            <span>{{ $estadisticas['total_compras'] }}</span>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['total_compras'] }}</div>
            <div class="stat-label">Total Compras</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['subtotal'], 2) }}</div>
            <div class="stat-label">Subtotal</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['total_iva'], 2) }}</div>
            <div class="stat-label">Total IVA</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['total_monto'], 2) }}</div>
            <div class="stat-label">Monto Total</div>
        </div>
    </div>

    <!-- Tabla de Compras -->
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">N° Comprobante</th>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 30%;">Proveedor</th>
                <th style="width: 18%;">Sucursal</th>
                <th style="width: 10%;" class="text-center">Productos</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($compras as $compra)
            <tr>
                <td><strong>{{ $compra->numero_comprobante }}</strong></td>
                <td>{{ $compra->fecha_hora->format('d/m/Y') }}</td>
                <td>{{ $compra->proveedore->persona->razon_social }}</td>
                <td>{{ $compra->sucursal->nombre }}</td>
                <td class="text-center">{{ $compra->productos->count() }}</td>
                <td class="text-right"><strong>Q {{ number_format($compra->total, 2) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No hay compras en el período seleccionado</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>TOTAL GENERAL:</strong></td>
                <td class="text-right"><strong>Q {{ number_format($estadisticas['total_monto'], 2) }}</strong></td>
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
