<?php

namespace App\CoreFacturalo\WS\Services;

use App\CoreFacturalo\WS\Response\BillResult;
use Illuminate\Support\Facades\Log;

/**
 * Class BillSender.
 */
class BillSender extends BaseSunat
{
    /**
     * @param string $filename
     * @param string $content
     *
     * @return mixed
     */
    public function send($filename, $content)
    {
        $client = $this->getClient();
        $result = new BillResult();

        // LOGGING: Inicio del envío a SUNAT
        Log::info('BillSender: Iniciando envío a SUNAT', [
            'filename' => $filename,
            'xml_size' => strlen($content)
        ]);

        try {
            $zipContent = $this->compress($filename.'.xml', $content);
            $params = [
                'fileName' => $filename.'.zip',
                'contentFile' => $zipContent,
            ];

            Log::debug('BillSender: Llamando SOAP sendBill', [
                'filename' => $filename.'.zip',
                'zip_size' => strlen($zipContent)
            ]);

            $response = $client->call('sendBill', ['parameters' => $params]);

            // LOGGING: Respuesta completa de SUNAT
            Log::info('BillSender: Respuesta recibida de SUNAT', [
                'filename' => $filename,
                'response_type' => gettype($response),
                'has_applicationResponse' => isset($response->applicationResponse),
                'response_properties' => is_object($response) ? get_object_vars($response) : null
            ]);

            $cdrZip = $response->applicationResponse;

            // LOGGING: CDR ZIP recibido de SUNAT (antes de descomprimir)
            Log::info('BillSender: CDR ZIP recibido de SUNAT', [
                'filename' => $filename,
                'cdr_zip_type' => gettype($cdrZip),
                'cdr_zip_size' => is_string($cdrZip) ? strlen($cdrZip) : 'not a string',
                'cdr_zip_is_empty' => empty($cdrZip),
                'cdr_zip_first_bytes' => is_string($cdrZip) && strlen($cdrZip) > 0 ? bin2hex(substr($cdrZip, 0, 20)) : 'N/A'
            ]);

            $result
                ->setCdrResponse($this->extractResponse($cdrZip))
                ->setCdrZip($cdrZip)
                ->setSuccess(true);

            Log::info('BillSender: Envío exitoso', ['filename' => $filename]);

        } catch (\SoapFault $e) {
            Log::error('BillSender: SoapFault capturado', [
                'filename' => $filename,
                'faultcode' => $e->faultcode,
                'faultstring' => $e->faultstring,
                'detail' => isset($e->detail) ? (string)$e->detail : null
            ]);

            $result->setError($this->getErrorFromFault($e));
        } catch (\Exception $e) {
            Log::error('BillSender: Excepción general capturada', [
                'filename' => $filename,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }

        return $result;
    }
}
