<?php

namespace Modules\Sire\Helpers;

use App\Traits\LockedEmissionTrait;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use Hyn\Tenancy\Environment;


class SireService
{
    /*
     * URLs Services
     */
    // scope
    public static $SCOPE = 'https://api-sire.sunat.gob.pe';
    // 5.1
    public static $TOKEN = 'https://api-seguridad.sunat.gob.pe/v1/clientessol/CLIENT_ID/oauth2/token/';
    // 5.2 Sale | codLibro = 140000
    // 5.33 Purchase | codLibro  = 080000
    public static $PERIODS = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/padron/web/omisos/COD_LIBRO/periodos';
    // 5.18 Sale     | type = rvie | suffix = exportapropuesta?codTipoArchivo=0
    // 5.34 Purchase | type = rce  | suffix = exportacioncomprobantepropuesta?codTipoArchivo=0&codOrigenEnvio=1
    public static $PROPOSAL = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/TYPE/propuesta/web/propuesta/PERIOD/SUFFIX';
    // 5.16 Sale
    // 5.31 Purchase
    public static $QUERY = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/consultaestadotickets?perIni=PERIOD&perFin=PERIOD&page=NUM_PAGE&perPage=20&numTicket=NUM_TICKET';
    // 5.17 Sale | suffix = &codLibro=140000
    // 5.32 Purchase | suffix = null
    public static $DOWNLOAD = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/archivoreporte?nomArchivoReporte=FILENAME&codTipoArchivoReporte=01SUFFIX';
    // 5.8 Sale
    public static $ACCEPT = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/propuesta/web/propuesta/PERIOD/aceptapropuesta';

    private function getCompany()
    {
        $company = Company::first();
        return $company;
    }

    public function getToken($force = false)
    {
        if ($force || !($token = Cache::get('sire_token'))) {
            $queryToken = $this->queryToken();
            if (!$queryToken['success']) {
                return $queryToken;
            }
            $token = $queryToken['access_token'];
        }

        return [
            'success' => true,
            'token' => $token
        ];
    }

