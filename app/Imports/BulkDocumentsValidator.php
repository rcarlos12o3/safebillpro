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
            }
        }

        // Validar CUSTOMER_ID
        if (!isset($row['customer_id']) || empty($row['customer_id'])) {
            $validatedRow['is_valid'] = false;
            $validatedRow['errors'][] = 'CUSTOMER_ID es requerido';
        } else {
            $customer = Person::whereType('customers')
                ->where('id', $row['customer_id'])
                ->first();

            if (!$customer) {
                $validatedRow['is_valid'] = false;
                $validatedRow['errors'][] = "Cliente ID {$row['customer_id']} no encontrado";
            } else {
                $validatedRow['customer_name'] = $customer->name;
                $validatedRow['customer_number'] = $customer->number;
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
}
