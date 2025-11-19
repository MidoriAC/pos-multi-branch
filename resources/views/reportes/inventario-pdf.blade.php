<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            border-left: 4px solid #4facfe;
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
            border: 2px solid #4facfe;
            background-color: #f8f9fa;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #4facfe;
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
            background-color: #4facfe;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 8px;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #ddd;
            font-size: 8px;
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
            background-color: #4facfe !important;
            color: white;
            font-weight: bold;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-normal {
            background-color: #28a745;
            color: white;
        }
        .badge-bajo {
            background-color: #ffc107;
            color: black;
        }
        .badge-agotado {
            background-color: #dc3545;
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
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE INVENTARIO</h1>
        <p>Fecha: {{ now()->format('d/m/Y H:i') }}</p>
        @if($sucursal)
        <p>Sucursal: {{ $sucursal->nombre }}</p>
        @else
        <p>Todas las Sucursales</p>
        @endif
        @if($filtroStock !== 'todos')
        <p>Filtro: {{ $filtroStock === 'bajo' ? 'Productos Bajo Stock' : 'Productos Agotados' }}</p>
        @endif
    </div>

    <!-- Información del Reporte -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Fecha de Generación:</span>
            <span>{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Productos:</span>
            <span>{{ $estadisticas['total_productos'] }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Productos Bajo Stock:</span>
            <span class="text-warning"><strong>{{ $estadisticas['productos_bajo_stock'] }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Productos Agotados:</span>
            <span class="text-danger"><strong>{{ $estadisticas['productos_agotados'] }}</strong></span>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['total_productos'] }}</div>
            <div class="stat-label">Total Productos</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">Q {{ number_format($estadisticas['valor_total'], 2) }}</div>
            <div class="stat-label">Valor Total</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['productos_bajo_stock'] }}</div>
            <div class="stat-label">Bajo Stock</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $estadisticas['productos_agotados'] }}</div>
            <div class="stat-label">Agotados</div>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Código</th>
                <th style="width: 25%;">Producto</th>
                <th style="width: 12%;">Marca</th>
                <th style="width: 15%;">Sucursal</th>
                <th style="width: 7%;" class="text-center">Ubicación</th>
                <th style="width: 7%;" class="text-center">Stock</th>
                <th style="width: 7%;" class="text-center">Mín.</th>
                <th style="width: 10%;" class="text-right">Precio</th>
                <th style="width: 9%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventarios as $inventario)
            <tr>
                <td><strong>{{ $inventario->producto->codigo }}</strong></td>
                <td>{{ $inventario->producto->nombre }}</td>
                <td>{{ $inventario->producto->marca->caracteristica->nombre ?? '-' }}</td>
                <td>{{ $inventario->sucursal->nombre }}</td>
                <td class="text-center">
                    {{ $inventario->ubicacion ? $inventario->ubicacion->codigo : '-' }}
                </td>
                <td class="text-center"><strong>{{ $inventario->stock_actual }}</strong></td>
                <td class="text-center">{{ $inventario->stock_minimo }}</td>
                <td class="text-right">Q {{ number_format($inventario->precio_venta, 2) }}</td>
                <td class="text-center">
                    @if($inventario->stock_actual == 0)
                    <span class="badge badge-agotado">AGOTADO</span>
                    @elseif($inventario->stock_actual <= $inventario->stock_minimo)
                    <span class="badge badge-bajo">BAJO</span>
                    @else
                    <span class="badge badge-normal">NORMAL</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No hay productos en inventario</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="8" class="text-right"><strong>VALORIZACIÓN TOTAL:</strong></td>
                <td class="text-right"><strong>Q {{ number_format($estadisticas['valor_total'], 2) }}</strong></td>
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
