<?php

namespace Modules\PseService\Http\Gior;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\CoreFacturalo\WS\Response\BillResult;
use Modules\PseService\Http\Gior\Endpoints;
use Modules\PseService\Http\Gior\Errors;
use App\Models\Tenant\Company;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;


/**
 * Class Service.
 */
final class Service
{
    private function company()
    {
        $company = Company::first();
        return $company;
    }

    /*
     * se consulta el api o el cache dependiendo del caso
     * se puede forzar un nuevo token en caso de que haga falta con el parametro force
     * si hay errores se devuelve lo que haya retornado el api con mensajes de su documentacion
     */
    public function getToken()
    {
        $queryToken = $this->queryToken();
        if (!$queryToken['success']) {
            throw new Exception("PSE - token. Code: {$queryToken['code']}; Description:  {$queryToken['error']}");
        }
        $token = $queryToken['token'];

        return [
            'success' => true,
            'token' => $token
        ];
    }

    private function queryToken()
    {
        $company = $this->company();
        $isProduction = $company->soap_type_id == 02;

        Log::info("=== INICIO queryToken ===");
        Log::info("Is Production: " . ($isProduction ? 'YES' : 'NO (NUBEFACT)'));

        if ($isProduction) {
            // Producción: ValidaPSE
            $url = Endpoints::TOKEN;
            $body = [
                'usuario' => $company->user_pse,
                'password' => $company->password_pse,
            ];

            $client = new Client();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($body),
                'verify' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getBody(), true);

            if($statusCode !== 200) {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'error' => Errors::getMessage($statusCode)
                ];
            }

