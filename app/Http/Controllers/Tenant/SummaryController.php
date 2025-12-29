<?php
namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\CoreFacturalo\Facturalo;
use App\Http\Requests\Tenant\{
    SummaryDocumentsRequest,
    SummaryRequest
};
use App\Http\Resources\Tenant\{
    DocumentCollection,
    SummaryResource,
    SummaryCollection
};
use App\Traits\SummaryTrait;
use App\Models\Tenant\{
    Document,
    Summary,
    Company
};
use Exception;
use App\Models\Tenant\Catalogs\SummaryStatusType;
use App\CoreFacturalo\Requests\Web\Validation\SummaryValidation;
use App\CoreFacturalo\Requests\Inputs\SummaryInput;


class SummaryController extends Controller
{
    use StorageDocument, SummaryTrait;
    
    public function __construct() {
        $this->middleware('input.request:summary,web', ['only' => ['store']]);
    }
    
    public function index() {
        return view('tenant.summaries.index');
    }
    
    public function records(Request $request) {
        
        // $records = Summary::where([ ['summary_status_type_id','1'], [ $request->column, 'like', "%{$request->value}%" ]])
        //     ->latest();

        $records = Summary::whereIn('summary_status_type_id', ['1', '2'])
                            ->where($request->column, 'like', "%{$request->value}%")
                            ->latest();
         
        return new SummaryCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión'
        ];
    }
    
    public function tables()
    {
        return [
            'summary_status_types' => SummaryStatusType::whereIn('id', ['1', '2'])->get(),
            'show_summary_status_type' => config('tenant.show_summary_status_type'),
        ];
    }

    public function documents(SummaryDocumentsRequest $request)
    {
        $company = Company::active();
        $date_of_reference = $request->input('date_of_reference');

        // Debug: Ver qué documentos hay en esa fecha
        $allDocumentsInDate = Document::where('date_of_issue', $date_of_reference)->get();
        \Log::info('=== DEBUG SUMMARY DOCUMENTS ===', [
            'date_of_reference' => $date_of_reference,
            'company_soap_type_id' => $company->soap_type_id,
            'total_documents_in_date' => $allDocumentsInDate->count(),
            'documents_by_group' => $allDocumentsInDate->groupBy('group_id')->map->count(),
            'documents_by_state' => $allDocumentsInDate->groupBy('state_type_id')->map->count(),
            'documents_by_soap_type' => $allDocumentsInDate->groupBy('soap_type_id')->map->count(),
            'documents_with_ticket_shipment' => $allDocumentsInDate->where('ticket_single_shipment', true)->count(),
            'sample_document' => $allDocumentsInDate->first() ? [
                'id' => $allDocumentsInDate->first()->id,
                'number' => $allDocumentsInDate->first()->number,
                'soap_type_id' => $allDocumentsInDate->first()->soap_type_id,
                'group_id' => $allDocumentsInDate->first()->group_id,
                'state_type_id' => $allDocumentsInDate->first()->state_type_id,
                'ticket_single_shipment' => $allDocumentsInDate->first()->ticket_single_shipment
            ] : null
        ]);

        $documents = Document::filterDocumentsForSummary($date_of_reference, $company->soap_type_id)->get();

        \Log::info('=== FILTERED DOCUMENTS ===', [
            'count' => $documents->count(),
            'document_numbers' => $documents->pluck('number')
        ]);

        // $documents = Document::query()
        //     ->where('date_of_issue', $request->input('date_of_reference'))
        //     ->where('soap_type_id', $company->soap_type_id)
        //     ->where('group_id', '02')
        //     ->where('state_type_id', '01')
        //     ->take(500)
        //     ->get();

        if (count($documents) === 0) {
            return [
                'success' => false,
                'message' => "No se encontraron documentos con la fecha {$date_of_reference}",
            ];
        }

        return [
            'success' => true,
            'data' => new DocumentCollection($documents)
        ];
        
    }
    
    public function store(SummaryRequest $request) {
        return $this->save($request);
    }
    
    public function status($summary_id) {

        try {

            return $this->query($summary_id);

        } catch (Exception $e) {

            // codigo personalizado cuando se lanza excepcion por problemas de sunat
            if($e->getCode() === 511){
                $this->updateUnknownErrorStatus($summary_id, $e);
            }

            return $this->getCustomErrorMessage($e->getMessage(), $e);
        }

    }

    public function destroy($voided_id)
    {
        $summary = Summary::find($voided_id);
        foreach ($summary->documents as $doc)
        {
            $doc->document->update([
                // 'state_type_id' => ($summary->summary_status_type_id === '1')?'01':'05'
                'state_type_id' => (in_array($summary->summary_status_type_id, ['1', '2']))?'01':'05'
            ]);
        }
        $summary->delete();

        return [
            'success' => true,
            'message' => 'Resumen eliminada con éxito'
        ];
    }
    
    public function record($id)
    {
        $record = new SummaryResource(Summary::findOrFail($id));

        return $record;
    }

    
    public function regularize($summary_id) {

        return DB::connection('tenant')->transaction(function() use($summary_id) {

            $summary = Summary::findOrFail($summary_id);

            foreach ($summary->documents as $doc)
            {
                $doc->document->update([
                    'state_type_id' => '05'
                ]);
            }

            $summary->update([
                'state_type_id' => '05',
                'manually_regularized' => true,
            ]);

            return [
                'success' => true,
                'message' => 'Los comprobantes fueron regularizados'
            ];

        });

    }


    public function cancelRegularize($summary_id) {

        Summary::findOrFail($summary_id)->update([
            'unknown_error_status_response' => false,
            'error_manually_regularized' => null
        ]);

        return [
            'success' => true,
            'message' => 'La operación fue cancelada'
        ];

    }


    /**
     * Obtener días con documentos pendientes en un rango de fechas
     */
    public function getDaysWithDocuments(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        $company = Company::active();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $days = Document::select('date_of_issue', DB::raw('COUNT(*) as total'))
            ->where('soap_type_id', $company->soap_type_id)
            ->where('group_id', '02')
            ->where('state_type_id', '01')
            ->whereBetween('date_of_issue', [$dateStart, $dateEnd])
            ->groupBy('date_of_issue')
            ->orderBy('date_of_issue', 'asc')
            ->get();

        return [
            'success' => true,
            'data' => $days,
            'total_days' => $days->count(),
            'total_documents' => $days->sum('total')
        ];
    }


    /**
     * Envío masivo de resúmenes por rango de fechas
     */
    public function storeMassive(Request $request)
    {
        $request->validate([
            'date_of_reference' => 'required|date',
            'summary_status_type_id' => 'required',
        ]);

        $company = Company::active();
        $dateOfReference = $request->input('date_of_reference');
        $summaryStatusTypeId = $request->input('summary_status_type_id');

        // Buscar documentos para esta fecha
        $documents = Document::filterDocumentsForSummary($dateOfReference, $company->soap_type_id)->get();

        if ($documents->isEmpty()) {
            return [
                'success' => false,
                'message' => "No hay documentos pendientes para {$dateOfReference}",
                'date' => $dateOfReference
            ];
        }

        try {
            // Preparar los datos para el resumen
            $inputs = [
                'date_of_reference' => $dateOfReference,
                'summary_status_type_id' => $summaryStatusTypeId,
                'documents' => $documents->map(function($doc) {
                    return ['id' => $doc->id];
                })->toArray()
            ];

            // Procesar los inputs igual que el middleware input.request:summary,web
            $inputs = SummaryValidation::validation($inputs);
            $inputs = SummaryInput::set($inputs, 'web');

            // Reemplazar el request con los inputs procesados
            $request->replace($inputs);

            $result = $this->save($request);

            return [
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'Resumen procesado',
                'date' => $dateOfReference,
                'documents_count' => $documents->count(),
                'data' => $result['data'] ?? null
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'date' => $dateOfReference,
                'documents_count' => $documents->count()
            ];
        }
    }


}
