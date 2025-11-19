<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\Sucursal;
use App\Models\ConfiguracionFel;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FELService
{
    private $sucursal;
    private $configuracion;

    public function __construct(Sucursal $sucursal)
    {
        $this->sucursal = $sucursal;
        $this->configuracion = $sucursal->configuracionFel;

        if (!$this->configuracion || !$this->configuracion->estado) {
            throw new Exception('La sucursal no tiene configuración FEL activa');
        }
    }

    /**
     * Certificar factura con el certificador FEL
     */
    public function certificarFactura(Venta $venta)
    {
        try {
            // Construir el XML de la factura
            $xml = $this->construirXMLFactura($venta);

            // Enviar al certificador según el proveedor
            $resultado = $this->enviarACertificador($xml, 'FACT');

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'uuid' => $resultado['uuid'],
                    'fecha_certificacion' => $resultado['fecha_certificacion'],
                    'xml' => $resultado['xml_certificado'],
                    'respuesta' => $resultado['respuesta_completa']
                ];
            }

            return [
                'success' => false,
                'error' => $resultado['error']
            ];

        } catch (Exception $e) {
            Log::error('Error al certificar FEL: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Anular factura FEL
     */
    public function anularFactura(Venta $venta, string $motivo)
    {
        try {
            if (!$venta->numero_autorizacion_fel) {
                throw new Exception('La venta no tiene un UUID de FEL para anular');
            }

            // Construir XML de anulación
            $xml = $this->construirXMLAnulacion($venta, $motivo);

            // Enviar al certificador
            $resultado = $this->enviarACertificador($xml, 'ANULACION', $venta->numero_autorizacion_fel);

            if ($resultado['success']) {
                return [
                    'success' => true,
                    'uuid_anulacion' => $resultado['uuid'],
                    'fecha_anulacion' => $resultado['fecha_certificacion']
                ];
            }

            return [
                'success' => false,
                'error' => $resultado['error']
            ];

        } catch (Exception $e) {
            Log::error('Error al anular FEL: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construir XML de la factura
     */
    private function construirXMLFactura(Venta $venta)
    {
        $venta->load(['productos', 'cliente.persona', 'sucursal']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<dte:GTDocumento xmlns:dte="http://www.sat.gob.gt/dte/fel/0.2.0" Version="0.1">';

        // SAT
        $xml .= '<dte:SAT ClaseDocumento="dte">';
        $xml .= '<dte:DTE ID="DatosCertificados">';

        // Emisor
        $xml .= '<dte:DatosEmision ID="DatosEmision">';
        $xml .= '<dte:DatosGenerales Tipo="FACT" FechaHoraEmision="' . $venta->fecha_hora->format('Y-m-d\TH:i:s') . '" CodigoMoneda="GTQ"/>';

        $xml .= '<dte:Emisor NITEmisor="' . $this->configuracion->nit_emisor . '" NombreEmisor="' . htmlspecialchars($this->configuracion->nombre_emisor) . '" CodigoEstablecimiento="' . $this->configuracion->codigo_establecimiento . '" NombreComercial="' . htmlspecialchars($this->configuracion->nombre_comercial) . '" AfiliacionIVA="' . $this->configuracion->afiliacion_iva . '">';
        $xml .= '<dte:DireccionEmisor>';
        $xml .= '<dte:Direccion>' . htmlspecialchars($venta->sucursal->direccion) . '</dte:Direccion>';
        $xml .= '<dte:CodigoPostal>01001</dte:CodigoPostal>';
        $xml .= '<dte:Municipio>Guatemala</dte:Municipio>';
        $xml .= '<dte:Departamento>Guatemala</dte:Departamento>';
        $xml .= '<dte:Pais>GT</dte:Pais>';
        $xml .= '</dte:DireccionEmisor>';
        $xml .= '</dte:Emisor>';

        // Receptor
        $cliente = $venta->cliente->persona;
        $xml .= '<dte:Receptor IDReceptor="' . ($cliente->nit ?? 'CF') . '" NombreReceptor="' . htmlspecialchars($cliente->razon_social) . '">';
        $xml .= '<dte:DireccionReceptor>';
        $xml .= '<dte:Direccion>' . htmlspecialchars($cliente->direccion) . '</dte:Direccion>';
        $xml .= '<dte:CodigoPostal>01001</dte:CodigoPostal>';
        $xml .= '<dte:Municipio>Guatemala</dte:Municipio>';
        $xml .= '<dte:Departamento>Guatemala</dte:Departamento>';
        $xml .= '<dte:Pais>GT</dte:Pais>';
        $xml .= '</dte:DireccionReceptor>';
        $xml .= '</dte:Receptor>';

        // Items
        $xml .= '<dte:Items>';
        $lineaItem = 1;

        foreach ($venta->productos as $producto) {
            $cantidad = $producto->pivot->cantidad;
            $precioUnitario = $producto->pivot->precio_venta;
            $descuento = $producto->pivot->descuento;
            $subtotal = ($cantidad * $precioUnitario) - $descuento;

            $xml .= '<dte:Item BienOServicio="B" NumeroLinea="' . $lineaItem . '">';
            $xml .= '<dte:Cantidad>' . $cantidad . '</dte:Cantidad>';
            $xml .= '<dte:UnidadMedida>' . ($producto->unidadMedida->codigo_fel ?? 'UNI') . '</dte:UnidadMedida>';
            $xml .= '<dte:Descripcion>' . htmlspecialchars($producto->nombre) . '</dte:Descripcion>';
            $xml .= '<dte:PrecioUnitario>' . number_format($precioUnitario, 2, '.', '') . '</dte:PrecioUnitario>';
            $xml .= '<dte:Precio>' . number_format($subtotal, 2, '.', '') . '</dte:Precio>';

            if ($descuento > 0) {
                $xml .= '<dte:Descuento>' . number_format($descuento, 2, '.', '') . '</dte:Descuento>';
            }

            // Impuestos
            $xml .= '<dte:Impuestos>';
            $xml .= '<dte:Impuesto>';
            $xml .= '<dte:NombreCorto>IVA</dte:NombreCorto>';
            $xml .= '<dte:CodigoUnidadGravable>1</dte:CodigoUnidadGravable>';
            $xml .= '<dte:MontoGravable>' . number_format($subtotal / 1.12, 2, '.', '') . '</dte:MontoGravable>';
            $xml .= '<dte:MontoImpuesto>' . number_format($subtotal - ($subtotal / 1.12), 2, '.', '') . '</dte:MontoImpuesto>';
            $xml .= '</dte:Impuesto>';
            $xml .= '</dte:Impuestos>';

            $xml .= '<dte:Total>' . number_format($subtotal, 2, '.', '') . '</dte:Total>';
            $xml .= '</dte:Item>';

            $lineaItem++;
        }

        $xml .= '</dte:Items>';

        // Totales
        $montoSinImpuesto = $venta->total - $venta->impuesto;
        $xml .= '<dte:Totales>';
        $xml .= '<dte:TotalImpuestos>';
        $xml .= '<dte:TotalImpuesto NombreCorto="IVA" TotalMontoImpuesto="' . number_format($venta->impuesto, 2, '.', '') . '"/>';
        $xml .= '</dte:TotalImpuestos>';
        $xml .= '<dte:GranTotal>' . number_format($venta->total, 2, '.', '') . '</dte:GranTotal>';
        $xml .= '</dte:Totales>';

        $xml .= '</dte:DatosEmision>';
        $xml .= '</dte:DTE>';
        $xml .= '</dte:SAT>';
        $xml .= '</dte:GTDocumento>';

        return $xml;
    }

    /**
     * Construir XML de anulación
     */
    private function construirXMLAnulacion(Venta $venta, string $motivo)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<dte:GTAnulacionDocumento xmlns:dte="http://www.sat.gob.gt/dte/fel/0.2.0" Version="0.1">';
        $xml .= '<dte:SAT>';
        $xml .= '<dte:AnulacionDTE ID="DatosAnulacion">';

        $xml .= '<dte:DatosGenerales ID="' . $venta->numero_autorizacion_fel . '" NumeroDocumentoAAnular="' . $venta->numero_autorizacion_fel . '" NITEmisor="' . $this->configuracion->nit_emisor . '" IDReceptor="' . ($venta->cliente->persona->nit ?? 'CF') . '" FechaEmisionDocumentoAnular="' . $venta->fecha_hora->format('Y-m-d') . '" FechaHoraAnulacion="' . now()->format('Y-m-d\TH:i:s') . '" MotivoAnulacion="' . htmlspecialchars($motivo) . '"/>';

        $xml .= '</dte:AnulacionDTE>';
        $xml .= '</dte:SAT>';
        $xml .= '</dte:GTAnulacionDocumento>';

        return $xml;
    }

    /**
     * Enviar XML al certificador
     */
    private function enviarACertificador(string $xml, string $tipo, ?string $uuidAnular = null)
    {
        try {
            // Firmar el XML
            $xmlFirmado = $this->firmarXML($xml);

            // Preparar petición según el proveedor
            $url = $this->configuracion->url_certificador;

            // Ejemplo genérico - adaptar según el proveedor FEL (INFILE, DIGIFACT, etc.)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->obtenerToken()
                ])
                ->post($url, [
                    'nit_emisor' => $this->configuracion->nit_emisor,
                    'correo_copia' => $this->sucursal->email,
                    'xml_dte' => base64_encode($xmlFirmado)
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Validar respuesta exitosa
                if (isset($data['uuid']) && isset($data['xml_certificado'])) {
                    return [
                        'success' => true,
                        'uuid' => $data['uuid'],
                        'fecha_certificacion' => $data['fecha_certificacion'] ?? now(),
                        'xml_certificado' => base64_decode($data['xml_certificado']),
                        'respuesta_completa' => $data
                    ];
                }

                return [
                    'success' => false,
                    'error' => $data['descripcion_errores'] ?? 'Error desconocido en certificación'
                ];
            }

            return [
                'success' => false,
                'error' => 'Error de comunicación con el certificador: ' . $response->status()
            ];

        } catch (Exception $e) {
            Log::error('Error al enviar a certificador: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Error de comunicación: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Firmar XML con la llave de certificación
     */
    private function firmarXML(string $xml)
    {
        // Implementar la firma digital del XML
        // Esto depende del certificador y de las librerías disponibles
        // Ejemplo simplificado:

        if (!$this->configuracion->llave_certificacion) {
            throw new Exception('No hay llave de certificación configurada');
        }

        // Aquí iría la lógica de firma digital real
        // Por ahora retornamos el XML sin modificar
        return $xml;
    }

    /**
     * Obtener token de autenticación del certificador
     */
    private function obtenerToken()
    {
        // Implementar según el proveedor
        // Puede requerir usuario/password o certificado

        try {
            $response = Http::post($this->configuracion->url_certificador . '/login', [
                'usuario' => $this->configuracion->usuario_certificador,
                'clave' => $this->configuracion->clave_certificador
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['token'] ?? '';
            }

            throw new Exception('Error al obtener token');

        } catch (Exception $e) {
            Log::error('Error al obtener token FEL: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar estado del servicio FEL
     */
    public function validarServicio()
    {
        try {
            $response = Http::timeout(10)
                ->get($this->configuracion->url_certificador . '/status');

            return $response->successful();

        } catch (Exception $e) {
            return false;
        }
    }
}