            Log::info("Token obtenido (ValidaPSE): OK");
            return [
                'success' => true,
                'code' => $statusCode,
                'token' => $data['token_acceso']
            ];
        } else {
            // Demo: Nubefact OAuth2
            $url = str_replace('{client_id}', Endpoints::NUBEFACT_TEST_CLIENT_ID, Endpoints::BETA_TOKEN);

            $body = [
                'grant_type' => 'password',
                'scope' => 'https://api-cpe.sunat.gob.pe',
                'client_id' => Endpoints::NUBEFACT_TEST_CLIENT_ID,
                'client_secret' => Endpoints::NUBEFACT_TEST_CLIENT_SECRET,
                'username' => $company->number . Endpoints::NUBEFACT_TEST_USERNAME_SUFFIX,
                'password' => Endpoints::NUBEFACT_TEST_PASSWORD,
            ];

            Log::info("URL Nubefact: " . $url);
            Log::info("Username: " . $body['username']);

            $client = new Client();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $body,
                'verify' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $data = json_decode($response->getBody(), true);

            Log::info("Status Code Nubefact Token: " . $statusCode);
            Log::info("Response Nubefact Token: " . json_encode($data));

            if($statusCode !== 200) {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'error' => isset($data['error']) ? $data['error'] : Errors::getMessage($statusCode)
                ];
            }

            Log::info("Token obtenido (Nubefact): OK");
            return [
                'success' => true,
                'code' => $statusCode,
                'token' => $data['access_token']
            ];
        }
    }

    private function updateToken($token, $expire)
    {
        Cache::put('pse_token', $token, now()->addSeconds($expire));
    }

    public function sendXml($xmlUnsigned, $filename)
    {
        $company = $this->company();
        $isProduction = $company->soap_type_id == 02;

        // Nubefact (demo) no tiene endpoint de generación, retornar el XML sin cambios
        if (!$isProduction) {
            return [
                'success' => true,
                'code' => 200,
                'xml_signed' => $xmlUnsigned, // Retornar el mismo XML
                'hash' => '', // No hay hash en este flujo
                'message' => 'Demo: XML listo para envío directo (Nubefact no requiere generación previa)',
            ];
        }

        // Producción: ValidaPSE
        $url = Endpoints::GENERATE;
        $token = $this->getToken();
        $body = [
            'tipo_integracion' => 0, //xml
            'nombre_archivo' => $filename,
            'contenido_archivo' => base64_encode($xmlUnsigned)
        ];

        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['token'],
                'Content-Type' => 'application/json',
            ],
            'verify' => false,
            'body' => json_encode($body),
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if($statusCode !== 200) {
            $error = Errors::getMessage($statusCode);
            $errors = $this->validateObject($data, 'errores');
            return [
                'success' => false,
                'message' => $error . ' | Detalles: '.json_encode($errors),
                'code' => $data['estado'],
            ];
        }

        if($statusCode == 200) {
            if($statusCode == $data['estado']) {
                return [
                    'success' => true,
                    'code' => $data['estado'],
                    'xml_signed' => $data['xml'],
                    'hash' => $data['codigo_hash'],
                    'message' => $data['mensaje'],
                ];
            }
        }
    }

    public function sendXmlSigned($filename, $xmlSigned, $hasSummary = false)
    {
        $company = $this->company();
        $isProduction = $company->soap_type_id == 02;

        Log::info("=== INICIO sendXmlSigned ===");
        Log::info("Filename: " . $filename);
        Log::info("Is Production: " . ($isProduction ? 'YES' : 'NO (NUBEFACT)'));

        $token = $this->getToken();

        if ($isProduction) {
            // Producción: ValidaPSE
            $url = Endpoints::SEND;
            $body = [
                'nombre_xml_firmado' => $filename,
                'contenido_xml_firmado' => base64_encode($xmlSigned)
            ];

            $client = new Client();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token['token'],
                    'Content-Type' => 'application/json',
                ],
                'verify' => false,
                'body' => json_encode($body),
            ]);
        } else {
            // Demo: Nubefact - Construir URL dinámica
            // Filename format: 20123456789-09-T001-00000123
            $urlTemplate = Endpoints::BETA_SEND;
            $url = str_replace(
                ['{numRucEmisor}-{codCpe}-{numSerie}-{numCpe}'],
                [$filename],
                $urlTemplate
            );

            // Comprimir XML en ZIP
            $zip = new \ZipArchive();
            $zipPath = sys_get_temp_dir() . '/' . $filename . '.zip';

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString($filename . '.xml', $xmlSigned);
                $zip->close();
            }

            $zipContent = file_get_contents($zipPath);
            $hashZip = hash('sha256', $zipContent); // Calcular hash SHA-256 del ZIP
            unlink($zipPath); // Eliminar archivo temporal

            Log::info("Hash ZIP calculado: " . $hashZip);

            // Nubefact espera un JSON con el ZIP en base64
            $body = json_encode([
                'archivo' => [
                    'nomArchivo' => $filename . '.zip',
                    'arcGreZip' => base64_encode($zipContent),
                    'hashZip' => $hashZip
                ]
            ]);

            Log::info("Sending to Nubefact URL: " . $url);

            $client = new Client();
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token['token'],
                    'Content-Type' => 'application/json',
                ],
                'verify' => false,
                'body' => $body,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        Log::info("---PSE sendXmlSigned---");
        Log::info("Production: " . ($isProduction ? 'YES' : 'NO (Nubefact)'));
        Log::info("Status: " . $statusCode);
        Log::info("Response: " . $responseBody);
        Log::info("---END PSE sendXmlSigned---");

        // Para Nubefact, la respuesta puede ser diferente
        if (!$isProduction) {
            // Nubefact retorna el CDR directamente o un ticket
            if ($statusCode == 200) {
                $data = json_decode($responseBody, true);

                // Si retorna ticket, es envío asíncrono
                if (isset($data['numTicket'])) {
                    return [
                        'success' => true,
                        'code' => 200,
                        'ticket' => $data['numTicket'],
                        'message' => 'GRE enviada correctamente, consulte con el ticket',
                        'rejected' => false,
                        'errors' => null,
                    ];
                }

                // Si no tiene ticket, la respuesta es sincrónica (CDR incluido)
                return [
                    'success' => true,
                    'code' => 200,
                    'message' => 'GRE aceptada por SUNAT',
                    'observations' => null,
                    'cdr' => base64_encode($responseBody), // El CDR viene en la respuesta
                    'rejected' => false,
                    'errors' => null,
                ];
            } else {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'message' => 'Error al enviar GRE: ' . $responseBody,
                    'errors' => $responseBody,
                ];
            }
        }

        // Producción: ValidaPSE
        $data = json_decode($responseBody, true);

        if($statusCode !== 200) {
            $error = Errors::getMessage($statusCode);
            $errors = $this->validateObject($data, 'errores');
            return [
                'success' => false,
                'code' => $data['estado'],
                'message' => $error,
                'errors' => $errors,
            ];
        }

        if($statusCode == 200) {
            if($statusCode == $data['estado']) {
                $mensaje = $this->validateObject($data, 'mensaje');
                $rechazado = $this->validateObject($data, 'rechazado');
                $errors = $this->validateObject($data, 'errores');

                if($hasSummary) {
                    $ticket = $this->validateObject($data, 'ticket');
                    return [
                        'success' => true,
                        'code' => $data['estado'],
                        'ticket' => $ticket,
                        'message' => $mensaje,
                        'rejected' => $rechazado,
                        'errors' => $errors,
                    ];
                }

                $observaciones = $this->validateObject($data, 'observaciones');
                $cdr = $this->validateObject($data, 'cdr');
                return [
                    'success' => true,
                    'code' => $data['estado'],
                    'message' => $mensaje,
                    'observations' => $observaciones,
                    'cdr' => $cdr,
                    'rejected' => $rechazado,
                    'errors' => $errors,
                ];
            }
        }
    }

    /*
     * valida key en el array de respuesta
     */
    private function validateObject($response, $key)
    {
        return isset($response[$key]) ? $response[$key] : null;
    }

    public function getCdrResponse($cdr_b64)
    {
        $cdr_xml = base64_decode($cdr_b64);

        $dom = new \DOMDocument();
        $dom->loadXML($cdr_xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', $dom->documentElement->namespaceURI);

        $responseCodeNode = $xpath->query('/x:ApplicationResponse/cac:DocumentResponse/cac:Response/cbc:ResponseCode')->item(0);
        $responseCode = $responseCodeNode ? $responseCodeNode->nodeValue : null;

        $descriptionNode = $xpath->query('/x:ApplicationResponse/cac:DocumentResponse/cac:Response/cbc:Description')->item(0);
        $description = $descriptionNode ? $descriptionNode->nodeValue : null;

        $nodes = $xpath->query('/x:ApplicationResponse/cbc:Note');
        $notes = [];
        if ($nodes->length > 0) {
            foreach ($nodes as $node) {
                $notes[] = $node->nodeValue;
            }
        }

        $response = [
            'code' => $responseCode,
            'description' => 'PSE - '.$description,
            'notes' => $notes,
        ];

        // Retornar el array
        return $response;
    }

    public function querySummary($filename)
    {
        $company = $this->company();
        $isProduction = $company->soap_type_id == 02;

        Log::info("=== INICIO querySummary ===");
        Log::info("Filename/Ticket: " . $filename);
        Log::info("Is Production: " . ($isProduction ? 'YES' : 'NO (NUBEFACT)'));

        $token = $this->getToken();

        if ($isProduction) {
            // Producción: ValidaPSE
            $url = Endpoints::QUERY . '/' . $filename;
        } else {
            // Demo: Nubefact - Construir URL dinámica
            // $filename es el numTicket para Nubefact
            $urlTemplate = Endpoints::BETA_QUERY;
            $url = str_replace('{numTicket}', $filename, $urlTemplate);
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['token'],
                'Content-Type' => 'application/json',
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        Log::info("---PSE querySummary---");
        Log::info("Production: " . ($isProduction ? 'YES' : 'NO (Nubefact)'));
        Log::info("Status: " . $statusCode);
        Log::info("Response: " . $responseBody);
        Log::info("---END PSE querySummary---");

        if ($statusCode !== 200) {
            $data = json_decode($responseBody, true);
            $error = Errors::getMessage($statusCode);
            $errors = $this->validateObject($data, 'errores');
            return [
                'success' => false,
                'message' => $error . ' | Detalles: '.json_encode($errors),
                'code' => $statusCode,
            ];
        }

        // Para Nubefact, parsear respuesta diferente
        if (!$isProduction) {
            $data = json_decode($responseBody, true);

            Log::info("Nubefact data parsed: " . json_encode($data));

            // Verificar codRespuesta
            $codRespuesta = $data['codRespuesta'] ?? null;

            if ($codRespuesta === '0') {
                // Aceptado - CDR disponible
                if (isset($data['arcCdr'])) {
                    return [
                        'success' => true,
                        'code' => 200,
                        'message' => 'GRE aceptada por SUNAT',
                        'observations' => null,
                        'cdr' => $data['arcCdr'], // CDR en base64
                        'rejected' => false,
                        'errors' => null,
                    ];
                }
            } elseif ($codRespuesta === '98') {
                // En proceso
                return [
                    'success' => false,
                    'code' => 202,
                    'message' => 'Documento aún en proceso',
                    'errors' => null,
                ];
            } elseif ($codRespuesta === '99') {
                // Rechazado
                $errorInfo = $data['error'] ?? null;
                $errorMessage = 'GRE rechazada por SUNAT';

                if ($errorInfo && isset($errorInfo['desError'])) {
                    $errorMessage = $errorInfo['desError'] . ' (Código: ' . ($errorInfo['numError'] ?? 'N/A') . ')';
                }

                // Si hay CDR a pesar del rechazo
                $cdr = isset($data['arcCdr']) ? $data['arcCdr'] : null;

                return [
                    'success' => true, // True para que procese el rechazo
                    'code' => 200,
                    'message' => $errorMessage,
                    'observations' => null,
                    'cdr' => $cdr,
                    'rejected' => true,
                    'errors' => $errorInfo,
                ];
            }

            // Caso no manejado
            return [
                'success' => false,
                'code' => 500,
                'message' => 'Respuesta no esperada de Nubefact: ' . json_encode($data),
                'errors' => $data,
            ];
        }

        // Producción: ValidaPSE
        $data = json_decode($responseBody, true);

        if($statusCode == 200 && $data['estado'] == $statusCode) {
            $mensaje = $this->validateObject($data, 'mensaje');
            $rechazado = $this->validateObject($data, 'rechazado');
            $errors = $this->validateObject($data, 'errores');
            $observaciones = $this->validateObject($data, 'observaciones');
            $cdr = $this->validateObject($data, 'cdr');
            return [
                'success' => true,
                'code' => $data['estado'],
                'message' => $mensaje,
                'observations' => $observaciones,
                'cdr' => $cdr,
                'rejected' => $rechazado,
                'errors' => $errors,
            ];
        }
    }

}