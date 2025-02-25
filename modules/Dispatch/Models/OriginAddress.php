<?php

namespace Modules\Dispatch\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Establishment;

class OriginAddress extends ModelTenant
{
    protected $fillable = [
        'address',
        'location_id',
        'is_default',
        'is_active',
        'establishment_id',
        'establishment_code',
        'country_id',
    ];

    protected $casts = [
        'location_id' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}
