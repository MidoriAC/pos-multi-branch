<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Sucursal;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FELService
{
    private $sucursal;
    private $configuracion;
    private $baseUrl;

    public function __construct(Sucursal $sucursal)
    {
        $this->sucursal = $sucursal;
        $this->configuracion = $sucursal->configuracionFel;

        if (!$this->configuracion || !$this->configuracion->estado) {
            throw new Exception('La sucursal no tiene configuración FEL activa');
        }

        $this->baseUrl = $this->configuracion->ambiente === 'PRODUCCION'
            ? 'https://nucgt.digifact.com/gt.com.apinuc/api'
            : 'https://testnucgt.digifact.com/api';
    }

 /**
     * Obtener Token de Autenticación (Login)
     * Doc: Seccion 2.1.2
     */
    private function obtenerToken()
    {
        // Cachear el token para no pedirlo en cada factura (dura aprox 24h)
        $cacheKey = 'fel_token_' . $this->sucursal->id;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // 1. Limpiar el NIT (quitar guiones)
        $nitLimpio = str_replace('-', '', $this->configuracion->nit_emisor);

        // 2. Rellenar con ceros a la izquierda hasta 12 dígitos
        $nitPad = str_pad($nitLimpio, 12, "0", STR_PAD_LEFT);

        // 3. Obtener el usuario de la BD
        $usuarioBD = trim($this->configuracion->usuario_certificador);

        // 4. Verificar si ya tiene el formato "GT." o si es solo el usuario
        // Digifact requiere: GT.0000NIT.USUARIO
        if (strpos($usuarioBD, 'GT.') === 0) {
            // en la BD ya lo GUARDA completo
            $usernameFinal = $usuarioBD;
        } else {
            // Si en la BD solo dice "JULIOCIF", SE FORMATEA
            $usernameFinal = "GT.{$nitPad}.{$usuarioBD}";
        }

        Log::info("Solicitando Token con Usuario: {$usernameFinal}");

        $response = Http::post("{$this->baseUrl}/login/get_token", [
            'Username' => $usernameFinal,
            'Password' => $this->configuracion->clave_certificador
        ]);

        if ($response->successful()) {
            $token = $response->json()['Token'] ?? null;

            if (!$token) {
                 throw new Exception('Digifact no devolvió el Token en la respuesta JSON.');
            }

            // Guardar en cache por 60 minutos
            Cache::put($cacheKey, $token, 60 * 60);
            return $token;
        }

        // Si falla,  qué devolvió Digifact para depurar
        throw new Exception('Error al obtener Token Digifact: ' . $response->body());
    }

    /**
     * Certificar Factura (NUC V2 JSON)
     * Doc: Seccion 2.1.4 (V2)
     */
    public function certificarFactura(Venta $venta)
    {
        Log::info("--- INICIO CERTIFICACIÓN FEL --- Venta ID: {$venta->id}");
        try {
            Log::info("Solicitando Token a Digifact...");
            $token = $this->obtenerToken();
            Log::info("Token obtenido correctamente (oculto por seguridad).");

            Log::info("Construyendo JSON NUC...");
            $nucData = $this->construirJSONNuc($venta);

            Log::info("Payload JSON enviado a Digifact:", $nucData);
            // Endpoint para transformar NUC JSON a FEL (Doc Pág 8)
            $url = "{$this->baseUrl}/v2/transform/nuc_json";

            // Parámetros Query requeridos
            // El NIT debe ir con ceros a la izquierda hasta completar 12 dígitos
            $nitEmisorPad = str_pad($this->configuracion->nit_emisor, 12, "0", STR_PAD_LEFT);

            Log::info("Enviando petición a: {$url}");
        Log::info("NIT Emisor: {$nitEmisorPad} | Usuario: {$this->configuracion->usuario_certificador}");

            $response = Http::timeout(60)
                ->withHeaders([
                'Authorization' => $token,
                'Content-Type' => 'application/json'
            ])->post("$url?TAXID={$nitEmisorPad}&USERNAME={$this->configuracion->usuario_certificador}&FORMAT=XML HTML PDF", $nucData);

            $result = $response->json();
            Log::info("--- RESPUESTA DIGIFACT ---");
        Log::info("Status Code HTTP: " . $response->status());
        Log::info("Cuerpo Respuesta:", $result);

            // Código 1 significa éxito en Digifact (Doc Pág 37)
            if ($response->successful() && isset($result['code']) && $result['code'] == 1) {

                Log::info("¡CERTIFICACIÓN EXITOSA!");
            Log::info("UUID: " . ($result['authNumber'] ?? 'N/A'));
            Log::info("Serie: " . ($result['batch'] ?? 'N/A'));
            Log::info("Numero: " . ($result['serial'] ?? 'N/A'));
                return [
                    'success' => true,
                    'uuid' => $result['authNumber'], // UUID
                    'serie' => $result['batch'],
                    'numero' => $result['serial'],
                    'fecha_certificacion' => $result['enrolledTimeStamp'], // Fecha Cert
                    'xml_certificado' => $result['responseData1'], // XML en Base64
                    'html_certificado' => $result['responseData2'] ?? null,
                    'pdf_certificado' => $result['responseData3'] ?? null,
                    'respuesta_completa' => $result
                ];
            }

            $errorMsg = $result['message'] ?? 'Error desconocido';
        $descMsg = $result['description'] ?? '';
        Log::error("FALLO EN CERTIFICACIÓN: $errorMsg - $descMsg");

            return [
                'success' => false,
                'error' => $result['message'] ?? 'Error desconocido de Digifact: ' . $response->body()
            ];

        } catch (Exception $e) {
            Log::error('Error FEL Digifact: ' . $e->getMessage());
            Log::error('EXCEPCIÓN CRÍTICA FEL: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Anular Factura
     * Doc: Seccion 2.1.5 CANCEL FEL
     */
   /**
     * Anular Factura
     * Doc: Seccion 2.1.5 CANCEL FEL
     */
    public function anularFactura(Venta $venta, string $motivo)
    {
        Log::info("--- INICIO ANULACIÓN FEL --- Venta ID: {$venta->id} | UUID A Anular: {$venta->numero_autorizacion_fel}");

        try {
            if (!$venta->numero_autorizacion_fel) {
                throw new Exception('La venta no tiene UUID para anular');
            }

            Log::info("Solicitando Token para anulación...");
            $token = $this->obtenerToken();

            // Preparación de datos
            // NOTA: Digifact a veces pide el NIT con ceros (str_pad) en este endpoint específico.
            // Si falla con "NIT Inválido", probaremos usar ltrim($nit, '0') como en la certificación.
            $nitEmisorPad = str_pad($this->configuracion->nit_emisor, 12, "0", STR_PAD_LEFT);

            // Receptor: Si es CF, se envía 'CF', si no, el NIT
            $idReceptor = $venta->cliente->persona->nit ?? 'CF';
            if(strtoupper($idReceptor) == 'CF' || empty($idReceptor)) $idReceptor = 'CF';

            // Construcción del Payload
            $payload = [
                'Taxid' => $nitEmisorPad,
                'Autorizacion' => $venta->numero_autorizacion_fel,
                'IdReceptor' => $idReceptor,
                // Fecha de emisión original del documento formato ISO
                'FechaEmisionDocumentoAnular' => $venta->fecha_hora->format('Y-m-d\TH:i:s'),
                'MotivoAnulacion' => $motivo,
                'Username' => $this->configuracion->usuario_certificador // Usuario API
            ];

            Log::info("Payload Anulación enviado a Digifact:", $payload);

            $url = "{$this->baseUrl}/CancelFelGT";
            Log::info("Enviando petición POST a: $url");

            $response = Http::withHeaders([
                'Authorization' => $token,
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            $result = $response->json();

            Log::info("--- RESPUESTA DIGIFACT ANULACIÓN ---");
            Log::info("Status Code HTTP: " . $response->status());
            Log::info("Cuerpo Respuesta:", $result);

            // Validar respuesta (Doc Pág 11 -> Codigo 1 es Éxito)
            if ($response->successful() && isset($result['Codigo']) && $result['Codigo'] == 1) {

                Log::info("¡ANULACIÓN EXITOSA!");
                Log::info("Fecha Certificación Anulación: " . ($result['Fecha_de_certificacion'] ?? 'N/A'));

                return [
                    'success' => true,
                    'uuid_anulacion' => $result['Autorizacion'] ?? 'N/A',
                    'fecha_anulacion' => $result['Fecha_de_certificacion'] ?? now(),
                    'xml_anulacion' => $result['ResponseDATA1'] ?? null
                ];
            }

            // Manejo de error
            $mensajeError = $result['Mensaje'] ?? 'Error desconocido en anulación';
            Log::error("FALLO EN ANULACIÓN: $mensajeError");

            return [
                'success' => false,
                'error' => $mensajeError
            ];

        } catch (Exception $e) {
            Log::error('EXCEPCIÓN CRÍTICA ANULACIÓN: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
/**
     * Construir Estructura NUC (JSON) - CORREGIDO V10 (Final - Claves de Frases)
     * Se corrige 'CodigoEscenario' por 'Escenario' según validador Digifact
     */
    private function construirJSONNuc(Venta $venta)
    {
        $venta->load(['productos.unidadMedida', 'cliente.persona', 'sucursal']);
        $cliente = $venta->cliente->persona;

        // 1. Limpieza de NITS
        $nitReceptor = $cliente->nit ?? 'CF';
        $nitReceptor = strtoupper(str_replace(['-', ' '], '', $nitReceptor));
        if (empty($nitReceptor) || $nitReceptor == 'CF') {
            $nitReceptor = 'CF';
        }

        $nitEmisor = str_replace('-', '', $this->configuracion->nit_emisor);
        $nitEmisor = ltrim($nitEmisor, '0');
        if (empty($nitEmisor)) $nitEmisor = str_replace('-', '', $this->configuracion->nit_emisor);

        // 2. Lógica de Frases
        $afiliacion = $this->configuracion->afiliacion_iva ?? 'GEN';
        $frases = [];

        if ($afiliacion === 'GEN') {
            $frases = [
                ['Name' => 'TipoFrase', 'Value' => '1', 'Data' => '1'],
                //  'Escenario' en lugar de 'CodigoEscenario'
                ['Name' => 'Escenario', 'Value' => '1', 'Data' => '1']
            ];
        } elseif ($afiliacion === 'PEQ') {
            $frases = [
                ['Name' => 'TipoFrase', 'Value' => '4', 'Data' => '1'],
                // 'Escenario' en lugar de 'CodigoEscenario'
                ['Name' => 'Escenario', 'Value' => '1', 'Data' => '1']
            ];
        }

        // 3. Procesamiento de Ítems
        $itemsArray = [];
        foreach ($venta->productos as $producto) {
            $cantidad = $producto->pivot->cantidad;
            $precioUnitario = $producto->pivot->precio_venta;
            $descuento = $producto->pivot->descuento;

            $totalLinea = ($cantidad * $precioUnitario) - $descuento;
            $montoGravable = $totalLinea / 1.12;
            $montoImpuesto = $totalLinea - $montoGravable;

            $um = $producto->unidadMedida->codigo_fel ?? 'UNI';
            if(strlen($um) > 3) $um = 'UNI';

            $item = [
                'Description' => substr($producto->nombre, 0, 500),
                'Type' => 'B',
                'Qty' => number_format($cantidad, 2, '.', ''),
                'UnitOfMeasure' => $um,
                'Price' => number_format($precioUnitario, 2, '.', ''),
                'Taxes' => [
                    'Tax' => [[
                        'Code' => '1',
                        'Description' => 'IVA',
                        'TaxableAmount' => number_format($montoGravable, 2, '.', ''),
                        'Amount' => number_format($montoImpuesto, 2, '.', '')
                    ]]
                ],
                'Totals' => [
                    'TotalItem' => number_format($totalLinea, 2, '.', '')
                ]
            ];

            if ($descuento > 0) {
                $item['Discounts'] = [
                    'Discount' => [[
                        'Amount' => number_format($descuento, 2, '.', '')
                    ]]
                ];
            }
            $itemsArray[] = $item;
        }

        // 4. Construcción del Array Principal NUC
        $nuc = [
            'Version' => '1.00',
            'CountryCode' => 'GT',
            'Header' => [
                'DocType' => 'FACT',
                'IssuedDateTime' => $venta->fecha_hora->format('Y-m-d\TH:i:s'),
                'Currency' => 'GTQ',
            ],
            'Seller' => [
                'TaxID' => $nitEmisor,
                'TaxIDAdditionalInfo' => [
                    ['Name' => 'AfiliacionIVA', 'Value' => $afiliacion],
                    ['Name' => 'NombreComercial', 'Value' => substr($this->configuracion->nombre_comercial, 0, 100)]
                ],
                'AdditionlInfo' => $frases,

                'Name' => substr($this->configuracion->nombre_emisor, 0, 100),
                'BranchInfo' => [
                    'Code' => (string)$this->configuracion->codigo_establecimiento,
                    'Name' => substr($this->configuracion->nombre_comercial, 0, 100),
                    'AddressInfo' => [
                        'Address' => substr($venta->sucursal->direccion, 0, 100),
                        'City' => '01001',
                        'District' => 'Guatemala',
                        'State' => 'Guatemala',
                        'Country' => 'GT'
                    ]
                ]
            ],
            'Buyer' => [
                'TaxID' => $nitReceptor,
                'Name' => substr($cliente->razon_social, 0, 100),
                'AddressInfo' => [
                    'Address' => substr($cliente->direccion ?? 'Ciudad', 0, 100),
                    'City' => '01001',
                    'District' => 'Guatemala',
                    'State' => 'Guatemala',
                    'Country' => 'GT'
                ]
            ],
            'Items' => $itemsArray,
            'Totals' => [
                'TotalTaxes' => [
                    'TotalTax' => [[
                        'Description' => 'IVA',
                        'Amount' => number_format($venta->impuesto, 2, '.', '')
                    ]]
                ],
                'GrandTotal' => [
                    'InvoiceTotal' => number_format($venta->total, 2, '.', '')
                ]
            ],
            'AdditionalDocumentInfo' => [
                'AdditionalInfo' => []
            ]
        ];

        return $nuc;
    }
}
