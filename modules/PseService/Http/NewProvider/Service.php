<?php

namespace Modules\PseService\Http\NewProvider;

use App\Models\Tenant\Company;
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
        $this->http = new Client(['verify' => false, 'http_errors' => false]);
    }

    private function baseUrl(): string
    {
        return rtrim($this->company->url_login_pse, '/');
    }

    private function getToken(): string
    {
        $token = $this->company->password_pse;

        if (empty($token)) {
            throw new Exception('PSE NuevoProveedor - Token no configurado. Ingresa el token en la configuración PSE.');
        }

        return $token;
    }

    /**
     * Orquesta: crear documento JSON → enviar a SUNAT → polling si aplica.
     * Para summaries y voided es siempre asíncrono — se devuelve el ticket sin polling.
     *
     * @param  mixed  $document  Puede ser Document, Summary u otro modelo de Facturalo
     * @param  string $facturaloType  'invoice'|'credit'|'debit'|'summary'|'voided'|'dispatch'
     */
    public function processDocument($document, string $facturaloType): array
    {
        $token = $this->getToken();

        Log::info('PSE NuevoProveedor - processDocument', [
            'type'     => $facturaloType,
            'filename' => $document->filename ?? null,
        ]);

        $endpoints = $this->resolveEndpoints($facturaloType);
        $payload   = $this->buildPayload($document, $facturaloType);

        $id     = $this->createDocument($payload, $endpoints['create'], $token);
        $result = $this->sendToSunat($id, $endpoints['send'], $token);

        // Summaries/voided son siempre asíncronos — guardar create id y ticket por separado.
        // ask/{create_id} + ticket en body.
        if (!empty($endpoints['async'])) {
            $result['provider_doc_id'] = $id; // create id (usado en ask/{id})
            return $result;
        }

        // Para invoices/notas: polling si CDR quedó pendiente
        if (!empty($result['ticket'])) {
            $result = $this->pollDocumentCdr($document, $token);
        }

        return $result;
    }

    /**
     * Consulta el CDR de un summary/voided por su ID de proveedor + ticket.
     * Llamado desde Facturalo::pseQuerySummary() para el nuevo proveedor.
     */
    public function querySummaryById(int $providerDocId, string $facturaloType, ?string $ticket = null): array
    {
        $token     = $this->getToken();
        $endpoints = $this->resolveEndpoints($facturaloType);

        return $this->pollCdrById($providerDocId, $endpoints['poll'], $token, 5, $ticket);
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
                    'async'  => false,
                ];
            case 'voided':
                return [
                    'create' => "{$base}/api/v2/voided",
                    'send'   => "{$base}/api/v2/voided/send",
                    'poll'   => "{$base}/api/v2/voided/ask",
                    'async'  => true,
                ];
            case 'summary':
                return [
                    'create' => "{$base}/api/v2/summary",
                    'send'   => "{$base}/api/v2/summary/send",
                    'poll'   => "{$base}/api/v2/summary/ask",
                    'async'  => true,
                ];
            case 'dispatch':
                return [
                    'create' => "{$base}/api/v2/despatch",
                    'send'   => "{$base}/api/v2/despatch/send",
                    'poll'   => "{$base}/api/v2/despatch/consult",
                    'async'  => false,
                ];
            default: // invoice, boleta
                return [
                    'create' => "{$base}/api/v2/invoice",
                    'send'   => "{$base}/api/v2/invoice/send",
                    'poll'   => null,
                    'async'  => false,
                ];
        }
    }

    // -------------------------------------------------------------------------
    // Payload builders
    // -------------------------------------------------------------------------

    private function buildPayload($document, string $facturaloType): array
    {
        switch ($facturaloType) {
            case 'credit':
            case 'debit':
                return $this->buildNotePayload($document);
            case 'summary':
                return $this->buildSummaryPayload($document);
            case 'voided':
                return $this->buildVoidedPayload($document);
            default:
                return $this->buildInvoicePayload($document);
        }
    }

    private function buildInvoicePayload($document): array
    {
        return [
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
            'totalIcbper'          => (float) ($document->total_plastic_bag_taxes ?? 0),
            'totalImpuestos'       => (float) ($document->total_taxes ?? 0),
            'totalValorVenta'      => (float) ($document->total_value ?? 0),
            'totalVenta'           => (float) ($document->total ?? 0),
            'cliente'              => $this->buildCliente($document->customer),
            'detallesInvoice'      => $this->buildItems($document),
            'plataforma'           => ['codigoPlataforma' => 'SAFEBILLPRO'],
        ];
    }

    private function buildNotePayload($document): array
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

    private function buildSummaryPayload($summary): array
    {
        // Extraer el correlativo del identifier (ej: "RC-20260601-1" → 1)
        $parts      = explode('-', $summary->identifier ?? '');
        $correlativo = (int) end($parts);

        return [
            'idTransaccionRequest' => $summary->identifier ?? $summary->filename,
            'versionUBL'           => $summary->ubl_version ?? '2.0',
            'correlativo'          => $correlativo,
            'fechaGeneracion'      => \Carbon\Carbon::now()->format('Y-m-d'),
            'fechaResumen'         => \Carbon\Carbon::now()->format('Y-m-d'),
            'plataforma'           => ['codigoPlataforma' => 'SAFEBILLPRO'],
            'detallesSummary'      => $this->buildSummaryItems($summary),
        ];
    }

    private function buildSummaryItems($summary): array
    {
        return $summary->documents->map(function ($summaryDoc) {
            $doc = $summaryDoc->document;
            if (!$doc) return null;

            $customer  = $doc->customer;
            $tipoMap   = ['1' => '1', '4' => '4', '6' => '6', '7' => '7', 'A' => '0'];
            $tipoCliente = $tipoMap[$customer->identity_document_type_id ?? '-'] ?? '0';
            $item = [
                'serie'           => $doc->series,
                'correlativo'     => (int) $doc->number,
                'tipoDocumento'   => $doc->document_type_id,
                'tipoCliente'     => $tipoCliente,
                'clienteNro'      => $customer->number ?? '',
                'estado'          => '1',
                'totalOGravadas'  => (float) ($doc->total_taxed ?? 0),
                'totalOExoneradas'=> (float) ($doc->total_exonerated ?? 0),
                'totalOInafectas' => (float) ($doc->total_unaffected ?? 0),
                'totalIcbper'     => (float) ($doc->total_plastic_bag_taxes ?? 0),
                'totalImpuestos'  => (float) ($doc->total_taxes ?? 0),
                'totalDescuentos' => (float) ($doc->total_discount ?? 0),
                'totalValorVenta' => (float) ($doc->total_value ?? 0),
                'totalVenta'      => (float) ($doc->total ?? 0),
            ];

            // Nota crédito/débito: incluir referencia al documento afectado
            if (in_array($doc->document_type_id, ['07', '08']) && $doc->note) {
                $note = $doc->note;
                $item['tipoDocumentoReferencia'] = $note->affected_document_type_id ?? null;
                $item['documentoReferencia']     = ($note->affected_document_series ?? '')
                                                 . '-' . ($note->affected_document_number ?? '');
            }

            return $item;
        })->filter()->values()->toArray();
    }

    private function buildVoidedPayload($voided): array
    {
        $parts       = explode('-', $voided->identifier ?? '');
        $correlativo = (int) end($parts);

        return [
            'idTransaccionRequest' => $voided->identifier ?? $voided->filename,
            'versionUBL'           => '2.0',
            'correlativo'          => $correlativo,
            'identificador'        => 'RA',
            'fechaGeneracion'      => \Carbon\Carbon::now()->format('Y-m-d'),
            'fechaComunicacion'    => \Carbon\Carbon::now()->format('Y-m-d'),
            'plataforma'           => ['codigoPlataforma' => 'SAFEBILLPRO'],
            'detallesVoided'       => $this->buildVoidedItems($voided),
        ];
    }

    private function buildVoidedItems($voided): array
    {
        return $voided->documents->map(function ($voidedDoc) {
            $doc = $voidedDoc->document;
            if (!$doc) return null;

            return [
                'tipoDocumento' => $doc->document_type_id,
                'serie'         => $doc->series,
                'correlativo'   => (int) $doc->number,
                'motivoBaja'    => $voidedDoc->description ?? 'Error en el documento',
            ];
        })->filter()->values()->toArray();
    }

    private function buildCliente($customer): array
    {
        // Mapear tipo SUNAT → código que acepta el proveedor
        $tipoMap = ['1' => '1', '4' => '4', '6' => '6', '7' => '7', 'A' => '0'];
        $rawTipo  = $customer->identity_document_type_id ?? '-';
        $tipo     = $tipoMap[$rawTipo] ?? '0';

        $cliente = [
            'tipoDocumento'   => $tipo,
            'numeroDocumento' => $customer->number ?? '',
        ];

        if ($tipo === '1') {
            // DNI: el proveedor requiere nombres y apellidos por separado
            $parts              = explode(' ', trim($customer->name ?? ''), 2);
            $cliente['nombres']   = $parts[0] ?? '';
            $cliente['apellidos'] = $parts[1] ?? '';
        } else {
            $cliente['razonSocial'] = $customer->name ?? '';
        }

        if (!empty($customer->address)) {
            $cliente['direccion'] = $customer->address;
        }

        return $cliente;
    }

    private function buildItems($document): array
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

        // CDR pendiente por ticket (normal en facturas y obligatorio en summaries)
        if (isset($data['ticket']) && !isset($data['cdrCode'])) {
            return [
                'success'     => true,
                'code'        => $data['sendCode'] ?? '0',
                'ticket'      => $data['ticket'],
                'send_doc_id' => $data['id'] ?? null, // ID del registro de envío, usado en ask/{id}
                'message'     => $data['sendDescription'] ?? 'Enviado - CDR pendiente',
                'cdr'         => null,
                'rejected'    => false,
                'errors'      => null,
            ];
        }

        return $this->parseResponse($data);
    }

    /** Polling para facturas/boletas: POST /api/v2/document/cdr */
    private function pollDocumentCdr($document, string $token, int $maxAttempts = 5): array
    {
        $url  = $this->baseUrl() . '/api/v2/document/cdr';
        $body = [
            'serie'         => $document->series,
            'correlativo'   => (string) $document->number,
            'tipoDocumento' => $document->document_type_id,
            'fechaEmision'  => $document->date_of_issue->format('Y-m-d'),
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

    /** Polling por ID de proveedor: POST /api/v2/summary/ask/{id} con ticket en body */
    public function pollCdrById(int $id, string $pollUrl, string $token, int $maxAttempts = 5, ?string $ticket = null): array
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            sleep(15);

            $options = ['headers' => ['Authorization' => 'Bearer ' . $token]];
            if ($ticket) {
                $options['json'] = ['ticket' => $ticket];
            }

            $response = $this->http->post("{$pollUrl}/{$id}", $options);

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
