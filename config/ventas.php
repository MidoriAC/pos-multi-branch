<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración General de Ventas
    |--------------------------------------------------------------------------
    */

    // Días límite para anular una venta (tanto FEL como recibo)
    'dias_limite_anulacion' => env('DIAS_LIMITE_ANULACION', 7),

    // Porcentaje de IVA
    'iva_porcentaje' => env('IVA_PORCENTAJE', 12),

    // Permitir ventas sin stock (0 = no, 1 = sí)
    'permitir_venta_sin_stock' => env('PERMITIR_VENTA_SIN_STOCK', false),

    // Días de validez para cotizaciones
    'dias_validez_cotizacion' => env('DIAS_VALIDEZ_COTIZACION', 15),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Facturación Electrónica (FEL)
    |--------------------------------------------------------------------------
    */

    'fel' => [
        // Timeout para conexión con certificador (segundos)
        'timeout' => env('FEL_TIMEOUT', 30),

        // Reintentos automáticos en caso de error
        'reintentos' => env('FEL_REINTENTOS', 3),

        // Ambiente por defecto (PRUEBAS o PRODUCCION)
        'ambiente_default' => env('FEL_AMBIENTE', 'PRUEBAS'),

        // Proveedores soportados
        'proveedores' => [
            'INFILE' => [
                'nombre' => 'Infile',
                'url_pruebas' => 'https://certificador.feel.com.gt/fel/certificacion',
                'url_produccion' => 'https://certificador.feel.com.gt/fel/certificacion',
            ],
            'DIGIFACT' => [
                'nombre' => 'Digifact',
                'url_pruebas' => 'https://felgtaws.digifact.com.gt/gt.com.apinug/api',
                'url_produccion' => 'https://felgta.digifact.com.gt/gt.com.apinug/api',
            ],
            'GUATEFACTURAS' => [
                'nombre' => 'Guatefacturas',
                'url_pruebas' => 'https://pruebas.guatefacturas.com/ws',
                'url_produccion' => 'https://ws.guatefacturas.com/ws',
            ]
        ],

        // Tipos de documento FEL
        'tipos_documento' => [
            'FACT' => 'Factura',
            'FCAM' => 'Factura Cambiaria',
            'FPEQ' => 'Factura Pequeño Contribuyente',
            'FCAP' => 'Factura Capitalizable',
            'FESP' => 'Factura Especial',
            'NABN' => 'Nota de Abono',
            'RDON' => 'Recibo por Donación',
            'RECI' => 'Recibo',
            'NDEB' => 'Nota de Débito',
            'NCRE' => 'Nota de Crédito'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Recibos
    |--------------------------------------------------------------------------
    */

    'recibos' => [
        // Prefijo para números de recibo
        'prefijo' => env('RECIBO_PREFIJO', 'RECI'),

        // Longitud del número correlativo
        'longitud_correlativo' => 8,

        // Mostrar logo en PDF
        'mostrar_logo' => true,

        // Texto de pie de página
        'pie_pagina' => 'Gracias por su compra',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Alertas
    |--------------------------------------------------------------------------
    */

    'alertas' => [
        // Generar alerta cuando el stock llegue al mínimo
        'generar_alerta_stock_minimo' => true,

        // Generar alerta cuando el stock sea 0
        'generar_alerta_stock_agotado' => true,

        // Días de anticipación para productos próximos a vencer
        'dias_alerta_vencimiento' => 30,
    ],

];
