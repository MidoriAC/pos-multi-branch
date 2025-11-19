<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Códigos de Barras - {{$producto->codigo}}</title>
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

        .header {
            text-align: center;
            margin-bottom: 5mm;
            padding-bottom: 3mm;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 14pt;
            margin-bottom: 2mm;
            font-weight: bold;
        }

        .header p {
            font-size: 8pt;
            color: #666;
            line-height: 1.4;
        }

        /* Tabla para layout de 3 columnas */
        .container {
            width: 100%;
            border-collapse: collapse;
        }

        .container td {
            width: 33.33%;
            padding: 1.5mm;
            vertical-align: top;
        }

        .etiqueta {
            width: 100%;
            height: 50mm;
            border: 1px dashed #ccc;
            padding: 2.5mm;
            align-content: center;
        }

        .encabezado {
            text-align: center;
            margin-bottom: 2mm;
        }

        .nombre-producto {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 1mm;
            line-height: 1.1;
            height: 2.2em;
            overflow: hidden;
        }

        .info-producto {
            font-size: 6pt;
            color: #666;
            line-height: 1.1;
        }

 .codigo-barras {
    width: 100%;
    text-align: center;     /* Centra el contenido interno */
    margin: 2mm 0;
}

.codigo-barras svg,
.codigo-barras div {
    display: inline-block;  /* Asegura que el código ocupe solo su ancho */
    margin: 0 auto;         /* Evita desplazamientos laterales */
}

        .codigo-texto {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
            margin-top: 1.5mm;
            margin-bottom: 1.5mm;
        }

        .pie {
            font-size: 5pt;
            color: #666;
            margin-top: 1.5mm;
        }

        .pie table {
            width: 100%;
            border-collapse: collapse;
        }

        .pie td {
            padding: 0;
        }

        .pie td:first-child {
            text-align: left;
            font-weight: bold;
        }

        .pie td:last-child {
            text-align: right;
        }

        @page {
            size: letter;
            margin: 8mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CÓDIGOS DE BARRAS</h1>
        <p>
            Producto: <strong>{{$producto->nombre}}</strong> |
            Código: <strong>{{$producto->codigo}}</strong> |
            Cantidad: <strong>{{$cantidad}}</strong> etiquetas |
            Generado: {{ date('d/m/Y H:i') }}
        </p>
    </div>

    <table class="container">
        @for($i = 0; $i < $cantidad; $i++)
            @if($i % 3 == 0)
            <tr>
            @endif
                <td>
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

                        <div class="codigo-barras" >
                             <div style="text-align:center;">
                            <?php
                                $generator = new \Picqer\Barcode\BarcodeGeneratorHTML();
                                echo $generator->getBarcode($producto->codigo, $generator::TYPE_CODE_128, 1.3, 28);
                            ?>
                             </div>
                        </div>

                        <div class="codigo-texto">{{$producto->codigo}}</div>

                        <div class="pie">
                            <table>
                                <tr>
                                    <td>
                                        @if($producto->marca)
                                            {{$producto->marca->caracteristica->nombre}}
                                        @endif
                                    </td>
                                    <td>{{ date('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            @if($i % 3 == 2)
            </tr>
            @endif
        @endfor

        @if($cantidad % 3 == 1)
            <td></td>
            <td></td>
        </tr>
        @elseif($cantidad % 3 == 2)
            <td></td>
        </tr>
        @endif
    </table>
</body>
</html>
