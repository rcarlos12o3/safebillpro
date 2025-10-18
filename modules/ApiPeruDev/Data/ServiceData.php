<?php

namespace Modules\ApiPeruDev\Data;

use App\Models\Tenant\Company;
use App\Models\Tenant\ExchangeRate;
use GuzzleHttp\Client;
use App\Models\System\Configuration as SystemConfiguration;
use App\Models\Tenant\Configuration as TenantConfig;
use App\Models\System\TrackApiPeruServices as SystemTrackApiPeruService;
use App\Models\Tenant\TrackApiPeruServices as TenantTrackApiPeruService;
use Illuminate\Support\Facades\URL;

class ServiceData
{
    protected $client;
    /**
     * @var TenantTrackApiPeruService|SystemConfiguration
     */
    protected $trackApi;
    /**
     * @var SystemTrackApiPeruService|TenantTrackApiPeruService
     */
    protected $configuration;
    /** @var Company */
    protected $company;
    protected $parameters;

    public function __construct()
    {
        $prefix = env('PREFIX_URL', null);
        $prefix = !empty($prefix) ? $prefix . "." : '';
        $app_url = $prefix . config('configuration.app_url_base');
        // $app_url = $prefix. env('APP_URL_BASE');
        $url = $_SERVER['HTTP_HOST'] ?? null;
        $company = null;
        // Desde admin
        $configuration = SystemConfiguration::query()->first();
        $trackApi = new SystemTrackApiPeruService();

        if ($url !== $app_url) {
            // desde cliente
            $configuration = TenantConfig::query()->first();
            $trackApi = new TenantTrackApiPeruService();
            $company = Company::first();
            if ($configuration->UseCustomApiPeruToken() == false) {
                $configuration = SystemConfiguration::query()->first();
                $trackApi = new SystemTrackApiPeruService();
            }
        }

        $url = $configuration->url_apiruc = !'' ? $configuration->url_apiruc : config('configuration.api_service_url');
        $token = $configuration->token_apiruc = !'' ? $configuration->token_apiruc : config('configuration.api_service_token');
        $this->configuration = $configuration;
        $this->trackApi = $trackApi;
        $this->company = $company;


        $this->client = new Client(['base_uri' => $url]);
        $this->parameters = [
            'http_errors' => false,
            'connect_timeout' => 10,
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * 1 => sunat/dni
     * 2 => validacion_multiple_cpe
     * 3 => CPE
     * 4 => tipo_de_cambio
     * 5 => printer_ticket
     *
     * @param int $service
     */
    public function saveService($service = 0, $response = [])
    {

        if (isset($response['message']) &&
            strpos($response['message'], 'Ha superado la cantidad de consultas mensuales') !== false) {
            // Si se ha superado la cantidad, no hace nada.
            return $this;

        }
        $number = null;
        if (!empty($this->company)) {
            $number = $this->company->number;
        }
        $this->trackApi->setService($number, $service);
        $this->trackApi->push();
        return $this;

    }

    public function service($type, $number)
    {

        $res = $this->client->request('GET', '/api/' . $type . '/' . $number, $this->parameters);
        $response = json_decode($res->getBody()->getContents(), true);

        $res_data = [];
        if ($response['success']) {
            $data = $response['data'];
            if ($type === 'dni') {
                $department_id = '';
                $province_id = null;
                $district_id = null;
                $address = null;
                if (key_exists('source', $response) && $response['source'] === 'apiperu.dev') {
                    if (strlen($data['ubigeo_sunat'])) {
                        $department_id = $data['ubigeo'][0];
                        $province_id = $data['ubigeo'][1];
                        $district_id = $data['ubigeo'][2];
                        $address = $data['direccion'];
                    }
                } else {
                    $department_id = $data['ubigeo'][0];
                    $province_id = $data['ubigeo'][1];
                    $district_id = $data['ubigeo'][2];
                    $address = $data['direccion'];
                }

                $res_data = [
                    'name' => $data['nombre_completo'],
                    'trade_name' => '',
                    'location_id' => [
                        $department_id,
                        $province_id,
                        $district_id
                    ],
                    'address' => $address,
                    'department_id' => $department_id,
                    'province_id' => $province_id,
                    'district_id' => $district_id,
                    'condition' => '',
                    'state' => '',
                ];
            }

            if ($type === 'ruc') {
                $address = '';
                $department_id = null;
                $province_id = null;
                $district_id = null;
                if (key_exists('source', $response) && $response['source'] === 'apiperu.dev') {
                    if (strlen($data['ubigeo_sunat'])) {
                        $department_id = $data['ubigeo'][0];
                        $province_id = $data['ubigeo'][1];
                        $district_id = $data['ubigeo'][2];
                        $address = $data['direccion'];
                    }
                } else {
                    $department_id = $data['ubigeo'][0];
                    $province_id = $data['ubigeo'][1];
                    $district_id = $data['ubigeo'][2];
                    $address = $data['direccion'];
                }

                $res_data = [
                    'name' => $data['nombre_o_razon_social'],
                    'trade_name' => '',
                    'address' => $address,
//                        'department_id' => $department_id,
//                        'province_id' => $province_id,
//                        'district_id' => $district_id,
                    'location_id' => $data['ubigeo'],
                    'condition' => $data['condicion'],
                    'state' => $data['estado'],
                ];
            }
            $response['data'] = $res_data;
        }
        $this->saveService(1, $response);
        return $response;
    }

public function massive_validate_cpe($data)
{
    try {
        \Log::info('=== ServiceData::massive_validate_cpe START (Individual Mode) ===');
        \Log::info('Input data count:', ['comprobantes' => count($data['comprobantes'] ?? [])]);
        
        $comprobantes = $data['comprobantes'] ?? [];
        $results = [];
        $processed_count = 0;
        
        foreach ($comprobantes as $comprobante) {
            try {
                // Usar el endpoint correcto de Factiliza
                $request_params = $this->parameters;
                $request_params['json'] = [
                    'ruc_emisor' => $comprobante['ruc_emisor'],
                    'codigo_tipo_documento' => $comprobante['codigo_tipo_documento'],
                    'serie_documento' => $comprobante['serie_documento'],
                    'numero_documento' => (string)$comprobante['numero_documento'],
                    'fecha_emision' => \Carbon\Carbon::parse($comprobante['fecha_de_emision'])->format('d/m/Y'),
                    'total' => (string)$comprobante['total']
                ];
                $request_params['headers']['Content-Type'] = 'application/json';
                
                $res = $this->client->request('POST', '/v1/sunat/cpe', $request_params);
                
                $statusCode = $res->getStatusCode();
                $body = $res->getBody()->getContents();
                
                if ($statusCode === 200) {
                    $individual_result = json_decode($body, true);
                    
                    if ($individual_result && isset($individual_result['data'])) {
                        $results[] = array_merge($comprobante, [
                            'comprobante_estado_codigo' => $individual_result['data']['comprobante_estado_codigo'] ?? '-',
                            'comprobante_estado_descripcion' => $individual_result['data']['comprobante_estado_descripcion'] ?? 'No validado'
                        ]);
                        $processed_count++;
                    } else {
                        $results[] = array_merge($comprobante, [
                            'comprobante_estado_codigo' => 'ERROR',
                            'comprobante_estado_descripcion' => 'Respuesta inválida de API'
                        ]);
                    }
                } else {
                    $results[] = array_merge($comprobante, [
                        'comprobante_estado_codigo' => 'ERROR',
                        'comprobante_estado_descripcion' => 'HTTP Error: ' . $statusCode
                    ]);
                }
                
                // Pausa para evitar rate limiting
                usleep(150000); // 0.15 segundos
                
            } catch (\Exception $e) {
                \Log::error('Error validating CPE: ' . $e->getMessage());
                
                $results[] = array_merge($comprobante, [
                    'comprobante_estado_codigo' => 'ERROR',
                    'comprobante_estado_descripcion' => 'Error: ' . $e->getMessage()
                ]);
            }
        }
        
        $response = [
            'success' => true,
            'message' => 'Validación completada',
            'data' => [
                'cantidad_de_comprobantes' => $processed_count,
                'comprobantes' => $results
            ]
        ];
        
        \Log::info('Validation completed:', [
            'total' => count($comprobantes),
            'processed' => $processed_count
        ]);
        
        $this->saveService(2, $response);
        return $response;
        
    } catch (\Exception $e) {
        \Log::error('General error in massive_validate_cpe: ' . $e->getMessage());
        
        return [
            'success' => false,
            'message' => 'Error en validación masiva: ' . $e->getMessage(),
            'data' => [
                'cantidad_de_comprobantes' => 0,
                'comprobantes' => []
            ]
        ];
    }
}

    public function cpe($company_number, $document_type_id, $series, $number, $date_of_issue, $total)
    {
        $form_params = [
            'ruc_emisor' => $company_number,
            'codigo_tipo_documento' => $document_type_id,
            'serie_documento' => $series,
            'numero_documento' => $number,
            'fecha_de_emision' => $date_of_issue,
            'total' => $total
        ];

        $this->parameters['form_params'] = $form_params;
        $res = $this->client->request('POST', '/api/cpe', $this->parameters);
        $this->saveService(3);

        return json_decode($res->getBody()->getContents(), true);
    }

    public function exchange($date)
    {
        $exchange = ExchangeRate::query()->where('date', $date)->first();
        if ($exchange) {
            return [
                'date' => $date,
                'purchase' => $exchange->purchase,
                'sale' => $exchange->sale
            ];
        }
        $form_params = [
            'fecha' => $date,
        ];

        $this->parameters['form_params'] = $form_params;
        $res = $this->client->request('POST', '/api/tipo_de_cambio', $this->parameters);
        $response = json_decode($res->getBody()->getContents(), true);

        if ($response['success']) {
            $data = $response['data'];
            ExchangeRate::query()->create([
                'date' => $data['fecha_busqueda'],
                'date_original' => $data['fecha_sunat'],
                'sale_original' => $data['venta'],
                'sale' => $data['venta'],
                'purchase_original' => $data['compra'],
                'purchase' => $data['compra'],
            ]);

            return [
                'date' => $data['fecha_busqueda'],
                'purchase' => $data['compra'],
                'sale' => $data['venta']
            ];
        }
        $this->saveService(4);

        return [
            'date' => $date,
            'purchase' => 1,
            'sale' => 1,
        ];
    }

    public function printer_ticket($data)
    {
        $this->parameters['form_params'] = $data;
        $res = $this->client->request('POST', '/api/printer_ticket', $this->parameters);
        $this->saveService(5);

        return json_decode($res->getBody()->getContents(), true);
    }
}
