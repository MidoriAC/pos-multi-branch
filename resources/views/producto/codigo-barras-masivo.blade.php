<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Barras - Productos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 5mm;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 5mm;
            justify-content: space-between;
        }

        .etiqueta {
            width: 80mm;
            height: 50mm;
            border: 1px dashed #ccc;
            padding: 5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 2mm;
        }

        .nombre-producto {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1mm;
            line-height: 1.2;
            max-height: 2.4em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .info-producto {
            font-size: 8pt;
            color: #666;
        }

        .codigo-barras {
            text-align: center;
            margin: 3mm 0;
        }

        .codigo-barras svg {
            max-width: 100%;
            height: auto;
        }

        .codigo-texto {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin-top: 1mm;
        }

        .pie {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            color: #666;
        }

        .marca {
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 2mm;
        }

        .header p {
            font-size: 10pt;
            color: #666;
        }

        @media print {
            body {
                margin: 0;
                padding: 5mm;
            }
            .etiqueta {
                border: none;
                page-break-inside: avoid;
            }
            .header {
                page-break-after: avoid;
            }
        }

        @page {
            size: letter;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CÓDIGOS DE BARRAS - PRODUCTOS</h1>
        <p>Generado el {{ date('d/m/Y H:i') }} | Total: {{$productos->count()}} productos</p>
    </div>

    <div class="container">
        @foreach($productos as $producto)
        <div class="etiqueta">
            <div class="encabezado">
                <div class="nombre-producto">{{$producto->nombre}}</div>
                <div class="info-producto">
                    @if($producto->presentacione)
                        {{$producto->presentacione->caracteristica->nombre}}
                    @endif
                    @if($producto->unidadMedida)
                        - {{$producto->unidadMedida->abreviatura}}
                    @endif
                </div>
            </div>

            <div class="codigo-barras">
                <?php
                    $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                    echo $generator->getBarcode($producto->codigo, $generator::TYPE_CODE_128, 2, 40);
                ?>
            </div>

            <div class="codigo-texto">{{$producto->codigo}}</div>

            <div class="pie">
                <span class="marca">
                    @if($producto->marca)
                        {{$producto->marca->caracteristica->nombre}}
                    @endif
                </span>
                <span>{{ date('d/m/Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>
</body>
</html>
