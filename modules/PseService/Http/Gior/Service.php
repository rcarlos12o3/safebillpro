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
        $url = $company->soap_type_id == 02 ? Endpoints::TOKEN : Endpoints::BETA_TOKEN;
        $body = [
            'usuario' => $company->user_pse,
            'contraseña' => $company->password_pse,
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

        if($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'token' => $data['token_acceso']
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
        $url = $company->soap_type_id == 02 ? Endpoints::GENERATE : Endpoints::BETA_GENERATE;
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
            // dd($body);
        // dd($response->object());
            $error = Errors::getMessage($statusCode);
            $errors = $this->validateObject($data, 'errores');
            // retorna error pero permite almacenar la data del documento
            // y continuar para ser enviado en otro momento
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
        $url = $company->soap_type_id == 02 ? Endpoints::SEND : Endpoints::BETA_SEND;
        $body = [
            'nombre_xml_firmado' => $filename,
            'contenido_xml_firmado' => base64_encode($xmlSigned)
        ];
        $token = $this->getToken();

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

        // dd($response,$statusCode,$data);
        Log::info("---PSE sendXmlSigned---");
        Log::info($statusCode);
        Log::info($data);
        Log::info("---END PSE sendXmlSigned---");

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
                // tomar en cuenta tipos de documento
                // ticket para envios que requieren 2 pasos
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
        $url = $company->soap_type_id == 02 ? Endpoints::QUERY : Endpoints::BETA_QUERY;
        $token = $this->getToken();

        $client = new Client();
        $response = $client->request('GET', $url.'/'.$filename, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['token'],
                'Content-Type' => 'application/json',
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if($statusCode !== 200) {
            // dd($body);
            // dd($response->object());
            $error = Errors::getMessage($response->status());
            $errors = $this->validateObject($data, 'errores');
            return [
                'success' => false,
                'message' => $error . ' | Detalles: '.json_encode($errors),
                'code' => $statusCode,
            ];
        }

        if($statusCode == 200) {
            if($data['estado'] == $statusCode) {
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

}