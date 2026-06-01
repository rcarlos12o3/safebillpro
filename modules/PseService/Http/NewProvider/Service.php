<?php

namespace Modules\PseService\Http\NewProvider;

use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Exception;

final class Service
{
    private Company $company;
    private Client $http;

    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->http = new Client(['verify' => false]);
    }

    private function baseUrl(): string
    {
        return rtrim($this->company->url_login_pse, '/');
    }

    private function getToken(): string
    {
        // Personal access token — se configura una vez, se usa directo como Bearer.
        $token = $this->company->password_pse;

        if (empty($token)) {
            throw new Exception('PSE NuevoProveedor - Token no configurado. Ingresa el token en la configuración PSE.');
        }

        return $token;
    }

    /**
     * Orquesta: crear documento JSON → enviar a SUNAT → polling si queda pendiente.
     * Devuelve la misma estructura que GiorService para compatibilidad con Facturalo.
     */
    public function processDocument(Document $document, string $facturaloType): array
    {
        $token = $this->getToken();

        Log::info('PSE NuevoProveedor - processDocument', [
            'type'     => $facturaloType,
            'doc_type' => $document->document_type_id,
            'filename' => $document->filename,
        ]);

        $endpoints = $this->resolveEndpoints($facturaloType);
        $payload   = $this->buildPayload($document, $facturaloType);

        $id     = $this->createDocument($payload, $endpoints['create'], $token);
        $result = $this->sendToSunat($id, $endpoints['send'], $token);

        // CDR pendiente por ticket
        if (!empty($result['ticket']) && !empty($endpoints['poll'])) {
            $result = $this->pollCdrById($id, $endpoints['poll'], $token);
        } elseif (!empty($result['ticket'])) {
            // Factura/boleta: CDR via endpoint genérico
            $result = $this->pollDocumentCdr($document, $token);
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Endpoints
    // -------------------------------------------------------------------------

    private function resolveEndpoints(string $facturaloType): array
    {
        $base = $this->baseUrl();

        switch ($facturaloType) {
            case 'credit':
            case 'debit':
                return [
                    'create' => "{$base}/api/v2/electronic-note",
                    'send'   => "{$base}/api/v2/electronic-note/send",
                    'poll'   => null,
                ];
            case 'voided':
                return [
                    'create' => "{$base}/api/v1/voided",
                    'send'   => "{$base}/api/v1/voided/send",
                    'poll'   => "{$base}/api/v1/voided/ask",
                ];
            case 'summary':
                return [
                    'create' => "{$base}/api/v1/summary",
                    'send'   => "{$base}/api/v1/summary/send",
                    'poll'   => "{$base}/api/v1/summary/ask",
                ];
            case 'dispatch':
                return [
                    'create' => "{$base}/api/v2/despatch",
                    'send'   => "{$base}/api/v2/despatch/send",
                    'poll'   => "{$base}/api/v2/despatch/consult",
                ];
            default: // invoice, boleta
                return [
                    'create' => "{$base}/api/v2/invoice",
                    'send'   => "{$base}/api/v2/invoice/send",
                    'poll'   => null, // usa pollDocumentCdr
                ];
        }
    }

    // -------------------------------------------------------------------------
    // Payload builders
    // -------------------------------------------------------------------------

    private function buildPayload(Document $document, string $facturaloType): array
    {
        switch ($facturaloType) {
            case 'credit':
            case 'debit':
                return $this->buildNotePayload($document);
            default:
                return $this->buildInvoicePayload($document);
        }
    }

    private function buildInvoicePayload(Document $document): array
    {
        $customer = $document->customer;

        $payload = [
            'idTransaccionRequest' => $document->series . '-' . $document->number,
            'versionUBL'           => $document->ubl_version ?? '2.1',
            'tipoOperacion'        => '0101',
            'tipoDocumento'        => $document->document_type_id,
            'serie'                => $document->series,
            'correlativo'          => (int) $document->number,
            'fechaEmision'         => $document->date_of_issue->format('Y-m-d'),
            'tipoMoneda'           => $document->currency_type_id,
            'totalOGravadas'       => (float) ($document->total_taxed ?? 0),
            'totalOExoneradas'     => (float) ($document->total_exonerated ?? 0),
            'totalOInafectas'      => (float) ($document->total_unaffected ?? 0),
            'totalIGV'             => (float) ($document->total_igv ?? 0),
            'totalImpuestos'       => (float) ($document->total_taxes ?? 0),
            'totalValorVenta'      => (float) ($document->total_value ?? 0),
            'totalVenta'           => (float) ($document->total ?? 0),
            'cliente'              => $this->buildCliente($customer),
            'detallesInvoice'      => $this->buildItems($document),
            'plataforma'           => ['codigoPlataforma' => $this->company->user_pse ?? ''],
        ];

        return $payload;
    }

    private function buildNotePayload(Document $document): array
    {
        $payload = $this->buildInvoicePayload($document);
        unset($payload['tipoOperacion']);

        $note = $document->note;
        if ($note) {
            $payload['tipoNota']   = $note->note_type_id ?? null;
            $payload['motivoNota'] = $note->note_concept_id ?? null;
            $payload['documentoAfectado'] = [
                'tipoDoc'     => $note->affected_document_type_id ?? null,
                'serie'       => $note->affected_document_series ?? null,
                'correlativo' => $note->affected_document_number ? (int) $note->affected_document_number : null,
            ];
        }

        return $payload;
    }

    private function buildCliente(object $customer): array
    {
        $cliente = [
            'tipoDoc'     => $customer->identity_document_type_id ?? '-',
            'numDoc'      => $customer->number ?? '',
            'razonSocial' => $customer->name ?? '',
        ];

        if (!empty($customer->address)) {
            $cliente['address'] = ['direccion' => $customer->address];
        }

        return $cliente;
    }

    private function buildItems(Document $document): array
    {
        return $document->items->map(function ($docItem) {
            $item = $docItem->item;
            return [
                'codProducto'       => $item->internal_id ?? $item->code ?? null,
                'unidad'            => $item->unit_type_id ?? 'NIU',
                'cantidad'          => (float) $docItem->quantity,
                'descripcion'       => $item->description ?? $item->name ?? '',
                'mtoValorUnitario'  => (float) $docItem->unit_value,
                'mtoBaseIgv'        => (float) $docItem->total_base_igv,
                'porcentajeIgv'     => (float) $docItem->percentage_igv,
                'igv'               => (float) $docItem->total_igv,
                'tipAfeIgv'         => $docItem->affectation_igv_type_id ?? '10',
                'totalImpuestos'    => (float) $docItem->total_taxes,
                'mtoValorVenta'     => (float) $docItem->total_value,
                'mtoPrecioUnitario' => (float) $docItem->unit_price,
                'total'             => (float) $docItem->total,
            ];
        })->toArray();
    }

    // -------------------------------------------------------------------------
    // HTTP calls
    // -------------------------------------------------------------------------

    private function createDocument(array $payload, string $url, string $token): int
    {
        $response = $this->http->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ]);

        $status = $response->getStatusCode();
        $data   = json_decode($response->getBody(), true);

        Log::info('PSE NuevoProveedor - createDocument', ['status' => $status, 'response' => $data]);

        if (($status !== 200 && $status !== 201) || empty($data['id'])) {
            throw new Exception('PSE NuevoProveedor - Create error. Status: ' . $status . '. ' . json_encode($data));
        }

        return (int) $data['id'];
    }

    private function sendToSunat(int $id, string $url, string $token): array
    {
        $response = $this->http->post("{$url}/{$id}", [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);

        $status = $response->getStatusCode();
        $data   = json_decode($response->getBody(), true);

        Log::info('PSE NuevoProveedor - sendToSunat', ['status' => $status, 'response' => $data]);

        if ($status !== 200) {
            return [
                'success' => false,
                'code'    => $data['code'] ?? (string) $status,
                'message' => $data['message'] ?? 'Error al enviar documento al proveedor PSE',
                'errors'  => $data,
            ];
        }

        // CDR pendiente por ticket
        if (isset($data['ticket']) && !isset($data['cdrCode'])) {
            return [
                'success' => true,
                'code'    => $data['sendCode'] ?? '0',
                'ticket'  => $data['ticket'],
                'message' => $data['sendDescription'] ?? 'Enviado - CDR pendiente',
                'cdr'     => null,
                'rejected'=> false,
                'errors'  => null,
            ];
        }

        return $this->parseResponse($data);
    }

    /** Polling para facturas/boletas: POST /api/v2/document/cdr */
    private function pollDocumentCdr(Document $document, string $token, int $maxAttempts = 5): array
    {
        $url  = $this->baseUrl() . '/api/v2/document/cdr';
        $body = [
            'serie'          => $document->series,
            'correlativo'    => (string) $document->number,
            'tipoDocumento'  => $document->document_type_id,
            'fechaEmision'   => $document->date_of_issue->format('Y-m-d'),
        ];

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep(10);

            $response = $this->http->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $body,
            ]);

            $status = $response->getStatusCode();
            $data   = json_decode($response->getBody(), true);

            Log::info("PSE NuevoProveedor - pollDocumentCdr attempt {$attempt}", [
                'status' => $status, 'response' => $data,
            ]);

            if ($status === 200 && isset($data['cdrCode'])) {
                return $this->parseResponse($data);
            }
        }

        return [
            'success' => false,
            'code'    => 'TIMEOUT',
            'message' => "CDR no disponible después de {$maxAttempts} intentos",
            'errors'  => null,
        ];
    }

    /** Polling para voided/summary/despatch: POST /api/v1/{type}/ask/{id} */
    private function pollCdrById(int $id, string $pollUrl, string $token, int $maxAttempts = 5): array
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep(10);

            $response = $this->http->post("{$pollUrl}/{$id}", [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);

            $status = $response->getStatusCode();
            $data   = json_decode($response->getBody(), true);

            Log::info("PSE NuevoProveedor - pollCdrById attempt {$attempt}", [
                'status' => $status, 'response' => $data,
            ]);

            if ($status === 200 && isset($data['cdrCode'])) {
                return $this->parseResponse($data);
            }
        }

        return [
            'success' => false,
            'code'    => 'TIMEOUT',
            'message' => "CDR no disponible después de {$maxAttempts} intentos",
            'errors'  => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Response parser
    // -------------------------------------------------------------------------

    private function parseResponse(array $data): array
    {
        $cdrCode  = $data['cdrCode'] ?? null;
        $rejected = ($cdrCode !== null && $cdrCode !== '0');

        return [
            'success'   => true,
            'code'      => $cdrCode,
            'send_code' => $data['sendCode'] ?? null,
            'message'   => $data['cdrDescription'] ?? $data['sendDescription'] ?? '',
            'cdr_url'   => $data['cdrPath'] ?? null,
            'xml_url'   => $data['xmlPath'] ?? null,
            'rejected'  => $rejected,
            'errors'    => $rejected ? ($data['cdrDescription'] ?? null) : null,
        ];
    }
}