    private function queryToken()
    {
        $company = $this->getCompany();
        $client_id = $company->sire_client_id;
        $url_base = self::$TOKEN;
        $url = str_replace('CLIENT_ID', $client_id, $url_base);
        $body = [
            'grant_type' => 'password',
            'scope' => self::$SCOPE,
            'client_id' => $client_id,
            'client_secret' => $company->sire_client_secret,
            'username' => $company->sire_username,
            'password' => $company->sire_password,
        ];

        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => $body,
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            $this->updateToken($data['access_token'], $data['expires_in']);
            return [
                'success' => true,
                'code' => $statusCode,
                'access_token' => $data['access_token']
            ];
        }
    }

    private function updateToken($token, $expire)
    {
        Cache::put('sire_token', $token, now()->addSeconds($expire));
    }

    public function getPeriods($type)
    {
        switch ($type) {
            case 'sale':
                $cod_libro = '140000';
                break;
            case 'purchase':
                $cod_libro = '080000';
                break;
        }
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url_base = self::$PERIODS;
        $url = str_replace('COD_LIBRO', $cod_libro, $url_base);
        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }

    public function getTicket($type, $period)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        switch ($type) {
            case 'sale':
                $suffix = 'exportapropuesta?codTipoArchivo=0';
                $url = str_replace(['TYPE','PERIOD','SUFFIX'], ['rvie', $period, $suffix], self::$PROPOSAL);
                break;
            case 'purchase':
                $suffix = 'exportacioncomprobantepropuesta?codTipoArchivo=0&codOrigenEnvio=1';
                $url = str_replace(['TYPE','PERIOD','SUFFIX'], ['rce', $period, $suffix], self::$PROPOSAL);
                break;
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }

    public function queryTicket($page, $period, $ticket, $type)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url = str_replace(['NUM_PAGE','PERIOD','NUM_TICKET'], [$page, $period, $ticket], self::$QUERY);

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            $records = $this->validateAndGetValue($data, 'registros');
            if (!empty($records)) {
                $record = $records[0];
                $status_code = $record['codEstadoProceso'];
                $filename = isset($record['archivoReporte']) ? $record['archivoReporte'][0]['nomArchivoReporte'] : null;

                $documents = null;
                if ($status_code == '06' && $filename != null) {
                    $documents = $this->queryFile($filename, $type,$period, $ticket);
                }
                return [
                    'success' => true,
                    'code' => $statusCode,
                    'data' => [
                        'status_code' => $status_code,
                        'filename' => $filename,
                        'documents' => $documents,
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'message' => 'SIRE. No hay registros en esta página'
                ];
            }
        }
    }

    function validateAndGetValue($object, $key) {
        return isset($object[$key]) ? $object[$key] : null;
    }

    public function queryFile($filename, $type, $period, $ticket)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        switch ($type) {
            case 'sale':
                $suffix = '&codLibro=140000&perTributario='.$period.'&codProceso=10&numTicket='.$ticket;
                $url = str_replace(['FILENAME','SUFFIX'], [$filename, $suffix], self::$DOWNLOAD);
                break;
            case 'purchase':
                $suffix = '&perTributario='.$period.'&codProceso=10&numTicket='.$ticket;
                $url = str_replace(['FILENAME','SUFFIX'], [$filename, $suffix], self::$DOWNLOAD);
                break;
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $response->getBody()
            ];
        }

        if ($statusCode === 200) {
            $file = $response->getBody()->getContents();

            $temp = sys_get_temp_dir() . '/' . $filename; // Almacena el archivo ZIP en una ubicación temporal
            file_put_contents($temp, $file);

            $zip = new \ZipArchive;
            if ($zip->open($temp) === true) {
                $numFiles = $zip->numFiles;
                $filename_txt = $zip->getNameIndex(0);

                $extractPath = storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . app(Environment::class)->tenant()->uuid . DIRECTORY_SEPARATOR . 'txt');
                if (!Storage::exists($extractPath)) {
                    Storage::makeDirectory($extractPath);
                }
                $zip->extractTo($extractPath);
                $zip->close();

                $path_txt = $extractPath . DIRECTORY_SEPARATOR . $filename_txt;
                if (Storage::disk('tenant')->exists('txt' . DIRECTORY_SEPARATOR . $filename_txt)) {
                    $lines = file($path_txt, FILE_IGNORE_NEW_LINES); // Storage no funcionó
                    array_shift($lines); // Eliminando cabeceras
                    $dataCollection = new Collection();
                    if($type == 'sale'){
                        foreach ($lines as $line) {
                            $values = explode('|', $line);
                            $serie = $values[7];
                            $number = (int) $values[8];
                            $dataCollection->push([
                                'service' => 'SUNAT',
                                'date' => $values[4], // 2023/05/01
                                'document_type' => $values[6],
                                'serie' => $serie,
                                'number' => $number,
                                'total' => $values[25]
                            ]);

                            $document = Document::where('series', $serie)
                                ->where('number', $number)
                                ->first();
                            if ($document) {
                                $dataCollection->push([
                                    'service' => 'PRO-5',
                                    'date' => $document->date_of_issue->format('Y/m/d'),
                                    'document_type' => $document->document_type_id,
                                    'serie' => $document->series,
                                    'number' => $document->number,
                                    'total' => $document->total
                                ]);
                            }
                        }
                    } else {
                        foreach ($lines as $line) {
                            $values = explode('|', $line);
                            $serie = $values[7];
                            $number = (int)$values[9];
                            $dataCollection->push([
                                'service' => 'SUNAT',
                                'date' => $values[4], //2023/05/01
                                'document_type' => $values[6],
                                'serie' => $serie,
                                'number' => $number,
                                'total' => $values[24]
                            ]);

                            $purchase = Purchase::where('series', $serie)
                                            ->where('number', $number)
                                            ->first();
                            if($purchase) {
                                $dataCollection->push([
                                    'service' => 'PRO-5',
                                    'date' => $purchase->date_of_issue->format('d/m/Y'),
                                    'document_type' => $purchase->document_type_id,
                                    'serie' => $purchase->series,
                                    'number' => $purchase->number,
                                    'total' => $purchase->total
                                ]);
                            }
                        }
                    }
                    return $dataCollection;
                } else {
                    // Error: el archivo no existe o se guardó incorrectamente
                    return [
                        'success' => false,
                        'code' => 500,
                        'message' => 'El archivo no existe o se guardó incorrectamente'
                    ];
                }

                unlink($temp);
            } else {
                // Manejo de errores al abrir el archivo ZIP
                return [
                    'success' => false,
                    'code' => 500,
                    'message' => 'Error al abrir el archivo ZIP'
                ];
            }
        }
    }

    public function sendAccept($period)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url = str_replace('PERIOD', $period, self::$ACCEPT);

        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }
}