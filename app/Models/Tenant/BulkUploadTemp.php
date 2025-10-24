<?php

namespace App\Models\Tenant;

class BulkUploadTemp extends ModelTenant
{
    protected $table = 'bulk_upload_temp';

    protected $fillable = [
        'user_id',
        'type',
        'batch_id',
        'date_of_issue',
        'row_data',
        'is_valid',
        'validation_errors',
        'status',
        'document_id',
        'process_error',
    ];

    protected $casts = [
        'row_data' => 'array',
        'is_valid' => 'boolean',
        'date_of_issue' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Scope para obtener registros de un batch específico
     */
    public function scopeOfBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope para obtener solo registros válidos
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope para obtener solo registros pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
