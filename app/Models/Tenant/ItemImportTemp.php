<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Builder;

class ItemImportTemp extends ModelTenant
{
    protected $table = 'items_import_temp';

    protected $fillable = [
        'user_id',
        'batch_id',
        'warehouse_id',
        'row_number',
        'row_data',
        'is_valid',
        'validation_errors',
        'status',
        'item_id',
        'process_error',
        'action',
        'preview_data'
    ];

    protected $casts = [
        'row_data' => 'array',
        'validation_errors' => 'array',
        'preview_data' => 'array',
        'is_valid' => 'boolean',
    ];

    /**
     * Get the user that uploaded the file
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the item (if processed)
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Scope for pending records
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for valid records
     */
    public function scopeValid(Builder $query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope for a specific batch
     */
    public function scopeBatch(Builder $query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Get validation errors as string
     */
    public function getErrorsStringAttribute()
    {
        if (!$this->validation_errors || !is_array($this->validation_errors)) {
            return '';
        }

        return implode(', ', $this->validation_errors);
    }

    /**
     * Get formatted preview data
     */
    public function getFormattedPreviewAttribute()
    {
        if (!$this->preview_data) {
            return null;
        }

        $preview = $this->preview_data;

        // Format prices
        if (isset($preview['sale_unit_price'])) {
            $preview['sale_unit_price'] = number_format($preview['sale_unit_price'], 2);
        }
        if (isset($preview['purchase_unit_price'])) {
            $preview['purchase_unit_price'] = number_format($preview['purchase_unit_price'], 2);
        }

        return $preview;
    }

    /**
     * Check if this is a new item
     */
    public function getIsNewAttribute()
    {
        return $this->action === 'create';
    }

    /**
     * Check if this is an update
     */
    public function getIsUpdateAttribute()
    {
        return $this->action === 'update';
    }

    /**
     * Get stats for a batch
     */
    public static function getBatchStats($batchId)
    {
        $records = self::where('batch_id', $batchId)->get();

        return [
            'total' => $records->count(),
            'valid' => $records->where('is_valid', true)->count(),
            'invalid' => $records->where('is_valid', false)->count(),
            'new_items' => $records->where('action', 'create')->count(),
            'update_items' => $records->where('action', 'update')->count(),
            'processed' => $records->where('status', 'processed')->count(),
            'pending' => $records->where('status', 'pending')->count(),
            'error' => $records->where('status', 'error')->count(),
        ];
    }

    /**
     * Delete all records for a batch
     */
    public static function deleteBatch($batchId)
    {
        return self::where('batch_id', $batchId)->delete();
    }
}