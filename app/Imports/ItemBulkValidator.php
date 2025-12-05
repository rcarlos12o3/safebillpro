<?php

namespace App\Imports;

use App\Models\Tenant\{
    Item,
    ItemUnitType,
    ItemImportTemp,
    Warehouse,
    ItemWarehouse
};
use App\Models\Tenant\Catalogs\UnitType;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemBulkValidator implements ToCollection
{
    protected $warehouseId;
    protected $userId;
    protected $batchId;
    protected $validatedRows = [];
    protected $existingItems = [];
    protected $existingCategories = [];
    protected $existingBrands = [];
    protected $rowNumber = 0;

    public function __construct($warehouseId, $userId)
    {
        $this->warehouseId = $warehouseId;
        $this->userId = $userId;
        $this->batchId = (string) Str::uuid();

        // Pre-load existing data for faster validation
        $this->loadExistingData();
    }

    /**
     * Process the collection from Excel
     */
    public function collection(Collection $rows)
    {
        // Skip header row (first row)
        unset($rows[0]);

        foreach ($rows as $index => $row) {
            $this->rowNumber = $index + 1; // Excel row number (1-based)
            $this->validateAndStoreRow($row);
        }
    }

    /**
     * Load existing data to memory for faster validation
     */
    protected function loadExistingData()
    {
        // Load existing items by internal_id
        $this->existingItems = Item::pluck('id', 'internal_id')->toArray();

        // Load categories by name
        $this->existingCategories = Category::pluck('id', 'name')
            ->mapWithKeys(function ($id, $name) {
                return [strtolower(trim($name)) => $id];
            })->toArray();

        // Load brands by name
        $this->existingBrands = Brand::pluck('id', 'name')
            ->mapWithKeys(function ($id, $name) {
                return [strtolower(trim($name)) => $id];
            })->toArray();
    }

    /**
     * Validate and store a single row
     */
    protected function validateAndStoreRow($row)
    {
        $rowData = $this->parseRow($row);
        $errors = [];
        $isValid = true;
        $action = null;
        $previewData = [];

        // Validate required fields
        if (empty($rowData['description'])) {
            $errors[] = 'La descripción del producto es requerida';
            $isValid = false;
        }

        if (empty($rowData['internal_id'])) {
            $errors[] = 'El código interno es requerido';
            $isValid = false;
        }

        // Check if item exists
        $existingItem = null;
        $existsInWarehouse = false;

        if (!empty($rowData['internal_id'])) {
            $existingItem = Item::where('internal_id', $rowData['internal_id'])->first();

            if ($existingItem) {
                // Verificar si el item ya tiene stock en ESTE almacén específico
                $itemWarehouse = $existingItem->warehouses()
                    ->where('warehouse_id', $this->warehouseId)
                    ->first();

                $existsInWarehouse = $itemWarehouse !== null;

                // La acción depende de si existe en ESTE almacén
                $action = $existsInWarehouse ? 'update' : 'create';

                if ($existsInWarehouse) {
                    $previewData['existing_description'] = $existingItem->description;
                    $previewData['existing_price'] = $existingItem->sale_unit_price;
                    $previewData['existing_stock'] = $itemWarehouse->stock ?? 0;
                }
            } else {
                $action = 'create';
            }
        }

        // Validate unit type
        if (!empty($rowData['unit_type_id'])) {
            $unitType = UnitType::where('id', $rowData['unit_type_id'])
                ->where('active', true)
                ->first();
            if (!$unitType) {
                $errors[] = "Tipo de unidad no válido: {$rowData['unit_type_id']}";
                $isValid = false;
            }
        } else {
            $errors[] = 'El tipo de unidad es requerido';
            $isValid = false;
        }

        // Validate currency
        if (!in_array($rowData['currency_type_id'], ['PEN', 'USD', 'EUR'])) {
            $errors[] = "Tipo de moneda no válido: {$rowData['currency_type_id']}";
            $isValid = false;
        }

        // Validate prices
        if (isset($rowData['sale_unit_price'])) {
            if (!is_numeric($rowData['sale_unit_price']) || $rowData['sale_unit_price'] < 0) {
                $errors[] = 'El precio de venta debe ser un número mayor o igual a 0';
                $isValid = false;
            } else {
                $previewData['new_sale_price'] = (float) $rowData['sale_unit_price'];
            }
        }

        if (isset($rowData['purchase_unit_price'])) {
            if (!is_numeric($rowData['purchase_unit_price']) || $rowData['purchase_unit_price'] < 0) {
                $errors[] = 'El precio de compra debe ser un número mayor o igual a 0';
                $isValid = false;
            }
        }

        // Validate IGV affectation types
        $validAffectations = ['10', '11', '12', '13', '14', '15', '16', '17', '20', '21', '30', '31', '32', '33', '34', '35', '36', '37', '40'];

        if (!empty($rowData['sale_affectation_igv_type_id'])) {
            if (!in_array($rowData['sale_affectation_igv_type_id'], $validAffectations)) {
                $errors[] = "Tipo de afectación IGV de venta no válido: {$rowData['sale_affectation_igv_type_id']}";
                $isValid = false;
            }
        }

        if (!empty($rowData['purchase_affectation_igv_type_id'])) {
            if (!in_array($rowData['purchase_affectation_igv_type_id'], $validAffectations)) {
                $errors[] = "Tipo de afectación IGV de compra no válido: {$rowData['purchase_affectation_igv_type_id']}";
                $isValid = false;
            }
        }

        // Validate stock
        if (isset($rowData['stock'])) {
            if (!is_numeric($rowData['stock']) || $rowData['stock'] < 0) {
                $errors[] = 'El stock debe ser un número mayor o igual a 0';
                $isValid = false;
            } else {
                $stockToAdd = (float) $rowData['stock'];

                if ($existsInWarehouse) {
                    // Item existe en ESTE almacén - El stock se SUMARÁ
                    $currentStock = $previewData['existing_stock'] ?? 0;
                    $previewData['stock_current'] = $currentStock;
                    $previewData['stock_to_add'] = $stockToAdd;
                    $previewData['stock_final'] = $currentStock + $stockToAdd;
                    $previewData['stock_action'] = 'sum'; // Indicador de que se sumará
                } else {
                    // Item nuevo O item existente pero primera vez en este almacén
                    $previewData['stock_to_add'] = $stockToAdd;
                    $previewData['stock_action'] = 'create'; // Indicador de stock inicial
                }
            }
        }

        // Validate stock_min
        if (isset($rowData['stock_min'])) {
            if (!is_numeric($rowData['stock_min']) || $rowData['stock_min'] < 0) {
                $errors[] = 'El stock mínimo debe ser un número mayor o igual a 0';
                $isValid = false;
            }
        }

        // Validate category (will be created if doesn't exist)
        if (!empty($rowData['category_name'])) {
            $categoryKey = strtolower(trim($rowData['category_name']));
            $previewData['category'] = $rowData['category_name'];

            if (!isset($this->existingCategories[$categoryKey])) {
                $previewData['category_action'] = 'Se creará nueva categoría';
            } else {
                $previewData['category_action'] = 'Categoría existente';
            }
        }

        // Validate brand (will be created if doesn't exist)
        if (!empty($rowData['brand_name'])) {
            $brandKey = strtolower(trim($rowData['brand_name']));
            $previewData['brand'] = $rowData['brand_name'];

            if (!isset($this->existingBrands[$brandKey])) {
                $previewData['brand_action'] = 'Se creará nueva marca';
            } else {
                $previewData['brand_action'] = 'Marca existente';
            }
        }

        // Validate image URL
        if (!empty($rowData['image_url'])) {
            if (!filter_var($rowData['image_url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'La URL de la imagen no es válida';
                $isValid = false;
            } else {
                $previewData['has_image'] = true;
                $previewData['image_url'] = $rowData['image_url'];
            }
        }

        // Validate lot data
        if (!empty($rowData['lot_code']) || !empty($rowData['date_of_due'])) {
            if (empty($rowData['lot_code'])) {
                $errors[] = 'Si especifica fecha de vencimiento, debe incluir código de lote';
                $isValid = false;
            }
            if (empty($rowData['date_of_due'])) {
                $errors[] = 'Si especifica código de lote, debe incluir fecha de vencimiento';
                $isValid = false;
            } else {
                // Validate date format
                $date = \DateTime::createFromFormat('Y-m-d', $rowData['date_of_due']);
                if (!$date || $date->format('Y-m-d') !== $rowData['date_of_due']) {
                    $errors[] = 'La fecha de vencimiento debe tener formato YYYY-MM-DD';
                    $isValid = false;
                } else {
                    $previewData['lot_info'] = "Lote: {$rowData['lot_code']} - Vence: {$rowData['date_of_due']}";
                }
            }
        }

        // Store the validation result in the temporary table
        ItemImportTemp::create([
            'user_id' => $this->userId,
            'batch_id' => $this->batchId,
            'warehouse_id' => $this->warehouseId,
            'row_number' => $this->rowNumber,
            'row_data' => $rowData,
            'is_valid' => $isValid,
            'validation_errors' => empty($errors) ? null : $errors,
            'status' => 'pending',
            'action' => $action,
            'preview_data' => $previewData
        ]);

        // Add to validated rows for return
        $this->validatedRows[] = [
            'row_number' => $this->rowNumber,
            'data' => $rowData,
            'is_valid' => $isValid,
            'errors' => $errors,
            'action' => $action,
            'preview' => $previewData
        ];
    }

    /**
     * Parse row data from Excel
     */
    protected function parseRow($row)
    {
        // Map Excel columns to array keys
        return [
            'description' => $this->getCellValue($row, 0),
            'internal_id' => $this->getCellValue($row, 1),
            'model' => $this->getCellValue($row, 2),
            'item_code' => $this->getCellValue($row, 3),
            'unit_type_id' => $this->getCellValue($row, 4),
            'currency_type_id' => $this->getCellValue($row, 5) ?: 'PEN',
            'sale_unit_price' => $this->getNumericValue($row, 6),
            'sale_affectation_igv_type_id' => $this->getCellValue($row, 7) ?: '10',
            'has_igv' => $this->getBooleanValue($row, 8),
            'purchase_unit_price' => $this->getNumericValue($row, 9),
            'purchase_affectation_igv_type_id' => $this->getCellValue($row, 10) ?: '10',
            'stock' => $this->getNumericValue($row, 11),
            'stock_min' => $this->getNumericValue($row, 12),
            'category_name' => $this->getCellValue($row, 13),
            'brand_name' => $this->getCellValue($row, 14),
            'name' => $this->getCellValue($row, 15),
            'second_name' => $this->getCellValue($row, 16),
            'lot_code' => $this->getCellValue($row, 17),
            'date_of_due' => $this->getCellValue($row, 18),
            'barcode' => $this->getCellValue($row, 19),
            'image_url' => $this->getCellValue($row, 20),
        ];
    }

    /**
     * Get cell value as string
     */
    protected function getCellValue($row, $index)
    {
        $value = $row[$index] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        return trim((string) $value);
    }

    /**
     * Get numeric value from cell
     */
    protected function getNumericValue($row, $index)
    {
        $value = $this->getCellValue($row, $index);
        if ($value === null) {
            return null;
        }
        // Remove any non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.-]/', '', $value);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Get boolean value from cell
     */
    protected function getBooleanValue($row, $index)
    {
        $value = $this->getCellValue($row, $index);
        if ($value === null) {
            return false;
        }
        $value = strtoupper($value);
        return in_array($value, ['SI', 'SÍ', 'YES', 'TRUE', '1']);
    }

    /**
     * Get the batch ID
     */
    public function getBatchId()
    {
        return $this->batchId;
    }

    /**
     * Get validated rows
     */
    public function getValidatedRows()
    {
        return $this->validatedRows;
    }

    /**
     * Get validation statistics
     */
    public function getStats()
    {
        $total = count($this->validatedRows);
        $valid = collect($this->validatedRows)->where('is_valid', true)->count();
        $invalid = $total - $valid;
        $toCreate = collect($this->validatedRows)->where('action', 'create')->count();
        $toUpdate = collect($this->validatedRows)->where('action', 'update')->count();

        return [
            'total' => $total,
            'valid' => $valid,
            'invalid' => $invalid,
            'to_create' => $toCreate,
            'to_update' => $toUpdate,
            'batch_id' => $this->batchId,
        ];
    }
}