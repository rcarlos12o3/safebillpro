<?php

namespace Modules\ApiPeruDev\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ApiPeruDev\Data\ServiceData;

class MassiveValidateController extends Controller
{
    public function tables()
    {
        $document_types = DocumentType::query()->whereIn('id', ['01', '03', '07', '08'])->get();

        return [
            'document_types' => $document_types,
        ];
    }

    public function validate(Request $request)
    {
        $company = Company::query()->first();
        $document_type_id = $request['document_type_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $period = $request['period'];
        $d_start = null;
        $d_end = null;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        $records = Document::query()
    	->where('soap_type_id', $company->soap_type_id)
    	->whereIn('state_type_id', ['01', '03', '05', '07', '09', '11', '13']) // Todos los estados
    	->where('document_type_id', $document_type_id)
    	->whereBetween('date_of_issue', [$d_start, $d_end])
    	->get();

        $soap_username = '';
        $c_soap_username = $company->soap_username;
        if (strlen($c_soap_username) > 11) {
            $soap_username = substr($c_soap_username, 11, strlen($c_soap_username) - 11);
        }
        $total_documents = 0;
        $groups = $records->chunk(100);
        foreach ($groups as $group) {
            $documents = [];
            foreach ($group as $row) {
                $documents[] = [
                    "ruc_emisor" => $company->number,
                    "codigo_tipo_documento" => $row->document_type_id,
                    "serie_documento" => $row->series,
                    "numero_documento" => $row->number,
                    "fecha_de_emision" => $row->date_of_issue->format('Y-m-d'),
                    "total" => $row->total
                ];
            }

            $data = [
                "ruc_empresa" => $company->number,
                "sol_usuario" => $soap_username,
                "clave_usuario" => $company->soap_password,
                "comprobantes" => $documents
            ];

            $res = $this->sendValidate($data, $company);
            if ($res['success']) {
                $total_documents += $res['data']['count_documents'];
            }
        }

        return [
            'success' => true,
            'message' => 'Se validaron ' . $total_documents . ' comprobantes'
        ];
    }

    private function sendValidate($data, $company)
{
    try {
        // LOG 1: Ver qué datos estamos enviando
        \Log::info('=== DEBUGGING MASSIVE VALIDATE ===');
        \Log::info('Data being sent to ServiceData:', $data);
        
        $res = (new ServiceData)->massive_validate_cpe($data);
        
        // LOG 2: Ver la respuesta completa del servicio
        \Log::info('Raw response from ServiceData:', ['response' => $res]);
        
        // SOLUCIÓN: Verificar que la respuesta tenga la estructura esperada
        if (!$res || !is_array($res)) {
            \Log::error('ServiceData returned invalid response', ['response' => $res]);
            return [
                'success' => false,
                'message' => 'Invalid response from validation service'
            ];
        }
        
        // LOG 3: Verificar estructura específica
        \Log::info('Response structure check:', [
            'has_success' => isset($res['success']),
            'success_value' => $res['success'] ?? 'not_set',
            'has_data' => isset($res['data']),
            'data_type' => isset($res['data']) ? gettype($res['data']) : 'not_set',
            'has_comprobantes' => isset($res['data']['comprobantes']),
            'message' => $res['message'] ?? 'no_message'
        ]);
        
        // Verificar que 'success' exista y sea true
        if (!isset($res['success']) || !$res['success']) {
            \Log::error('ServiceData validation failed', ['response' => $res]);
            return [
                'success' => false,
                'message' => $res['message'] ?? 'Validation service failed'
            ];
        }
        
        // Verificar que 'data' y 'comprobantes' existan antes de iterar
        if (!isset($res['data']) || !is_array($res['data'])) {
            \Log::error('ServiceData response missing data array', ['response' => $res]);
            return [
                'success' => false,
                'message' => 'Invalid data structure in response'
            ];
        }
        
        if (!isset($res['data']['comprobantes']) || !is_array($res['data']['comprobantes'])) {
            \Log::error('ServiceData response missing comprobantes array', ['response' => $res]);
            return [
                'success' => false,
                'message' => 'Invalid comprobantes data in response'
            ];
        }
        
        // LOG 4: Ver cuántos comprobantes tenemos
        \Log::info('Comprobantes found:', [
            'count' => count($res['data']['comprobantes']),
            'first_comprobante' => $res['data']['comprobantes'][0] ?? 'none'
        ]);
        
        // Ahora sí podemos iterar de forma segura
        $processed_count = 0;
        foreach ($res['data']['comprobantes'] as $row) {
            if (!is_array($row)) {
                \Log::warning('Skipping non-array row:', ['row' => $row]);
                continue;
            }
            
            $state_type_id = null;
            $codigo_estado = $row['comprobante_estado_codigo'] ?? null;
            
            // LOG 5: Ver cada comprobante
            \Log::info('Processing comprobante:', [
                'codigo_estado' => $codigo_estado,
                'tipo_documento' => $row['codigo_tipo_documento'] ?? 'missing',
                'serie' => $row['serie_documento'] ?? 'missing',
                'numero' => $row['numero_documento'] ?? 'missing'
            ]);
            
            switch ($codigo_estado) {
                case '-':
                case '0':
                    $state_type_id = '01';
                    break;
                case '1':
                    $state_type_id = '05';
                    break;
                case '2':
                    $state_type_id = '11';
                    break;
            }
            
            if (!is_null($state_type_id) && isset($row['codigo_tipo_documento'], $row['serie_documento'], $row['numero_documento'], $row['fecha_de_emision'], $row['total'])) {
                $updated = Document::query()
                    ->where('soap_type_id', $company->soap_type_id)
                    ->where('document_type_id', $row['codigo_tipo_documento'])
                    ->where('series', $row['serie_documento'])
                    ->where('number', $row['numero_documento'])
                    ->where('date_of_issue', $row['fecha_de_emision'])
                    ->where('total', $row['total'])
                    ->update([
                        'state_type_id' => $state_type_id
                    ]);
                
                \Log::info('Document update result:', [
                    'updated_rows' => $updated,
                    'new_state' => $state_type_id,
                    'document' => $row['serie_documento'] . '-' . $row['numero_documento']
                ]);
                
                $processed_count++;
            } else {
                \Log::warning('Skipping comprobante due to missing data:', [
                    'state_type_id' => $state_type_id,
                    'has_required_fields' => [
                        'codigo_tipo_documento' => isset($row['codigo_tipo_documento']),
                        'serie_documento' => isset($row['serie_documento']),
                        'numero_documento' => isset($row['numero_documento']),
                        'fecha_de_emision' => isset($row['fecha_de_emision']),
                        'total' => isset($row['total'])
                    ]
                ]);
            }
        }
        
        \Log::info('Processing complete:', [
            'total_comprobantes' => count($res['data']['comprobantes']),
            'processed_count' => $processed_count,
            'cantidad_de_comprobantes' => $res['data']['cantidad_de_comprobantes'] ?? 'not_set'
        ]);
        
        return [
            'success' => true,
            'data' => [
                'count_documents' => $res['data']['cantidad_de_comprobantes'] ?? 0,
            ]
        ];
        
    } catch (\Exception $e) {
        \Log::error('Error in sendValidate method', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $data
        ]);
        
        return [
            'success' => false,
            'message' => 'Error processing validation: ' . $e->getMessage()
        ];
    }
}
}
