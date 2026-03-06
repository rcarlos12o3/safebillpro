<?php

namespace App\CoreFacturalo\WS\Zip;

use Illuminate\Support\Facades\Log;

/**
 * Class ZipFileDecompress.
 */
class ZipFileDecompress
{
    /**
     * Extract files.
     *
     * @param string        $content
     * @param callable|null $filter
     *
     * @return array
     */
    public function decompress($content, callable $filter = null)
    {
        // Validar que el contenido no esté vacío
        if (empty($content)) {
            Log::error('ZipFileDecompress: Empty content received from SUNAT');
            return [];
        }

        // LOGGING: Tamaño de la respuesta de SUNAT
        Log::info('ZipFileDecompress: Processing ZIP from SUNAT', [
            'content_size' => strlen($content),
            'content_first_bytes' => substr($content, 0, 20)
        ]);

        $temp = tempnam(sys_get_temp_dir(), time().'.zip');

        if ($temp === false) {
            Log::error('ZipFileDecompress: Failed to create temp file');
            return [];
        }

        $written = file_put_contents($temp, $content);
        if ($written === false) {
            Log::error('ZipFileDecompress: Failed to write content to temp file', ['temp' => $temp]);
            return [];
        }

        Log::debug('ZipFileDecompress: Temp file created', [
            'temp' => $temp,
            'bytes_written' => $written
        ]);

        $zip = new \ZipArchive();
        $output = [];
        $openResult = $zip->open($temp);

        if (true === $openResult) {
            Log::debug('ZipFileDecompress: ZIP opened successfully', [
                'num_files' => $zip->numFiles,
                'temp' => $temp
            ]);

            if ($zip->numFiles > 0) {
                $output = iterator_to_array($this->getFiles($zip, $filter));

                Log::info('ZipFileDecompress: Files extracted successfully', [
                    'extracted_count' => count($output),
                    'filenames' => array_column($output, 'filename')
                ]);

                // Loggear tamaño del primer archivo (el XML CDR)
                if (count($output) > 0) {
                    Log::debug('ZipFileDecompress: First file details', [
                        'filename' => $output[0]['filename'],
                        'content_size' => strlen($output[0]['content']),
                        'content_preview' => substr($output[0]['content'], 0, 100)
                    ]);
                }
            } else {
                Log::warning('ZipFileDecompress: ZIP file has 0 files inside', ['temp' => $temp]);
            }

            $zip->close();
        } else {
            Log::error('ZipFileDecompress: Failed to open ZIP file', [
                'temp' => $temp,
                'error_code' => $openResult,
                'file_exists' => file_exists($temp),
                'file_size' => file_exists($temp) ? filesize($temp) : 0,
                'zip_error_string' => $this->getZipErrorMessage($openResult)
            ]);
        }

        if (file_exists($temp)) {
            @unlink($temp); // @ para suprimir warning
        } else {
            Log::warning('ZipFileDecompress: Temp file disappeared before cleanup', ['temp' => $temp]);
        }

        return $output;
    }

    /**
     * Obtener mensaje de error legible de ZipArchive
     */
    private function getZipErrorMessage($code)
    {
        $errors = [
            \ZipArchive::ER_OK => 'No error',
            \ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
            \ZipArchive::ER_RENAME => 'Renaming temporary file failed',
            \ZipArchive::ER_CLOSE => 'Closing zip archive failed',
            \ZipArchive::ER_SEEK => 'Seek error',
            \ZipArchive::ER_READ => 'Read error',
            \ZipArchive::ER_WRITE => 'Write error',
            \ZipArchive::ER_CRC => 'CRC error',
            \ZipArchive::ER_ZIPCLOSED => 'Zip archive already closed',
            \ZipArchive::ER_NOENT => 'No such file',
            \ZipArchive::ER_EXISTS => 'File already exists',
            \ZipArchive::ER_OPEN => 'Cannot open file',
            \ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
            \ZipArchive::ER_ZLIB => 'Zlib error',
            \ZipArchive::ER_MEMORY => 'Memory allocation failure',
            \ZipArchive::ER_CHANGED => 'Entry has been changed',
            \ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
            \ZipArchive::ER_EOF => 'Premature EOF',
            \ZipArchive::ER_INVAL => 'Invalid argument',
            \ZipArchive::ER_NOZIP => 'Not a zip archive',
            \ZipArchive::ER_INTERNAL => 'Internal error',
            \ZipArchive::ER_INCONS => 'Zip archive inconsistent',
            \ZipArchive::ER_REMOVE => 'Cannot remove file',
            \ZipArchive::ER_DELETED => 'Entry has been deleted',
        ];

        return $errors[$code] ?? 'Unknown error code: ' . $code;
    }

    private function getFiles(\ZipArchive $zip, $filter)
    {
        $total = $zip->numFiles;
        for ($i = 0; $i < $total; ++$i) {
            $name = $zip->getNameIndex($i);
            if (false === $name) {
                continue;
            }

            if (!$filter || $filter($name)) {
                yield [
                    'filename' => $name,
                    'content' => $zip->getFromIndex($i),
                ];
            }
        }
    }
}
