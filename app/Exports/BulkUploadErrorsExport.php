<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Tenant\BulkUploadTemp;

class BulkUploadErrorsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Obtener todos los registros con errores del batch
        $records = BulkUploadTemp::ofBatch($this->batchId)
            ->where(function($query) {
                $query->where('is_valid', false)
                      ->orWhere('status', 'error');
            })
            ->orderBy('id')
            ->get();

        return $records->map(function($record) {
            $row = $record->row_data;

            // Errores de validación
            $validationErrors = '';
            if (!$record->is_valid && $record->validation_errors) {
                $errors = json_decode($record->validation_errors, true);
                $validationErrors = is_array($errors) ? implode('; ', $errors) : $record->validation_errors;
            }

            // Errores de procesamiento
            $processError = $record->process_error ?? '';

            // Combinar todos los errores
            $allErrors = trim($validationErrors . ($validationErrors && $processError ? '; ' : '') . $processError);

            return [
                'FILA' => $record->id,
                'DOCUMENTO_ID' => $row['documento_id'] ?? '',
                'ITEM_ID' => $row['item_id'] ?? '',
                'PRODUCTO' => $row['producto'] ?? '',
                'SERIE' => $row['serie'] ?? '',
                'TIPO_DOC' => $row['tipo_documento'] ?? '',
                'NUM_DOC' => $row['numero_documento'] ?? '',
                'CLIENTE' => $row['nombre_cliente'] ?? '',
                'CANTIDAD' => $row['cantidad'] ?? '',
                'ESTADO' => strtoupper($record->status),
                'ERRORES' => $allErrors,
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'FILA',
            'DOCUMENTO_ID',
            'ITEM_ID',
            'PRODUCTO',
            'SERIE',
            'TIPO_DOC',
            'NUM_DOC',
            'CLIENTE',
            'CANTIDAD',
            'ESTADO',
            'ERRORES',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0000'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,  // FILA
            'B' => 15, // DOCUMENTO_ID
            'C' => 10, // ITEM_ID
            'D' => 30, // PRODUCTO
            'E' => 12, // SERIE
            'F' => 10, // TIPO_DOC
            'G' => 15, // NUM_DOC
            'H' => 30, // CLIENTE
            'I' => 10, // CANTIDAD
            'J' => 12, // ESTADO
            'K' => 50, // ERRORES
        ];
    }
}
