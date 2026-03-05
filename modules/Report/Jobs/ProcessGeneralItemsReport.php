<?php

namespace Modules\Report\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Tenant\DownloadTray;
use Hyn\Tenancy\Environment;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Modules\Report\Exports\GeneralItemExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SaleNoteItem;

class ProcessGeneralItemsReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use StorageDocument;

    public $website_id;
    public $tray_id;
    public $request_data;
    public $format;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $website_id, int $tray_id, array $request_data, string $format)
    {
        $this->website_id = $website_id;
        $this->tray_id = $tray_id;
        $this->request_data = $request_data;
        $this->format = $format;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("ProcessGeneralItemsReport Start WebsiteId => " . $this->website_id);

        $website = Website::find($this->website_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($website);

        $tray = DownloadTray::find($this->tray_id);

        if (empty($tray)) {
            Log::debug("ProcessGeneralItemsReport: Tray not found. Tray ID: " . $this->tray_id);
            return;
        }

        try {
            $data_of_period = $this->getDataOfPeriod($this->request_data);
            $data_type = $this->getDataType($this->request_data);

            $document_type_id = $this->request_data['document_type_id'] ?? null;
            $d_start = $data_of_period['d_start'];
            $d_end = $data_of_period['d_end'];

            $person_id = $this->request_data['person_id'] ?? null;
            $type_person = $this->request_data['type_person'] ?? null;
            $item_id = $this->request_data['item_id'] ?? null;
            $brand_id = $this->request_data['brand_id'] ?? null;
            $category_id = $this->request_data['category_id'] ?? null;
            $user_id = $this->request_data['user_id'] ?? null;
            $user_type = $this->request_data['user_type'] ?? 'VENDEDOR';
            $web_platform_id = $this->request_data['web_platform_id'] ?? null;

            Log::debug("ProcessGeneralItemsReport: Fetching records...");

            // Obtener registros con eager loading optimizado
            $query = $this->dataItems(
                $d_start,
                $d_end,
                $document_type_id,
                $data_type,
                $person_id,
                $type_person,
                $item_id,
                $web_platform_id,
                $brand_id,
                $category_id,
                $user_id,
                $user_type
            );

            // Procesar en chunks para evitar consumir toda la memoria
            $records = $query->latest('id')->get();

            Log::debug("ProcessGeneralItemsReport: Records fetched. Count: " . $records->count());

            $type = ($this->request_data['type'] == 'sale') ? 'Ventas_' : 'Compras_';
            $request_apply_conversion_to_pen = $this->request_data['apply_conversion_to_pen'] ?? false;

            if ($this->format === 'pdf') {
                ini_set("pcre.backtrack_limit", "50000000");
                ini_set('memory_limit', '4026M');

                Log::debug("ProcessGeneralItemsReport: Generating PDF...");

                $pdf = PDF::loadView('report::general_items.report_pdf', compact("records", "type", "document_type_id", "request_apply_conversion_to_pen"))
                    ->setPaper('a4', 'landscape');

                $filename = 'Reporte_General_Productos_' . $type . Carbon::now()->format('YmdHis') . '-' . $tray->user_id;

                Log::debug("ProcessGeneralItemsReport: Uploading PDF...");

                $this->uploadStorage($filename, $pdf->output(), 'download_tray_pdf');

                $tray->file_name = $filename;
                $tray->path = 'download_tray_pdf';

            } else {
                ini_set('memory_limit', '4026M');
                ini_set("pcre.backtrack_limit", "50000000");

                Log::debug("ProcessGeneralItemsReport: Generating Excel...");

                $filename = 'Reporte_General_Productos_' . $type . Carbon::now()->format('YmdHis') . '-' . $tray->user_id;

                $generalItemExport = new GeneralItemExport();
                $generalItemExport
                    ->records($records)
                    ->type($this->request_data['type'])
                    ->document_type_id($document_type_id)
                    ->request_apply_conversion_to_pen($request_apply_conversion_to_pen);

                Log::debug("ProcessGeneralItemsReport: Uploading Excel...");

                $generalItemExport->store(DIRECTORY_SEPARATOR . "download_tray_xlsx" . DIRECTORY_SEPARATOR . $filename . '.xlsx', 'tenant');

                $tray->file_name = $filename;
                $tray->path = 'download_tray_xlsx';
            }

            $tray->date_end = Carbon::now();
            $tray->status = 'FINISHED';
            $tray->save();

            Log::debug("ProcessGeneralItemsReport: Finished successfully");

        } catch (Exception $e) {
            Log::error("ProcessGeneralItemsReport Error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            $tray->status = 'ERROR';
            $tray->date_end = Carbon::now();
            $tray->save();
        }

        Log::debug("ProcessGeneralItemsReport: Finish transaction");
    }

    /**
     * Optimized query with eager loading
     */
    private function dataItems($date_start, $date_end, $document_type_id, $data_type, $person_id, $type_person, $item_id, $web_platform_id, $brand_id, $category_id, $user_id, $user_type)
    {
        $documents_excluded = ['11', '09'];

        if ($document_type_id && $document_type_id == '80') {
            $relation = 'sale_note';

            $data = SaleNoteItem::with([
                'sale_note.user',
                'relation_item.lots',
                'relation_item.brand',
                'relation_item.category',
                'relation_item.web_platform',
                'relation_item.sets.individual_item'
            ])
            ->whereHas('sale_note', function($query) use($date_start, $date_end, $user_id, $documents_excluded){
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->latest();
                // No usar whereTypeUser() en Jobs - no hay usuario autenticado en la cola
                if(!empty($user_id)){
                    $query->where('user_id',$user_id);
                }
                $query->whereNotIn('state_type_id', $documents_excluded);
            });

        } else {
            $model = $data_type['model'];
            $relation = $data_type['relation'];

            $document_types = $document_type_id ? [$document_type_id] : ['01','03'];

            // Eager loading optimizado según el tipo
            $with_relations = [
                $relation . '.user',
                $relation . '.seller',
                'relation_item.lots',
                'relation_item.brand',
                'relation_item.category',
                'relation_item.web_platform',
                'relation_item.sets.individual_item'
            ];

            if ($model == DocumentItem::class) {
                // Document usa 'person' en vez de 'customer'
                $with_relations[] = $relation . '.person.department';
                $with_relations[] = $relation . '.person.province';
                $with_relations[] = $relation . '.person.district';
            } else {
                // Purchase usa 'supplier'
                $with_relations[] = $relation . '.supplier.person';
            }

            $data = $model::with($with_relations)
                ->whereHas($relation, function ($query) use ($date_start, $date_end, $document_types, $model, $documents_excluded) {
                    $query
                        ->whereBetween('date_of_issue', [$date_start, $date_end])
                        ->whereIn('document_type_id', $document_types)
                        ->latest();
                    // No usar whereTypeUser() en Jobs - no hay usuario autenticado en la cola
                    if ($model == 'App\Models\Tenant\DocumentItem') {
                        $query->whereNotIn('state_type_id', $documents_excluded);
                    }
                });

            if ($user_id && $user_type === 'CREADOR') {
                $data = $data->whereHas($relation.'.user', function($query) use($user_id){
                    $query->where('user_id', $user_id);
                });
            }
            if ($user_id && $user_type === 'VENDEDOR') {
                $data = $data->whereHas($relation . '.seller', function ($query) use ($user_id) {
                    $query->where('seller_id', $user_id);
                });
            }
        }

        if($person_id && $type_person){
            $column = ($type_person == 'customers') ? 'customer_id':'supplier_id';
            $data = $data->whereHas($relation, function($query) use($column, $person_id){
                $query->where($column, $person_id);
            });
        }

        if($item_id){
            $data = $data->where('item_id', $item_id);
        }

        if($web_platform_id || $brand_id || $category_id){
            $data = $data->whereHas('relation_item', function($q) use($web_platform_id, $brand_id, $category_id){
                if ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                }
                if ($brand_id) {
                    $q->where('brand_id', $brand_id);
                }
                if ($category_id) {
                    $q->where('category_id', $category_id);
                }
            });
        }

        return $data;
    }

    private function getDataOfPeriod($request)
    {
        $period = $request['period'] ?? 'month';
        $date_start = $request['date_start'] ?? Carbon::now()->format('Y-m-d');
        $date_end = $request['date_end'] ?? Carbon::now()->format('Y-m-d');
        $month_start = $request['month_start'] ?? Carbon::now()->format('Y-m');
        $month_end = $request['month_end'] ?? Carbon::now()->format('Y-m');

        $d_start = null;
        $d_end = null;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start)->startOfMonth()->format('Y-m-d');
                $d_end = Carbon::parse($month_start)->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start)->startOfMonth()->format('Y-m-d');
                $d_end = Carbon::parse($month_end)->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        return compact('d_start', 'd_end');
    }

    private function getDataType($request)
    {
        if($request['type'] == 'sale'){
            $data['model'] = DocumentItem::class;
            $data['relation'] = 'document';
        }else{
            $data['model'] = PurchaseItem::class;
            $data['relation'] = 'purchase';
        }

        return $data;
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::error("ProcessGeneralItemsReport failed: " . $exception->getMessage());

        $tray = DownloadTray::find($this->tray_id);
        if ($tray) {
            $tray->status = 'ERROR';
            $tray->date_end = Carbon::now();
            $tray->save();
        }
    }
}
