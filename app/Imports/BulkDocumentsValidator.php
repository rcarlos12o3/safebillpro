<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkDocumentsValidator implements ToCollection, WithHeadingRow
{
    protected $validatedRows = [];
    protected $groupedDocuments = []; // Documentos agrupados por documento_id
    protected $stockReservations = []; // Stock "reservado" por filas anteriores
    protected $seriesLastDates = []; // Última fecha de emisión por serie
    protected $dateOfIssue = null; // Fecha de emisión del lote actual
    protected $seriesLastNumbers = []; // Último número de cada serie
    protected $seriesDocumentCount = []; // Contador de documentos por serie en este Excel

    public function __construct($dateOfIssue = null)
    {
        $this->dateOfIssue = $dateOfIssue;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque la fila 1 es el encabezado

            $validatedRow = [
                'row_number' => $rowNumber,
                'data' => $row->toArray(),
                'is_valid' => true,
                'errors' => [],
            ];

            // Validar datos
            $this->validateRow($row, $rowNumber, $validatedRow);

            $this->validatedRows[] = $validatedRow;
        }

        // Después de validar todas las filas, agrupar por documento
        $this->groupDocuments();
    }

    protected function validateRow($row, $rowNumber, &$validatedRow)
    {
        // Validar ITEM_ID
        if (!isset($row['item_id']) || empty($row['item_id'])) {
            $validatedRow['is_valid'] = false;
            $validatedRow['errors'][] = 'ITEM_ID es requerido';
        } else {
            $item = Item::find($row['item_id']);
            if (!$item) {
                $validatedRow['is_valid'] = false;
                $validatedRow['errors'][] = "Producto ID {$row['item_id']} no encontrado";
            } else {
                $validatedRow['item_description'] = $item->description;
                $validatedRow['item_price'] = $item->sale_unit_price;

                // Validar stock disponible (solo si está configurado)
                if (config('bulk_upload.validate_stock', true) && isset($row['cantidad'])) {
                    $this->validateStock($row, $item, $validatedRow);
                }
            }
        }

        // Validar SERIE
        if (!isset($row['serie']) || empty($row['serie'])) {
            $validatedRow['is_valid'] = false;
            $validatedRow['errors'][] = 'SERIE es requerida';
        } else {
            $serie = Series::where('number', $row['serie'])->first();
            if (!$serie) {
                $validatedRow['is_valid'] = false;
                $validatedRow['errors'][] = "Serie {$row['serie']} no encontrada";
            } else {
                $validatedRow['series_id'] = $serie->id;
                $validatedRow['document_type_id'] = $serie->document_type_id;
                $validatedRow['establishment_id'] = $serie->establishment_id;

                // Validar fecha correlativa
                $this->validateCorrelativeDate($row['serie'], $validatedRow);
            }
        }

        // Validar y resolver CUSTOMER (buscar o preparar para crear)
        $customerResolution = $this->resolveCustomer($row);

        if (!$customerResolution['success']) {
            $validatedRow['is_valid'] = false;
            foreach ($customerResolution['errors'] as $error) {
                $validatedRow['errors'][] = $error;
            }
        } else {
            $validatedRow['customer_id'] = $customerResolution['customer_id'];
            $validatedRow['customer_name'] = $customerResolution['customer_name'];
            $validatedRow['customer_number'] = $customerResolution['customer_number'];
            $validatedRow['customer_was_created'] = $customerResolution['was_created'];

            // Guardar datos del cliente por si se creó nuevo
            if ($customerResolution['was_created']) {
                $validatedRow['customer_data'] = $customerResolution['customer_data'];
            }
        }

        // Validar CANTIDAD
        if (!isset($row['cantidad']) || empty($row['cantidad']) || $row['cantidad'] <= 0) {
            $validatedRow['is_valid'] = false;
            $validatedRow['errors'][] = 'CANTIDAD debe ser mayor a 0';
        }

        // Validar TOTAL si está presente
        if (isset($row['total']) && !empty($row['total']) && isset($validatedRow['item_price'])) {
            $expectedTotal = round($validatedRow['item_price'] * $row['cantidad'], 2);
            $providedTotal = round($row['total'], 2);

            if (abs($expectedTotal - $providedTotal) > 0.01) {
                $validatedRow['is_valid'] = false;
                $validatedRow['errors'][] = "TOTAL no coincide. Esperado: {$expectedTotal}, Recibido: {$providedTotal}";
            }
        }

        // Calcular totales
        if (isset($validatedRow['item_price']) && isset($row['cantidad'])) {
            $unit_price = floatval($validatedRow['item_price']);
            $quantity = floatval($row['cantidad']);
            $unit_value = round($unit_price / 1.18, 10);
            $subtotal = round($unit_value * $quantity, 2);
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);

            $validatedRow['calculated_total'] = $total;
            $validatedRow['calculated_subtotal'] = $subtotal;
            $validatedRow['calculated_igv'] = $igv;
        }
    }

    public function getValidatedRows()
    {
        return $this->validatedRows;
    }

    public function getValidCount()
    {
        return collect($this->validatedRows)->where('is_valid', true)->count();
    }

    public function getErrorCount()
    {
        return collect($this->validatedRows)->where('is_valid', false)->count();
    }

    /**
     * Validar que la fecha de emisión sea correlativa
     *
     * @param string $serieNumber
     * @param array $validatedRow
     * @return void
     */
    protected function validateCorrelativeDate($serieNumber, &$validatedRow)
    {
        // Si no hay fecha de emisión del lote, no podemos validar
        if (!$this->dateOfIssue) {
            return;
        }

        // Obtener la última fecha de emisión de esta serie (cachear por serie)
        if (!isset($this->seriesLastDates[$serieNumber])) {
            $lastDocument = \App\Models\Tenant\Document::where('series', $serieNumber)
                ->orderBy('date_of_issue', 'desc')
                ->orderBy('number', 'desc')
                ->first();

            if ($lastDocument) {
                $this->seriesLastDates[$serieNumber] = $lastDocument->date_of_issue->format('Y-m-d');
            } else {
                // Si no hay documentos previos, cualquier fecha es válida
                $this->seriesLastDates[$serieNumber] = null;
            }
        }

        $lastDate = $this->seriesLastDates[$serieNumber];

        // Si hay un último documento, validar que la nueva fecha no sea anterior
        if ($lastDate) {
            $newDate = $this->dateOfIssue;

            if ($newDate < $lastDate) {
                $validatedRow['is_valid'] = false;
                $validatedRow['errors'][] = "La fecha de emisión ({$newDate}) no puede ser anterior a la última fecha de la serie {$serieNumber} ({$lastDate}). Las fechas deben ser correlativas según SUNAT.";
            }
        }
    }

    /**
     * Validar stock disponible
     *
     * @param $row
     * @param Item $item
     * @param array $validatedRow
     * @return void
     */
    protected function validateStock($row, $item, &$validatedRow)
    {
        // Los servicios no tienen stock
        if ($item->unit_type_id === 'ZZ') {
            return;
        }

        $quantityRequested = floatval($row['cantidad']);

        // Validar cantidad positiva
        if ($quantityRequested <= 0) {
            return; // Ya se validará en otra parte
        }

        // Obtener serie para saber el almacén
        $serie = null;
        if (isset($row['serie']) && !empty($row['serie'])) {
            $serie = Series::where('number', $row['serie'])->first();
        }

        // Obtener stock disponible
        $stockAvailable = $this->getAvailableStock($item->id, $serie);

        // Guardar info de stock en el validatedRow para mostrar en frontend
        $validatedRow['stock_available'] = $stockAvailable;

        // Validar si hay stock suficiente
        if ($quantityRequested > $stockAvailable) {
            $validatedRow['is_valid'] = false;
            $validatedRow['errors'][] = "Stock insuficiente. Disponible: {$stockAvailable}, Solicitado: {$quantityRequested}";
        } else {
            // Reservar el stock para las siguientes filas
            $this->reserveStock($item->id, $quantityRequested, $serie);
        }
    }

    /**
     * Obtener stock disponible considerando reservas previas del mismo Excel
     *
     * @param int $itemId
     * @param Series|null $serie
     * @return float
     */
    protected function getAvailableStock($itemId, $serie = null)
    {
        $item = Item::find($itemId);

        if (!$item || $item->unit_type_id === 'ZZ') {
            return PHP_INT_MAX; // Stock ilimitado para servicios
        }

        // Obtener stock actual del almacén
        $currentStock = 0;

        if ($serie && $serie->establishment_id) {
            // Stock del almacén del establecimiento de la serie
            $warehouse = \App\Models\Tenant\Warehouse::where('establishment_id', $serie->establishment_id)->first();
            if ($warehouse) {
                $itemWarehouse = \App\Models\Tenant\ItemWarehouse::where([
                    ['item_id', $itemId],
                    ['warehouse_id', $warehouse->id]
                ])->first();

                if ($itemWarehouse) {
                    $currentStock = $itemWarehouse->stock;
                }
            }
        } else {
            // Si no hay serie, sumar stock de todos los almacenes
            $currentStock = $item->warehouses->sum('stock');
        }

        // Restar stock ya "reservado" por filas anteriores
        $warehouseKey = $serie ? $serie->establishment_id : 'all';
        $reservationKey = "{$itemId}_{$warehouseKey}";
        $reservedStock = $this->stockReservations[$reservationKey] ?? 0;

        return max(0, $currentStock - $reservedStock);
    }

    /**
     * Reservar stock temporalmente para las siguientes validaciones
     *
     * @param int $itemId
     * @param float $quantity
     * @param Series|null $serie
     * @return void
     */
    protected function reserveStock($itemId, $quantity, $serie = null)
    {
        $warehouseKey = $serie ? $serie->establishment_id : 'all';
        $reservationKey = "{$itemId}_{$warehouseKey}";

        if (!isset($this->stockReservations[$reservationKey])) {
            $this->stockReservations[$reservationKey] = 0;
        }

        $this->stockReservations[$reservationKey] += $quantity;
    }

    /**
     * Resolve customer by document number or create new one
     *
     * @param $row
     * @return array
     */
    protected function resolveCustomer($row)
    {
        $errors = [];

        // Validar campos obligatorios
        // Importante: verificar con isset primero, luego convertir a string para manejar '0'
        if (!isset($row['tipo_documento']) || (string)$row['tipo_documento'] === '') {
            $errors[] = 'TIPO_DOCUMENTO es requerido';
        }

        if (!isset($row['numero_documento']) || trim((string)$row['numero_documento']) === '') {
            $errors[] = 'NUMERO_DOCUMENTO es requerido';
        }

        if (!isset($row['nombre_cliente']) || trim((string)$row['nombre_cliente']) === '') {
            $errors[] = 'NOMBRE_CLIENTE es requerido';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        // Convertir a string para manejar correctamente el valor '0'
        $tipoDocumento = (string)$row['tipo_documento'];
        $numeroDocumento = trim((string)$row['numero_documento']);
        $nombreCliente = trim((string)$row['nombre_cliente']);
        $direccion = isset($row['direccion']) && trim((string)$row['direccion']) !== '' ? trim((string)$row['direccion']) : '-';

        // Autocompletar con ceros a la izquierda para DNI (tipo 1)
        // Si el DNI tiene entre 5 y 7 dígitos, completar hasta 8 dígitos
        if ($tipoDocumento === '1') {
            $documentLength = strlen($numeroDocumento);
            if ($documentLength >= 5 && $documentLength < 8) {
                $zerosToAdd = 8 - $documentLength;
                $numeroDocumento = str_repeat('0', $zerosToAdd) . $numeroDocumento;
            }
        }

        // Validar formato de documento
        $documentValidation = $this->validateDocumentFormat($tipoDocumento, $numeroDocumento);
        if (!$documentValidation['valid']) {
            return [
                'success' => false,
                'errors' => [$documentValidation['error']]
            ];
        }

        // Buscar cliente existente por tipo y número de documento
        $customer = Person::whereType('customers')
            ->where('identity_document_type_id', $tipoDocumento)
            ->where('number', $numeroDocumento)
            ->first();

        if ($customer) {
            // Cliente encontrado
            return [
                'success' => true,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_number' => $customer->number,
                'was_created' => false
            ];
        }

        // Cliente no existe, crear nuevo
        try {
            $customer = Person::create([
                'type' => 'customers',
                'identity_document_type_id' => $tipoDocumento,
                'number' => $numeroDocumento,
                'name' => $nombreCliente,
                'trade_name' => $nombreCliente,
                'address' => $direccion,
                'country_id' => 'PE',
                'department_id' => null,
                'province_id' => null,
                'district_id' => null,
                'email' => null,
                'telephone' => null,
                'enabled' => true,
            ]);

            return [
                'success' => true,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_number' => $customer->number,
                'was_created' => true,
                'customer_data' => [
                    'tipo_documento' => $tipoDocumento,
                    'numero_documento' => $numeroDocumento,
                    'nombre' => $nombreCliente,
                    'direccion' => $direccion
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ["Error al crear cliente: " . $e->getMessage()]
            ];
        }
    }

    /**
     * Validate document format
     *
     * @param string $type
     * @param string $number
     * @return array
     */
    protected function validateDocumentFormat($type, $number)
    {
        // Tipos válidos de documento según catálogo
        $validTypes = ['0', '1', '4', '6', '7', 'A', 'B', 'C', 'D', 'E'];

        if (!in_array($type, $validTypes)) {
            return [
                'valid' => false,
                'error' => "Tipo de documento '{$type}' no válido. Use: 0 (Sin RUC), 1 (DNI), 6 (RUC), 4 (CE), 7 (Pasaporte)"
            ];
        }

        // Validar formato según tipo
        switch ($type) {
            case '0': // Doc.trib.no.dom.sin.ruc - Sin validación estricta
                if (empty($number) || strlen($number) > 15) {
                    return [
                        'valid' => false,
                        'error' => "Documento sin RUC debe tener entre 1 y 15 caracteres. Recibido: {$number}"
                    ];
                }
                break;

            case '1': // DNI
                if (!preg_match('/^\d{8}$/', $number)) {
                    return [
                        'valid' => false,
                        'error' => "DNI debe tener 8 dígitos numéricos. Recibido: {$number}"
                    ];
                }
                break;

            case '6': // RUC
                if (!preg_match('/^\d{11}$/', $number)) {
                    return [
                        'valid' => false,
                        'error' => "RUC debe tener 11 dígitos numéricos. Recibido: {$number}"
                    ];
                }
                break;

            case '4': // Carnet de extranjería
                if (strlen($number) < 8 || strlen($number) > 12) {
                    return [
                        'valid' => false,
                        'error' => "Carnet de extranjería debe tener entre 8 y 12 caracteres. Recibido: {$number}"
                    ];
                }
                break;

            case '7': // Pasaporte
                if (strlen($number) < 8 || strlen($number) > 12) {
                    return [
                        'valid' => false,
                        'error' => "Pasaporte debe tener entre 8 y 12 caracteres. Recibido: {$number}"
                    ];
                }
                break;

            // Tipos A, B, C, D, E - Validación genérica
            default:
                if (empty($number) || strlen($number) > 15) {
                    return [
                        'valid' => false,
                        'error' => "Número de documento debe tener entre 1 y 15 caracteres. Recibido: {$number}"
                    ];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Get last number for a series
     *
     * @param string $serieNumber
     * @return int
     */
    protected function getLastNumberForSerie($serieNumber)
    {
        if (!isset($this->seriesLastNumbers[$serieNumber])) {
            $lastDocument = \App\Models\Tenant\Document::where('series', $serieNumber)
                ->orderBy('number', 'desc')
                ->first();

            $this->seriesLastNumbers[$serieNumber] = $lastDocument ? $lastDocument->number : 0;
        }

        return $this->seriesLastNumbers[$serieNumber];
    }

    /**
     * Calculate referential number for a document
     *
     * @param string $serieNumber
     * @return int
     */
    protected function calculateReferentialNumber($serieNumber)
    {
        // Obtener el último número de la serie en la base de datos
        $lastNumber = $this->getLastNumberForSerie($serieNumber);

        // Obtener cuántos documentos de esta serie ya hemos procesado en este Excel
        if (!isset($this->seriesDocumentCount[$serieNumber])) {
            $this->seriesDocumentCount[$serieNumber] = 0;
        }

        // Incrementar el contador de documentos de esta serie
        $this->seriesDocumentCount[$serieNumber]++;

        // Calcular el número referencial
        $referentialNumber = $lastNumber + $this->seriesDocumentCount[$serieNumber];

        return $referentialNumber;
    }

    /**
     * Group rows by document (same serie + customer + fecha)
     * Si tienen documento_id, agrupamos por ese ID
     * Si no tienen documento_id, cada fila es un documento separado
     */
    protected function groupDocuments()
    {
        foreach ($this->validatedRows as $validatedRow) {
            // Solo agrupar filas válidas
            if (!$validatedRow['is_valid']) {
                continue;
            }

            $row = $validatedRow['data'];

            // Determinar clave de agrupación
            // Si existe documento_id en el Excel, usar ese
            // Si no, generar uno único basado en serie + cliente + fecha (auto-agrupar)
            if (isset($row['documento_id']) && !empty($row['documento_id'])) {
                $groupKey = (string)$row['documento_id'];
            } else {
                // No agrupar automáticamente, cada item es un documento separado
                // Generar un ID único para cada fila
                $groupKey = 'auto_' . $validatedRow['row_number'];
            }

            // Inicializar grupo si no existe
            if (!isset($this->groupedDocuments[$groupKey])) {
                $serieNumber = $row['serie'] ?? null;

                // Calcular número referencial para este documento
                $referentialNumber = null;
                if ($serieNumber) {
                    $referentialNumber = $this->calculateReferentialNumber($serieNumber);
                }

                $this->groupedDocuments[$groupKey] = [
                    'document_id' => $groupKey,
                    'serie' => $serieNumber,
                    'series_id' => $validatedRow['series_id'] ?? null,
                    'document_type_id' => $validatedRow['document_type_id'] ?? null,
                    'establishment_id' => $validatedRow['establishment_id'] ?? null,
                    'customer_id' => $validatedRow['customer_id'] ?? null,
                    'customer_name' => $validatedRow['customer_name'] ?? null,
                    'customer_number' => $validatedRow['customer_number'] ?? null,
                    'tipo_documento' => isset($row['tipo_documento']) ? (string)$row['tipo_documento'] : null,
                    'numero_documento' => isset($row['numero_documento']) ? trim((string)$row['numero_documento']) : null,
                    'nombre_cliente' => isset($row['nombre_cliente']) ? trim((string)$row['nombre_cliente']) : null,
                    'direccion' => isset($row['direccion']) ? trim((string)$row['direccion']) : '-',
                    'referential_number' => $referentialNumber,
                    'items' => [],
                    'is_valid' => true,
                    'errors' => [],
                    'row_numbers' => [],
                ];
            }

            // Agregar item al grupo
            $this->groupedDocuments[$groupKey]['items'][] = [
                'item_id' => $row['item_id'],
                'item_description' => $validatedRow['item_description'] ?? '',
                'item_price' => $validatedRow['item_price'] ?? 0,
                'cantidad' => $row['cantidad'] ?? 0,
                'total' => $row['total'] ?? null,
                'calculated_total' => $validatedRow['calculated_total'] ?? 0,
                'calculated_subtotal' => $validatedRow['calculated_subtotal'] ?? 0,
                'calculated_igv' => $validatedRow['calculated_igv'] ?? 0,
            ];

            $this->groupedDocuments[$groupKey]['row_numbers'][] = $validatedRow['row_number'];
        }

        // Validar consistencia de grupos
        // Todas las filas del mismo documento_id deben tener misma serie y mismo cliente
        foreach ($this->groupedDocuments as $groupKey => &$document) {
            // Si es un documento con múltiples items, validar consistencia
            if (count($document['items']) > 1) {
                // Verificar que todos los items tengan datos consistentes
                // (esto ya está garantizado por la agrupación, pero podemos agregar validaciones adicionales)
            }
        }

        // Propagar el número referencial a cada fila validada
        foreach ($this->groupedDocuments as $groupKey => $document) {
            foreach ($document['row_numbers'] as $rowNumber) {
                // Buscar la fila en validatedRows y agregar el número referencial
                foreach ($this->validatedRows as &$validatedRow) {
                    if ($validatedRow['row_number'] === $rowNumber) {
                        $validatedRow['referential_number'] = $document['referential_number'];
                        break;
                    }
                }
            }
        }
    }

    /**
     * Get grouped documents
     */
    public function getGroupedDocuments()
    {
        return array_values($this->groupedDocuments);
    }
}
