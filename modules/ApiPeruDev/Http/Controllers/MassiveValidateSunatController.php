<?php

namespace Modules\ApiPeruDev\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\CoreFacturalo\Services\IntegratedQuery\{
    AuthApi,
    ValidateCpe,
};

class MassiveValidateSunatController extends Controller
{
    protected $access_token;

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

        // Validar que tenga configurado Client ID y Secret
        if (!$company->integrated_query_client_id || !$company->integrated_query_client_secret) {
            return [
                'success' => false,
                'message' => 'Debe configurar Client ID y Client Secret en Configuración > Empresa > Consulta integrada de CPE'
            ];
        }

        // Obtener token de SUNAT
        $auth_api = (new AuthApi())->getToken();
        if (!$auth_api['success']) {
            return $auth_api;
        }

        $this->access_token = $auth_api['data']['access_token'];

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
            ->whereIn('state_type_id', ['01', '03', '05', '07', '09', '11', '13'])
            ->where('document_type_id', $document_type_id)
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->get();

        if ($records->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No se encontraron documentos para validar en el periodo seleccionado'
            ];
        }

        \Log::info('=== MASSIVE VALIDATE SUNAT START ===');
        \Log::info('Total documents to validate: ' . $records->count());

        $total_documents = 0;
        $total_updated = 0;
        $total_errors = 0;
        $groups = $records->chunk(100);

        foreach ($groups as $group_index => $group) {
            \Log::info("Processing group " . ($group_index + 1) . " of " . $groups->count());

            foreach ($group as $document) {
                try {
                    $validate_cpe = new ValidateCpe(
                        $this->access_token,
                        $company->number,
                        $document->document_type_id,
                        $document->series,
                        $document->number,
                        $document->date_of_issue,
                        $document->total
                    );

                    $response = $validate_cpe->search();

                    if ($response['success']) {
                        $sunat_state_type_id = $response['data']['state_type_id'];

                        // Solo actualizar si el estado es diferente
                        if ($sunat_state_type_id && $document->state_type_id !== $sunat_state_type_id) {
                            $document->update([
                                'state_type_id' => $sunat_state_type_id
                            ]);
                            $total_updated++;

                            \Log::info("Document updated: {$document->series}-{$document->number} from {$document->state_type_id} to {$sunat_state_type_id}");
                        }

                        $total_documents++;
                    } else {
                        $total_errors++;
                        \Log::warning("Error validating {$document->series}-{$document->number}: " . ($response['message'] ?? 'Unknown error'));
                    }

                    // Pausa para evitar rate limiting (0.2 segundos entre consultas)
                    usleep(200000);

                } catch (\Exception $e) {
                    $total_errors++;
                    \Log::error("Exception validating document {$document->id}: " . $e->getMessage());
                }
            }
        }

        \Log::info('=== MASSIVE VALIDATE SUNAT END ===');
        \Log::info("Results - Total: {$total_documents}, Updated: {$total_updated}, Errors: {$total_errors}");

        return [
            'success' => true,
            'message' => "Validación completada. Documentos consultados: {$total_documents}, Actualizados: {$total_updated}, Errores: {$total_errors}",
            'data' => [
                'total_documents' => $total_documents,
                'total_updated' => $total_updated,
                'total_errors' => $total_errors
            ]
        ];
    }
}
