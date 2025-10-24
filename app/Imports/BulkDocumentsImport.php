<?php

namespace App\Imports;

use App\CoreFacturalo\Facturalo;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BulkDocumentsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $success_count = 0;
    protected $error_count = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque la fila 1 es el encabezado y el índice empieza en 0

            try {
                DB::connection('tenant')->transaction(function () use ($row, $rowNumber) {
                    $this->processRow($row, $rowNumber);
                });

                $this->success_count++;

            } catch (\Exception $e) {
                $this->error_count++;
                $this->errors[] = "Fila {$rowNumber}: " . $e->getMessage();

                // Log para debugging
                \Log::error("Error en fila {$rowNumber}: " . $e->getMessage(), [
                    'row' => $row->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    protected function processRow($row, $rowNumber)
    {
        // Debug: Log de la fila completa
        \Log::info("Procesando fila {$rowNumber}", ['data' => $row->toArray()]);

        // Validar campos requeridos
        if (!isset($row['item_id']) || empty($row['item_id'])) {
            throw new \Exception("ITEM_ID es requerido. Columnas disponibles: " . implode(', ', array_keys($row->toArray())));
        }

        if (!isset($row['serie']) || empty($row['serie'])) {
            throw new \Exception("SERIE es requerida");
        }

        if (!isset($row['customer_id']) || empty($row['customer_id'])) {
            throw new \Exception("CUSTOMER_ID es requerido");
        }

        if (!isset($row['cantidad']) || empty($row['cantidad']) || $row['cantidad'] <= 0) {
            throw new \Exception("CANTIDAD debe ser mayor a 0");
        }

        // Buscar el producto
        $item = Item::find($row['item_id']);
        if (!$item) {
            throw new \Exception("Producto con ID {$row['item_id']} no encontrado");
        }

        // Buscar el cliente
        $customer = Person::whereType('customers')
            ->where('id', $row['customer_id'])
            ->first();

        if (!$customer) {
            throw new \Exception("Cliente con ID {$row['customer_id']} no encontrado");
        }

        // Buscar la serie
        $serie = Series::where('number', $row['serie'])->first();
        if (!$serie) {
            throw new \Exception("Serie {$row['serie']} no encontrada");
        }

        // Obtener establecimiento
        $establishment = Establishment::where('id', $serie->establishment_id)->first();
        if (!$establishment) {
            throw new \Exception("Establecimiento no encontrado para la serie {$row['serie']}");
        }

        // Validar TOTAL si está presente (para detectar cambios en precio)
        if (!empty($row['total'])) {
            $expectedTotal = round($item->sale_unit_price * $row['cantidad'], 2);
            $providedTotal = round($row['total'], 2);

            if (abs($expectedTotal - $providedTotal) > 0.01) {
                throw new \Exception("TOTAL no coincide. Esperado: {$expectedTotal}, Recibido: {$providedTotal}. Posible modificación de precio.");
            }
        }

        // Preparar datos del documento
        $quantity = floatval($row['cantidad']);
        $unit_price = floatval($item->sale_unit_price);

        // Calcular valores con IGV (asumiendo 18%)
        $unit_value = round($unit_price / 1.18, 10);
        $subtotal = round($unit_value * $quantity, 2);
        $igv = round($subtotal * 0.18, 2);
        $total = round($subtotal + $igv, 2);

        // Determinar tipo de documento basado en la serie
        $document_type_id = $serie->document_type_id;

        // Construir item para el documento
        $documentItem = [
            'item_id' => $item->id,
            'item' => [
                'id' => $item->id,
                'description' => $item->description,
                'unit_type_id' => $item->unit_type_id,
                'currency_type_id' => 'PEN',
                'sale_unit_price' => $unit_price,
            ],
            'quantity' => $quantity,
            'unit_value' => $unit_value,
            'unit_price' => $unit_price,
            'affectation_igv_type_id' => $item->sale_affectation_igv_type_id ?? '10',
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

        // Preparar datos del documento completo
        $documentData = [
            'type' => 'invoice', // Tipo requerido por Facturalo
            'establishment_id' => $establishment->id,
            'document_type_id' => $document_type_id,
            'series_id' => $serie->id,
            'date_of_issue' => Carbon::now()->format('Y-m-d'),
            'time_of_issue' => Carbon::now()->format('H:i:s'),
            'customer_id' => $customer->id,
            'currency_type_id' => 'PEN',
            'exchange_rate_sale' => 1,
            'total_prepayment' => 0,
            'total_charge' => 0,
            'total_discount' => 0,
            'total_exportation' => 0,
            'total_free' => 0,
            'total_taxed' => $subtotal,
            'total_unaffected' => 0,
            'total_exonerated' => 0,
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
            'operation_type_id' => '0101',
            'date_of_due' => Carbon::now()->format('Y-m-d'),
            'items' => [$documentItem],
            'charges' => [],
            'discounts' => [],
            'attributes' => [],
            'guides' => [],
            'payments' => [],
            'actions' => [
                'format_pdf' => 'a4'
            ],
            'created_from_bulk_upload' => true,
        ];

        // Crear documento usando Facturalo
        $facturalo = new Facturalo();
        $facturalo->save($documentData);
        $facturalo->createXmlUnsigned();
        $facturalo->createPdf();

        // Intentar enviar a SUNAT (opcional, puede fallar sin detener el proceso)
        try {
            $service_pse_xml = $facturalo->servicePseSendXml();
            $facturalo->signXmlUnsigned($service_pse_xml['xml_signed']);
            $facturalo->updateHash($service_pse_xml['hash']);
            $facturalo->updateQr();
            $facturalo->senderXmlSignedBill($service_pse_xml['code']);
        } catch (\Exception $e) {
            // Log pero no detener el proceso
            \Log::warning("No se pudo enviar a SUNAT fila {$rowNumber}: " . $e->getMessage());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->success_count;
    }

    public function getErrorCount()
    {
        return $this->error_count;
    }
}
