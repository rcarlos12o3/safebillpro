<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tenant\Document;
use App\Models\Tenant\Person;
use App\Models\Tenant\Item;
use App\Models\Tenant\Series;
use App\Models\Tenant\BulkUploadTemp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkUploadController extends Controller
{
    /**
     * Display the bulk upload index page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tenant.bulk_upload.index');
    }

    /**
     * Get records for DataTable
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function records(Request $request)
    {
        // Aquí puedes agregar la lógica para listar los registros de carga masiva
        // Por ahora retornamos un array vacío
        return response()->json([
            'data' => []
        ]);
    }

    /**
     * Upload Excel file for bulk processing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            'type' => 'required|in:documents,customers,items', // Tipo de carga
            'date_of_issue' => 'required|date' // Fecha de emisión
        ]);

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            $dateOfIssue = $request->input('date_of_issue');

            if ($type === 'documents') {
                // Validar el Excel
                $validator = new \App\Imports\BulkDocumentsValidator();
                Excel::import($validator, $file);

                $validatedRows = $validator->getValidatedRows();
                $validCount = $validator->getValidCount();
                $errorCount = $validator->getErrorCount();

                // Generar batch_id único
                $batchId = Str::uuid()->toString();

                // Guardar en tabla temporal
                foreach ($validatedRows as $row) {
                    BulkUploadTemp::create([
                        'user_id' => auth()->id(),
                        'type' => $type,
                        'batch_id' => $batchId,
                        'date_of_issue' => $dateOfIssue,
                        'row_data' => $row['data'],
                        'is_valid' => $row['is_valid'],
                        'validation_errors' => $row['is_valid'] ? null : json_encode($row['errors']),
                        'status' => 'pending',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Archivo validado. {$validCount} registros válidos, {$errorCount} con errores.",
                    'data' => [
                        'batch_id' => $batchId,
                        'valid_count' => $validCount,
                        'error_count' => $errorCount,
                        'rows' => $validatedRows,
                    ]
                ]);

            } elseif ($type === 'customers') {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo de carga masiva de clientes próximamente disponible'
                ], 501);
            } elseif ($type === 'items') {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo de carga masiva de productos próximamente disponible'
                ], 501);
            }

        } catch (\Exception $e) {
            \Log::error('Error en carga masiva: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template file
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->input('type', 'documents');
        $itemIds = $request->input('items'); // IDs de productos separados por coma

        if ($type === 'documents' && $itemIds) {
            return $this->generateDocumentTemplate($itemIds);
        }

        return response()->json([
            'success' => false,
            'message' => 'Plantilla no disponible aún'
        ], 404);
    }

    /**
     * Generate custom document template with selected items
     *
     * @param string $itemIds
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function generateDocumentTemplate($itemIds)
    {
        $ids = explode(',', $itemIds);
        $items = Item::whereIn('id', $ids)->get();

        if ($items->isEmpty()) {
            abort(404, 'No se encontraron productos');
        }

        $export = new \App\Exports\BulkDocumentTemplateExport($items);

        return Excel::download($export, 'plantilla_carga_masiva_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Get upload history
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history()
    {
        // Aquí puedes agregar la lógica para obtener el historial
        // de cargas masivas realizadas

        return response()->json([
            'data' => []
        ]);
    }

    /**
     * Process confirmed batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string'
        ]);

        try {
            $batchId = $request->input('batch_id');

            // Obtener todos los registros válidos del batch
            $records = BulkUploadTemp::ofBatch($batchId)
                ->valid()
                ->pending()
                ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay registros válidos para procesar'
                ], 422);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($records as $record) {
                try {
                    DB::connection('tenant')->transaction(function () use ($record, &$successCount) {
                        $this->processRecord($record);
                        $successCount++;
                    });
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Fila {$record->id}: " . $e->getMessage();

                    // Marcar como error en la BD
                    $record->update([
                        'status' => 'error',
                        'process_error' => $e->getMessage()
                    ]);

                    \Log::error("Error procesando registro {$record->id}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => $errorCount === 0,
                'message' => $errorCount === 0
                    ? "¡Proceso completado! Se crearon {$successCount} documentos."
                    : "Proceso completado con errores. Exitosos: {$successCount}, Errores: {$errorCount}",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error procesando batch: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a single record and create document
     *
     * @param BulkUploadTemp $record
     * @return void
     */
    private function processRecord($record)
    {
        $row = $record->row_data;
        $dateOfIssue = $record->date_of_issue;

        // Aquí va la misma lógica que tenía en BulkDocumentsImport::processRow()
        // pero usando $row y $dateOfIssue del record temporal

        $item = Item::find($row['item_id']);
        $customer = Person::whereType('customers')->where('id', $row['customer_id'])->first();
        $serie = Series::where('number', $row['serie'])->first();
        $establishment = \App\Models\Tenant\Establishment::where('id', $serie->establishment_id)->first();

        // Obtener el siguiente número correlativo para la serie
        $lastNumber = Document::getLastNumberBySerie($serie->number);
        $nextNumber = $lastNumber + 1;

        // Usar el helper EstablishmentInput para construir los datos del establecimiento correctamente
        $establishmentData = \App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput::set($establishment->id);

        // Usar el helper PersonInput para construir los datos del cliente correctamente
        $customerData = \App\CoreFacturalo\Requests\Inputs\Common\PersonInput::set($customer->id);

        // Determinar group_id según tipo de documento
        $groupId = ($serie->document_type_id === '01') ? '01' : '02';

        // Obtener tipo de afectación del IGV del producto
        $affectationIgvType = $item->sale_affectation_igv_type_id ?? '10';

        $quantity = floatval($row['cantidad']);
        $unit_price = floatval($item->sale_unit_price);

        // Calcular según tipo de afectación
        if ($affectationIgvType === '10') {
            // GRAVADO: El precio incluye IGV, hay que separarlo
            $unit_value = round($unit_price / 1.18, 10);
            $subtotal = round($unit_value * $quantity, 2);
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);
            $has_igv = true;
        } else {
            // EXONERADO ('20') o INAFECTO ('30'): El precio NO incluye IGV
            $unit_value = $unit_price;
            $subtotal = round($unit_value * $quantity, 2);
            $igv = 0;
            $total = $subtotal;
            $has_igv = false;
        }

        $documentItem = [
            'item_id' => $item->id,
            'item' => [
                'id' => $item->id,
                'description' => $item->description,
                'item_type_id' => $item->item_type_id ?? '01',
                'internal_id' => $item->internal_id ?? null,
                'item_code' => $item->item_code ?? null,
                'item_code_gs1' => $item->item_code_gs1 ?? null,
                'unit_type_id' => $item->unit_type_id,
                'currency_type_id' => 'PEN',
                'sale_unit_price' => $unit_price,
                'purchase_unit_price' => $item->purchase_unit_price ?? 0,
                'has_igv' => $has_igv,
                'is_set' => $item->is_set ?? false,
                'amount_plastic_bag_taxes' => $item->amount_plastic_bag_taxes ?? 0,
                'lots' => [],
                'IdLoteSelected' => null,
                'model' => $item->model ?? null,
                'presentation' => [],
            ],
            'quantity' => $quantity,
            'unit_value' => $unit_value,
            'unit_price' => $unit_price,
            'affectation_igv_type_id' => $affectationIgvType,
            'total_base_igv' => $subtotal,
            'percentage_igv' => 18.00,
            'total_igv' => $igv,
            'system_isc_type_id' => null,
            'total_base_isc' => 0,
            'percentage_isc' => 0,
            'total_isc' => 0,
            'total_base_other_taxes' => 0,
            'percentage_other_taxes' => 0,
            'total_other_taxes' => 0,
            'total_taxes' => $igv,
            'price_type_id' => '01',
            'unit_type_id' => $item->unit_type_id,
            'total_value' => $subtotal,
            'total_charge' => 0,
            'total_discount' => 0,
            'total' => $total,
            'attributes' => [],
            'charges' => [],
            'discounts' => [],
        ];

        $documentData = [
            'type' => 'invoice',
            'user_id' => auth()->id(),
            'external_id' => Str::uuid()->toString(),
            'establishment_id' => $establishment->id,
            'establishment' => $establishmentData,
            'soap_type_id' => $serie->document_type_id,
            'state_type_id' => '01',
            'ubl_version' => '2.1',
            'group_id' => $groupId,
            'document_type_id' => $serie->document_type_id,
            'series_id' => $serie->id,
            'series' => $serie->number,
            'number' => $nextNumber,
            'date_of_issue' => $dateOfIssue,
            'time_of_issue' => now()->format('H:i:s'),
            'customer_id' => $customer->id,
            'customer' => $customerData,
            'currency_type_id' => 'PEN',
            'exchange_rate_sale' => 1,
            'total_prepayment' => 0,
            'total_charge' => 0,
            'total_discount' => 0,
            'total_exportation' => 0,
            'total_free' => 0,
            'total_taxed' => $affectationIgvType === '10' ? $subtotal : 0,
            'total_unaffected' => $affectationIgvType === '30' ? $subtotal : 0,
            'total_exonerated' => $affectationIgvType === '20' ? $subtotal : 0,
            'total_igv' => $igv,
            'total_base_isc' => 0,
            'total_isc' => 0,
            'total_base_other_taxes' => 0,
            'total_other_taxes' => 0,
            'total_plastic_bag_taxes' => 0,
            'total_taxes' => $igv,
            'total_value' => $subtotal,
            'total' => $total,
            'subtotal' => $total,
            'items' => [$documentItem],
            'charges' => [],
            'discounts' => [],
            'attributes' => [],
            'guides' => [],
            'payments' => [],
            'payment_condition_id' => '01', // Contado
            'fee' => [], // Sin cuotas (pago al contado)
            'actions' => ['format_pdf' => 'a4'],
            'invoice' => [
                'operation_type_id' => '0101',
                'date_of_due' => $dateOfIssue,
            ],
            'note' => null,
            'hotel' => null,
            'transport' => null,
            'perception' => null,
            'detraction' => null,
            'retention' => null,
            'prepayments' => [],
            'related' => [],
            'legends' => [],
            'additional_information' => null,
            'additional_data' => null,
            'plate_number' => null,
            'send_server' => false,
            'payment_method_type_id' => null,
            'reference_data' => null,
            'terms_condition' => '',
            'dispatches_relateds' => null,
            'sale_notes_relateds' => null,
            'is_editable' => true,
            'total_pending_payment' => 0,
            'tip' => null,
            'ticket_single_shipment' => false,
            'point_system' => false,
            'point_system_data' => null,
            'agent_id' => null,
            'dispatch_ticket_pdf' => false,
            'hotel_data_persons' => null,
            'source_module' => null,
            'hotel_rent_id' => null,
            'has_prepayment' => 0,
            'affectation_type_prepayment' => null,
            'was_deducted_prepayment' => 0,
            'pending_amount_prepayment' => 0,
            'total_igv_free' => 0,
            'data_json' => null,
            'created_from_bulk_upload' => true,
        ];

        $facturalo = new \App\CoreFacturalo\Facturalo();
        $facturalo->save($documentData);
        $facturalo->createXmlUnsigned();
        $facturalo->createPdf();

        try {
            $service_pse_xml = $facturalo->servicePseSendXml();
            $facturalo->signXmlUnsigned($service_pse_xml['xml_signed']);
            $facturalo->updateHash($service_pse_xml['hash']);
            $facturalo->updateQr();
            $facturalo->senderXmlSignedBill($service_pse_xml['code']);
        } catch (\Exception $e) {
            \Log::warning("No se pudo enviar a SUNAT: " . $e->getMessage());
        }

        $document = $facturalo->getDocument();

        // Actualizar record temporal
        $record->update([
            'status' => 'processed',
            'document_id' => $document->id
        ]);
    }

    /**
     * Delete temp records by batch_id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBatch(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string'
        ]);

        try {
            BulkUploadTemp::ofBatch($request->input('batch_id'))->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registros temporales eliminados'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registros: ' . $e->getMessage()
            ], 500);
        }
    }
}
